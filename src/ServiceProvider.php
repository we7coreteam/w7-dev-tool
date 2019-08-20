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

namespace W7\DevTool;

use W7\Core\Provider\ProviderAbstract;
use W7\DevTool\Command\Config\ListCommand as ConfigListCommand;
use W7\DevTool\Command\Make\ControllerCommand;
use W7\DevTool\Command\Make\HandlerCommand;
use W7\DevTool\Command\Make\ProviderCommand;
use W7\DevTool\Command\Route\ListCommand as RouteListCommand;
use W7\DevTool\Command\Vendor\MakeCommand;
use W7\DevTool\Command\Vendor\PublishCommand;

class ServiceProvider extends ProviderAbstract {
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register() {
		$this->registerCommand('config:list', ConfigListCommand::class);
		$this->registerCommand('route:list', RouteListCommand::class);
		$this->registerCommand('vendor:make', MakeCommand::class);
		$this->registerCommand('vendor:publish', PublishCommand::class);
		$this->registerCommand('make:provider', ProviderCommand::class);
		$this->registerCommand('make:handler', HandlerCommand::class);
		$this->registerCommand('make:controller', ControllerCommand::class);
	}

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot() {
	}
}
