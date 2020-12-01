<?php

namespace Wigo3;

use Parser;
use Xml;

class MultiParserHooks {
	/**
	 * @param string $input
	 * @param string[] $args
	 * @param Parser $parser
	 * @return string
	 */
	public static function multi( $input, $args, $parser ) {
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

		// inject js
		$parserOutput = $parser->getOutput();
		$parserOutput->addModules( 'ext.wigo3.multi' );
		$parserOutput->addJsConfigVars( 'wigo3MultiVoteId', $voteid );

		// avoid hacking wigo votes
		$voteid = "multi" . $voteid;

		$dbr = wfGetDB( DB_REPLICA );
		// get the total number of votes
		$sum = intval( $dbr->selectField(
			'wigovote',
			'count(vote)',
			[ 'id' => $voteid ],
			__METHOD__,
			[ 'GROUP BY' => 'id' ] ) );

		$lines = preg_split( '/\n+/', trim( $input ) );
		foreach ( $lines as $i => $line ) {
			// get the result for this option
			$results[$i] = intval( $dbr->selectField(
				'wigovote',
				'count(vote)',
				[ 'id' => $voteid, "vote" => $i ],
				__METHOD__
			) );

			$jsVoteId = Xml::encodeJsVar( $voteid );
			$htmlVoteId = htmlspecialchars( $voteid );
			$htmlJsVoteId = htmlspecialchars( $jsVoteId );

			$line = "<span id=\"{$htmlVoteId}-{$i}\">{$line}</span>";
			$resultstr[$i] = "<span id=\"{$htmlVoteId}-{$i}-result\">" . $results[$i] . "</span>";
			$outputlines[] = $parser->recursiveTagParse( $line );
		}

		if ( array_key_exists( 'closed', $args )
			&& strcasecmp( $args['closed'], "yes" ) === 0
		) {
			$output = "<table class=\"multivote\" cellspacing=\"2\" cellpadding=\"2\" border=\"0\">";
			foreach ( $outputlines as $i => $line ) {
				if ( $sum == 0 ) {
					$percent = 0;
				} else {
					$percent = $results[$i] / $sum * 100;
				}
				// phpcs:disable Generic.Files.LineLength.TooLong
				$output .= <<<HTML
<tr>
	<td class="multioption" style="width:20em;">
		$line
	</td>
	<td class="multiresult" style="width:2em;">"
		{$resultstr[$i]}
	</td>
	<td style="margin:0; padding:0;">
		<div class="votecolumnback" style="border: 1px solid black; background:#F0F0F0; width:220px; height:1em;">
			<div id="$htmlVoteId-{$i}-column" class="votecolumnfront" style="background:blue; width:{$percent}%; height:100%;"></div>
		</div>
	</td>
</tr>
HTML;
				// phpcs:enable Generic.Files.LineLength.TooLong

			}
			$output .= "</table>";
			return $output;
		} else {
			$output = "<table class=\"multivote\" cellspacing=\"2\" cellpadding=\"2\" border=\"0\">";
			$numLines = count( $outputlines );
			$votetitle = wfMessage( "multi-votetitle" )->escaped();
			$votebutton = wfMessage( "multi-votebutton" )->escaped();

			foreach ( $outputlines as $i => $line ) {
				if ( $sum == 0 ) {
					$percent = 0;
				} else {
					$percent = $results[$i] / $sum * 100;
				}
				// phpcs:disable Generic.Files.LineLength.TooLong
				$output .= <<<HTML
<tr>
	<td class="multioption" style="width:14em;">
		$line
	</td>
	<td class="multiresult" style="width:2em;">
		$resultstr[$i]
	</td>
	<td class="multibutton" style="padding-left:1em; padding-right:1em;">
		<a href="javascript:mediaWiki.multivote.send($htmlJsVoteId,$i,$numLines)" title="$votetitle">$votebutton</a>
	</td>
	<td style="margin:0; padding:0;">
		<div class="votecolumnback" style="border: 1px solid black; background:#F0F0F0; width:220px; height:1em;">
			<div id="{$htmlVoteId}-{$i}-column" class="votecolumnfront" style="background:blue; width:{$percent}%; height:100%;"></div>
		</div>
	</td>
</tr>
HTML;
			}
			$output .= "</table>";
			// phpcs:enable Generic.Files.LineLength.TooLong
			return $output;
		}
	}
}
