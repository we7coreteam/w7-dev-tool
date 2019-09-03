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

use W7\Console\Command\GeneratorCommandAbstract;

class ExceptionCommand extends GeneratorCommandAbstract {
	protected $description = 'generate exception';

	protected function before() {
		$this->name = ucfirst($this->name) . 'Exception';
	}

	protected function getStub() {
		return dirname(__DIR__, 1) . '/Stubs/Exception.stub';
	}

	protected function replaceStub() {
		$stubFile = $this->name . '.stub';
		$this->replace('{{ DummyNamespace }}', 'W7\App\Exception\\' . $this->name, $stubFile);
		$this->replace('{{ DummyClass }}', $this->name, $stubFile);
	}

	protected function savePath() {
		return 'app/Exception';
	}
}
