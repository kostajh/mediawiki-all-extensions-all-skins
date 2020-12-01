<?php

namespace BlueSpice\Social\Comments\Hook\BSSocialEntityOutputRenderAfterContent;

use BlueSpice\Social\Entity;
use BlueSpice\Social\Hook\BSSocialEntityOutputRenderAfterContent;

/**
 * Adds a comment count to every non comment entity view
 */
class AddCommentSection extends BSSocialEntityOutputRenderAfterContent {

	protected function doProcess() {
		$oEntity = $this->oEntityOutput->getEntity();

		if ( !$oEntity instanceof Entity ) {
			return true;
		}
		if ( !$oEntity->exists() || $oEntity->hasParent() ) {
			return true;
		}
		if ( !$oEntity->getConfig()->get( 'CanHaveChildren' ) ) {
			return true;
		}

		$sView = '';
		$aChildren = array_filter( $oEntity->getChildren(), function ( Entity $e ) {
			return !$e->get( Entity::ATTR_ARCHIVED, false );
		} );

		$countChildren = count( $aChildren );

		$sView .= \Html::openElement( "a", [
			'class' => 'bs-social-entityaftercontent-comment'
		] );

		// Only present on mobile view.
		$sView .= \Html::element(
			'span',
			[ 'class' => 'bs-social-count-short' ],
			$countChildren
		);

		$msg = wfMessage(
			'bs-socialcomments-commenttext',
			$countChildren
		);

		$sView .= \Html::element(
			'span',
			[ 'class' => 'bs-social-count-default' ],
			$msg->parse()
		);

		$sView .= \Html::closeElement( "a" );

		$this->aViews[] = $sView;

		return true;
	}
}
