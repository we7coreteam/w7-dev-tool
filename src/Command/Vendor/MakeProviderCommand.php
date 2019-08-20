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

namespace W7\DevTool\Command\Vendor;

use W7\DevTool\Command\GeneratorCommandAbstract;

class MakeProviderCommand extends GeneratorCommandAbstract {
	protected $description = 'generate provider';

	protected function before() {
		$this->name = ucfirst($this->name);
		if ($this->filesystem->exists($this->rootPath() . $this->name . '.php')) {
			throw new \Exception('the provider ' . $this->name . ' is existed');
		}
	}

	protected function getStub() {
		return dirname(__DIR__, 1).'/Stubs/provider.stub';
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
