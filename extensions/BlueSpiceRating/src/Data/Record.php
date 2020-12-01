<?php

namespace BlueSpice\Rating\Data;

class Record extends \BlueSpice\Data\Record {
	const ID = 'rat_id';
	const REFTYPE = 'rat_reftype';
	const REF = 'rat_ref';
	const USERID = 'rat_userid';
	const USERIP = 'rat_userip';
	const VALUE = 'rat_value';
	const CREATED = 'rat_created';
	const TOUCHED = 'rat_touched';
	const SUBTYPE = 'rat_subtype';
	const CONTEXT = 'rat_context';

	public function __clone() {
		$this->dataSet = clone $this->dataSet;
		$this->status = null;
	}
}
