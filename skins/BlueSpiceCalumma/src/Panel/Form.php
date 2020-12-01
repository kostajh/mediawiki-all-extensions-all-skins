<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\Calumma\SkinDataPanel;

class Form extends SkinDataPanel {

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		$form = \HTMLForm::factory( 'ooui', $this->definition['content'] );
		$form->setTitle( \Title::newFromText( 'ABC' ) );
		$form->prepareForm();
		return $form->getHTML( false );
	}
}
