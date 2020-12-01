<?php
	class ApiGooglePageStatsUpdate extends ApiBase {
		public function execute() {
			global $wgGATPProtectAPI, $wgSecretKey;

			$apiResult = $this->getResult();
			$dbw = wfGetDB( DB_MASTER );
			$params = $this->extractRequestParams();
			$success = 'false';
			$tableName = 'page_google_stats';

			// check, if the api is protected and if the key is correct
			if (
				$wgGATPProtectAPI &&
				$params['key'] !== $wgSecretKey
			) {
				// false key
				$text = 'Wrong key, try 42.';
			} else {
				// delete all rows to insert a complete new set of data
				$del = $dbw->delete(
					$tableName,
					'*'
				);

				if ( $del ) {
					// get the actual data from Google Analytics
					$data = GoogleAnalyticsTopPages::getData( $this->getRequest() );

					// insert the new data
					$res = $dbw->insert(
						$tableName,
						$data
					);
					if ( $res ) {
						$success = 'true';
					}
				}
				$text = $dbw->lastError();
			}
			// build result array
			$r = [
				'success' => $success,
				'text' => $text
			];
			// add result to API output
			$apiResult->addValue( null, $this->getModuleName(), $r );
		}

		public function mustBePosted() {
			return true;
		}

		public function getAllowedParams() {
			return [
				'key' => [
					ApiBase::PARAM_TYPE => 'string',
				],
			];
		}
	}
