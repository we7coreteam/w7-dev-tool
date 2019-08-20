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

namespace W7\DevTool\Command\Vendor;

use W7\Console\Command\GeneratorCommandAbstract;

class MakeCommand extends GeneratorCommandAbstract {
	protected $description = 'generate package';

	protected function getStub() {
		return dirname(__DIR__, 1) . '/Stubs/package-stubs';
	}

	protected function replaceStub() {
		$this->replace('{{ namespace }}', $this->packageName(), 'route/api.stub');
		$this->replace('{{ namespace }}', $this->packageNamespace(), 'src/ServiceProvider.stub');
		$this->replace('{{ namespace }}', $this->packageNamespace(), 'src/Controller/HomeController.stub');
		$this->replace('{{ namespace }}', $this->packageNamespace(), 'src/Middleware/HomeMiddleware.stub');
		$this->replace('{{ namespace }}', $this->packageNamespace(), 'src/Model/Entity/Api/App.stub');
		$this->replace('{{ namespace }}', $this->packageNamespace(), 'src/Model/Logic/AppLogic.stub');
		$this->replace('{{ name }}', $this->name, 'composer.json');
		$this->replace('{{ escapedNamespace }}', $this->escapedPackageNamespace(), 'composer.json');
	}

	protected function after() {
		$this->addRepositoryToRootComposer();
		$this->addPackageToRootComposer();

		$this->composerUpdate();

		$config = iconfig()->getServer();
		$config = $config['http'];
		$this->output->info('启动server后,可访问 http://' . $config['host'] . ':' . $config['port'] . '/' . $this->packageName() . '/home 验证扩展包是否创建成功.');
	}

	/**
	 * Add a path repository for the tool to the application's composer.json file.
	 *
	 * @return void
	 */
	protected function addRepositoryToRootComposer() {
		$composer = json_decode(file_get_contents(BASE_PATH . '/composer.json'), true);

		$composer['repositories'][] = [
			'type' => 'path',
			'url' => './'.$this->savePath(),
		];

		file_put_contents(
			BASE_PATH . '/composer.json',
			str_replace('    ', '	', json_encode($composer, JSON_PRETTY_PRINT | (JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES)))
		);
	}

	/**
	 * Add a package entry for the tool to the application's composer.json file.
	 *
	 * @return void
	 */
	protected function addPackageToRootComposer() {
		$composer = json_decode(file_get_contents(BASE_PATH . '/composer.json'), true);

		$composer['require'][$this->name] = 'dev-master';

		file_put_contents(
			BASE_PATH . '/composer.json',
			str_replace('    ', '	', json_encode($composer, JSON_PRETTY_PRINT | (JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES)))
		);
	}

	protected function composerUpdate() {
		exec('composer update');
	}

	/**
	 * Get the package's base name.
	 *
	 * @return string
	 */
	protected function packageName() {
		return strtolower(explode('/', $this->name)[1]);
	}

	/**
	 * Get the package's namespace.
	 *
	 * @return string
	 */
	protected function packageNamespace() {
		$namespace = explode('/', $this->name);
		foreach ($namespace as &$item) {
			$item = ucfirst($item);
		}
		return implode('\\', $namespace);
	}

	/**
	 * Get the package's escaped namespace.
	 *
	 * @return string
	 */
	protected function escapedPackageNamespace() {
		return str_replace('\\', '\\\\', $this->packageNamespace());
	}

	/**
	 * Get the path to the tool.
	 *
	 * @return string
	 */
	protected function savePath() {
		return 'components/' . $this->name;
	}

	protected function getRealPath() {
		return $this->rootPath();
	}
}
