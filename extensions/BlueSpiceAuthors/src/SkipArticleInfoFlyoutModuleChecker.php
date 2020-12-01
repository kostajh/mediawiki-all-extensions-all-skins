<?php

namespace BlueSpice\Authors;

use BlueSpice\Utility\PagePropHelper;
use Config;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;
use Title;
use User;
use WebRequest;

class SkipArticleInfoFlyoutModuleChecker {

	/**
	 *
	 * @var Config
	 */
	private $config = null;

	/**
	 *
	 * @var Title
	 */
	private $title = null;

	/**
	 *
	 * @var WebRequest
	 */
	private $request = null;

	/**
	 * @var User
	 */
	private $user = null;

	/**
	 *
	 * @var PagePropHelper
	 */
	private $pagePropHelper = null;

	/**
	 * @var PermissionManager
	 */
	private $permManager = null;

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public static function callback( $context ) {
		$services = MediaWikiServices::getInstance();
		$config = $services->getConfigFactory()->makeConfig( 'bsg' );
		$title = $context->getTitle();
		$request = $context->getRequest();
		$user = $context->getUser();
		$pagePropHelper = $services->getService( 'BSUtilityFactory' )->getPagePropHelper( $title );
		$permManager = $services->getPermissionManager();

		$checker = new static( $config, $title, $request, $user, $pagePropHelper, $permManager );

		// This is a design issue in `BlueSpice\ArticleInfo\Panel\Flyout`. If the "skip-callback"
		//returns `false` the module is being skipped, not the other way round.
		$result = !$checker->shouldSkip();
		return $result;
	}

	/**
	 *
	 * @param Config $config
	 * @param Title $title
	 * @param WebRequest $request
	 * @param User $user
	 * @param PagePropHelper $pagePropHelper
	 * @param PermissionManager $permManager
	 */
	public function __construct(
		$config,
		$title,
		$request,
		User $user,
		$pagePropHelper,
		PermissionManager $permManager
	) {
		$this->config = $config;
		$this->title = $title;
		$this->request = $request;
		$this->user = $user;
		$this->pagePropHelper = $pagePropHelper;
		$this->permManager = $permManager;
	}

	/**
	 *
	 * @return bool
	 */
	public function shouldSkip() {
		if ( $this->config->get( 'AuthorsShow' ) === false ) {
			return true;
		}

		if ( $this->request->getVal( 'action', 'view' ) != 'view' ) {
			return true;
		}

		$excludeNS = $this->config->get( 'AuthorsNamespaceBlacklist' );
		if ( in_array( $this->title->getNamespace(), $excludeNS ) ) {
			return true;
		}

		if ( !$this->title->exists() ) {
			return true;
		}

		if ( !$this->permManager->userCan( 'read', $this->user, $this->title ) ) {
			return true;
		}

		// Do not display if __NOAUTHORS__ keyword is found
		$noAuthors = $this->pagePropHelper->getPageProp( 'bs_noauthors' );
		if ( $noAuthors === '' ) {
			return true;
		}
		return false;
	}
}
