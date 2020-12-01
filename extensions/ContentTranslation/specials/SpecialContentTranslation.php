<?php
/**
 * Contains the special page Special:ContentTranslation.
 *
 * @copyright See AUTHORS.txt
 * @license GPL-2.0-or-later
 */

use ContentTranslation\PreferenceHelper;
use ContentTranslation\SiteMapper;
use ContentTranslation\Translation;
use ContentTranslation\TranslationWork;
use ContentTranslation\Translator;
use MediaWiki\MediaWikiServices;

/**
 * Implements the core of the Content Translation extension:
 * a special page that shows Content Translation user interface.
 */
class SpecialContentTranslation extends ContentTranslationSpecialPage {
	public function __construct() {
		parent::__construct( 'ContentTranslation' );
	}

	public function getDescription() {
		return $this->msg( 'cx' )->text();
	}

	public function isListed() {
		return PreferenceHelper::isEnabledForUser( $this->getUser() );
	}

	public function enableCXBetaFeature() {
		$out = $this->getOutput();
		$out->addJsConfigVars( 'wgContentTranslationBetaFeatureEnabled', true );

		$user = $this->getUser();
		// Promise to persist the setting post-send
		DeferredUpdates::addCallableUpdate( function () use ( $user ) {
			$optionsManager = MediaWikiServices::getInstance()->getUserOptionsManager();
			$user = $user->getInstanceForUpdate();
			$optionsManager->setOption( $user, 'cx', '1' );
			$optionsManager->saveOptions( $user );
		} );
	}

	public function isValidCampaign( $campaign ) {
		global $wgContentTranslationCampaigns;

		if ( $this->getUser()->isAnon() ) {
			// Campaigns are only for logged in users.
			return false;
		}
		return $campaign !== null
			&& isset( $wgContentTranslationCampaigns[$campaign] )
			&& $wgContentTranslationCampaigns[$campaign];
	}

	/**
	 * Check if the request has a token to use CX.
	 * With a valid cx token override beta feature settings.
	 * @return bool
	 */
	private function hasValidToken() {
		global $wgContentTranslationTranslateInTarget;

		$request = $this->getRequest();

		if ( $this->getUser()->isAnon() ) {
			// Tokens are valid only for logged in users.
			return false;
		}

		$title = $request->getVal( 'page' );

		if ( $title === null ) {
			return false;
		}

		// PHP mangles spaces so that foo%20bar is converted to foo_bar and that $_COOKIE['foo bar']
		// *does not* work. Go figure. It also mangles periods, so that foo.bar is converted to
		// foo_bar, but that *does* work because MediaWiki's getCookie transparently maps periods to
		// underscores. If there is any further bugs reported about this, please use base64.
		$title = strtr( $title, ' ', '_' );

		$from = $request->getVal( 'from' );
		$to = $request->getVal( 'to' );
		if ( $from === null || $to === null ) {
			return false;
		}
		$cookieName = implode( '_', [ 'cx', $title, $from, $to ] );

		$hasToken = $request->getCookie( $cookieName, '' ) !== null;

		// Since we can only publish to the current wiki, enforce that the target language matches
		// the wiki we are currently on. If not, redirect the user back to dashboard, where he can
		// start again with parameters filled (and redirected to the correct wiki).
		if ( $wgContentTranslationTranslateInTarget ) {
			$tokenIsValid = $to === SiteMapper::getCurrentLanguageCode();
			return $hasToken && $tokenIsValid;
		}

		// For development (single instance) use, there is no need to validate the token, because
		// we don't redirect.
		return $hasToken;
	}

	/**
	 * @inheritDoc
	 */
	protected function canUserProceed() {
		$hasValidToken = $this->hasValidToken();
		$campaign = $this->getRequest()->getVal( 'campaign' );
		$isCampaign = $this->isValidCampaign( $campaign );

		// Direct access, isListed only affects Special:SpecialPages
		if ( !PreferenceHelper::isEnabledForUser( $this->getUser() ) ) {
			if ( $hasValidToken || $isCampaign ) {
				// User has a token or a valid campaign param.
				// Enable cx for the user in this wiki.
				$this->enableCXBetaFeature();
			} else {
				if ( $campaign ) {
					// Show login page if the URL has campaign parameter
					$this->requireLogin();
				}
				// Invalid or missing campaign param
				$this->getOutput()->showErrorPage(
					'cx',
					'cx-specialpage-enable-betafeature',
					[
						SpecialPage::getTitleFor( 'ContentTranslation' )
							->getCanonicalURL( [ 'campaign' => 'specialcx' ] )
					]
				);
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns true if user requested to open the translation view,
	 * false if CX dashboard is requested.
	 *
	 * @return bool
	 */
	protected function onTranslationView() {
		return $this->hasValidToken();
	}

	protected function isVueDashboard() {
		$config = $this->getConfig();
		return $config->get( 'ContentTranslationVueDashboard' ) &&
			!$this->onTranslationView();
	}

	/**
	 * @inheritDoc
	 */
	protected function initModules() {
		global $wgContentTranslationTranslateInTarget;

		$out = $this->getOutput();

		if ( $this->onTranslationView() ) {
			$initModule = 'mw.cx.init.legacy';
			if ( $this->shouldUseNewVersion() ) {
				// Change init module to use CX2
				$initModule = 'mw.cx.init';
			}
			$out->addModules( $initModule );
			// If Wikibase is installed, load the module for linking
			// the published article with the source article
			if ( $wgContentTranslationTranslateInTarget
				&& ExtensionRegistry::getInstance()->isLoaded( 'WikibaseClient' ) ) {
				$out->addModules( 'ext.cx.wikibase.link' );
			}
		} else {
			if ( $this->isVueDashboard() ) {
				$out->addModules( 'mw.cx3' );
			} else {
				$out->addModules( 'ext.cx.dashboard' );
				$out->addMeta( 'viewport', 'width=device-width, initial-scale=1' );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function addJsConfigVars() {
		global $wgContentTranslationUnmodifiedMTThresholdForPublish,
			$wgContentTranslationCampaigns,
			$wgContentTranslationExcludedNamespaces,
			$wgContentTranslationPublishRequirements,
			$wgContentTranslationEnableSuggestions,
			$wgContentTranslationTargetNamespace,
			$wgRecommendToolAPIURL,
			$wgContentTranslationEnableMT;

		$out = $this->getOutput();

		if ( $this->onTranslationView() ) {
			$version = $this->shouldUseNewVersion() ? 2 : 1;

			$out->addJsConfigVars( [
				'wgContentTranslationUnmodifiedMTThresholdForPublish' =>
					$wgContentTranslationUnmodifiedMTThresholdForPublish,
				'wgContentTranslationCampaigns' => $wgContentTranslationCampaigns,
				'wgContentTranslationPublishRequirements' => $wgContentTranslationPublishRequirements,
				'wgContentTranslationVersion' => $version,
				'wgContentTranslationEnableMT' => $wgContentTranslationEnableMT
			] );

			if ( $version === 1 ) {
				$out->addJsConfigVars(
					'wgContentTranslationTargetNamespace',
					$wgContentTranslationTargetNamespace
				);
			}
		} else {
			$out->addJsConfigVars( [
				'wgContentTranslationEnableSuggestions' => $wgContentTranslationEnableSuggestions,
				'wgRecommendToolAPIURL' => $wgRecommendToolAPIURL,
				'wgContentTranslationExcludedNamespaces' => $wgContentTranslationExcludedNamespaces
			] );
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function afterExecute( $subPage ) {
		$campaign = $this->getRequest()->getVal( 'campaign' );
		$user = $this->getUser();

		// Anonymous users cannot have global preferences
		if ( $campaign === null || $user->isAnon() ) {
			return;
		}

		$persistentEntrypointCampaigns = [ 'contributions-page', 'contributionsmenu' ];
		if ( PreferenceHelper::getGlobalPreference( $user, 'cx-entrypoint-fd-status' ) !== 'shown' ) {
			if ( in_array( $campaign, $persistentEntrypointCampaigns ) ) {
				// The user accessed CX using a persistent invitation.
				// It means, user is aware of the entrypoint. No need to show the feature discovery again
				PreferenceHelper::setGlobalPreference( $user, 'cx-entrypoint-fd-status', 'shown' );
			} else {
				// The user accessed CX using a non-persistent invitation.
				// Show a one-time indicator to tell user that they can access CX using persistent entrypoints
				// Set a global preference that the feature discovery is set for the user
				// This preference has three possible values: `pending`, `shown`, 'notshown'
				PreferenceHelper::setGlobalPreference( $user, 'cx-entrypoint-fd-status', 'pending' );
			}
		}
	}

	/**
	 * Determine whether CX2 should be used.
	 *
	 * @return bool True if we should ship version 2 of Content Translation
	 */
	private function shouldUseNewVersion() {
		$request = $this->getRequest();
		$translator = new Translator( $this->getUser() );
		$work = new TranslationWork(
			$request->getVal( 'page' ),
			$request->getVal( 'from' ),
			$request->getVal( 'to' )
		);
		$translation = Translation::findForTranslator( $work, $translator );

		if ( !$translation ) {
			return true;
		}

		if ( $translation->translation['status'] === 'deleted' ) {
			return true;
		}

		return $translation->translation['cxVersion'] === 2;
	}
}
