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

use W7\Console\Command\GeneratorCommandAbstract;
use W7\Core\Exception\CommandException;

class CommandCommand extends GeneratorCommandAbstract {
	protected $description = 'generate command';
	private $path;

	protected function before() {
		if (strpos($this->name, '/') === false) {
			throw new CommandException('option name error,  the correct format is namespace/name');
		}
		$path = explode('/', $this->name);
		foreach ($path as &$item) {
			$item = ucfirst($item);
		}
		$path[count($path) - 1] .= 'Command';

		$this->name = end($path);
		array_pop($path);
		$this->path = implode('/', $path);
	}

	protected function getStub() {
		return dirname(__DIR__, 1) . '/Stubs/Command.stub';
	}

	protected function replaceStub() {
		$stubFile = $this->name . '.stub';
		$this->replace('{{ DummyNamespace }}', 'W7\App\Command\\' . str_replace('/', '\\', $this->path), $stubFile);
		$this->replace('{{ DummyClass }}', $this->name, $stubFile);
	}

	protected function savePath() {
		return 'app/Command/' . $this->path;
	}
}
