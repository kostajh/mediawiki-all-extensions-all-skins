<?php
/**
 WantedPagesFromNS v1.0.0 beta -- Shows list of wanted page from specified namespace

 Author: Kazimierz Król

 Code based largely on DPL Forum extension by Ross McClure
 https://www.mediawiki.org/wiki/User:Algorithm

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License along
 with this program; if not, write to the Free Software Foundation, Inc.,
 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 http://www.gnu.org/copyleft/gpl.html

 * @file
 * @ingroup Extensions
 */

class WantedPagesFromNS {

	/**
	 * Register the <wantedpagens> tag with the parser.
	 *
	 * @param Parser $parser
	 */
	public static function onParserFirstCallInit( $parser ) {
		$parser->setHook( 'wantedpagens', [ __CLASS__, 'renderPageList' ] );
	}

	/**
	 * Callback for the above function, renders contents of the <wantedpagens> tag.
	 *
	 * @param string $input User-supplied input, if any
	 * @param array $args User-supplied arguments to the tag, if any
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string
	 */
	public static function renderPageList( $input, array $args, Parser $parser, PPFrame $frame ) {
		$f = new WantedPagesFromNS();
		return $f->parse( $input, $parser );
	}

	// Gets value from the parameter list
	function get( $name, $value = null, $parser = null ) {
		if ( preg_match( "/^\s*$name\s*=\s*(.*)/mi", $this->sInput, $matches ) ) {
			$arg = trim( $matches[1] );
			if ( is_int( $value ) ) {
				return intval( $arg );
			} elseif ( is_null( $parser ) ) {
				return htmlspecialchars( $arg );
			} else {
				return $parser->replaceVariables( $arg );
			}
		}
		return $value;
	}

	function msg( $type, $error = null ) {
		if ( $error && ( $this->get( 'suppresserrors' ) == 'true' ) ) {
			return '';
		}

		return wfMessage( $type )->escaped();
	}

	function parse( &$input, &$parser ) {
		$this->sInput =& $input;

		$arg = $this->get( 'namespace', '', $parser );
		$iNamespace = MediaWiki\MediaWikiServices::getInstance()->getContentLanguage()->getNsIndex( $arg );
		if ( !$iNamespace ) {
			if ( ( $arg ) || ( $arg === '0' ) ) {
				$iNamespace = intval( $arg );
			} else {
				$iNamespace = - 1;
			}
		}
		if ( $iNamespace < 0 ) {
			return $this->msg( 'wpfromns-nons', 1 );
		}

		$output = '';

		$count = 1;
		$start = 0;
		if ( !( $this->get( 'cache' ) == 'true' ) ) {
			$parser->getOutput()->updateCacheExpiry( 0 );
		}
		if ( $start < 0 ) {
			$start = 0;
		}

		$dbr = wfGetDB( DB_REPLICA );
		// The SQL below is derived from includes/specials/SpecialWantedpages.php
		$res = $dbr->select(
			[
				'pagelinks',
				'pg1' => 'page',
				'pg2' => 'page'
			],
			[
				'namespace' => 'pl_namespace',
				'title' => 'pl_title',
				'value' => 'COUNT(*)'
			],
			[
				'pg1.page_namespace IS NULL',
				'pl_namespace' => $iNamespace
				// 'pg2.page_namespace != ' . NS_MEDIAWIKI
			],
			__METHOD__,
			[ 'GROUP BY' => [ 'pl_namespace', 'pl_title' ] ],
			[
				'pg1' => [
					'LEFT JOIN', [
						'pl_namespace = pg1.page_namespace',
						'pl_title = pg1.page_title'
					]
				],
				'pg2' => [ 'LEFT JOIN', 'pl_from = pg2.page_id' ]
			]
		);

		foreach ( $res as $row ) {
			$title = Title::makeTitle( $row->namespace, $row->title );

			$wlh = SpecialPage::getTitleFor( 'Whatlinkshere' );
			$label = wfMessage( 'wpfromns-links', $row->value )->text();

			$output .= '<li>' . Linker::link( $title, $title->getText(), [], [], [ 'broken' ] ) .
				' ('  . Linker::link( $wlh , $label, [], [ 'target' => $title->getPrefixedText() ] ) .
				')' . "</li>\n";
		}

		if ( $output ) {
			return '<ul>' . $output . "</ul>\n";
		} else {
			// no pages found
			return wfMessage( 'wpfromns-nores' )->text();
		}
	}
}
