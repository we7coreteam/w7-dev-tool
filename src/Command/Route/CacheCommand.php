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

namespace W7\Command\Command\Route;

use W7\Console\Command\CommandAbstract;
use W7\Core\Exception\CommandException;
use W7\Core\Route\RouteDispatcher;
use W7\Core\Route\RouteMapping;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\SwooleServerAbstract;

class CacheCommand extends CommandAbstract {
	protected $description = 'create a route cache file for faster route registration';

	protected function configure() {
		$this->addOption('--force', '-f', null, 'force overwrite file');
	}

	protected function handle($options) {
		$routeCachedPath = RouteDispatcher::getCachedRoutePath();
		if (!file_exists($routeCachedPath)) {
			mkdir($routeCachedPath, 0777, true);
		}

		/**
		 * @var SwooleServerAbstract $server
		 */
		foreach (ServerEnum::$ALL_SERVER as $serverType => $server) {
			if ($server::$masterServer) {
				$cacheFile = $routeCachedPath . strtolower($serverType) . '.' . RouteDispatcher::$routeCacheFileName;
				if (file_exists($cacheFile)) {
					continue;
				}

				$this->makeRouteCacheByServerType($serverType, $cacheFile);
			}
		}

		$this->output->success('Routes cached successfully!');
	}

	protected function makeRouteCacheByServerType($serverType, $cacheFile) {
		$routes = $this->getRouteMappingByServerType($serverType)->getMapping();

		foreach ($routes[0] as $method => $route) {
			foreach ($route as $key => $item) {
				if ($item['handler'] instanceof \Closure) {
					throw new CommandException("Unable to prepare route [{$item['uri']}] for serialization. Uses Closure.");
				}
			}
		}

		foreach ($routes[1] as $method => $routeGroup) {
			foreach ($routeGroup as $route) {
				foreach ($route['routeMap'] as $item) {
					$item = $item[0];
					if ($item['handler'] instanceof \Closure) {
						throw new CommandException("Unable to prepare route [{$item['uri']}] for serialization. Uses Closure.");
					}
				}
			}
		}

		file_put_contents(
			$cacheFile,
			'<?php return ' . var_export($routes, true) . ';'
		);
	}

	protected function getRouteMappingByServerType($serverType) : RouteMapping {
		$routeMappingClass = '\W7\\' . ucfirst($serverType) . '\Route\RouteMapping';
		if (!class_exists($routeMappingClass)) {
			$routeMappingClass = RouteMapping::class;
		}
		return new $routeMappingClass();
	}
}
