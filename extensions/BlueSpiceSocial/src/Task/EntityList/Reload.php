<?php

namespace BlueSpice\Social\Task\EntityList;

use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\Task;
use Status;

class Reload extends Task {

	/**
	 * @return Status
	 */
	protected function doExecute() {
		return Status::newGood();
	}

	/**
	 * @return ParamDefinition[]
	 */
	public function getArgsDefinitions() {
		return [];
	}

}
