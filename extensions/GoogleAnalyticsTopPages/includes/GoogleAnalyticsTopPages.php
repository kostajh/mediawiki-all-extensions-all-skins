<?php
	class GoogleAnalyticsTopPages {
		public static function getData( WebRequest $request ) {
			global $wgGATPServiceAccountName, $wgGATPKeyFileLocation, $wgGATPAppName,
				$wgGATPProfileId, $wgGATPInterval;

			// create a new Google_Client object
			$client = new Google_Client();
			// set app name
			$client->setApplicationName( $wgGATPAppName );
			// Create the needed Google Analytics service object
			$service = new Google_Service_Analytics( $client );

			// check, if the client is already authenticated
			if ( $request->getSessionData( 'service_token' ) !== null ) {
				$client->setAccessToken( $request->getSessionData( 'service_token' ) );
			}

			// load the certificate key file
			$key = file_get_contents( $wgGATPKeyFileLocation );
			// create the service account credentials
			$cred = new Google_Auth_AssertionCredentials(
				$wgGATPServiceAccountName,
				[ 'https://www.googleapis.com/auth/analytics.readonly' ],
				$key
			);
			// set the credentials
			$client->setAssertionCredentials( $cred );
			if ( $client->getAuth()->isAccessTokenExpired() ) {
				// authenticate the service account
				$client->getAuth()->refreshTokenWithAssertion( $cred );
			}
			// set the service_token to the session for future requests
			$request->setSessionData( 'service_token', $client->getAccessToken() );

			// get the start and end time for the request
			$startTime = date( 'Y-m-d', time() - $wgGATPInterval * 86400 );
			$endTime = date( 'Y-m-d', time() );

			// do the request to Google Analytics
			$response = $service->data_ga->get(
				'ga:' . $wgGATPProfileId,
				$startTime,
				$endTime,
				'ga:pageviews,ga:exits',
				[
					'dimensions' => 'ga:pagePath',
					'max-results' => 10,
					'sort' => '-ga:pageviews'
				]
			);

			// our response array
			$titles = [];
			foreach ( $response['rows'] as $row ) {
				// try to get an actual title object of the returned pagePath
				$title = self::makeTitle( $row[0] );
				if ( $title ) {
					$titles[] = [
						'page_title' => $title->getText(),
						'page_visitors' => $row[1]
					];
				}
			}

			return $titles;
		}

		public static function makeTitle( $text ) {
			global $wgArticlePath;

			// try to make a Title object without modifications
			$title = Title::newFromText( $text );
			if ( $title instanceof Title && $title->exists() ) {
				return $title;
			}

			// remove $wgArticle
			$article = str_replace( '$1', '', $wgArticlePath );
			$text = str_replace( $article, '', $text );
			$title = Title::newFromText( $text );
			if ( $title instanceof Title && $title->exists() ) {
				return $title;
			}

			// try parse_url and parse_str (maybe it's an index.php?title=Page construct)
			$url = parse_url( $text );
			if ( isset( $url['query'] ) ) {
				parse_str( $url['query'], $query );
				if ( isset( $query['title'] ) ) {
					$title = Title::newFromText( $query['title'] );
					if ( $title instanceof Title && $title->exists() ) {
						return $title;
					}
				}
			}

			return false;
		}

		/**
		 * Render the content which should be added with the gatp-parser tag
		 *
		 * @param string $input Inout between the tags
		 * @param array $args Tag arguments
		 * @param Parser $parser The parser object which is parsing the page with the tag
		 * @param PPFrame $frame The parent frame object
		 *
		 * @return string
		 */
		public static function renderParserTag( $input, array $args, Parser $parser, PPFrame $frame ) {
			$dbw = wfGetDB( DB_REPLICA );
			$result = '';

			// get the actual list of top pages
			$res = $dbw->select(
				'page_google_stats',
				[
					'page_title',
					'page_visitors'
				],
				'',
				__METHOD__,
				[
					// FIXME: Should be configurable
					'LIMIT' => '10',
					'ORDER BY' => 'page_visitors DESC'
				]
			);

			// if there was an error or no rows, return empty string
			if ( !$res ) {
				return '';
			}

			// build the list of top pages
			$result .= Html::openElement( 'ol', [ 'class' => 'special' ] );
			foreach ( $res as $value ) {
				$title = Title::newFromText( $value->page_title );
				if ( $title->exists() ) {
					$result .= self::makeListItem( $title );
				}
			}
			$result .= Html::closeElement( 'ol' );

			return $result;
		}

		/**
		 * Creates a list item (<li>) with a link. Label and location comes from the title object.
		 *
		 * @param Title $title Title object to get information from
		 * @return string Formatted HTML
		 */
		private static function makeListItem( Title $title ) {
			return Html::rawElement( 'li', [],
				Linker::linkKnown( $title, $title->getText() ) );
		}
	}
