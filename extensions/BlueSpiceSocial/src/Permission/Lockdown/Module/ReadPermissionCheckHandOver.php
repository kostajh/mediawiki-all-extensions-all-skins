<?php

namespace BlueSpice\Social\Permission\Lockdown\Module;

use BlueSpice\EntityFactory;
use BlueSpice\Social\Entity;
use Config;
use IContextSource;
use MediaWiki\MediaWikiServices;
use Message;
use Title;
use User;

class ReadPermissionCheckHandOver extends \BlueSpice\Permission\Lockdown\Module {

	/**
	 *
	 * @var EntityFactory
	 */
	protected $entityFactory = null;

	/**
	 *
	 * @param Config $config
	 * @param IContextSource $context
	 * @param MediaWikiServices $services
	 * @param EntityFactory $entityFactory
	 */
	protected function __construct( Config $config, IContextSource $context,
		MediaWikiServices $services, EntityFactory $entityFactory ) {
		parent::__construct( $config, $context, $services );

		$this->entityFactory = $entityFactory;
	}

	/**
	 *
	 * @param Config $config
	 * @param IContextSource $context
	 * @param MediaWikiServices $services
	 * @param EntityFactory|null $entityFactory
	 * @return \static
	 */
	public static function getInstance( Config $config, IContextSource $context,
		MediaWikiServices $services, array $entityFactory = null ) {
		if ( !$entityFactory ) {
			$entityFactory = $services->getService( 'BSEntityFactory' );
		}

		return new static(
			$config,
			$context,
			$services,
			$entityFactory
		);
	}

	/**
	 *
	 * @param Title $title
	 * @param User $user
	 * @return bool
	 */
	public function applies( Title $title, User $user ) {
		return $title->getNamespace() === NS_SOCIALENTITY;
	}

	/**
	 *
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @return bool
	 */
	public function mustLockdown( Title $title, User $user, $action ) {
		if ( $action !== 'read' ) {
			return false;
		}
		$entity = $this->entityFactory->newFromSourceTitle( $title );
		if ( !$entity instanceof Entity ) {
			return false;
		}
		return !$entity->userCan()->isOK();
	}

	/**
	 *
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @return Message
	 */
	public function getLockdownReason( Title $title, User $user, $action ) {
		$entity = $this->entityFactory->newFromSourceTitle( $title );
		return $entity->userCan()->getMessage();
	}

}
