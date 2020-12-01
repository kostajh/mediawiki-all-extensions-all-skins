<?php

namespace EUCopyrightCampaign\Api;

class GetRepresentatives extends \ApiBase {
	protected $country;
	protected $representatives;

	public function execute() {
		$this->readInParameters();
		$this->getSortedRepresentatives();
		$this->returnData();
	}

	/**
	 * Returns an array of allowed params for this API
	 *
	 * @return array
	 */
	protected function getAllowedParams() {
		return [
			'country' => [
				\ApiBase::PARAM_TYPE => 'string',
				\ApiBase::PARAM_REQUIRED => true
			]
		];
	}

	/**
	 * Sets local variables from params
	 */
	protected function readInParameters() {
		$this->country = $this->getParameter( 'country' );
	}

	/**
	 * Sorts and groups representatives
	 */
	protected function getSortedRepresentatives() {
		$rawData = $this->readIntoFile();

		$representatives = [];
		foreach ( $rawData as $representative ) {
			$representative->fullName =
				$representative->firstName . ' ' . $representative->lastName;

			$representatives[] = $representative;
		}

		shuffle( $representatives );
		$this->representatives = $representatives;
	}

	/**
	 * Gets array of all representatives available
	 * in the JSON file for given county
	 *
	 * @return array
	 */
	protected function readIntoFile() {
		$filename = __DIR__ . "/../../data/json/{$this->country}.json";
		if ( !file_exists( $filename ) ) {
			return [];
		}

		$raw = file_get_contents( $filename );
		$json = \FormatJson::decode( $raw );
		if ( !property_exists( $json, 'representatives' ) ) {
			return [];
		}
		return $json->representatives;
	}

	/**
	 * Retuns data to client
	 */
	protected function returnData() {
		$result = $this->getResult();

		$success = 1;
		if ( empty( $this->representatives ) ) {
			$success = 0;
		}
		$result->addValue( null, 'success', $success );
		$result->addValue( null, 'representatives', $this->representatives );
	}

}
