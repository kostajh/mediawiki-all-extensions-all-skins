<?php

namespace BlueSpice\Social\Profile\AssocLinksProvider\Profile;

use BlueSpice\Html\Descriptor\TitleLink;
use Config;
use IContextSource;
use Message;

class Classic extends TitleLink {

	/**
	 *
	 * @return Message
	 */
	public function getLabel() {
		return $this->context->msg( 'bs-socialprofile-switch-classicprofile-label' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getTooltip() {
		return $this->context->msg( 'bs-socialprofile-switch-classicprofile-tooltip' );
	}

	/**
	 *
	 * @return string
	 */
	public function getHref() {
		return $this->title->getLinkURL( [ 'classicprofile' => "true" ] );
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
		if ( $context->getRequest()->getBool( 'classicprofile' ) ) {
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

		return [ 'profile-classic' => new static( $context, $config ) ];
	}
}
