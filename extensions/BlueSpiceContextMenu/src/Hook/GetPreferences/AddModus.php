<?php

namespace BlueSpice\ContextMenu\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class AddModus extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-contextmenu-modus'] = [
			'type' => 'radio',
			'label-message' => 'bs-contextmenu-pref-modus',
			'section' => 'rendering/contextmenu',
			'options' => [
				wfMessage( 'bs-contextmenu-pref-modus-ctrl-and-right-mouse' )->text() => 'ctrl',
				wfMessage( 'bs-contextmenu-pref-modus-just-right-mouse' )->text() => 'no-ctrl'
			],
		];
		return true;
	}
}
