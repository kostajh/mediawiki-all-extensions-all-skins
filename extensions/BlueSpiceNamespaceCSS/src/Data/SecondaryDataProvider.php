<?php

namespace BlueSpice\NamespaceCSS\Data;

class SecondaryDataProvider extends \BlueSpice\Data\SecondaryDataProvider {

	/**
	 *
	 * @var \MediaWiki\Linker\LinkRenderer
	 */
	protected $linkrenderer = null;

	/**
	 *
	 * @param \MediaWiki\Linker\LinkRenderer $linkrenderer
	 */
	public function __construct( $linkrenderer ) {
		$this->linkrenderer = $linkrenderer;
	}

	/**
	 *
	 * @param Record &$dataSet
	 */
	protected function doExtend( &$dataSet ) {
		$title = \Title::newFromText( $dataSet->get( Record::SOURCE_PAGE ) );
		$dataSet->set(
			Record::SOURCE_PAGE_LINK,
			$this->linkrenderer->makeLink( $title )
		);
	}
}
