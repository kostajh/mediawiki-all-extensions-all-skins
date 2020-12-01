<?php

namespace BlueSpice\Bookshelf\Panel;

use BlueSpice\Calumma\IFlyout;
use BlueSpice\Calumma\Panel\BasePanel;
use Message;

class GeneralBooksFlyout extends BasePanel implements IFlyout {

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		return '';
	}

	/**
	 *
	 * @return Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-bookshelfui-nav-link-title-all-books' );
	}

	/**
	 *
	 * @return array
	 */
	public function getTriggerRLDependencies() {
		return [ 'ext.bluespice.bookshelf.flyout' ];
	}

	/**
	 *
	 * @return string
	 */
	public function getTriggerCallbackFunctionName() {
		return 'bs.bookshelf.flyoutTriggerCallback';
	}

	/**
	 *
	 * @return Message|string
	 */
	public function getFlyoutIntroMessage() {
		return wfMessage( 'bs-bookshelfui-flyout-intro' )->parse();
	}

	/**
	 *
	 * @return Message
	 */
	public function getFlyoutTitleMessage() {
		return wfMessage( 'bs-bookshelfui-flyout-title' );
	}

	/**
	 *
	 * @return string
	 */
	public function getIconCls() {
		return 'bs-icon-books';
	}
}
