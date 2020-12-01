<?php

namespace BlueSpice\SaferEdit\EnvironmentChecker;

use BlueSpice\SaferEdit\IEnvironmentChecker;
use IContextSource;

abstract class Base implements IEnvironmentChecker {
	/**
	 * @var IContextSource
	 */
	protected $context;

	/**
	 * @param IContextSource $context
	 * @return IEnvironmentChecker
	 */
	public static function factory( IContextSource $context ) {
		return new static( $context );
	}

	/**
	 * @param IContextSource $context
	 */
	protected function __construct( $context ) {
		$this->context = $context;
	}

	/**
	 *
	 * @return bool
	 */
	protected function userCanEdit() {
		$title = $this->context->getTitle();
		$user = $this->context->getUser();
		if ( \MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'edit', $user, $title )
		) {
			return true;
		}
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getEditedTitle( &$title ) {
		return true;
	}

	/**
	 *
	 * @param bool &$result
	 */
	abstract public function isEditMode( &$result );

	/**
	 *
	 * @param bool &$result
	 */
	abstract public function shouldShowWarning( &$result );
}
