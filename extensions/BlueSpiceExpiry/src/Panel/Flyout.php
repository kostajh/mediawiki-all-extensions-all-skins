<?php

namespace BlueSpice\Expiry\Panel;

use BlueSpice\Calumma\IActiveStateProvider;
use BlueSpice\Calumma\IFlyout;
use BlueSpice\Calumma\Panel\BasePanel;
use Message;
use QuickTemplate;

class Flyout extends BasePanel implements IFlyout, IActiveStateProvider {

	/**
	 *
	 * @var string Timestamp in TS_MW format
	 */
	protected $expiryTS = '';

	/**
	 *
	 * @param QuickTemplate $skintemplate
	 * @param string $expiryTS Timestamp in TS_MW format
	 */
	public function __construct( QuickTemplate $skintemplate, $expiryTS ) {
		parent::__construct( $skintemplate );

		$this->expiryTS = $expiryTS;
	}

	/**
	 * @return Message
	 */
	public function getFlyoutTitleMessage() {
		return Message::newFromKey( 'bs-expiry-flyout-title' );
	}

	/**
	 * @return Message
	 */
	public function getFlyoutIntroMessage() {
		return Message::newFromKey( 'bs-expiry-flyout-intro' );
	}

	/**
	 * @return Message
	 */
	public function getTitleMessage() {
		return Message::newFromKey( 'bs-expiry-nav-link-title-expiry' );
	}

	/**
	 * @return string
	 */
	public function getBody() {
		if ( empty( $this->expiryTS ) ) {
			return '';
		}

		$currentTS = wfTimestampNow();

		$cssClass = 'expired';
		$message = Message::newFromKey( 'bs-expiry-flyout-body-hint-expired' );
		if ( $currentTS < $this->expiryTS ) {
			$cssClass = 'expires';
			$message = Message::newFromKey( 'bs-expiry-flyout-body-hint-expires' );
		}

		$lang = $this->skintemplate->getSkin()->getLanguage();
		$formattedDate = $lang->date( $this->expiryTS, true );

		return \Html::element(
			'div',
			[
				'class' => "flyout-body-hint $cssClass"
			],
			$message->params( $formattedDate )->text()
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getTriggerCallbackFunctionName() {
		return 'bs.expiry.flyoutCallback';
	}

	/**
	 *
	 * @return string[]
	 */
	public function getTriggerRLDependencies() {
		return [ 'ext.bluespice.expiry.flyout' ];
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @return bool
	 */
	public function shouldRender( $context ) {
		return $context->getUser()->isLoggedIn();
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return 'bs-expiry-flyout';
	}

	public function isActive() {
		if ( !empty( $this->expiryTS ) ) {
			return true;
		}

		return false;
	}

}
