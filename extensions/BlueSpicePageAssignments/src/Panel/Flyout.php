<?php

namespace BlueSpice\PageAssignments\Panel;

use BlueSpice\Calumma\IFlyout;
use BlueSpice\Calumma\Panel\BasePanel;
use Message;

class Flyout extends BasePanel implements IFlyout {

	/**
	 * @return Message
	 */
	public function getFlyoutTitleMessage() {
		return wfMessage( 'bs-pageassignments-flyout-title' );
	}

	/**
	 * @return Message
	 */
	public function getFlyoutIntroMessage() {
		return wfMessage( 'bs-pageassignments-flyout-intro' );
	}

	/**
	 * @return Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-pageassignments-nav-link-title-pageassignments' );
	}

	/**
	 * @return string
	 */
	public function getBody() {
		return '';
	}

	/**
	 *
	 * @return string
	 */
	public function getTriggerCallbackFunctionName() {
		return 'bs.pageassignments.flyoutCallback';
	}

	/**
	 *
	 * @return array
	 */
	public function getTriggerRLDependencies() {
		return [ 'ext.bluespice.pageassignments.flyout' ];
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @return bool
	 */
	public function shouldRender( $context ) {
		$title = $context->getTitle();
		if ( !$title || !$title->exists() ) {
			return false;
		}
		if ( !\MediaWiki\MediaWikiServices::getInstance()->getPermissionManager()
			->userCan( 'read', $context->getUser(), $title )
		) {
			return false;
		}

		return true;
	}
}
