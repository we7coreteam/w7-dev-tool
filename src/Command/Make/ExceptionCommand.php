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

use Symfony\Component\Console\Input\InputOption;
use W7\Core\Exception\CommandException;

class ExceptionCommand extends GeneratorCommandAbstract {
	const TYPE_RESPONSE = 'response';

	protected $description = 'generate exception';
	protected $typeSuffix = 'exception';

	//生成异常的类型，普通，Response
	protected $type;
	private $supportType = [self::TYPE_RESPONSE];

	protected function configure() {
		$this->addOption('--type', null, InputOption::VALUE_OPTIONAL, 'handler type');
		parent::configure();
	}

	protected function handle($options) {
		$this->type = $this->input->getOption('type');
		if (!empty($this->type)) {
			if (!in_array($this->type, $this->supportType)) {
				throw new CommandException('not support the type');
			}
		}
		parent::handle($options);
	}

	protected function getStub() {
		if (empty($this->type)) {
			return dirname(__DIR__, 1) . '/Stubs/Exception.stub';
		} elseif ($this->type == self::TYPE_RESPONSE) {
			return dirname(__DIR__, 1) . '/Stubs/ExceptionResponse.stub';
		}
	}

	protected function savePath() {
		return 'app/Exception/';
	}
}
