<?php

namespace BlueSpice\Expiry\PageInfoElement;

use BlueSpice\Expiry\Extension as Expiry;
use BlueSpice\IPageInfoElement;
use BlueSpice\PageInfoElement;
use MediaWiki\MediaWikiServices;
use Message;

class Expired extends PageInfoElement {
	public $expire = 'undefined';
	public $expId = '';

	/**
	 *
	 * @var bool
	 */
	private $showMenu = false;

	/**
	 *
	 * @return Message
	 */
	public function getLabelMessage() {
		return $this->msg( 'bs-expiry-pageinfoelement-expired-label' );
	}

	/**
	 *
	 * @return string
	 */
	public function getName() {
		return "expiry-expired";
	}

	/**
	 *
	 * @return Message
	 */
	public function getTooltipMessage() {
		return $this->msg( 'bs-expiry-pageinfoelement-expired-tooltip' );
	}

	/**
	 *
	 * @param \ContextSource $context
	 * @return bool
	 */
	public function shouldShow( $context ) {
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		$userCanRead = $pm->userCan(
			'read',
			$context->getUser(),
			$context->getTitle()
		);
		if ( !$userCanRead ) {
			return false;
		}
		$id = $context->getTitle()->getArticleID();
		$expiry = Expiry::getExpiryForPage( $id );

		if ( !$expiry || empty( $expiry['exp_date'] ) ) {
			return false;
		}

		$userCanDelete = $pm->userCan(
			'expiry-delete',
			$context->getUser(),
			$context->getTitle()
		);
		// Currently there is only the deletion of the expiration in the pageInfo
		// menu. This may change in the future, as we also could add a
		// "update expiration date" menu item
		if ( $userCanDelete ) {
			$this->showMenu = true;
		}
		$this->expId = $expiry['exp_id'];

		$this->expire = 'expired';

		return true;
	}

	/**
	 *
	 * @return string
	 */
	public function getItemClass() {
		return IPageInfoElement::ITEMCLASS_CONTRA;
	}

	/**
	 *
	 * @return string
	 */
	public function getUrl() {
		return '';
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlClass() {
		return 'bs-expiry-pageinfo-page-' . $this->expire;
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return 'pageinfo-expiry-expired';
	}

	/**
	 *
	 * @return string
	 */
	public function getType() {
		return IPageInfoElement::TYPE_MENU;
	}

	/**
	 *
	 * @return string
	 */
	public function getMenu() {
		if ( !$this->showMenu ) {
			return '';
		}
		return $this->makeMenu();
	}

	/**
	 *
	 * @return string
	 */
	public function makeMenu() {
		$html = '';
		$label = $this->msg( 'bs-expiry-pageinfoelement-unexpire-label' );
		$tooltip = $this->msg( 'bs-expiry-pageinfoelement-unexpire-tooltip' );

		$html .= \Html::openElement( 'ul' );
		$html .= \Html::openElement( 'li' );

		$html .= \Html::element(
				'a',
				[
					'href' => "#",
					'class' => 'bs-expiry-unexpire',
					'data-expid' => $this->expId,
					'title' => $tooltip->plain()
				],
				$label->plain()
			);

		$html .= \Html::closeElement( 'li' );
		$html .= \Html::closeElement( 'ul' );

		return $html;
	}
}
