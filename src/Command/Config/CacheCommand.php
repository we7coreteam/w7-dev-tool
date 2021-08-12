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

use SplFileInfo;
use Symfony\Component\Finder\Finder;
use W7\App;
use W7\Console\Command\CommandAbstract;
use W7\Core\Bootstrap\ProviderBootstrap;
use W7\Core\Provider\ProviderAbstract;

class CacheCommand extends CommandAbstract {
	protected $description = 'create config cache file';

	protected function handle($options) {
		$this->call('config:clear');
		App::getApp()->exit();
		$app = new App();

		$configCachedPath = $app->getConfigCachePath();
		if (!file_exists($configCachedPath)) {
			mkdir($configCachedPath, 0777, true);
		}

		try {
			$files = Finder::create()
				->in(App::getApp()->getBasePath() . '/config')
				->files()
				->ignoreDotFiles(true)
				->name('/^[\w\W\d]+.php$/');
			/**
			 * @var SplFileInfo $file
			 */
			foreach ($files as $file) {
				$fileName = $file->getFilenameWithoutExtension();
				$config = $this->getConfig()->get($fileName);
				if (isset($config)) {
					file_put_contents(
						$configCachedPath . $fileName . '.php',
						'<?php return ' . var_export($config, true) . ';'
					);
				}
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
}
