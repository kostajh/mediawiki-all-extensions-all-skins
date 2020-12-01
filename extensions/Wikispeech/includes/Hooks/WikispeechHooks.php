<?php

namespace MediaWiki\Wikispeech\Hooks;

/**
 * @file
 * @ingroup Extensions
 * @license GPL-2.0-or-later
 */

use Action;
use ApiBase;
use ApiMain;
use ApiMessage;
use DatabaseUpdater;
use Exception;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Wikispeech\SpeechoidConnector;
use MediaWiki\Wikispeech\VoiceHandler;
use OutputPage;
use Skin;
use SkinTemplate;
use User;

class WikispeechHooks {

	/**
	 * Investigates whether or not configuration is valid.
	 *
	 * Writes all invalid configuration entries to the log.
	 *
	 * @since 0.1.3
	 * @return bool true if all configuration passes validation
	 */
	private static function validateConfiguration() {
		$success = true;
		$config = MediaWikiServices::getInstance()
			->getConfigFactory()
			->makeConfig( 'wikispeech' );

		$speechoidUrl = $config->get( 'WikispeechSpeechoidUrl' );
		if ( !filter_var( $speechoidUrl, FILTER_VALIDATE_URL ) ) {
			LoggerFactory::getInstance( 'Wikispeech' )
				->warning( __METHOD__ . ': Configuration value for ' .
					'\'WikispeechSpeechoidUrl\' is not a valid URL: {value}',
					[ 'value' => $speechoidUrl ]
			);
			$success = false;
		}
		$speechoidResponseTimeoutSeconds = $config
			->get( 'WikispeechSpeechoidResponseTimeoutSeconds' );
		if ( $speechoidResponseTimeoutSeconds &&
			!is_int( $speechoidResponseTimeoutSeconds ) ) {
			LoggerFactory::getInstance( 'Wikispeech' )
				->warning( __METHOD__ . ': Configuration value ' .
					'\'WikispeechSpeechoidResponseTimeoutSeconds\' ' .
					'is not a falsy or integer value.'
				);
			$success = false;
		}

		$utteranceTimeToLiveDays = $config
			->get( 'WikispeechUtteranceTimeToLiveDays' );
		if ( $utteranceTimeToLiveDays === null ) {
			LoggerFactory::getInstance( 'Wikispeech' )
				->warning( __METHOD__ . ': Configuration value for ' .
					'\'WikispeechUtteranceTimeToLiveDays\' is missing.'
			);
			$success = false;
		}
		$utteranceTimeToLiveDays = intval( $utteranceTimeToLiveDays );
		if ( $utteranceTimeToLiveDays < 0 ) {
			LoggerFactory::getInstance( 'Wikispeech' )
				->warning( __METHOD__ . ': Configuration value for ' .
					'\'WikispeechUtteranceTimeToLiveDays\' must not be negative.'
			);
			$success = false;
		}

		$minimumMinutesBetweenFlushExpiredUtterancesJobs = $config
			->get( 'WikispeechMinimumMinutesBetweenFlushExpiredUtterancesJobs' );
		if ( $minimumMinutesBetweenFlushExpiredUtterancesJobs === null ) {
			LoggerFactory::getInstance( 'Wikispeech' )
				->warning( __METHOD__ . ': Configuration value for ' .
					'\'WikispeechMinimumMinutesBetweenFlushExpiredUtterancesJobs\' ' .
					'is missing.'
			);
			$success = false;
		}
		$minimumMinutesBetweenFlushExpiredUtterancesJobs = intval(
			$minimumMinutesBetweenFlushExpiredUtterancesJobs
		);
		if ( $minimumMinutesBetweenFlushExpiredUtterancesJobs < 0 ) {
			LoggerFactory::getInstance( 'Wikispeech' )
				->warning( __METHOD__ . ': Configuration value for ' .
					'\'WikispeechMinimumMinutesBetweenFlushExpiredUtterancesJobs\'' .
					' must not be negative.'
			);
			$success = false;
		}

		$fileBackendName = $config->get( 'WikispeechUtteranceFileBackendName' );
		if ( $fileBackendName === null ) {
			LoggerFactory::getInstance( 'Wikispeech' )
				->warning( __METHOD__ . ':  Configuration value ' .
					'\'WikispeechUtteranceFileBackendName\' is missing.'
			);
			// This is not a failure.
			// It will fall back on default, but admin should be aware.
		} elseif ( !is_string( $fileBackendName ) ) {
			LoggerFactory::getInstance( 'Wikispeech' )
				->warning( __METHOD__ . ': Configuration value ' .
					'\'WikispeechUtteranceFileBackendName\' is not a string value.'
			);
			$success = false;
		}

		$fileBackendContainerName = $config
			->get( 'WikispeechUtteranceFileBackendContainerName' );
		if ( $fileBackendContainerName === null ) {
			LoggerFactory::getInstance( 'Wikispeech' )
				->warning( __METHOD__ . ': Configuration value ' .
					'\'WikispeechUtteranceFileBackendContainerName\' is missing.'
			);
			$success = false;
		} elseif ( !is_string( $fileBackendContainerName ) ) {
			LoggerFactory::getInstance( 'Wikispeech' )
				->warning( __METHOD__ . ': Configuration value ' .
					'\'WikispeechUtteranceFileStore\' is not a string value.'
			);
			$success = false;
		}

		return $success;
	}

	/**
	 * Hook for BeforePageDisplay.
	 *
	 * Enables JavaScript.
	 *
	 * @param OutputPage $out The OutputPage object.
	 * @param Skin $skin Skin object that will be used to generate the page,
	 *  added in MediaWiki 1.13.
	 */
	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		if ( !self::shouldWikispeechRun( $out ) ) {
			return;
		}
		$showPlayer = MediaWikiServices::getInstance()
			->getUserOptionsLookup()
			->getOption( $out->getUser(), 'wikispeechShowPlayer' );
		if ( $showPlayer ) {
			LoggerFactory::getInstance( 'Wikispeech' )->info(
				__METHOD__ . ': Loading player.'
			);
			$out->addModules( [ 'ext.wikispeech' ] );
		} else {
			LoggerFactory::getInstance( 'Wikispeech' )->info(
				__METHOD__ . ': Adding option to load player.'
			);
			$out->addModules( [ 'ext.wikispeech.loader' ] );
		}
		$config = MediaWikiServices::getInstance()
			->getConfigFactory()
			->makeConfig( 'wikispeech' );
		$out->addJsConfigVars( [
			'wgWikispeechKeyboardShortcuts' => $config->get( 'WikispeechKeyboardShortcuts' ),
			'wgWikispeechContentSelector' => $config->get( 'WikispeechContentSelector' ),
			'wgWikispeechSkipBackRewindsThreshold' => $config->get( 'WikispeechSkipBackRewindsThreshold' ),
			'wgWikispeechHelpPage' => $config->get( 'WikispeechHelpPage' ),
			'wgWikispeechFeedbackPage' => $config->get( 'WikispeechFeedbackPage' )
		] );
	}

	/**
	 * Checks if Wikispeech should run.
	 *
	 * Returns true if all of the following are true:
	 * * User has enabled Wikispeech in the settings
	 * * User is allowed to listen to pages
	 * * Wikispeech configuration is valid
	 * * Wikispeech is enabled for the page's namespace
	 * * Revision is current
	 * * Page's language is enabled for Wikispeech
	 * * The action is "view"
	 *
	 * @since 0.1.5
	 * @param OutputPage $out
	 * @return bool
	 */
	private static function shouldWikispeechRun( OutputPage $out ) {
		$logger = LoggerFactory::getInstance( 'Wikispeech' );

		$wikispeechEnabled = MediaWikiServices::getInstance()
			->getUserOptionsLookup()
			->getOption( $out->getUser(), 'wikispeechEnable' );
		if ( !$wikispeechEnabled ) {
			$logger->info( __METHOD__ . ': Not loading Wikispeech: disabled by user.' );
			return false;
		}

		$userIsAllowed = MediaWikiServices::getInstance()
			->getPermissionManager()
			->userHasRight( $out->getUser(), 'wikispeech-listen' );
		if ( !$userIsAllowed ) {
			$logger->info( __METHOD__ . ': Not loading Wikispeech: user lacks right "wikispeech-listen".' );
			return false;
		}

		if ( !self::validateConfiguration() ) {
			$logger->info( __METHOD__ . ': Not loading Wikispeech: config invalid.' );
			return false;
		}

		$namespace = $out->getTitle()->getNamespace();
		$config = MediaWikiServices::getInstance()
			->getConfigFactory()
			->makeConfig( 'wikispeech' );
		$validNamespaces = $config->get( 'WikispeechNamespaces' );
		if ( !in_array( $namespace, $validNamespaces ) ) {
			$logger->info( __METHOD__ . ': Not loading Wikispeech: unsupported namespace.' );
			return false;
		}

		if ( !$out->isRevisionCurrent() ) {
			$logger->info( __METHOD__ . ': Not loading Wikispeech: non-current revision.' );
			return false;
		}

		$pageContentLanguage = null;
		if ( $namespace == NS_MEDIA || $namespace < 0 ) {
			// cannot get pageContentLanguage of e.g. a Special page or a
			// virtual page. These should all use the interface language.
			$pageContentLanguage = $out->getLanguage();
		} else {
			$pageContentLanguage = $out->getWikiPage()->getTitle()->getPageLanguage();
		}
		$validLanguages = array_keys( $config->get( 'WikispeechVoices' ) );
		if ( !in_array( $pageContentLanguage->getCode(), $validLanguages ) ) {
			$logger->info( __METHOD__ . ': Not loading Wikispeech: unsupported language.' );
			return false;
		}

		$actionName = Action::getActionName( $out );
		if ( $actionName !== 'view' ) {
			$logger->info( __METHOD__ . ': Not loading Wikispeech: unsupported action.' );
			return false;
		}

		return true;
	}

	/**
	 * Hook for ApiBeforeMain.
	 *
	 * Calls configuration validation for logging purposes on API calls,
	 * but doesn't stop the use of the API due to invalid configuration.
	 * Generally a user would not call the API at this point as the module
	 * wouldn't actually have been added in onBeforePageDisplay.
	 *
	 * @since 0.1.3
	 * @param ApiMain &$main The ApiMain instance being used.
	 */
	public static function onApiBeforeMain( &$main ) {
		self::validateConfiguration();
	}

	/**
	 * Conditionally register static configuration variables for the
	 * ext.wikispeech module only if that module is loaded.
	 *
	 * @param array &$vars The array of static configuration variables.
	 */
	public static function onResourceLoaderGetConfigVars( &$vars ) {
		global $wgWikispeechSpeechoidUrl;
		$vars[ 'wgWikispeechSpeechoidUrl' ] =
			$wgWikispeechSpeechoidUrl;
		global $wgWikispeechNamespaces;
		$vars['wgWikispeechNamespaces'] =
			$wgWikispeechNamespaces;
	}

	/**
	 * Add Wikispeech options to Special:Preferences.
	 *
	 * @param User $user current User object.
	 * @param array &$preferences Preferences array.
	 */
	public static function onGetPreferences( $user, &$preferences ) {
		$logger = LoggerFactory::getInstance( 'Wikispeech' );
		$config = MediaWikiServices::getInstance()
			->getConfigFactory()
			->makeConfig( 'wikispeech' );
		$speechoidConnector = new SpeechoidConnector( $config );
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		$voiceHandler = new VoiceHandler(
			$logger,
			$config,
			$speechoidConnector,
			$cache
		);
		$preferences['wikispeechEnable'] = [
			'type' => 'toggle',
			'label-message' => 'prefs-wikispeech-enable',
			'section' => 'wikispeech'
		];
		$preferences['wikispeechShowPlayer'] = [
			'type' => 'toggle',
			'label-message' => 'prefs-wikispeech-show-player',
			'section' => 'wikispeech'
		];
		self::addVoicePreferences( $preferences, $voiceHandler );
		self::addSpeechRatePreferences( $preferences );
	}

	/**
	 * Add preferences for selecting voices per language.
	 *
	 * @param array &$preferences Preferences array.
	 * @param VoiceHandler $voiceHandler
	 */
	private static function addVoicePreferences( &$preferences, $voiceHandler ) {
		global $wgWikispeechVoices;
		foreach ( $wgWikispeechVoices as $language => $voices ) {
			$languageKey = 'wikispeechVoice' . ucfirst( $language );
			$mwLanguage = MediaWikiServices::getInstance()->getLanguageFactory()->getLanguage( 'en' );
			$languageName = $mwLanguage->getVariantname( $language );
			$options = [];
			try {
				$defaultVoice = $voiceHandler->getDefaultVoice( $language );
				$options["Default ($defaultVoice)"] = '';
			} catch ( Exception $e ) {
				$options["Default"] = '';
			}
			foreach ( $voices as $voice ) {
				$options[$voice] = $voice;
			}
			$preferences[$languageKey] = [
				'type' => 'select',
				'label' => $languageName,
				'section' => 'wikispeech/wikispeech-voice',
				'options' => $options
			];
		}
	}

	/**
	 * Add preferences for selecting speech rate.
	 *
	 * @param array &$preferences Preferences array.
	 */
	private static function addSpeechRatePreferences( &$preferences ) {
		$options = [
			'400%' => 4.0,
			'200%' => 2.0,
			'150%' => 1.5,
			'100%' => 1.0,
			'75%' => 0.75,
			'50%' => 0.5
		];
		$preferences['wikispeechSpeechRate'] = [
			'type' => 'select',
			'label-message' => 'prefs-wikispeech-speech-rate',
			'section' => 'wikispeech/wikispeech-voice',
			'options' => $options
		];
	}

	/**
	 * Check if the user is allowed to use a API module.
	 *
	 * @since 0.1.3
	 * @param ApiBase $module
	 * @param User $user
	 * @param ApiMessage &$message
	 * @return bool
	 */
	public static function onApiCheckCanExecute( $module, $user, &$message ) {
		if (
			$module->getModuleName() == 'wikispeech-listen' &&
			!MediaWikiServices::getInstance()->getPermissionManager()
				->userHasRight( $user, 'wikispeech-listen' )
		) {
			$message = ApiMessage::create(
				'apierror-wikispeech-listen-notallowed'
			);
			return false;
		}
		return true;
	}

	/**
	 * Add tab for activating Wikispeech player.
	 *
	 * @since 0.1.5
	 * @param SkinTemplate $skinTemplate The skin template on which
	 *  the UI is built.
	 * @param array &$links Navigation links.
	 */
	public static function onSkinTemplateNavigation( SkinTemplate $skinTemplate, array &$links ) {
		$out = $skinTemplate->getOutput();
		if ( self::shouldWikispeechRun( $out ) ) {
			$links['actions']['listen'] = [
				'class' => 'ext-wikispeech-listen',
				'text' => $skinTemplate->msg( 'wikispeech-listen' )->text(),
				'href' => 'javascript:void(0)'
			];
		}
	}

	/**
	 * Creates utterance database tables.
	 *
	 * @since 0.1.5
	 * @param DatabaseUpdater $updater
	 */
	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		$updater->addExtensionTable(
			'wikispeech_utterance',
			__DIR__ . '/../../sql/wikispeech_utterance_v1.sql'
		);
	}
}
