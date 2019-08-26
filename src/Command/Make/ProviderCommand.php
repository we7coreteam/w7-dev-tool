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

class ProviderCommand extends GeneratorCommandAbstract {
	protected $description = 'generate provider';

	protected function before() {
		$this->name = ucfirst($this->name) . 'Provider';
	}

	protected function getStub() {
		return dirname(__DIR__, 1) . '/Stubs/Provider.stub';
	}

	protected function replaceStub() {
		$stubFile = $this->name . '.stub';
		$this->replace('{{ DummyNamespace }}', 'W7\App\Provider', $stubFile);
		$this->replace('{{ DummyClass }}', $this->name, $stubFile);
	}

	protected function savePath() {
		return 'app/Provider';
	}
}
