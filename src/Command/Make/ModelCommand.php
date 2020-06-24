<?php

/**
 * Rangine Command Tool
 *
 * (c) We7Team 2019 <https://www.rangine.com>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com for more details
 */

namespace W7\Command\Command\Make;

use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use W7\Core\Exception\CommandException;
use W7\Core\Facades\DB;

class ModelCommand extends GeneratorCommandAbstract {
	protected $description = 'generate model';
	protected $typeSuffix = '';

	private $column = [];
	private $table = '';
	private $connection = 'default';

	protected function configure() {
		$this->addOption('--table', null, InputOption::VALUE_REQUIRED, 'table name');
		$this->addOption('--connection', null, InputOption::VALUE_OPTIONAL, 'table connection', $this->connection);
		parent::configure();
	}

	protected function handle($options) {
		$this->table = $this->input->getOption('table');
		$this->connection = $this->input->getOption('connection');

		if (!empty($this->table)) {
			try {
				$this->column = $this->getTableColumn($this->table, $this->connection);
			} catch (\Exception $e) {
				throw new CommandException($e->getMessage());
			}
		} else {
			$this->column = [
				'primaryKey' => '',
				'field' => [],
			];
		}

		if (empty($options['name']) && !empty($this->table)) {
			$options['name'] = $this->parseTableNameToClassName($this->table);
		}
		parent::handle($options);
	}

	protected function replaceStub() {
		parent::replaceStub();

		$stubFile = $this->name['class'] . '.stub';

		$this->replace('{{ DummyTableName }}', $this->table, $stubFile);
		$this->replace('{{ DummyConnection }}', $this->connection, $stubFile);
		$this->replace('{{ DummyTableKey }}', $this->column['primaryKey'], $stubFile);
		$this->replace('{{ DummyFillAble }}', "'" .  implode("', '", $this->column['field']) . "'", $stubFile);
	}

	protected function getStub() {
		return dirname(__DIR__, 1) . '/Stubs/Model.stub';
	}

	protected function savePath() {
		return 'app/Model/Entity/';
	}

	private function getTableName($tableName, $connection = 'default') {
		$tablePrefix = DB::connection($connection)->getTablePrefix();
		if (!Str::startsWith($tableName, $tablePrefix)) {
			return $tablePrefix . $tableName;
		} else {
			return $tableName;
		}
	}

	private function getTableColumn($tableName, $connection = 'default') {
		$db = DB::connection($connection);
		$prefixTableName = $this->getTableName($tableName);
		if (!$db->getDoctrineSchemaManager()->tablesExist($prefixTableName)) {
			throw new \RuntimeException('table ' . $tableName . ' not exist');
		}

		$columnList = $db->getDoctrineSchemaManager()->listTableColumns($prefixTableName);
		$primaryKeys = $db->getDoctrineSchemaManager()->listTableDetails($prefixTableName)->getPrimaryKey()->getColumns();

		$columns = [];
		/**
		 * @var Column $row
		 */
		foreach ($columnList as $row) {
			if (in_array($row->getName(), $primaryKeys)) {
				$columns['primaryKey'] = $row->getName();
			} else {
				$columns['field'][] = $row->getName();
			}
		}

		return $columns;
	}

	private function parseTableNameToClassName($table) {
		$name = [];

		$tables = explode('_', $table);
		foreach ($tables as $row) {
			$name[] = ucfirst($row);
		}

		return implode("\\", $name);
	}
}
