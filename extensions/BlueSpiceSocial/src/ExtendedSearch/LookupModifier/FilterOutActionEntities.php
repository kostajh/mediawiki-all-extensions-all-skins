<?php

namespace BlueSpice\Social\ExtendedSearch\LookupModifier;

use BlueSpice\ExtensionAttributeBasedRegistry;
use BS\ExtendedSearch\Source\LookupModifier\Base as LookupModifierBase;
use MediaWiki\MediaWikiServices;

class FilterOutActionEntities extends LookupModifierBase {

	public function apply() {
		$entityConfigFactory = MediaWikiServices::getInstance()->getService(
			'BSEntityConfigFactory'
		);

		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationEntityRegistry'
		);
		foreach ( $registry->getAllKeys() as $type ) {
			$typeConfig = $entityConfigFactory->newFromType( $type );
			if ( $typeConfig->get( 'ExtendedSearchListable' ) == false ) {
				$this->oLookup->addBoolMustNotTerms( 'entitydata.type', $type );
			}
		}
	}

	public function undo() {
		$this->oLookup->removeBoolMustNot( 'entitydata.type' );
	}

}
