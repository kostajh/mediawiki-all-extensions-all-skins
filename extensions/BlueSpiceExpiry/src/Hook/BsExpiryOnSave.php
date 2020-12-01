<?php
namespace BlueSpice\Expiry\Hook;

use BlueSpice\Hook;
use Config;
use IContextSource;
use Title;
use User;

abstract class BsExpiryOnSave extends Hook {
	/**
	 *
	 * @var \stdClass
	 */
	protected $taskData = null;

	/**
	 *
	 * @var int
	 */
	protected $id = null;

	/**
	 *
	 * @var User
	 */
	protected $user = null;

	/**
	 *
	 * @var Title
	 */
	protected $title = null;

	/**
	 *
	 * @param \stdClass $taskData
	 * @param int $id
	 * @param int $articleId
	 * @param int $userId
	 * @param User $user
	 * @param Title $title
	 * @return bool
	 */
	public static function callback( $taskData, $id, $articleId, $userId, $user, $title ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$taskData,
			$id,
			$user,
			$title
		);
		return $hookHandler->process();
	}

	/**
	 * @param IContextSource $context
	 * @param Config $config
	 * @param \stdClass $taskData
	 * @param int $id
	 * @param User $user
	 * @param Title $title
	 */
	public function __construct( $context, $config, $taskData, $id, $user, $title ) {
		parent::__construct( $context, $config );

		$this->taskData = $taskData;
		$this->id = $id;
		$this->user = $user;
		$this->title = $title;
	}
}
