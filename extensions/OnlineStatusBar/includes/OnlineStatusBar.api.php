<?php
/**
 * Hooks for OnlineStatusBar api's
 *
 * @group Extensions
 */

use MediaWiki\MediaWikiServices;

class ApiOnlineStatus extends ApiQueryBase {
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'onlinestatus' );
	}

	public function execute() {
		$params = $this->extractRequestParams();
		$result = OnlineStatusBar::getUserInfoFromString( $params['user'] );

		// if user is IP and we track them
		if ( $result === false && User::isIP( $params['user'] )  ) {
			$result = OnlineStatusBar::getAnonFromString( $params['user'] );
		}

		if ( $result === false ) {
			$ret = 'unknown';
			$gender = 'unknown';
		} else {
			$ret = $result[0];
			$genderCache = MediaWikiServices::getInstance()->getGenderCache();
			$gender = $genderCache->getGenderOf( $result[1]->getName() );
		}

		$this->getResult()->addValue(
			null, $this->getModuleName(), array(
				'status' => $ret,
				'gender' => $gender
			)
		);
	}

	public function getAllowedParams() {
		// params
		return array(
			'user' => array (
					ApiBase::PARAM_TYPE => 'string',
					ApiBase::PARAM_REQUIRED => true
				),
		);
	}
  
	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=query&prop=onlinestatus&onlinestatususer=Petrb',
		);
	}
  
	/**
	* @see ApiBase::getExamplesMessages()
	*/
	protected function getExamplesMessages() {
		return array(
			'action=query&prop=onlinestatus'
				=> 'apihelp-query+onlinestatus-example',
		);
	}
}
