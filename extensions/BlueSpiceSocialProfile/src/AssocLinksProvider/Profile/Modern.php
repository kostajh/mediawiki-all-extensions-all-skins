<?php

namespace BlueSpice\Social\Profile\AssocLinksProvider\Profile;

use BlueSpice\Html\Descriptor\TitleLink;
use Config;
use IContextSource;
use Message;

class Modern extends TitleLink {

	/**
	 *
	 * @return Message
	 */
	public function getLabel() {
		return $this->context->msg( 'bs-socialprofile-switch-modernprofile-label' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getTooltip() {
		return $this->context->msg( 'bs-socialprofile-switch-modernprofile-tooltip' );
	}

	/**
	 *
	 * @return string
	 */
	public function getHref() {
		return $this->title->getLinkURL();
	}

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @return ILink[]
	 */
	public static function factory( $context, $config ) {
		if ( $context->getRequest()->getVal( 'action', 'view' ) !== 'view' ) {
			return [];
		}
		if ( !$context->getRequest()->getBool( 'classicprofile' ) ) {
			return [];
		}
		if ( !$context->getTitle() ) {
			return [];
		}
		if ( $context->getTitle()->getNamespace() !== NS_USER ) {
			return [];
		}
		if ( $context->getTitle()->isSubpage() ) {
			return [];
		}

		return [ 'profile-modern' => new static( $context, $config ) ];
	}
}
