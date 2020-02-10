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

class CommandCommand extends GeneratorCommandAbstract {
	protected $description = 'generate command';
	protected $typeSuffix = 'command';

	protected function getStub() {
		return dirname(__DIR__, 1) . '/Stubs/Command.stub';
	}

	protected function savePath() {
		return 'app/Command/';
	}
}
