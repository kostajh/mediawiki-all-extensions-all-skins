<?php
/**
 * RandomImageByCategory extension
 * Usage example: <randomimagebycategory width="200" categories="Featured Image"/>
 * Supported parameters: width, limit, categories
 *
 * @file
 * @ingroup Extensions
 * @author Aaron Wright <aaron.wright@gmail.com>
 * @author David Pean <david.pean@gmail.com>
 * @author Jack Phoenix
 * @link https://www.mediawiki.org/wiki/Extension:RandomImageByCategory Documentation
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

class RandomImageByCategory {

	public static function registerTag( &$parser ) {
		$parser->setHook( 'randomimagebycategory', [ __CLASS__, 'getRandomImage' ] );
	}

	public static function getRandomImage( $input, $args, $parser ) {
		$parser->getOutput()->updateCacheExpiry( 0 );

		$categories = ( isset( $args['categories'] ) ) ? trim( $args['categories'] ) : '';

		if ( isset( $args['limit'] ) && is_numeric( $args['limit'] ) ) {
			$limit = $args['limit'];
		} else {
			$limit = 10;
		}

		if ( isset( $args['width'] ) && is_numeric( $args['width'] ) ) {
			$width = $args['width'];
		} else {
			$width = 200;
		}

		$services = MediaWikiServices::getInstance();
		$cache = $services->getMainWANObjectCache();
		$key = $cache->makeKey( 'image', 'random', $limit, str_replace( ' ', '', $categories ) );
		$data = $cache->get( $key );
		$image_list = [];

		if ( !$data ) {
			wfDebug( "Getting random image list from DB\n" );
			$ctg = $parser->replaceVariables( $categories );
			$ctg = $parser->mStripState->unstripBoth( $ctg );
			$ctg = str_replace( "\,", '#comma#', $ctg );
			$aCat = explode( ',', $ctg );

			$category_match = [];
			foreach ( $aCat as $sCat ) {
				if ( $sCat != '' ) {
					$category_match[] = Title::newFromText( trim( str_replace( '#comma#', ',', $sCat ) ) )->getDBkey();
				}
			}

			if ( count( $category_match ) == 0 ) {
				return '';
			}

			$params['ORDER BY'] = 'page_id';
			if ( !empty( $limit ) ) {
				$params['LIMIT'] = $limit;
			}

			$dbr = wfGetDB( DB_REPLICA );
			$res = $dbr->select(
				[ 'page', 'categorylinks' ],
				[ 'page_title' ],
				[ 'cl_to' => $category_match, 'page_namespace' => NS_FILE ],
				__METHOD__,
				$params,
				[ 'categorylinks' => [ 'INNER JOIN', 'cl_from=page_id' ] ]
			);
			$image_list = [];
			foreach ( $res as $row ) {
				$image_list[] = $row->page_title;
			}
			$cache->set( $key, $image_list, 60 * 15 );
		} else {
			$image_list = $data;
			wfDebug( "Cache hit for random image list\n" );
		}

		$random_image = '';
		$thumbnail = '';
		if ( count( $image_list ) > 0 ) {
			$random_image = $image_list[ array_rand( $image_list, 1 ) ];
		}

		if ( $random_image ) {
			$image_title = Title::makeTitle( NS_FILE, $random_image );
			$render_image = $services->getRepoGroup()->findFile( $random_image );
			$thumb_image = $render_image->transform( [ 'width' => $width ] );
			$thumbnail = "<a href=\"" . htmlspecialchars( $image_title->getFullURL() ) . "\">{$thumb_image->toHtml()}</a>";
		}

		return $thumbnail;
	}
}
