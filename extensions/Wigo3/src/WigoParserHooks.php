<?php

namespace Wigo3;

use Html;
use Xml;

class WigoParserHooks {
	/**
	 * @param string $input
	 * @param string[] $args
	 * @param \Parser $parser
	 * @param \PPFrame $frame
	 * @return string
	 */
	public static function votecp( $input, $args, $parser, $frame ) {
		return self::vote( $input, $args, $parser, $frame, true );
	}

	/**
	 * @param string $input
	 * @param string[] $args
	 * @param \Parser $parser
	 * @param \PPFrame $frame
	 * @param bool $cp Whether the conservapedia/capture variant was called
	 * @return string
	 */
	public static function vote( $input, $args, $parser, $frame, $cp = false ) {
		$voteid = $args['poll'];
		$voteid = str_replace( ' ', '_', $voteid );
		if ( !$voteid ) {
			static $err = null;
			if ( $err === null ) {
				$err = wfMessage( 'wigoerror' )->text();
			}
			$output = $parser->recursiveTagParse( $input, $frame );
			return "<p><span style='color:red;'>{$err}</span> {$output}</p>";
		}

		$parserOutput = $parser->getOutput();
		$parserOutput->addModules( 'ext.wigo3.wigo3' );

		// Register the tag in the ParserOutput for WigoDataUpdate
		$polls = $parserOutput->getExtensionData( 'wigo' );
		if ( $polls === null ) {
			$polls = new \ArrayObject;
			$parserOutput->setExtensionData( 'wigo', $polls );
		}
		$strippedText = $parser->getStripState()->unstripNoWiki( $input );
		$strippedText = $parser->getStripState()->killMarkers( $strippedText );
		$polls->append( [
			'id' => $voteid,
			'cp' => $cp,
			'text' => $strippedText
		] );

		$counts = VoteStore::getVoteCounts( $voteid );
		$votes = $counts['plus'] - $counts['minus'];
		$countvotes = $counts['plus'] + $counts['minus'] + $counts['zero'];

		// backwards compatible img tag handling
		if ( !$cp && array_key_exists( 'img', $args )
			&& ( !strcmp( $args['img'], 'on' ) || !strcmp( $args['img'], 'expanded' ) )
		) {
			$input = preg_replace(
				'/\[[^\]]*conservapedia\.com[^\]]*\]/i',
				"$0<sup>[[:Image:{$args['poll']}_x.png|img]]</sup>", $input
			);
			$x = 0;
			do {
				$input = preg_replace(
					'/(<sup>\[\[:Image:' . $args['poll'] . '_)x(\.png\|img\]\]<\/sup>)/',
					"$1 {$x}$2", $input, 1, $count
				);
				++$x;
			} while ( $count );
		}

		$output = $parser->recursiveTagParse( $input, $frame );
		// votecp img tag handling
		if ( $cp ) {
			$output = self::addCaptureImages( $output, $parser, $frame );
		}

		$totalvotes = wfMessage( 'wigovotestotald' )
			->params( $countvotes, $counts['plus'], $counts['zero'], $counts['minus'] )
			->escaped();
		$distribtitle = wfMessage( 'wigovotedistrib' )
			->params( $counts['plus'], $counts['zero'], $counts['minus'] )
			->escaped();
		if ( $countvotes != 0 ) {
			$uppercent = ( $counts['plus'] / $countvotes ) * 100;
			$downpercent = ( $counts['minus'] / $countvotes ) * 100;
			$neutralpercent = ( $counts['zero'] / $countvotes ) * 100;
		} else {
			$uppercent = 0;
			$downpercent = 0;
			$neutralpercent = 0;
		}

		if ( $uppercent != 0 ) {
			$uppercent .= "%";
		}
		if ( $neutralpercent != 0 ) {
			$neutralpercent .= "%";
		}
		if ( $downpercent != 0 ) {
			$downpercent .= "%";
		}

		$htmlVoteId = htmlspecialchars( $voteid );
		$jsVoteId = htmlspecialchars( Xml::encodeJsVar( $voteid ) );
		$displayNoneIfEmpty = $totalvotes == 0 ? "display:none;" : "";

		if ( array_key_exists( 'closed', $args ) && strcasecmp( $args['closed'], "yes" ) === 0 ) {
			// phpcs:disable Generic.Files.LineLength.TooLong
			return <<<HTML
<table class="vote" cellspacing="2" cellpadding="2" border="0">
	<tr>
		<td style="white-space:nowrap;">
			<table id="{$htmlVoteId}-dist" class="wigodistribution" style="width:48px; height:6px; border:1px solid grey; margin:0; padding:0; border-spacing:0;" title="{$distribtitle}">
				<tr>
					<td class="wigodist-up" style="border:none; background-color:limegreen; margin:0; padding:0; width:{$uppercent}; height:100%; $displayNoneIfEmpty"></td>
					<td class="wigodist-neutral" style="border:none; background-color:orange; margin:0; padding:0; width:{$neutralpercent}; height:100%; $displayNoneIfEmpty"></td>
					<td class="wigodist-down" style="border:none; background-color:red; margin:0; padding:0; width:{$downpercent}; height:100%; $displayNoneIfEmpty"></td>
				</tr>
			</table>
		</td>
		<td style="min-width:25px; text-align:center;">
			<span id="{$htmlVoteId}" title="{$totalvotes}">{$votes}</span>
		</td>
		<td style="vertical-align:middle;">
			<!--{$htmlVoteId}-->$output
		</td>
	</tr>
</table>
HTML;
			// phpcs:enable Generic.Files.LineLength.TooLong
		} else {
			// get up-down images
			static $up = null;
			static $down = null;
			static $reset = null;
			static $altup = null;
			static $altdown = null;
			static $altreset = null;
			static $titleup = null;
			static $titledown = null;
			static $titlereset = null;

			if ( $up === null || $down === null || $reset === null
				|| $altup === null || $altdown === null || $altreset === null
				|| $titleup === null || $titledown === null || $titlereset === null
			) {
				global $wgExtensionAssetsPath;
				$up = "$wgExtensionAssetsPath/Wigo3/images/wigovoteup.png";
				$down = "$wgExtensionAssetsPath/Wigo3/images/wigovotedown.png";
				$reset = "$wgExtensionAssetsPath/Wigo3/images/wigovoteneutral.png";
				$altup = wfMessage( 'wigoaltup' )->escaped();
				$altdown = wfMessage( 'wigoaltdown' )->escaped();
				$altreset = wfMessage( 'wigoaltreset' )->escaped();
				$titleup = wfMessage( 'wigotitleup' )->escaped();
				$titledown = wfMessage( 'wigotitledown' )->escaped();
				$titlereset = wfMessage( 'wigotitlereset' )->escaped();
			}

			// phpcs:disable Generic.Files.LineLength.TooLong
			return "<table class=\"vote\" cellspacing=\"2\" cellpadding=\"2\" border=\"0\">" .
			"<tr>" .
			"<td style=\"white-space:nowrap;\">" .
				"<a href=\"javascript:mediaWiki.wigo.voteup($jsVoteId)\" id=\"{$htmlVoteId}-up\" class=\"wigobutton wigoupbutton\">" .
				"<img alt=\"{$altup}\" title=\"{$titleup}\" src=\"$up\"></a>" .
				"<a href=\"javascript:mediaWiki.wigo.votereset($jsVoteId)\" id=\"{$htmlVoteId}-neutral\" class=\"wigobutton wigoneutralbutton\">" .
				"<img alt=\"{$altreset}\" title=\"{$titlereset}\" src=\"$reset\"></a>" .
				"<a href=\"javascript:mediaWiki.wigo.votedown($jsVoteId)\" id=\"{$htmlVoteId}-down\" class=\"wigobutton wigodownbutton\">" .
				"<img alt=\"{$altdown}\" title=\"{$titledown}\" src=\"$down\"></a>" .
				"<table id=\"{$htmlVoteId}-dist\" class=\"wigodistribution\" style=\"width:100%; height:6px; border:1px solid grey; margin:0; padding:0; border-spacing:0;\" title=\"{$distribtitle}\">" .
					"<tr>" .
						"<td class=\"wigodist-up\" style=\"border:none; background-color:limegreen; margin:0; padding:0; width:{$uppercent}; height:100%; " . ( $totalvotes == 0 ? "display:none;" : "" ) . "\"></td>" .
						"<td class=\"wigodist-neutral\" style=\"border:none; background-color:orange; margin:0; padding:0; width:{$neutralpercent}; height:100%; " . ( $totalvotes == 0 ? "display:none;" : "" ) . "\"></td>" .
						"<td class=\"wigodist-down\" style=\"border:none; background-color:red; margin:0; padding:0; width:{$downpercent}; height:100%; " . ( $totalvotes == 0 ? "display:none;" : "" ) . "\"></td>" .
					"</tr></table>" .
			"</td>" .
			"<td style=\"min-width:25px; text-align:center;\">" .
				"<span id=\"{$htmlVoteId}\" title=\"{$totalvotes}\">{$votes}</span>" .
			"</td>" .
			"<td style=\"vertical-align:middle;\">" .
				"<!--$htmlVoteId-->$output" .
			"</td>" .
			"</tr>" .
			"</table>";
			// phpcs:enable Generic.Files.LineLength.TooLong
		}
	}

	/**
	 * @param string $output
	 * @param \Parser $parser
	 * @param \PPFrame $frame
	 * @return string
	 */
	private static function addCaptureImages( $output, $parser, $frame ) {
		$matchi = preg_match_all( '/(<a[^>]*href="([^"]*conservapedia\.com[^"]*)"[^>]*>' .
			'(?:[^<]|<[^\/]|<\/[^a]|<\/a[^>])*<\/a>)(?!<span class="wigocapture">)/i',
			$output, $matches, PREG_OFFSET_CAPTURE );
		if ( !$matchi ) {
			return $output;
		}
		$newoutput = substr( $output, 0, $matches[1][0][1] );
		for ( $i = 0; $i < $matchi; ++$i ) {
			$imgname = 'capture_' . sha1( $matches[2][$i][0] ) . '.png';
			$text = $matches[1][$i][0];
			$img = $parser->recursiveTagParse(
				"<span class=\"wigocapture\"><sup>[[:Image:$imgname|img]]</sup></span>",
				$frame );
			if ( $i == $matchi - 1 ) {
				$nextlength = strlen( $output ) - ( $matches[1][$i][1] + strlen( $text ) );
			} else {
				$nextlength = $matches[1][$i + 1][1] - ( $matches[1][$i][1] + strlen( $text ) );
			}
			$newoutput .= substr( $output, $matches[1][$i][1], strlen( $text ) ) . $img .
				substr( $output, $matches[1][$i][1] + strlen( $text ), $nextlength );
		}
		return $newoutput;
	}

	/**
	 * @param string $input
	 * @param string[] $args
	 * @param \Parser $parser
	 * @param \PPFrame $frame
	 * @return string
	 */
	public static function capture( $input, $args, $parser, $frame ) {
		$output = $parser->recursiveTagParse( $input, $frame );
		$matchi = preg_match_all(
			'/(<a[^>]*href="([^"]*)"[^>]*>(?:[^<]|<[^\/]|<\/[^a]|<\/a[^>])*<\/a>)' .
			'(?!<span class="wigocapture">)/i', $output, $matches, PREG_OFFSET_CAPTURE );
		if ( $matchi > 0 ) {
			$newoutput = substr( $output, 0, $matches[1][0][1] );
		}
		for ( $i = 0; $i < $matchi;++$i ) {
			$imgname = 'capture_' . sha1( $matches[2][$i][0] ) . '.png';
			$text = $matches[1][$i][0];
			$img = $parser->recursiveTagParse(
				"<span class=\"wigocapture\"><sup>[[:Image:$imgname|img]]</sup></span>", $frame );
			if ( $i == $matchi - 1 ) {
				$nextlength = strlen( $output ) - ( $matches[1][$i][1] + strlen( $text ) );
			} else {
				$nextlength = $matches[1][$i + 1][1] - ( $matches[1][$i][1] + strlen( $text ) );
			}
			$newoutput .= substr( $output, $matches[1][$i][1], strlen( $text ) ) . $img .
				substr( $output, $matches[1][$i][1] + strlen( $text ), $nextlength );
		}
		if ( $matchi > 0 ) {
			$output = $newoutput;
		}
		return $output;
	}

	/**
	 * the parser function, returns the encoded image filename
	 *
	 * @param \Parser $parser
	 * @param string $param1
	 * @return string
	 */
	public static function captureencode( $parser, $param1 ) {
		return 'capture_' . sha1( $param1 ) . '.png';
	}

	/**
	 * @param string $input
	 * @param string[] $args
	 * @param \Parser $parser
	 * @param \PPFrame $frame
	 * @return string
	 */
	public static function bestof( $input, $args, $parser, $frame ) {
		$parser->getOutput()->updateCacheExpiry( 0 );

		$cutoff = $args['cutoff'] ?? null;
		$year = $args['year'] ?? null;
		$month = $args['month'] ?? null;
		$keyword = $args['keyword'] ?? null;
		$output = '';

		if ( isset( $args['dynamic'] ) ) {
			global $wgRequest;
			$cutoff = $wgRequest->getVal( 'bfcutoff', $cutoff );
			$year = $wgRequest->getVal( 'bfyear', $year );
			$month = $wgRequest->getVal( 'bfmonth', $month );
			$keyword = $wgRequest->getVal( 'bfsearch', $keyword );

			$lang = $parser->getTargetLanguage();
			$selected = $month === null ? 'all' : intval( $month );
			$monthOpts[] = Xml::option(
				wfMessage( 'monthsall' )->text(), 'all', $selected === 'all' );
			for ( $i = 1; $i < 13; ++$i ) {
				$monthOpts[] = Xml::option( $lang->getMonthName( $i ), $i, $selected === $i );
			}
			$formInside = Xml::openElement( 'table', [ 'cellpadding' => 8 ] ) .
				Xml::openElement( 'tr' ) .
				Xml::openElement( 'td' ) .
				Xml::label( wfMessage( 'wigo-bestof-cutoff' )->text(), 'bfcutoff' ) .
				Xml::closeElement( 'td' ) .
				Xml::openElement( 'td' ) .
				Xml::label( wfMessage( 'wigo-bestof-month' )->text(), 'bfmonth' ) .
				Xml::closeElement( 'td' ) .
				Xml::openElement( 'td' ) .
				Xml::label( wfMessage( 'wigo-bestof-filter' )->text(), 'bfsearch' ) .
				Xml::closeElement( 'td' ) .
				Xml::closeElement( 'tr' ) . "\n" .
				Xml::openElement( 'tr' ) .
				Xml::openElement( 'td' ) .
				Xml::input( 'bfcutoff', 3, $cutoff, [ 'maxlength' => 3 ] ) . ' ' .
				Xml::closeElement( 'td' ) .
				Xml::openElement( 'td' ) .
				Xml::input( 'bfyear', 4, $year, [ 'maxlength' => 4 ] ) . ' ' .
				Xml::openElement( 'select',
					[ 'name' => 'bfmonth', 'class' => 'mw-month-selector' ] ) .
				implode( "\n", $monthOpts ) .
				Xml::closeElement( 'select' ) .
				Xml::closeElement( 'td' ) .
				Xml::openElement( 'td' ) .
				Xml::input( 'bfsearch', 25, $keyword ) . ' ' .
				Xml::closeElement( 'td' ) .
				Xml::openElement( 'td' ) .
				Xml::submitButton( wfMessage( 'wigo-bestof-submit' )->text() ) .
				Xml::closeElement( 'td' ) .
				Xml::closeElement( 'tr' ) .
				Xml::closeElement( 'table' );

			foreach ( $wgRequest->getValues() as $key => $value ) {
				if ( $key != 'bfcutoff' && $key != 'bfyear' && $key != 'bfmonth'
					&& $key != 'bfsearch'
				) {
					$formInside .= "\n" . Html::hidden( $key, $value );
				}
			}
			$form = Xml::openElement(
					'form', [
					'id' => 'bestofoption',
					'action' => '',
					'method' => 'GET'
				] ) .
				Xml::fieldset( wfMessage( 'wigo-bestof-legend' )->text(), $formInside ) .
				Xml::closeElement( 'form' );
			$output = $form;
		}
		$list = self::getBestOf( $args['poll'], $cutoff, $month === 'all' ? null : $month,
			$year, $keyword, $parser, $frame );
		$output .= $list;
		return $output;
	}

	/**
	 * @param string $pollid
	 * @param string|null $cutoff
	 * @param string|null $month
	 * @param string|null $year
	 * @param string|null $keyword
	 * @param \Parser $parser
	 * @param \PPFrame $frame
	 * @return string
	 */
	private static function getBestOf( $pollid, $cutoff, $month, $year, $keyword, $parser, $frame ) {
		$dbr = wfGetDB( DB_REPLICA );
		$conds = [ "vote_id " . $dbr->buildLike( $pollid, $dbr->anyString() ) ];
		$options = [ 'GROUP BY' => 'vote_id', 'ORDER BY' => 'total DESC' ];
		if ( $cutoff != null ) {
			$options['HAVING'] = "total >= " . intval( $cutoff );
		}
		if ( $month != null && $year != null ) {
			$month = str_pad( $month, 2, '0', STR_PAD_LEFT );
			$conds[] = "timestamp " . $dbr->buildLike( $year, $month, $dbr->anyString() );
		} elseif ( $month == null && $year != null ) {
			$conds[] = "timestamp " . $dbr->buildLike( $year, $dbr->anyString() );
		} elseif ( $month != null && $year == null ) {
			$month = str_pad( $month, 2, '0', STR_PAD_LEFT );
			$conds[] =
				"timestamp " .
				$dbr->buildLike( $dbr->anyChar(), $dbr->anyChar(), $dbr->anyChar(), $dbr->anyChar(),
					$month, $dbr->anyString() );
		}
		if ( $keyword != null ) {
			$conds[] = "text " . $dbr->buildLike( $dbr->anyString(), $keyword, $dbr->anyString() );
		}
		$res = $dbr->select(
			[ 'wigovote', 'wigotext' ],
			[
				'sum(case vote when 1 then 1 else 0 end) as plus',
				'sum(case vote when -1 then 1 else 0 end) as minus',
				'sum(case vote when 0 then 1 else 0 end) as zero',
				'ifnull(sum(vote),0) as total',
				'vote_id',
				'text',
				'is_cp',
			],
			$conds, __METHOD__, $options,
			[ 'wigotext' => [ 'RIGHT JOIN', 'id=vote_id' ] ] );
		$output = "<table cellspacing=\"2\" cellpadding=\"2\" border=\"0\">";
		static $sep = null;
		if ( $sep === null ) {
			$sep = ' ' . wfMessage( 'wigo-bestof-tooltipseparator' )->text() . ' ';
		}
		foreach ( $res as $row ) {
			$plus = $row->plus;
			$minus = $row->minus;
			$zero = $row->zero;
			$numvotes = $plus + $minus + $zero;
			$total = $plus - $minus;
			$totalvotes =
				htmlspecialchars( $row->vote_id ) . $sep .
				wfMessage( 'wigovotestotald' )
					->params( $numvotes, $plus, $zero, $minus )
					->title( $parser->getTitle() )
					->parse();
			$text = $parser->recursiveTagParse( $row->text );
			if ( $row->is_cp ) {
				self::addCaptureImages( $text, $parser, $frame );
			}
			$output .= "<tr><td width=\"20px\" valign=\"top\" title=\"{$totalvotes}\">" .
				"{$total}</td><td>{$text}</td></tr>";
		}
		$output .= "</table>";

		return $output;
	}
}
