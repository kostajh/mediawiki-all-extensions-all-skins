<?php

namespace BlueSpice\Readers\Data\PageReaders;

use BlueSpice\Readers\Data\Record;
use MediaWiki\MediaWikiServices;

class SecondaryDataProvider extends \BlueSpice\Data\SecondaryDataProvider {

	/**
	 *
	 * @param Record &$dataSet
	 * @return null
	 */
	protected function doExtend( &$dataSet ) {
		$factory = MediaWikiServices::getInstance()->getService( 'BSRendererFactory' );
		$user = \User::newFromId( $dataSet->get( Record::USER_ID ) );
		if ( $user instanceof \User == false ) {
			return;
		}

		$image = $factory->get( 'userimage', new \BlueSpice\Renderer\Params( [
			'user' => $user,
			'width' => "48",
			'height' => "48",
		] ) );
		$dataSet->set( Record::USER_IMAGE_HTML, $image->render() );
	}
}
