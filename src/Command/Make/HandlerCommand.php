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

use Symfony\Component\Console\Input\InputOption;
use W7\Core\Exception\CommandException;

class HandlerCommand extends GeneratorCommandAbstract {
	protected $description = 'generate handler';
	protected $type;
	protected $typeSuffix = 'handler';
	protected $supportType = ['session', 'log', 'cache', 'view', 'exception'];
	protected $ignoreNameOfType = ['exception'];

	protected function configure() {
		$this->addOption('--type', null, InputOption::VALUE_REQUIRED, 'handler type');
		parent::configure();
	}

	protected function handle($options) {
		if (empty($this->input->getOption('type'))) {
			throw new CommandException("option type Can't be empty");
		}
		$this->type = $this->input->getOption('type');
		if (!in_array($this->type, $this->supportType)) {
			throw new CommandException('not support the type');
		}
		if (in_array($this->type, $this->ignoreNameOfType)) {
			$options['name'] = $this->type;
		} else {
			$options['name'] = $this->input->getOption('name');
		}
		parent::handle($options);
	}

	protected function getStub() {
		return dirname(__DIR__, 1) . '/Stubs/' . ucfirst($this->type) . 'Handler.stub';
	}

	protected function savePath() {
		return 'app/Handler/' . ucfirst($this->type) . '/';
	}

	protected function after() {
		$this->composer->dumpAutoloads();
		parent::after();
	}
}
