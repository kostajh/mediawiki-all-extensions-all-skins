<?php

use MediaWiki\HookContainer\HookContainer;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\Hook\NewRevisionFromEditCompleteHook;
use MediaWiki\Page\Hook\RevisionFromEditCompleteHook;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\User\UserIdentity;

/**
 * Handle running FlaggedRevs's hooks
 * @author DannyS712
 */
class FlaggedRevsHookRunner implements
	NewRevisionFromEditCompleteHook,
	RevisionFromEditCompleteHook,
	FlaggedRevsFRGenericSubmitFormReadyHook,
	FlaggedRevsRevisionReviewFormAfterDoSubmitHook
{

	/** @var HookContainer */
	private $hookContainer;

	/**
	 * @param HookContainer $hookContainer
	 */
	public function __construct( HookContainer $hookContainer ) {
		$this->hookContainer = $hookContainer;
	}

	/**
	 * Convenience getter for static contexts
	 *
	 * See also core's Hooks::runner
	 *
	 * @return self
	 */
	public static function getRunner() {
		return new self(
			MediaWikiServices::getInstance()->getHookContainer()
		);
	}

	/**
	 * @note Core hook that is run, DEPRECATED since 1.35
	 *
	 * @param WikiPage $wikiPage WikiPage edited
	 * @param Revision $rev New revision
	 * @param int|bool $originalRevId If the edit restores or repeats an earlier revision (such as a
	 *   rollback or a null revision), the ID of that earlier revision. False otherwise.
	 *   (Used to be called $baseID.)
	 * @param User $user Editing user
	 * @param string[] &$tags Tags to apply to the edit and recent change. This is empty, and
	 *   replacement is ignored, in the case of import or page move.
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onNewRevisionFromEditComplete( $wikiPage, $rev, $originalRevId,
		$user, &$tags
	) {
		return $this->hookContainer->run(
			'NewRevisionFromEditComplete',
			[ $wikiPage, $rev, $originalRevId, $user, &$tags ]
		);
	}

	/**
	 * @note Core hook that is run
	 *
	 * @param WikiPage $wikiPage WikiPage edited
	 * @param RevisionRecord $rev New revision
	 * @param int|bool $originalRevId If the edit restores or repeats an earlier revision (such as a
	 *   rollback or a null revision), the ID of that earlier revision. False otherwise.
	 *   (Used to be called $baseID.)
	 * @param UserIdentity $user Editing user
	 * @param string[] &$tags Tags to apply to the edit and recent change. This is empty, and
	 *   replacement is ignored, in the case of import or page move
	 */
	public function onRevisionFromEditComplete( $wikiPage, $rev, $originalRevId,
		$user, &$tags
	) {
		$this->hookContainer->run(
			'RevisionFromEditComplete',
			[ $wikiPage, $rev, $originalRevId, $user, &$tags ]
		);
	}

	/**
	 * @param FRGenericSubmitForm $form
	 * @param string &$result
	 * @return bool|void
	 */
	public function onFlaggedRevsFRGenericSubmitFormReady( $form, &$result ) {
		return $this->hookContainer->run(
			'FlaggedRevsFRGenericSubmitFormReady',
			[ $form, &$result ]
		);
	}

	/**
	 * @param RevisionReviewForm $form
	 * @param string|bool $status
	 */
	public function onFlaggedRevsRevisionReviewFormAfterDoSubmit( $form, $status ) {
		$this->hookContainer->run(
			'FlaggedRevsRevisionReviewFormAfterDoSubmit',
			[ $form, $status ]
		);
	}

}
