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

class ListenerCommand extends RemoveCommandAbstract {
	protected $description = 'remove listener';
	protected $typeSuffix = 'listener';
	private $isRemoveEvent;

	protected function savePath() {
		if ($this->isRemoveEvent) {
			return 'app/Event/';
		}
		return 'app/Listener/';
	}

	protected function after() {
		$clone = clone $this;
		if (!$clone->isRemoveEvent) {
			$clone->isRemoveEvent = true;
			$clone->typeSuffix = 'event';
			return $clone->handle($this->input->getOptions());
		} else {
			//触发包管理插件，自动更新event配置文件
			$this->composer->dumpAutoloads();
		}
	}
}
