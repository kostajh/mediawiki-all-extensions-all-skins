<?php

class ApiBuggy extends ApiBase {
	public function execute() {
		throw new Exception( 'Buggy test exception via API' );
	}
}
