<?php

/**
 * Rangine Dev Tool
 *
 * (c) We7Team 2019 <https://www.rangine.com>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com for more details
 */

namespace W7\DevTool\Command\Db;

use Illuminate\Database\Schema\MySqlBuilder;
use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\CommandAbstract;

abstract class DatabaseCommandAbstract extends CommandAbstract {
	protected $operate;
	/**
	 * @var MySqlBuilder
	 */
	protected $schema;

	protected function configure() {
		$this->addOption('connection', '-c', InputOption::VALUE_OPTIONAL, 'database channel', 'default');
	}

	protected function handle($options) {
		$options['connection'] = $options['connection'] ?? 'default';
		$this->schema = idb()->connection($options['connection'])->getSchemaBuilder();
		return $this->{$this->operate}($options);
	}
}
