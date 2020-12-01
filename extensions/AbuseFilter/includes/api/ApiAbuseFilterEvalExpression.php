<?php

use MediaWiki\Extension\AbuseFilter\AbuseFilterServices;
use MediaWiki\Extension\AbuseFilter\VariableGenerator\VariableGenerator;

class ApiAbuseFilterEvalExpression extends ApiBase {
	/**
	 * @see ApiBase::execute()
	 */
	public function execute() {
		$afPermManager = AbuseFilterServices::getPermissionManager();
		// "Anti-DoS"
		if ( !$afPermManager->canViewPrivateFilters( $this->getUser() ) ) {
			$this->dieWithError( 'apierror-abusefilter-canteval', 'permissiondenied' );
		}

		$params = $this->extractRequestParams();

		$status = $this->evaluateExpression( $params['expression'] );
		if ( !$status->isGood() ) {
			$this->dieWithError( $status->getErrors()[0] );
		} else {
			$res = $status->getValue();
			$res = $params['prettyprint'] ? AbuseFilter::formatVar( $res ) : $res;
			$this->getResult()->addValue(
				null,
				$this->getModuleName(),
				ApiResult::addMetadataToResultVars( [ 'result' => $res ] )
			);
		}
	}

	/**
	 * @param string $expr
	 * @return Status
	 */
	private function evaluateExpression( string $expr ) : Status {
		$parser = AbuseFilterServices::getParserFactory()->newParser();
		if ( $parser->checkSyntax( $expr ) !== true ) {
			return Status::newFatal( 'abusefilter-tools-syntax-error' );
		}

		$vars = new AbuseFilterVariableHolder();
		// Generic vars are the only ones available
		$generator = new VariableGenerator( $vars );
		$vars = $generator->addGenericVars()->getVariableHolder();
		$vars->setVar( 'timestamp', wfTimestamp( TS_UNIX ) );
		$parser->setVariables( $vars );

		return Status::newGood( $parser->evaluateExpression( $expr ) );
	}

	/**
	 * @see ApiBase::getAllowedParams()
	 * @return array
	 */
	public function getAllowedParams() {
		return [
			'expression' => [
				ApiBase::PARAM_REQUIRED => true,
			],
			'prettyprint' => [
				ApiBase::PARAM_TYPE => 'boolean'
			]
		];
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 * @return array
	 */
	protected function getExamplesMessages() {
		return [
			'action=abusefilterevalexpression&expression=lcase("FOO")'
				=> 'apihelp-abusefilterevalexpression-example-1',
			'action=abusefilterevalexpression&expression=lcase("FOO")&prettyprint=1'
				=> 'apihelp-abusefilterevalexpression-example-2',
		];
	}
}
