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

namespace W7\Command\Command\Remove;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use W7\Command\Support\Composer;
use W7\Console\Command\CommandAbstract;
use W7\Core\Exception\CommandException;

abstract class RemoveCommandAbstract extends CommandAbstract {
	/**
	 * 指定当前命令要生成的文件的类型
	 * @var string
	 */
	protected $typeSuffix;
	/**
	 * @var Composer
	 */
	protected $composer;
	/**
	 * @var Filesystem
	 */
	protected $filesystem;
	/**
	 * @var string
	 */
	protected $name;

	public function __construct(string $name = null) {
		parent::__construct($name);
		$this->filesystem = new Filesystem();
		$this->composer = new Composer($this->filesystem, BASE_PATH);
	}

	protected function configure() {
		$this->addOption('--name', null, InputOption::VALUE_REQUIRED, 'the generate file name, allows with namespaces');
	}

	protected function handle($options) {
		if (empty($options['name'])) {
			throw new CommandException('the option name not null');
		}

		$this->name = $this->parseFileName($options['name'], $this->typeSuffix);

		$this->remove();
		$this->after();

		$this->output->success($this->name . ' remove successfully.');
	}

	protected function remove() {
		$this->filesystem->delete($this->rootPath() . '.php');
	}

	protected function after() {
		$this->composer->dumpAutoloads();
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
			str_replace("\\", '/', $this->name),
		]);
		return rtrim($savePath, '/');
	}

	/**
	 * 根据传入的带命名空间的文件名，格式化成正式的命名空间写法
	 * @param $fileName
	 * @param string $suffix
	 * @return mixed|string
	 */
	private function parseFileName($fileName, $suffix = '') {
		if (strpos($fileName, '/') !== false) {
			$fileName = str_replace('/', "\\", $fileName);
		}
		$path = explode("\\", $fileName);
		foreach ($path as &$item) {
			$item = ucfirst($item);
		}

		if (!empty($suffix)) {
			$path[count($path) - 1] .= ucfirst($suffix);
		}

		$fileName = implode("\\", $path);

		return $fileName;
	}

	abstract protected function savePath();
}
