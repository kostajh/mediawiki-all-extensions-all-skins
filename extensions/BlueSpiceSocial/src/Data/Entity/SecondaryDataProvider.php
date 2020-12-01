<?php

namespace BlueSpice\Social\Data\Entity;

use BlueSpice\Data\ISecondaryDataProvider;
use MediaWiki\Linker\LinkRenderer;

class SecondaryDataProvider implements ISecondaryDataProvider {

	/**
	 *
	 * @var LinkRenderer
	 */
	protected $linkrenderer = null;

	/**
	 *
	 * @param LinkRenderer $linkrenderer
	 */
	public function __construct( $linkrenderer ) {
		$this->linkrenderer = $linkrenderer;
	}

	/**
	 *
	 * @param Record[] $dataSets
	 * @return Record[]
	 */
	public function extend( $dataSets ) {
		// i guess it does nothing at the moment ¯\_(ツ)_/¯
		return $dataSets;
	}
}
