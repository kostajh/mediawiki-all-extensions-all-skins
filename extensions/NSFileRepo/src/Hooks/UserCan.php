<?php

namespace NSFileRepo\Hooks;

use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;

class UserCan {

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @var \IContextSource
	 */
	protected $context = null;

	/**
	 * @var \Title
	 */
	protected $title = null;

	/**
	 * @var \User
	 */
	protected $user = null;

	/**
	 * @var string
	 */
	protected $action = '';

	/**
	 * @var boolean
	 */
	protected $result = true;

	/**
	 * @var PermissionManager
	 */
	private $permManager;

	/**
	 * Check individual namespace protection
	 * @param Title $title
	 * @param user $user
	 * @param string $action
	 * @param mixed $result
	 * @return boolean
	 */
	public static function handle( &$title, &$user, $action, &$result ) {
		$instance = new self(
			\RequestContext::getMain(),
			new \MultiConfig( [
				new \NSFileRepo\Config(),
				MediaWikiServices::getInstance()->getMainConfig()
			] ),
			$title,
			$user,
			$action,
			$result,
			MediaWikiServices::getInstance()->getPermissionManager()
		);

		return $instance->process();
	}

	/**
	 * See static method "handle"
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param \Title $title
	 * @param \User $user
	 * @param string $action
	 * @param boolean $result
	 * @param PermissionManager $permManager
	 */
	public function __construct(
		\IContextSource $context,
		\Config $config,
		\Title &$title,
		\User &$user,
		$action,
		&$result,
		PermissionManager $permManager
	) {
		$this->context = $context;
		$this->config = $config;
		$this->title = $title;
		$this->user = $user;
		$this->action = $action;
		$this->result =& $result;
		$this->permManager = $permManager;
	}

	/**
	 *
	 * @return boolean
	 */
	public function process() {
		$whitelistRead = $this->config->get( 'WhitelistRead' );
		if ( $whitelistRead !== false && in_array( $this->title->getPrefixedText(), $whitelistRead ) ) {
			return true;
		}

		if( $this->title->getNamespace() !== NS_FILE ) {
			return true;
		}

		$ntitle = \Title::newFromText( $this->title->getDBkey() );

		//When image title cannot be created, due to upload errors,
		//$this->title->getDBKey() is empty, resulting in an invaid
		//title object in Title::newFromText
		if( !$ntitle instanceof \Title ) {
			return true;
		}

		//Additional check for NS_MAIN: If a user is not allowed to read NS_MAIN he should also be not allowed
		//to view files with no namespace-prefix as they are logically assigned to namespace NS_MAIN
		$titleIsNSMAIN =  $ntitle->getNamespace() === NS_MAIN;
		$titleNSaboveThreshold = $ntitle->getNamespace() > $this->config->get( 'NamespaceThreshold' );
		if( $titleIsNSMAIN || $titleNSaboveThreshold ) {
			$errors = $this->permManager->getPermissionErrors(
				$this->action,
				$this->user,
				$ntitle
			);
			if( !empty( $errors ) ) {
				$this->result = false;
				return false;
			}
		}

		return true;
	}
}
