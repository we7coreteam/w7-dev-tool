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

use W7\Console\Command\GeneratorCommandAbstract;

class Command extends GeneratorCommandAbstract {
	protected $description = 'generate command';

	protected function before() {
		$this->name = ucfirst($this->name) . 'Command';
	}

	protected function getStub() {
		return dirname(__DIR__, 1) . '/Stubs/Command.stub';
	}

	protected function replaceStub() {
		$stubFile = $this->name . '.stub';
		$this->replace('{{ DummyNamespace }}', 'W7\App\Command', $stubFile);
		$this->replace('{{ DummyClass }}', $this->name, $stubFile);
	}

	protected function savePath() {
		return 'app/Command';
	}
}
