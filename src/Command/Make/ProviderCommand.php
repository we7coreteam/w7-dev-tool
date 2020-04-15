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

class ProviderCommand extends GeneratorCommandAbstract {
	protected $description = 'generate provider';
	protected $typeSuffix = 'provider';

	protected function getStub() {
		return dirname(__DIR__, 1) . '/Stubs/Provider.stub';
	}

	protected function savePath() {
		return 'app/Provider/';
	}

	protected function after() {
		//触发包管理插件，自动更新provider配置文件
		$this->composer->dumpAutoloads();
		parent::after();
	}
}
