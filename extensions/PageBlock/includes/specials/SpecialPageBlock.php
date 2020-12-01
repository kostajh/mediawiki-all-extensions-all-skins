<?php

class SpecialPageBlock extends FormSpecialPage {

	/** @var Title */
	protected $title;

	/** @var User we want to restrict */
	protected $user;

	protected $hasRestrictions = false;

	public function __construct() {
		parent::__construct( 'PageBlock', 'page-block' );
	}

	/**
	 * @param HTMLForm $form
	 */
	protected function alterForm( HTMLForm $form ) {
		// There's probably a better place to put this...
		$this->getOutput()->addModules( 'ext.PageBlock.special' );
	}

	/**
	 * Static so we can also call it in SpecialListRestrictions
	 * @param string|null $par
	 * @return array Title, User
	 */
	public static function parseParameter( $par ) {
		$parts = explode( '/', $par );
		// Only check for username if there are multiple parts
		$ret = [ null, null ];
		if ( count( $parts ) > 1 ) {
			$username = array_pop( $parts );
			$user = User::newFromName( $username );
			if ( $user !== false && $user->getId() !== 0 ) {
				$ret[1] = $user;
			} else {
				// No user, make it part of the title
				$parts[] = $username;
			}
		}
		$title = Title::newFromText( implode( '/', $parts ) );
		if ( $title && $title->exists() ) {
			$ret[0] = $title;
		}

		return $ret;
	}

	/**
	 * Lets users do crazy things like Special:PageBlock/$pagename/$username
	 * @param string $par
	 */
	protected function setParameter( $par ) {
		parent::setParameter( $par );
		if ( $this->par ) {
			list( $this->title, $this->user ) = self::parseParameter( $this->par );
		}
	}

	/**
	 * @param string $type edit, move, or whatever.
	 * @return string
	 */
	protected function getCurrentExpiry( $type ) {
		if ( !$this->user || !$this->title ) {
			return '';
		}

		$restrictions = PageBlock::getPageRestrictions( $this->title, $this->user );
		if ( !$restrictions ) {
			return '';
		}
		$this->hasRestrictions = true;
		if ( isset( $restrictions[$this->user->getId()][$type] ) ) {
			$expiry = $restrictions[$this->user->getId()][$type];
			if ( $expiry !== 'infinity' ) {
				$expiry = $this->getLanguage()->formatExpiry( $expiry );
			}
			return $expiry;
		}
		return '';
	}

	protected function getFormFields() {
		$editExpiry = $this->getCurrentExpiry( 'edit' );
		$moveExpiry = $this->getCurrentExpiry( 'move' );

		// @todo maybe use a different message than Special:Block?
		$suggestedDurations = SpecialBlock::getSuggestedDurations();
		return [
			'page' => [
				'type' => 'text',
				'name' => 'page',
				'required' => true,
				'label-message' => 'pageblock-form-title',
				'default' => $this->title ?: '',
			],
			'user' => [
				'type' => 'text',
				'name' => 'user',
				'required' => true,
				'label-message' => 'pageblock-form-user',
				'default' => $this->user ?: '',
			],
			'edit' => [
				'type' => 'check',
				'name' => 'edit',
				'required' => true,
				'label-message' => 'pageblock-form-edit',
				'default' => $this->user ?: '',
			],
			'editexpiry' => [
				// Shhh, I totally stole this from Special:Block
				'type' => !count( $suggestedDurations ) ? 'text' : 'selectorother',
				'label-message' => 'ipbexpiry',
				// 'tabindex' => '2',
				'options' => $suggestedDurations,
				'other' => $this->msg( 'ipbother' )->text(),
				'default' => $this->getCurrentExpiry( 'edit' ),
			],
			'sameformove' => [
				'type' => 'check',
				'name' => 'sameformove',
				'label-message' => 'pageblock-form-sameformove',
				'default' => $editExpiry === $moveExpiry,
			],
			'move' => [
				'type' => 'check',
				'name' => 'move',
				'required' => true,
				'label-message' => 'pageblock-form-move',
				'default' => $this->user ?: '',
			],
			'moveexpiry' => [
				// Shhh, I totally stole this from Special:Block
				'type' => !count( $suggestedDurations ) ? 'text' : 'selectorother',
				'label-message' => 'ipbexpiry',
				// 'tabindex' => '2',
				'options' => $suggestedDurations,
				'other' => $this->msg( 'ipbother' )->text(),
				'default' => $this->getCurrentExpiry( 'move' ),
			],
			'reason' => [
				'type' => 'text',
				'name' => 'reason',
				'label-message' => 'pageblock-form-reason',
			],

		];
	}

	/**
	 * @param array $data
	 * @return Status
	 */
	public function onSubmit( array $data ) {
		$status = new Status;
		// Let's do some input validation...
		$title = Title::newFromText( $data['page'] );
		if ( !$title || !$title->exists() ) {
			$status->fatal( 'pageblock-invalid-title' );
		}
		$this->title = $title;

		$user = User::newFromName( $data['user'] );
		if ( !$user || $user->getId() === 0 ) {
			$status->fatal( 'pageblock-invalid-user' );
		}
		$this->user = $user;

		$editExpiry = SpecialBlock::parseExpiryInput( $data['editexpiry'] );
		if ( $editExpiry === false && $data['edit'] ) {
			$status->fatal( 'pageblock-invalid-expiry' );
		}

		$moveExpiry = SpecialBlock::parseExpiryInput( $data['moveexpiry'] );
		if ( $moveExpiry === false && $data['move'] && !$data['sameformove'] ) {
			$status->fatal( 'pageblock-invalid-expiry' );
		}

		if ( !$data['edit'] && !$data['move'] && !$this->hasRestrictions ) {
			$status->fatal( 'pageblock-must-check-something' );
		}

		if ( !$status->isOK() ) {
			return $status;
		}

		$restrictions = [];

		if ( $data['edit'] ) {
			$restrictions['edit'] = $editExpiry;
		}

		if ( $data['move'] || $data['sameformove'] ) {
			$restrictions['move'] = $data['sameformove'] ? $editExpiry : $moveExpiry;
		}

		PageBlock::updateRestrictions( $title, $user, $restrictions );
		PageBlock::createLogEntry(
			$this->getUser(),
			$data['reason'],
			$title,
			$user,
			$restrictions,
			$this->hasRestrictions
		);
		return $status;
	}

	public function onSuccess() {
		$this->getOutput()->addHTML( $this->msg( 'pageblock-success' )->rawParams(
					Linker::userLink( $this->user->getId(), $this->user->getName() ),
					Linker::link( $this->title )
		)->parse() );
	}
}
