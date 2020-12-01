<?php

namespace BlueSpice\ReadConfirmation;

use Title;
use User;

interface IMechanism {

	/**
	 * @param Title $title
	 * @param User $user
	 * @param int|null $revId
	 * @return bool
	 */
	public function canConfirm( Title $title, User $user, $revId = null );

	/**
	 * @param Title $title
	 * @param User $user
	 * @param int|null $revId
	 * @return bool
	 */
	public function confirm( Title $title, User $user, $revId = null );

	/**
	 * @param Title $title
	 * @param User $userAgent
	 * @return User[]|bool
	 */
	public function notify( Title $title, User $userAgent );

	/**
	 * @return void
	 */
	public function autoNotify();

	/**
	 * @return void
	 */
	public function wireUpNotificationTrigger();

	/**
	 * @param array $userIds
	 * @param array $pageIds
	 * @return array [ <page_id> => [ <user_id1>, <user_id2>, ...], ... ]
	 */
	public function getCurrentReadConfirmations( array $userIds = [], array $pageIds = [] );

	/**
	 * @param Title $title
	 * @return mixed
	 */
	public function mustRead( Title $title );
}
