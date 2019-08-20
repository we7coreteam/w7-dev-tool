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

namespace W7\DevTool\Command\Make;

use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\GeneratorCommandAbstract;
use W7\Core\Exception\CommandException;

class HandlerCommand extends GeneratorCommandAbstract {
	protected $description = 'generate handler';
	protected $type;

	protected function configure() {
		$this->addOption('--type', null, InputOption::VALUE_REQUIRED, 'handler type');
		parent::configure();
	}

	protected function before() {
		$this->name = ucfirst($this->name) . 'Handler';
		if (empty($this->input->getOption('type'))) {
			throw new CommandException("option type Can't be empty");
		}
		$this->type = $this->input->getOption('type');
		if (!in_array($this->type, ['session', 'log', 'cache'])) {
			throw new CommandException('not support the type');
		}
	}

	protected function getStub() {
		return dirname(__DIR__, 1) . '/Stubs/' . ucfirst($this->type) . 'Handler.stub';
	}

	protected function replaceStub() {
		$stubFile = $this->name . '.stub';
		$this->replace('{{ DummyNamespace }}', 'W7\App\Handler\\' . ucfirst($this->type) . '\\' . $this->name, $stubFile);
		$this->replace('{{ DummyClass }}', $this->name, $stubFile);
	}

	protected function savePath() {
		return 'app/Handler/' . ucfirst($this->type);
	}
}
