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

class ControllerCommand extends GeneratorCommandAbstract {
	protected $description = 'generate controller';
	protected $typeSuffix = 'controller';

	protected function getStub() {
		return dirname(__DIR__, 1) . '/Stubs/Controller.stub';
	}

	protected function savePath() {
		return 'app/Controller/';
	}

	protected function after() {
		$this->addRoute();
	}

	private function addRoute() {
		$routeGroup = '\\';
		if (!empty($this->name['path'])) {
			$routeGroup = '\\' . $this->name['path'] . '\\';
		}
		$route = strtolower(str_replace('\\', '/', $routeGroup . substr($this->name['class'], 0, strlen($this->name['class']) - 10)));
		$route = "Router::get('" . $route . "', '{$this->name['namespace']}" . '\\' . "{$this->name['class']}@index');";
		$group = !empty($this->name['path']) ? explode("\\", $this->name['path'])[0] : 'common';
		$path = BASE_PATH . '/route/' . strtolower($group) . '.php';
		if (!file_exists($path)) {
			file_put_contents($path, '<?php 

use W7\Core\Facades\Router;

' . $route);
			$this->output->info('路由信息已生成在 /route/' . strtolower($group) . '.php 中');
		} else {
			$this->output->info('请使用 ' . $route . ' 注册控制器路由');
		}
	}
}
