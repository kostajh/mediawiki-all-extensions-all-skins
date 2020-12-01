<?php

namespace Wigo3;

use DatabaseUpdater;
use DeferrableUpdate;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RenderedRevision;
use Parser;
use Title;

class Hooks {

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:$wgExtensionFunctions
	 */
	public static function extensionSetup() {
		global $wgAjaxExportList;
		$wgAjaxExportList[] = [ WigoAjax::class, 'vote' ];
		$wgAjaxExportList[] = [ WigoAjax::class, 'vote2' ];
		$wgAjaxExportList[] = [ WigoAjax::class, 'votebatch' ];
		$wgAjaxExportList[] = [ WigoAjax::class, 'invalidate' ];
		$wgAjaxExportList[] = [ WigoAjax::class, 'getmyvotes' ];
		$wgAjaxExportList[] = [ MultiAjax::class, 'vote' ];
		$wgAjaxExportList[] = [ MultiAjax::class, 'getmyvote' ];
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/LoadExtensionSchemaUpdates
	 * @param DatabaseUpdater $updater
	 */
	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		$updater->addExtensionTable( 'wigovote', __DIR__ . '/../sql/wigovote.sql' );
		$updater->addExtensionTable( 'wigotext', __DIR__ . '/../sql/wigotext.sql' );
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
	 * @param Parser $parser
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		// wigo3
		$parser->setHook( 'vote',  [ WigoParserHooks::class, 'vote' ] );
		$parser->setHook( 'votecp', [ WigoParserHooks::class, 'votecp' ] );
		$parser->setHook( 'capture', [ WigoParserHooks::class, 'capture' ] );
		$parser->setFunctionHook( 'captureencode', [ WigoParserHooks::class, 'captureencode' ] );
		$parser->setHook( 'bestof', [ WigoParserHooks::class, 'bestof' ] );

		// multi
		$parser->setHook( 'multi', [ MultiParserHooks::class, 'multi' ] );

		// slider
		$parser->setHook( 'slider', [ SliderParserHooks::class, 'slider' ] );
		$parser->setHook( 'sliders', [ SliderParserHooks::class, 'sliders' ] );

		// checkbox
		$parser->setHook( 'checkbox', [ CheckboxParserHooks::class, 'checkbox' ] );
		$parser->setHook( 'checkboxes', [ CheckboxParserHooks::class, 'checkboxes' ] );
	}

	/**
	 * @see https://gerrit.wikimedia.org/r/571109
	 * @param Parser $parser
	 * @param string &$text
	 * @return bool
	 */
	public static function onParserPreSaveTransformComplete( $parser, &$text ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		if ( !$config->get( 'Wigo3ReplaceNextpoll' ) ) {
			return true;
		}
		$newnums = [];
		$matchi = preg_match_all( '/(?<!<nowiki>)(<vote(cp|)\s*nextpoll=([^>]*)>)/i',
			$text, $matches, PREG_OFFSET_CAPTURE );
		if ( $matchi > 0 ) {
			$newtext = substr( $text, 0, $matches[1][0][1] );
		}
		for ( $i = 0; $i < $matchi; ++$i ) {
			$curr = $matches[1][$i][0];
			$cp = $matches[2][$i][0];
			$pollid = $matches[3][$i][0];
			// fix pollid with numbers
			$pollid = preg_replace( '/\d+$/', '', $pollid );

			// find the next id
			if ( array_key_exists( $pollid, $newnums ) ) {
				$num = $newnums[$pollid];
			} else {
				$num = 0;
			}
			$wigos = preg_split( "/<\/vote(cp|)>/", $text );
			if ( count( $wigos ) != 0 ) {
				for ( $j = 0; $j < count( $wigos ); ++$j ) {
					$start = strpos( $wigos[$j], "<vote" );
					$wigos[$j] = substr( $wigos[$j], $start );
					$matchi2 = preg_match( "/(?<!next)poll={$pollid}([^\s]*)/",
						$wigos[$j], $matches2 );
					if ( $matchi2 == 1 ) {
						$tempi = intval( $matches2[1] );
						// on error this will be 0, so we can skip error checking
						if ( $tempi > $num ) {
							$num = $tempi;
						}
					}
				}
			}
			++$num;

			$newtag = "<vote{$cp} poll={$pollid}{$num}>";
			$nextlength = ( ( $i == $matchi - 1 )
				? ( strlen( $text ) - ( $matches[1][$i][1] + strlen( $curr ) ) )
				: ( $matches[1][$i + 1][1] - ( $matches[1][$i][1] + strlen( $curr ) ) ) );
			$newtext .= $newtag
				. substr( $text, $matches[1][$i][1] + strlen( $curr ), $nextlength );
			$newnums[$pollid] = $num;
		}
		if ( $matchi > 0 ) {
			$text = $newtext;
		}
		return true;
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/RevisionDataUpdates
	 * @param Title $title
	 * @param RenderedRevision $renderedRevision
	 * @param DeferrableUpdate[] &$updates
	 */
	public static function onRevisionDataUpdates( $title, $renderedRevision, &$updates ) {
		$polls = $renderedRevision->getSlotParserOutput( 'main' )->getExtensionData( 'wigo' );
		if ( $polls ) {
			$updates[] = new WigoDataUpdate( $polls );
		}
	}
}
