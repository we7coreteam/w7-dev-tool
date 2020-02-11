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


use http\Exception\RuntimeException;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use W7\Core\Exception\CommandException;

class ModelCommand extends GeneratorCommandAbstract {
	protected $description = 'generate model';
	protected $typeSuffix = '';

	private $column = [];
	private $table = '';
	private $connection = 'default';

	protected function configure() {
		$this->addOption('--table', null, InputOption::VALUE_REQUIRED, 'table name');
		$this->addOption('--connection', null, InputOption::VALUE_OPTIONAL, 'table connection');
		parent::configure();
	}

	protected function handle($options) {
		$this->table = $this->input->getOption('table');
		$this->connection = $this->input->getOption('connection') ?: 'default';

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
		$tablePrefix = idb()->connection($connection)->getTablePrefix();
		if (!Str::startsWith($tableName, $tablePrefix)) {
			return $tablePrefix . $tableName;
		} else {
			return $tableName;
		}
	}

	private function getTableColumn($tableName, $connection = 'default') {
		$db = idb()->connection($connection);

		$dirver = [
			'mysql' => [
				'query' => [
					'sql' => "SELECT * FROM information_schema.columns WHERE table_schema = ? AND table_name = ?",
					'params' => [$db->getDatabaseName(), $this->getTableName($tableName)]
				],
				'field' => [
					'name' => 'COLUMN_NAME'
				],
				'primary' => [
					'name' => 'COLUMN_KEY',
					'value' => 'PRI'
				]
			],

			'sqlite' => [
				'query' => [
					'sql' => "PRAGMA TABLE_INFO('" . $this->getTableName($tableName) . "')",
					'params' => [],
				],
				'field' => [
					'name' => 'name'
				],
				'primary' => [
					'name' => 'pk',
					'value' => '1'
				]
			]
		];

		$currentDriver = $dirver[$db->getConfig('driver')];
		if (empty($currentDriver)) {
			throw new RuntimeException('This database is not supported');
		}
		$result = $db->select($currentDriver['query']['sql'], $currentDriver['query']['params']);

		if (empty($result)) {
			throw new RuntimeException('The table is empty');
		}

		$column = [];
		foreach ($result as $row) {
			if ($row->{$currentDriver['primary']['name']} == $currentDriver['primary']['value']) {
				$column['primaryKey'] = $row->{$currentDriver['field']['name']};
			} else {
				$column['field'][] = $row->{$currentDriver['field']['name']};
			}
		}
		return $column;
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
