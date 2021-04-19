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
use W7\Core\Bootstrap\ProviderBootstrap;
use W7\Core\Config\Config;
use W7\Core\Config\Env\Env;
use W7\Core\Provider\ProviderAbstract;

class CacheCommand extends CommandAbstract {
	protected $description = 'create config cache file';

	protected function handle($options) {
		$this->call('config:clear');

		(new Env(App::getApp()->getBasePath()))->load();
		$payload = $this->loadConfigFile(App::getApp()->getBasePath() . '/config');
		$config = new Config($payload);

		$configCachedPath = App::getApp()->getConfigCachePath();
		if (!file_exists($configCachedPath)) {
			mkdir($configCachedPath, 0777, true);
		}

		$configFileTree = glob(App::getApp()->getBasePath() . '/config/*.php');
		if (empty($configFileTree)) {
			$this->output->note('nothing to cache');
			return false;
		}

		try {
			foreach ($configFileTree as $path) {
				$key = pathinfo($path, PATHINFO_FILENAME);

				file_put_contents(
					$configCachedPath . $key . '.php',
					'<?php return ' . var_export($config->get($key), true) . ';'
				);
			}
		} catch (\Throwable $e) {
			$this->call('config:clear');
			throw $e;
		}

		$this->rebuildProviderConfig();

		$this->output->success('Config cached successfully!');
	}

	protected function rebuildProviderConfig() {
		$providers = $this->getConfig()->get('provider', []);
		$providers['deferred'] = [];
		/**
		 * @var ProviderAbstract $provider
		 */
		foreach (array_merge(ProviderBootstrap::$providerMap, $providers['providers'] ?? []) as $providerMap) {
			foreach ((array)$providerMap as $provider) {
				$provider = new $provider();
				$deferredServices = $provider->providers();
				//如果有延迟加载服务，不对其进行注册
				if ($deferredServices) {
					foreach ($deferredServices as $deferredService) {
						$providers['deferred'][$deferredService] = $providers['deferred'][$deferredService] ?? [];
						$providers['deferred'][$deferredService] = array_merge($providers['deferred'][$deferredService], [get_class($provider)]);
					}
				}
			}
		}

		file_put_contents(App::getApp()->getBasePath() . '/vendor/composer/rangine/autoload/config/provider.php', '<?php return ' . var_export($providers, true) . ';');
	}

	/**
	 * 临时方案，下个版本删除
	 * @param $configDir
	 * @return array
	 */
	public function loadConfigFile($configDir) {
		$payload = [];
		$configFileTree = glob($configDir . '/*.php');
		if (empty($configFileTree)) {
			return $payload;
		}

		foreach ($configFileTree as $path) {
			$key = pathinfo($path, PATHINFO_FILENAME);
			$config = include $path;
			if (is_array($config)) {
				$payload[$key] = $this->payload[$key] ?? [];
				$payload[$key] = array_merge_recursive($payload[$key], $config);
			}
		}

		return $payload;
	}
}
