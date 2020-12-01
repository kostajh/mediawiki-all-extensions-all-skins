<?php

namespace NSFileRepo;

use MediaWiki\MediaWikiServices;

class NamespaceList {

	/**
	 *
	 * @var \User
	 */
	protected $user = null;

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @var \Language
	 */
	protected $lang = null;

	/**
	 *
	 * @param \User $user
	 * @param \Config $config
	 * @param \Language $lang
	 */
	public function __construct( \User $user, \Config $config, \Language $lang ) {
		$this->user = $user;
		$this->config = new \MultiConfig([
			$config,
			new \HashConfig( [
				Config::CONFIG_SKIP_TALK => true,
				Config::CONFIG_THRESHOLD => 0,
				Config::CONFIG_BLACKLIST => []
			] )
		]);
		$this->lang = $lang;
	}

	/**
	 * @return MWNamespace[] With namespace id as an index
	 */
	public function getReadable() {
		return $this->getNamespacesByPermission( 'read' );
	}

	/**
	 * @return MWNamespace[] With namespace id as an index
	 */
	public function getEditable() {
		return $this->getNamespacesByPermission( 'edit' );
	}

	protected function getNamespacesByPermission( $permission ) {
		$availableNamespaces = $this->lang->getNamespaces();

		$namespaces = [];
		foreach( $availableNamespaces as $nsId => $nsText ) {

			if( $this->skip( $nsId, $permission ) ) {
				continue;
			}

			if( $nsId === NS_MAIN ) {
				$nsText = wfMessage('nsfilerepo-nsmain')->plain();
			}

			$canonicalName = \MWNamespace::getCanonicalName( $nsId );
			$namespaces[$nsId] = new MWNamespace( $nsId, $canonicalName , $nsText );
		}

		return $namespaces;
	}

	protected function skip( $nsId, $permission = '' ) {

		if( $nsId < $this->config->get( Config::CONFIG_THRESHOLD ) && $nsId !== NS_MAIN ) {
			return true;
		}

		if( in_array( $nsId, $this->config->get( Config::CONFIG_BLACKLIST ) ) ) {
			return true;
		}

		if( $this->config->get( Config::CONFIG_SKIP_TALK )
				&& \MWNamespace::isTalk( $nsId ) ) {
			return true;
		}

		if( !empty( $permission ) ) {
			$title = \Title::makeTitle( $nsId, 'Dummy' );
			if ( class_exists( \MediaWiki\Permissions\PermissionManager::class ) ) {
				// MediaWiki 1.33+
				return !MediaWikiServices::getInstance()->getPermissionManager()
					->userCan( $permission, $this->user, $title );
			} else {
				return !$title->userCan( $permission, $this->user );
			}
		}

		return false;
	}

}
