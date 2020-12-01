<?php

/*
 * This is a custom API handler to expand templates with a given
 * list of parameters.
 *
 * @author Clark Verbrugge
 * @Licence CC BY-SA 3.0
 */

use MediaWiki\MediaWikiServices;

class ApiDebugTemplates extends ApiBase {
	public function execute() {
		$params = $this->extractRequestParams();

		$title_obj = Title::newFromText( $params[ 'title' ] );

		if ( !$title_obj || $title_obj->isExternal() ) {
			$this->dieWithError( [ 'apierror-invalidtitle', wfEscapeWikiText( $params['title'] ) ] );
		}

		// The frame field is a JSON-encoded object
		$frame = FormatJson::parse( $params[ 'frame' ], FormatJson::FORCE_ASSOC );

		$result = $this->getResult();

		if ( $frame->isGood() ) {
			$options = ParserOptions::newFromContext( $this->getContext() );
			$parsed = MediaWikiServices::getInstance()->getParser()->preprocess( $params[ 'text' ],
				$title_obj,
				$options,
				null,
				$frame->getValue() );
			$this->getResult()->addValue( null, $this->getModuleName(),
				 [ 'result' => $parsed ] );
		} else {
			$this->getErrorFormatter()->addMessagesFromStatus( $this->getModuleName(), $frame );
		}
		return true;
	}

	/**
	 * Force the existence of our parameters.
	 *
	 * @return object Array of parameter to arrays
	 */
	public function getAllowedParams() {
		return array_merge( parent::getAllowedParams(), [
				'text' => [
					ApiBase::PARAM_TYPE => 'string',
					ApiBase::PARAM_REQUIRED => true
				],
				'frame' => [
					ApiBase::PARAM_TYPE => 'string',
					ApiBase::PARAM_DFLT => '{}'
					// ApiBase::PARAM_REQUIRED => true
				],
				'title' => [
					ApiBase::PARAM_DFLT => 'API'
				],
			] );
	}

	/**
	 * Provide an example of usage
	 *
	 * @return object Array showing an example use and help text
	 */
	public function getExamplesMessages() {
		return [
			'action=expandframe&text={{{a}}}&frame={"a":"b"}'
			=> 'apihelp-expandframe-example-1'
		];
	}
}
