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
use W7\Core\Config\Config;
use W7\Core\Config\Env\Env;

class CacheCommand extends CommandAbstract {
	protected $description = 'create config cache file';

	protected function handle($options) {
		$this->call('config:clear');

		(new Env(BASE_PATH))->load();
		$payload = $this->loadConfigFile(BASE_PATH . '/config');
		$config = new Config($payload);

		$configCachedPath = App::getApp()->getConfigCachePath();
		if (!file_exists($configCachedPath)) {
			mkdir($configCachedPath, 0777, true);
		}

		$configFileTree = glob(BASE_PATH . '/config/*.php');
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

		$this->output->success('Config cached successfully!');
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
