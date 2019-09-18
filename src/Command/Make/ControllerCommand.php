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

class ControllerCommand extends GeneratorCommandAbstract {
	protected $description = 'generate controller';
	private $path;
	private $route;

	protected function before() {
		$this->route = '/' . $this->name;
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
		$namespace = empty($this->path) ? '' : '\\' . str_replace('/', '\\', $this->path);
		$this->replace('{{ DummyNamespace }}', 'W7\App\Controller' . $namespace, $stubFile);
		$this->replace('{{ DummyClass }}', $this->name, $stubFile);
	}

	protected function savePath() {
		return 'app/Controller/' . $this->path;
	}

	protected function after() {
		$this->addRoute();
	}

	private function addRoute() {
		$namespace = '\W7\App\Controller\\' . (empty($this->path) ? '' : str_replace('/', '\\', $this->path) . '\\');

		$route = "irouter()->get('" . $this->route . "', ['" . $namespace . $this->name . "', 'index']);";

		$group = !empty($this->path) ? explode('/', $this->path)[0] : 'common';
		$path = BASE_PATH . '/route/' . strtolower($group) . '.php';
		if (!file_exists($path)) {
			file_put_contents($path, '<?php 
' . $route);
		} else {
			$this->output->info('请复制 ' . $route . ' 到对应路由文件中');
		}
	}
}
