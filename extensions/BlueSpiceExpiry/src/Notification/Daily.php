<?php

namespace BlueSpice\Expiry\Notification;

use Title;
use User;

class Daily extends Expiry {

	public const TYPE = 'bs-expiry-daily';

	/**
	 *
	 * @param User $agent
	 * @param Title $title
	 * @param User[] $affectedUsers
	 * @param string $comment
	 */
	public function __construct( $agent, $title, $affectedUsers, $comment = '' ) {
		parent::__construct( static::TYPE, $agent, $title, $affectedUsers, $comment );
	}
}
