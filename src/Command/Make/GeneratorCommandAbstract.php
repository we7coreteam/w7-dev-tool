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

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use W7\Command\Support\Composer;
use W7\Console\Command\CommandAbstract;
use W7\Core\Exception\CommandException;

abstract class GeneratorCommandAbstract extends CommandAbstract {
	const NAMESPACE_ROOT = 'W7\\';

	/**
	 * @var Composer
	 */
	protected $composer;
	/**
	 * @var Filesystem
	 */
	protected $filesystem;
	/**
	 * 当前生成的文件名称
	 * @var array
	 */
	protected $name = [
		'namespace' => '',
		'class' => '',
	];

	/**
	 * 指定当前命令要生成的文件的类型
	 * @var string
	 */
	protected $typeSuffix;

	public function __construct(string $name = null) {
		parent::__construct($name);
		$this->filesystem = new Filesystem();
		$this->composer = new Composer($this->filesystem, BASE_PATH);
	}

	protected function configure() {
		$this->addOption('--name', null, InputOption::VALUE_REQUIRED, 'the generate file name, allows with namespaces');
		$this->addOption('--force', '-f', null, 'force overwrite file');
	}

	protected function handle($options) {
		if (empty($options['name'])) {
			throw new CommandException('the option name not null');
		}
		$fileName = $this->parseFileName($options['name'], $this->typeSuffix);

		//切分命名空间和类名
		$namespace = explode('\\', $fileName) ?? [];
		$classname = array_pop($namespace);

		$this->name = [
			//生成目录时，从用户指定的命名空间开始生成。
			'path' => implode("\\", $namespace),
			'class' => $classname,
		];
		if (empty($options['force']) && $this->filesystem->exists($this->getRealPath())) {
			throw new CommandException($this->name['class'] . ' already exists!');
		}
		$this->name['namespace'] = rtrim($this->rootNameSpace() . $this->name['path'], '\\');

		$this->copyStub();
		$this->replaceStub();
		$this->renameStubs();

		$this->after();

		$this->output->success($this->name['class'] . ' created successfully.');
	}

	abstract protected function getStub();
	abstract protected function savePath();

	protected function copyStub() {
		if ($this->filesystem->isDirectory($this->getStub())) {
			$this->filesystem->copyDirectory($this->getStub(), $this->rootPath());
		} else {
			if (!$this->filesystem->exists($this->rootPath())) {
				$this->filesystem->makeDirectory($this->rootPath(), 0755, true);
			}
			$this->filesystem->copy($this->getStub(), $this->rootPath() . $this->name['class'] . '.stub');
		}
	}

	protected function replaceStub() {
		$stubFile = $this->name['class'] . '.stub';

		$this->replace('{{ DummyNamespace }}', $this->name['namespace'] ? $this->name['namespace'] : '', $stubFile);
		$this->replace('{{ DummyClass }}', $this->name['class'], $stubFile);
	}

	protected function stubsToRename() {
		$stubs = [];
		if ($this->filesystem->isDirectory($this->getStub())) {
			foreach ((new Finder)->in($this->rootPath())->files() as $file) {
				if ($file->getExtension() == 'stub') {
					$stubs[] = $file->getPathname();
				}
			}
		} else {
			$stubs[] = $this->rootPath() . $this->name['class'] . '.stub';
		}

		return $stubs;
	}

	protected function renameStubs() {
		foreach ($this->stubsToRename() as $stub) {
			$this->filesystem->move($stub, str_replace('.stub', '.php', $stub));
		}
	}

	protected function after() {
	}

	/**
	 * Replace the given string in the given file.
	 *
	 * @param  string  $search
	 * @param  string  $replace
	 * @param  string  $path
	 * @return void
	 */
	protected function replace($search, $replace, $path = null) {
		$path = $this->rootPath() . ($path ? $path : $this->name['class'] . '.stub');
		file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
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
			str_replace("\\", '/', $this->name['path']),
		]);
		return sprintf('%s/', rtrim($savePath, '/'));
	}

	protected function getRealPath() {
		return sprintf('%s%s.php', $this->rootPath(), $this->name['class']);
	}

	protected function rootNameSpace() {
		$savePath = $this->savePath();

		if (empty($savePath)) {
			return self::NAMESPACE_ROOT;
		}

		$namespace = self::NAMESPACE_ROOT;
		foreach (explode('/', $savePath) as $row) {
			if (empty($row)) {
				continue;
			}
			$namespace .= ucfirst($row) . '\\';
		}

		return $namespace;
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
		unset($item);

		if (!empty($suffix)) {
			$path[count($path) - 1] .= ucfirst($suffix);
		}

		$fileName = implode("\\", $path);

		return $fileName;
	}
}
