<?php

class ApiGAEJobQueue extends ApiBase {

	public function execute() {
		$req = $this->getRequest();
		if ( $req->getHeader( 'X-AppEngine-TaskName' ) === false ) {
			// This header will only be present if the request is via
			// the TaskHandler system. Deny any external requests to avoid
			// malicious code from being run.
			$this->dieUsage( 'Missing "X-AppEngine-TaskName" header', 'externalrequest' );
		}

		$params = $this->extractRequestParams();
		$title = Title::makeTitle( $params['namespace'], $params['titletext'] );
		$job = Job::factory(
			$params['type'],
			$title,
			unserialize( $params['params'] )
		);
		$job->run(); // @todo Implement error handling

		$this->getResult()->addValue(
			null,
			$this->getModuleName(),
			array( 'result' => 'success' ) + $params
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Internal API module to run the JobQueue. External usage is not allowed.';
	}

	public function getAllowedParams() {
		return array(
			'namespace' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'titletext' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'type' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'params' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'namespace' => 'Numerical namespace id',
			'titletext' => 'Text of title without namespace',
			'type' => 'Job type',
			'params' => 'PHP serialized parameters for the job'
		);
	}

	public function mustBePosted() {
		return true;
	}

	public function isWriteMode() {
		return true;
	}
}
