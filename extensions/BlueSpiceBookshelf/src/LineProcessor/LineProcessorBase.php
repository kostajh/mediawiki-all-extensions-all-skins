<?php

namespace BlueSpice\Bookshelf\LineProcessor;

use BlueSpice\Bookshelf\ILineProcessor;

abstract class LineProcessorBase implements ILineProcessor {

	public static function factory() {
		return new static();
	}

	public function isFinal() {
		return false;
	}
}
