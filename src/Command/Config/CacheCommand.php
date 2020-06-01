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

use W7\App;
use W7\Console\Command\CommandAbstract;
use W7\Core\Provider\ProviderAbstract;

class CacheCommand extends CommandAbstract {
	protected $description = 'create config cache file';

	protected function handle($options) {
		$this->call('config:clear');

		$configCachedPath = App::getApp()->getConfigCachePath();
		if (!file_exists($configCachedPath)) {
			mkdir($configCachedPath, 0777, true);
		}

		$configFileTree = glob(BASE_PATH . '/config/*.php');
		if (empty($configFileTree)) {
			$this->output->note('nothing to cache');
			return false;
		}

		foreach ($configFileTree as $path) {
			$key = pathinfo($path, PATHINFO_FILENAME);

			file_put_contents(
				$configCachedPath . $key . '.php',
				'<?php return ' . var_export(iconfig()->getUserConfig($key), true) . ';'
			);
		}

		$this->cacheProviderConfig();

		$this->output->success('Config cached successfully!');
	}

	private function cacheProviderConfig() {
		$providerConfigFile = iconfig()->getBuiltInConfigPath() . '/provider.php';
		$config = include $providerConfigFile;
		$config['providers'] = $config['providers'] ?? [];

		$deferredProviders = [];
		foreach ($config['providers'] as $name => $providers) {
			foreach ($providers as $index => $provider) {
				/**
				 * @var ProviderAbstract $providerObj
				 */
				$providerObj = new $provider($name);
				$dependServices = $providerObj->providers();
				if ($dependServices) {
					foreach ($dependServices as $dependService) {
						$deferredProviders[$dependService] = $provider;
					}
					unset($providers[$index]);
				}
			}

			if (!$providers) {
				unset($config['providers'][$name]);
			}
		}

		$config['deferred'] = $deferredProviders;

		file_put_contents(
			$providerConfigFile,
			'<?php return ' . var_export($config, true) . ';'
		);
	}
}
