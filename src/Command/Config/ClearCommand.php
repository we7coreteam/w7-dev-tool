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

namespace W7\Command\Command\Config;

use Illuminate\Filesystem\Filesystem;
use W7\App;
use W7\Console\Command\CommandAbstract;

class ClearCommand extends CommandAbstract {
	protected $description = 'remove the config cache file';

	protected function handle($options) {
		$filesystem = new Filesystem();
		$filesystem->deleteDirectory(App::getApp()->getConfigCachePath());

		$this->output->success('Config cache cleared!');
	}
}
