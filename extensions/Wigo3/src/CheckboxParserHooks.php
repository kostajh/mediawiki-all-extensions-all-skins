<?php

namespace Wigo3;

use Html;
use Parser;
use Sanitizer;

class CheckboxParserHooks {
	/**
	 * @param string $input
	 * @param string[] $args
	 * @param Parser $parser
	 * @return string
	 */
	public static function checkboxes( $input, $args, $parser ) {
		$voteid = $args['poll'];
		$voteid = str_replace( ' ', '_', $voteid );
		if ( !$voteid ) {
			static $err = null;
			if ( $err === null ) {
				$err = wfMessage( 'wigoerror' )->escaped();
			}
			$output = $parser->recursiveTagParse( $input );
			return "<p><span style='color:red;'>{$err}</span> {$output}</p>";
		}

		// avoid conflicts - checkbox will add check prefix
		$voteid = "set" . $voteid;

		$set = $args['set'];
		if ( !$set ) {
			// fail silently
			return '';
		}

		// Get the checkbox set
		$list = wfMessage( "checkboxes/{$set}" )->text();
		$list = preg_replace( "/\*/", "", $list );
		$options = explode( "\n", $list );

		$output = '<div class="checkboxset">';
		foreach ( $options as $option ) {
			$parts = explode( ';', $option );
			if ( count( $parts ) >= 2 ) {
				$title = $parts[0];
				$id = $parts[1];
				$id = Sanitizer::escapeClass( str_replace( ' ', '_', $id ) );
			} else {
				// someone forgot the id, generate one for them
				$title = $parts[0];
				$id = Sanitizer::escapeClass( $parts[0] );
			}
			$htmlId = htmlspecialchars( "check{$voteid}-check-{$id}" );
			$htmlTitle = htmlspecialchars( $title );
			$output .= "<p><checkbox poll=\"$htmlId\"" .
				" closed=yes bulkmode=yes>{$htmlTitle}</checkbox></p>";
		}
		$output .= "</div>";

		return $parser->recursiveTagParse( $output );
	}

	/**
	 * @param string $input
	 * @param string[] $args
	 * @param Parser $parser
	 * @return string
	 */
	public static function checkbox( $input, $args, $parser ) {
		$voteid = $args['poll'];
		$voteid = str_replace( ' ', '_', $voteid );
		if ( !$voteid ) {
			static $err = null;
			if ( $err === null ) {
				$err = wfMessage( 'wigoerror' )->text();
			}
			$output = $parser->recursiveTagParse( $input );
			return "<p><span style='color:red;'>{$err}</span> {$output}</p>";
		}

		if ( array_key_exists( 'bulkmode', $args ) && strcasecmp( $args['bulkmode'], "yes" ) === 0 ) {
			$bulkmode = true;
		} else {
			$bulkmode = false;
		}

		// avoid conflicts
		$voteid = "check" . $voteid;
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select( 'wigovote',
			[ 'count(vote)', 'sum(vote)' ],
			[ 'id' => $voteid, "vote >= 0", "vote <= 1" ],
			__METHOD__,
			[ 'GROUP BY' => 'id' ]
		);
		$row = $res->fetchRow();
		$votes = $row['sum(vote)'] ?? 0;
		$countvotes = $row['count(vote)'] ?? 0;
		$res->free();

		// get my vote
		$myvote = null;

		$output = $parser->recursiveTagParse( $input );

		// parse magic only, to allow plural
		$totalvotes = wfMessage( 'wigovotestotal' )->params( $countvotes )->text();

		return Html::check( "checkbox-input-{$voteid}", $myvote === 1,
			[
				'class' => 'checkbox-input',
				'id' => "checkbox-input-{$voteid}",
				'disabled' => 'disabled'
			] ) . ' ' .
			Html::rawElement( 'label', [ 'for' => "checkbox-input-{$voteid}" ], $output ) .
			' (' .
			Html::element( 'span',
				[ 'id' => $voteid, 'title' => $totalvotes ],
				$votes
			) .
			')';
	}
}
