<?php

namespace PasswordlessLogin;

use Config;
use DatabaseUpdater;
use MediaWiki\Auth\AuthenticationRequest;
use MediaWiki\MediaWikiServices;
use OutputPage;
use PasswordlessLogin\adapter\HTMLImageField;
use PasswordlessLogin\adapter\HTMLPlayStoreField;
use PasswordlessLogin\model\QRCodeRequest;
use Skin;

class Hooks {
	public static $addFrontendModules = false;

	/**
	 * Constructs the API URL for this server based on the provided config. The API URL may be
	 * overwritten by an extension configuration parameter.
	 *
	 * @param Config $mainConfig The MediaWiki main configuration object
	 * @param Config $config The extension configuration object
	 * @return string
	 */
	public static function constructApiUrl( Config $mainConfig, Config $config ) {
		if ( $config->has( 'PLDevApiUrl' ) ) {
			$apiUrl = $config->get( 'PLDevApiUrl' );
		} else {
			$apiUrl = $mainConfig->get( 'Server' ) . wfScript( 'api' );
		}

		return $apiUrl;
	}

	/**
	 * The LoadExtensionSchemaUpdates hook handler to add the required extension database tables.
	 *
	 * @param DatabaseUpdater|null $updater
	 * @return bool
	 */
	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater = null ) {
		$sql = __DIR__ . '/sql';
		$updater->addExtensionUpdate( [
			'addTable',
			'passwordlesslogin_devices',
			"$sql/devices.sql",
			true,
		] );
		$updater->addExtensionUpdate( [
			'addTable',
			'passwordlesslogin_challenges',
			"$sql/challenges.sql",
			true,
		] );

		return true;
	}

	/**
	 * The BeforePageDisplay hook handler to add necessary styles modules to the OutputPage when
	 * on the UserLogin page.
	 *
	 * @param OutputPage &$out
	 * @param Skin &$skin
	 */
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		if ( !self::$addFrontendModules ) {
			return;
		}
		if ( $out->getTitle()->isSpecial( 'Userlogin' ) ) {
			$config =
				MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'passwordless' );
			$out->addJsConfigVars( [
				'PLEnableApiVerification' => $config->get( 'PLEnableApiVerification' )
			] );
			$out->addModules( 'ext.PasswordlessLogin.login' );
			$out->addModuleStyles( 'ext.PasswordlessLogin.login.styles' );
		}
	}

	/**
	 * Converts the extension specific auth form fields into actual HTMLForm fields.
	 *
	 * @param array $requests
	 * @param array $fieldInfo
	 * @param array &$formDescriptor
	 * @param string $action one of the AuthManager::ACTION_* constants
	 */
	public static function onAuthChangeFormFields(
		array $requests, array $fieldInfo, array &$formDescriptor, $action
	) {
		/** @var QRCodeRequest $req */
		$req = AuthenticationRequest::getRequestByClass( $requests, QRCodeRequest::class, true );
		if ( !$req ) {
			return;
		}

		$formDescriptor['qrCode']['class'] = HTMLImageField::class;
		$formDescriptor['qrCode']['data-uri'] = $formDescriptor['qrCode']['default'];

		$formDescriptor['googleplay']['class'] = HTMLPlayStoreField::class;
	}
}
