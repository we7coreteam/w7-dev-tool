<?php

namespace W7\Command\Support;

use Illuminate\Support\Composer as ComposerAbstract;

class Composer extends ComposerAbstract {
	public function update($extra = '') {
		$extra = $extra ? (array) $extra : [];

		$command = array_merge($this->findComposer(), ['update'], $extra);

		$this->getProcess($command)->run();
	}
}