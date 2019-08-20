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

class ControllerCommand extends GeneratorCommandAbstract {
	protected $description = 'generate controller';
	private $path;

	protected function before() {
		$path = explode('/', $this->name);
		foreach ($path as &$item) {
			$item = ucfirst($item);
		}
		$path[count($path) - 1] .= 'Controller';

		$this->name = end($path);
		array_pop($path);
		$this->path = implode('/', $path);
	}

	protected function getStub() {
		return dirname(__DIR__, 1) . '/Stubs/Controller.stub';
	}

	protected function replaceStub() {
		$stubFile = $this->name . '.stub';
		$namespace = empty($this->path) ? $this->name : str_replace('/', '\\', $this->path) . '\\' . $this->name;
		$this->replace('{{ DummyNamespace }}', 'W7\App\Controller\\' . $namespace, $stubFile);
		$this->replace('{{ DummyClass }}', $this->name, $stubFile);
	}

	protected function savePath() {
		return 'app/Controller/' . $this->path;
	}
}
