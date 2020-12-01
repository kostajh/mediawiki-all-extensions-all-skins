<?php

namespace MediaWiki\Extension\FlexiSkin\Api;

use ApiUsageException;
use FormatJson;
use MediaWiki\Extension\FlexiSkin\FlexiSkin;
use MediaWiki\Extension\FlexiSkin\FlexiSkinManager;
use MediaWiki\Extension\FlexiSkin\IFlexiSkinSubscriber;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\CommonUserInterface\LessVars;

class Preview extends FlexiSkinApiBase {
	public function execute() {
		$this->returnResult(
			$this->getLoadData()
		);
	}

	/**
	 *
	 * @return array
	 */
	protected function getAllowedParams() {
		return [
			'config' => [
				static::PARAM_TYPE => 'string',
				static::PARAM_REQUIRED => true,
				static::PARAM_HELP_MSG => 'apihelp-flexiskin-preview-param-config',
			]
		];
	}

	protected function returnResult( $data ) {
		$result = $this->getResult();
		$result->addValue( null, 'loadData', $data );
	}

	/**
	 * @return array
	 * @throws ApiUsageException
	 */
	private function getLoadData() {
		$this->checkPermissions();

		$vars = [];
		$config = FormatJson::decode( $this->getParameter( 'config' ), 1 );
		$flexiSkin = new FlexiSkin( null, 'preview', $config );

		/** @var FlexiSkinManager $flexiSkinManager */
		$flexiSkinManager = MediaWikiServices::getInstance()->get( 'FlexiSkinManager' );
		foreach ( $flexiSkinManager->getPlugins() as $pluginKey => $plugin ) {
			$vars += $plugin->getLessVars( $flexiSkin );
		}
		$vars = $this->filterOutDefaults( $vars );

		$subscribingModules = [];
		/**
		 * @var string $subKey
		 * @var IFlexiSkinSubscriber $subscriber
		 */
		foreach ( $flexiSkinManager->getSubscribers() as $subKey => $subscriber ) {
			$subscribingModules = array_merge(
				$subscribingModules,
				$subscriber->getAffectedRLModules()
			);
		}

		return [
			'vars' => $vars,
			'modules' => $subscribingModules
		];
	}

	/**
	 * @param array $vars
	 * @return array
	 */
	private function filterOutDefaults( $vars ) {
		$lessVars = LessVars::getInstance()->getAllVars();
		return array_filter( $vars, function ( $value, $var ) use ( $lessVars ) {
			if ( !isset( $lessVars[$var] ) ) {
				return true;
			}
			if ( $lessVars[$var] === $value ) {
				return false;
			}

			return true;
		}, ARRAY_FILTER_USE_BOTH );
	}
}
