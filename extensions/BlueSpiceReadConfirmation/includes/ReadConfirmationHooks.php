<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionStoreRecord;
use MediaWiki\Storage\EditResult;

class ReadConfirmationHooks {

	/**
	 * Automatically set a page revision as read if user is creator of the
	 * revision
	 *
	 * @param WikiPage $wikiPage
	 * @param User $user
	 * @param string $summary
	 * @param int $flags
	 * @param RevisionStoreRecord $revision
	 * @param EditResult $editResult
	 * @return bool
	 */
	public static function onPageSaveComplete( WikiPage $wikiPage, User $user, string $summary,
		int $flags, RevisionStoreRecord $revision, EditResult $editResult ) {
		if ( $flags & EDIT_MINOR ) {
			return true;
		}
		global $wgNamespacesWithEnabledReadConfirmation;

		$iNS = $wikiPage->getTitle()->getNamespace();
		if ( !isset( $wgNamespacesWithEnabledReadConfirmation[$iNS] )
			|| $wgNamespacesWithEnabledReadConfirmation[$iNS] === false ) {
			return true;
		}

		$factory = MediaWikiServices::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
		if ( !$factory ) {
			return true;
		}
		$target = $factory->newFromTargetTitle( $wikiPage->getTitle() );
		if ( !$target ) {
			return true;
		}
		if ( $target->isUserAssigned( $user ) ) {
			return true;
		}

		$aRow = [
			'rc_rev_id' => $revision->getId(),
			'rc_user_id' => $user->getId()
		];

		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( 'bs_readconfirmation', $aRow );
		$aRow['rc_timestamp'] = wfTimestampNow();
		$dbw->insert( 'bs_readconfirmation', $aRow );

		return true;
	}

	/**
	 * Hook handler for
	 * NamespaceManager::getMetaFields
	 *
	 * @param array &$aMetaFields
	 * @return bool Always true
	 */
	public static function onNamespaceManager_getMetaFields( &$aMetaFields ) {
		$aMetaFields[] = [
				'name' => 'read_confirmation',
				'type' => 'boolean',
				'sortable' => true,
				'filter' => [
					'type' => 'boolean'
				],
				'label' => wfMessage( 'bs-readconfirmation-label-ns-manager' )->plain()
		];
		return true;
	}

	/**
	 * Hook handler for
	 * NamespaceManager::editNamespace
	 *
	 * @param array &$aNamespaceDefinition
	 * @param int &$iNs
	 * @param array $aAdditionalSettings
	 * @param bool $bUseInternalDefaults
	 * @return bool Always true
	 */
	public static function onNamespaceManager_editNamespace( &$aNamespaceDefinition, &$iNs,
		$aAdditionalSettings, $bUseInternalDefaults ) {
		if ( empty( $aNamespaceDefinition[$iNs] ) ) {
			$aNamespaceDefinition[$iNs] = [];
		}

		if ( isset( $aAdditionalSettings['read_confirmation'] ) ) {
			$aNamespaceDefinition[$iNs][ 'read_confirmation' ]
				= $aAdditionalSettings['read_confirmation'];
		} else {
			$aNamespaceDefinition[$iNs][ 'read_confirmation' ] = false;
		}
		return true;
	}

	/**
	 * Hook handler for
	 * NamespaceManager::WriteNamespaceConfiguration
	 *
	 * @param string &$sSaveContent
	 * @param string $sConstName
	 * @param int $iNs
	 * @param array $aDefinition
	 * @return bool Always true
	 */
	public static function onNamespaceManager_writeNamespaceConfiguration( &$sSaveContent, $sConstName,
		$iNs, $aDefinition ) {
		global $wgNamespacesWithEnabledReadConfirmation;
		if ( isset( $aDefinition[ 'read_confirmation' ] )
			&& $aDefinition['read_confirmation'] === true ) {
			$sSaveContent .= "\$GLOBALS['wgNamespacesWithEnabledReadConfirmation'][{$sConstName}] = true;\n";
		} elseif ( isset( $aDefinition[ 'read_confirmation' ] )
			&& $aDefinition['read_confirmation'] === false ) {
			return true;
		}
		if ( isset( $wgNamespacesWithEnabledReadConfirmation[$iNs] )
			&& $wgNamespacesWithEnabledReadConfirmation[$iNs] === true ) {
			$sSaveContent .= "\$GLOBALS['wgNamespacesWithEnabledReadConfirmation'][{$sConstName}] = true;\n";
		}
		return true;
	}

	/**
	 * Hook handler for
	 * BSApiNamespaceStoreMakeData
	 *
	 * @param array &$aResult
	 * @return bool Always true
	 */
	public static function onBSApiNamespaceStoreMakeData( &$aResult ) {
		global $wgNamespacesWithEnabledReadConfirmation;

		foreach ( $aResult as &$aSingleResult ) {
			$iNs = $aSingleResult['id'];
			$activated = false;
			if ( isset( $wgNamespacesWithEnabledReadConfirmation[$iNs] )
				&& $wgNamespacesWithEnabledReadConfirmation[$iNs] === true ) {
				$activated = true;
			}
			$aSingleResult[ 'read_confirmation' ] = [
				'value' => $activated,
				'disabled' => $aSingleResult['isTalkNS']
			];
		}

		return true;
	}
}
