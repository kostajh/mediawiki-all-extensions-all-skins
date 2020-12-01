<?php

namespace BlueSpice\Bookshelf;

use ArrayObject;

class TreeNode extends ArrayObject {

	public function __construct() {
		parent::__construct();
		$this->init();
	}

	public function toArray() {
		return (array)$this;
	}

	// TODO: Maybe implement some getter/setters

	private function init() {
		$this['type'] = '';
		$this['title'] = '';
		$this['display-title'] = '';
		$this['article-id'] = -1;
		$this['is-redirect'] = -1;
		$this['bookshelf'] = [
			'type' => 'text'
		];
	}
}
