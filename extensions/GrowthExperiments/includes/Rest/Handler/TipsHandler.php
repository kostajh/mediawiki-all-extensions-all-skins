<?php

namespace GrowthExperiments\Rest\Handler;

use ApiBase;
use DerivativeContext;
use GrowthExperiments\HelpPanel\Tips\TipsAssembler;
use GrowthExperiments\NewcomerTasks\ConfigurationLoader\ConfigurationLoader;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MWException;
use RequestContext;

/**
 * Handle incoming requests to obtain tips for a skin, editor, task type id,
 * and language. Returns a JSON response that can be placed into the suggested
 * edits guidance screen in the help panel.
 */
class TipsHandler extends SimpleHandler {

	private const MAX_CACHE_AGE_SECONDS = 3600;

	/**
	 * @var TipsAssembler
	 */
	private $tipsAssembler;
	/**
	 * @var ConfigurationLoader
	 */
	private $configurationLoader;

	/**
	 * @param TipsAssembler $tipsAssembler
	 * @param ConfigurationLoader $configurationLoader
	 */
	public function __construct(
		TipsAssembler $tipsAssembler, ConfigurationLoader $configurationLoader
	) {
		$this->tipsAssembler = $tipsAssembler;
		$this->configurationLoader = $configurationLoader;
	}

	/**
	 * @param string $skin
	 * @param string $editor
	 * @param string $tasktypeid
	 * @param string $uselang
	 * @return Response
	 * @throws MWException
	 */
	public function run( string $skin, string $editor, string $tasktypeid, string $uselang
	) {
		$context = new DerivativeContext( RequestContext::getMain() );
		if ( $uselang ) {
			$context->setLanguage( $uselang );
		}
		$this->tipsAssembler->setMessageLocalizer( $context );
		$taskTypes = $this->configurationLoader->getTaskTypes();
		$response = $this->getResponseFactory()->createJson(
			$this->tipsAssembler->getTips(
				$skin,
				$editor,
				$taskTypes,
				$tasktypeid,
				$context->getLanguage()->getDir()
			)
		);
		$response->setHeader( 'Cache-Control', 'public, max-age=' . self::MAX_CACHE_AGE_SECONDS );
		return $response;
	}

	public function needsWriteAccess() {
		return false;
	}

	/** @inheritDoc */
	public function getParamSettings() {
		return [
			'skin' => [
				self::PARAM_SOURCE => 'path',
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => 'string',
			],
			'editor' => [
				self::PARAM_SOURCE => 'path',
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => 'string',
			],
			'tasktypeid' => [
				self::PARAM_SOURCE => 'path',
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => array_keys(
					$this->configurationLoader->getTaskTypes()
				)
			],
			'uselang' => [
				self::PARAM_SOURCE => 'path',
				ApiBase::PARAM_REQUIRED => true,
			]
		];
	}
}
