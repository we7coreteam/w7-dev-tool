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
		$this->clearProviderConfigCache();

		$this->output->success('Config cache cleared!');
	}

	private function clearProviderConfigCache() {
		$providerConfigFile = iconfig()->getBuiltInConfigPath() . '/provider.php';
		$config = include $providerConfigFile;
		$config['providers'] = $config['providers'] ?? [];
		$config['deferred'] = $config['deferred'] ?? [];

		$deferredProviders = [];
		foreach ($config['deferred'] as $name => $providers) {
			$deferredProviders = array_merge($deferredProviders, $providers);
		}
		foreach ($deferredProviders as $provider) {
			$config['providers'][$provider] = [$provider];
		}
		$config['deferred'] = [];

		file_put_contents(
			$providerConfigFile,
			'<?php return ' . var_export($config, true) . ';'
		);
	}
}
