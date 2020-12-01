<?php

namespace Wigo3;

use Parser;
use Sanitizer;

/**
 * <slider> and <sliders> show archived slider votes. To reduce the maintenance
 * burden, they no longer support interactive voting using a slider control.
 */
class SliderParserHooks {
	/**
	 * @param string $input
	 * @param string[] $args
	 * @param Parser $parser
	 * @return string
	 */
	public static function sliders( $input, $args, $parser ) {
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

		// avoid conflicts - sliders will add slider prefix
		$voteid = "set" . $voteid;

		$set = $args['set'];
		if ( !$set ) {
			// fail silently
			return '';
		}

		// get minimum and maximum values if supplied
		if ( array_key_exists( 'min', $args ) &&
			( intval( $args['min'], 10 ) !== 0 || $args['min'] == '0' )
		) {
			$minvalue = intval( $args['min'] );
		} else {
			$minvalue = 0;
		}
		if ( array_key_exists( 'max', $args ) &&
			( intval( $args['max'], 10 ) !== 0 || $args['max'] == '0' )
		) {
			$maxvalue = intval( $args['max'] );
		} else {
			$maxvalue = 10;
		}

		// Get the slider set
		$list = wfMessage( "sliders/{$set}" )->text();
		$list = preg_replace( "/\*/", "", $list );
		$options = explode( "\n", $list );

		$output = '<div class="sliderset">' .
			"<table class=\"slidervote\" cellspacing=\"2\" cellpadding=\"2\" border=\"0\">";
		foreach ( $options as $option ) {
			$parts = explode( ';', $option );
			if ( count( $parts ) >= 2 ) {
				$title = $parts[0];
				$id = $parts[1];
				$id = str_replace( ' ', '_', $id );
			} else {
				// someone forgot the id, generate one for them
				$title = $parts[0];
				$id = Sanitizer::escapeClass( $parts[0] );
			}
			$output .= "<p>" .
				\Xml::element( 'slider',
					[
						'poll' => "{$voteid}-slider-{$id}",
						'min' => $minvalue,
						'max' => $maxvalue,
						'closed' => 'yes',
						'bulkmode' => 'yes'
					],
					$title
				) .
				"</p>";
		}
		$output .= "</table></div>";

		return $parser->recursiveTagParse( $output );
	}

	/**
	 * @param string $input
	 * @param string[] $args
	 * @param Parser $parser
	 * @return string
	 */
	public static function slider( $input, $args, $parser ) {
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

		// get minimum and maximum values if supplied
		if ( array_key_exists( 'min', $args )
				&& ( intval( $args['min'], 10 ) !== 0 || $args['min'] == '0' )
		) {
			$minvalue = intval( $args['min'] );
		} else {
			$minvalue = 0;
		}
		if ( array_key_exists( 'max', $args )
				&& ( intval( $args['max'], 10 ) !== 0 || $args['max'] == '0' )
		) {
			$maxvalue = intval( $args['max'] );
		} else {
			$maxvalue = 10;
		}

		if ( array_key_exists( 'bulkmode', $args )
			&& strcasecmp( $args['bulkmode'], "yes" ) === 0
		) {
			$bulkmode = true;
		} else {
			$bulkmode = false;
		}

		// avoid hacking wigo votes
		$voteid = "slider" . $voteid;
		$dbr = wfGetDB( DB_REPLICA );
		$row = $dbr->selectRow(
			'wigovote',
			'sum(vote), count(vote)',
			[ 'id' => $voteid, "vote >= {$minvalue}", "vote <= {$maxvalue}" ],
			__METHOD__,
			[ 'GROUP BY' => 'id' ] );
		$votes = 0;
		$countvotes = 0;
		if ( $row ) {
			$votes = $row['sum(vote)'];
			$countvotes = $row['count(vote)'];
		}

		if ( $countvotes != 0 ) {
			$voteaverage = round( $votes / $countvotes, 2 );
		} else {
			$voteaverage = "no votes";
		}

		$output = $parser->recursiveTagParse( $input );

		// parse magic only, to allow plural
		$totalvotes = wfMessage( 'wigovotestotal' )->params( $countvotes )->escaped();

		$htmlVoteId = htmlspecialchars( $voteid );

		$html = '';
		if ( !$bulkmode ) {
			$html .= "<table class=\"slidervote\" cellspacing=\"2\" cellpadding=\"2\" border=\"0\">";
		}
		$html .=
		  "<tr>" .
			"<td>" .
			  "<!--$htmlVoteId-->$output" .
			"</td>" .
			"<td style=\"min-width:25px; text-align:center;\">" .
			  "<span id=\"{$htmlVoteId}\" title=\"{$totalvotes}\">{$voteaverage}</span>" .
			"</td>" .
		  "</tr>";
		if ( !$bulkmode ) {
			$html .= "</table>";
		}
		return $html;
	}
}
