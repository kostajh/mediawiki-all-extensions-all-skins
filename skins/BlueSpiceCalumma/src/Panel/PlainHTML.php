<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\Calumma\SkinDataPanel;

class PlainHTML extends SkinDataPanel {

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		return $this->definition['content'];
	}
}
