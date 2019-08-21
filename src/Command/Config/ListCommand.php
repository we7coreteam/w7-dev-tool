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

namespace W7\DevTool\Command\Config;

use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\CommandAbstract;
use W7\Core\Exception\CommandException;

class ListCommand extends CommandAbstract {
	protected $description = 'gets user configuration information';

	protected function configure() {
		$this->addOption('--search', '-s', InputOption::VALUE_REQUIRED, 'configuration to search for, for example:  app.database.default');
	}

	protected function handle($options) {
		if (empty($options['search'])) {
			throw new CommandException('the option search not be empty');
		}

		$search = $options['search'];
		$options = explode('.', $search);
		$config = iconfig()->getUserConfig($options[0]);
		array_shift($options);

		$config = $this->getData($options, $config);

		$this->output->writeList(['your ' . $search. ' config:' => $config]);
	}

	private function getData($options, $config) {
		foreach ($options as $item) {
			if (empty($config[$item])) {
				return [];
			}
			$config = $config[$item];
		}
		return $config;
	}
}
