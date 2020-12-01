<?php

namespace BlueSpice\Calumma\FlexiSkin;

use MediaWiki\Extension\FlexiSkin\IFlexiSkinSubscriber;

class Subscriber implements IFlexiSkinSubscriber {
	public static function factory() {
		return new static();
	}

	/**
	 * @inheritDoc
	 */
	public function getAffectedRLModules(): array {
		return [
			'skin.bluespicecalumma.theme.default',
			'skin.bluespicecalumma.styles'
		];
	}
}
