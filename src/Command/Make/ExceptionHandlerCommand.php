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

class ExceptionHandlerCommand extends GeneratorCommandAbstract {
	protected $description = 'generate exception handler';

	protected function before() {
		$this->name = ucfirst($this->name) . 'Handler';
	}

	protected function handle($options) {
		$options['name'] = 'Exception';
		parent::handle($options);
	}

	protected function getStub() {
		return dirname(__DIR__, 1) . '/Stubs/ExceptionHandler.stub';
	}

	protected function replaceStub() {
	}

	protected function savePath() {
		return 'app/Handler/Exception';
	}
}
