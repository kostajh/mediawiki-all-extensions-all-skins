<?php

namespace BlueSpice\NamespaceCSS\Maintenance;

use BlueSpice\NamespaceCSS\Helper;
use MediaWiki\MediaWikiServices;

class MoveToDotCSS extends \LoggedUpdateMaintenance {

	/**
	 *
	 * @return bool
	 */
	protected function doDBUpdates() {
		$toMigrate = $this->collectTitles();

		if ( count( $toMigrate ) < 1 ) {
			$this->output(
				"bs-namespace-css-move-to-dot-css -> No data to migrate\n"
			);
			return true;
		}
		$this->output(
			"...bs-namespace-css-move-to-dot-css -> migration...\n\n"
		);
		foreach ( $toMigrate as $idx => $titles ) {
			$this->output(
				"$idx: {$titles['old']->getFullText()} => {$titles['new']->getFullText()}..."
			);
			try {
				$move = new \MovePage( $titles['old'], $titles['new'] );
				$status = $move->move(
					$this->getUser(),
					$this->getReason(),
					false
				);
			} catch ( \Exception $e ) {
				$this->output( "... failed: {$e->getMessage()}\n" );
			}
			if ( !$status->isOK() ) {
				$this->output( "... failed: {$status->getMessage()}\n" );
			}
			$this->output( "... OK\n" );
		}

		return true;
	}

	/**
	 *
	 * @return array
	 */
	protected function collectTitles() {
		$toMigrate = [];
		foreach ( \MWNamespace::getCanonicalNamespaces() as $idx => $ns ) {
			if ( \MWNamespace::isTalk( $idx ) ) {
				continue;
			}
			$title = $this->buildOldTitleFromNamespaceIndex( $idx );
			if ( !$title ) {
				continue;
			}
			if ( !$title->exists() ) {
				continue;
			}
			$newTitle = Helper::buildTitleFromNamespaceIndex( $idx );
			if ( !$newTitle ) {
				continue;
			}
			$toMigrate[$idx] = [
				'old' => $title,
				'new' => $newTitle,
			];
		}
		return $toMigrate;
	}

	/**
	 *
	 * @return string
	 */
	protected function getReason() {
		return 'Moved by Updater ' . __CLASS__;
	}

	/**
	 *
	 * @return \User
	 */
	protected function getUser() {
		return MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getMaintenanceUser()->getUser();
	}

	/**
	 *
	 * @param int $idx
	 * @return \Title|false
	 */
	protected function buildOldTitleFromNamespaceIndex( $idx ) {
		$text = Helper::buildNamespaceNameFromNamespaceIndex( $idx );
		if ( !$text ) {
			return false;
		}
		return \Title::newFromText( $text . "_css", NS_MEDIAWIKI );
	}

	/**
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'bs-namespace-css-move-to-dot-css3';
	}

}
