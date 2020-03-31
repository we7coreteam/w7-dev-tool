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

namespace W7\Command\Command\Vendor;

use W7\Command\Command\Make\GeneratorCommandAbstract;
use W7\Core\Exception\CommandException;

class MakeCommand extends GeneratorCommandAbstract {
	protected $description = 'generate package';

	protected function before() {
		if (count(explode('/', $this->name)) != 2) {
			throw new CommandException('component name error, the correct format is namespace/name, for example w7/test');
		}
	}

	protected function getStub() {
		return dirname(__DIR__, 1) . '/Stubs/package-stubs';
	}

	protected function replaceStub() {
		$this->replace('{{ namespace }}', $this->packageName(), 'route/api.stub');
		$this->replace('{{ namespace }}', $this->packageNamespace(), 'src/ServiceProvider.stub');
		$this->replace('{{ namespace }}', $this->packageNamespace(), 'src/Exception/HttpException.stub');
		$this->replace('{{ namespace }}', $this->packageNamespace(), 'src/Controller/HomeController.stub');
		$this->replace('{{ namespace }}', $this->packageNamespace(), 'src/Middleware/HomeMiddleware.stub');
		$this->replace('{{ name }}', $this->packageName(), 'composer.json');
		$this->replace('{{ escapedNamespace }}', $this->escapedPackageNamespace(), 'composer.json');
	}

	protected function after() {
		$this->addRepositoryToRootComposer();
		$this->addPackageToRootComposer();

		$this->composerUpdate();

		$config = iconfig()->getServer();
		$config = $config['http'];
		$this->output->note('启动server后,可访问 http://' . $config['host'] . ':' . $config['port'] . '/' . $this->packageName() . '/home 验证扩展包是否创建成功.');
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

		$composer['require'][$this->packageName()] = 'dev-master';

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
		return strtolower($this->name['path'] . '/' . $this->name['class']);
	}

	/**
	 * Get the package's namespace.
	 *
	 * @return string
	 */
	protected function packageNamespace() {
		return $this->name['path'] . '\\' . str_replace('-', '', $this->name['class']);
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
		return 'components/';
	}

	/**
	 * Get the path to the tool.
	 *
	 * @return string
	 */
	protected function rootPath() {
		$savePath = implode('/', [
			BASE_PATH,
			trim($this->savePath(), '/'),
			str_replace('\\', '/', strtolower($this->name['path'])),
			str_replace('\\', '/', strtolower($this->name['class']))
		]);
		return sprintf('%s/', rtrim($savePath, '/'));
	}
}
