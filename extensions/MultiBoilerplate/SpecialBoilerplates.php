<?php
/**
 * Special:boilerplates, provides a list of MediaWiki:Multiboilerplate or $wgMultiBoilerplateOptions
 * For more info see https://mediawiki.org/wiki/Extension:Multiboilerplate
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Extensions
 * @ingroup SpecialPage
 * @author Al Maghi
 *
 * @TODO fix code duplication (here and in MultiBoilerplate.hooks.php)
 * @TODO Use special page to actually edit [[MediaWiki:MultiBoilerplate]]?
 */

class SpecialBoilerplates extends IncludableSpecialPage {

	function __construct() {
		parent::__construct( 'Boilerplates' );
		$this->mIncludable = true;
	}

	public function execute( $par ) {
		global $wgMultiBoilerplateOptions;
		$output = $this->getOutput();
		$boilerplates = '';

		if ( !$this->mIncluding ) {
			$this->setHeaders();
			$output->addWikiMsg( 'multiboilerplate-special-pagetext' );
		}
		if ( is_array( $wgMultiBoilerplateOptions ) && !empty( $wgMultiBoilerplateOptions ) ) {
			foreach ( $wgMultiBoilerplateOptions as $name => $template ) {
				$boilerplates .= "* [[$template]]\n";
			}

			if ( !$this->mIncluding ) {
				$output->addWikiMsg( 'multiboilerplate-special-define-in-localsettings' );
			}
			$output->addWikiTextAsInterface( $boilerplates );

		} else {
			$rows = wfMessage( 'Multiboilerplate' )->inContentLanguage()->text();
			$rows = preg_split( '/\r\n|\r|\n/', $rows );

			foreach ( $rows as $row ) {
				if ( substr( ltrim( $row ), 0, 1 ) === '*' ) {
					$row = ltrim( $row, '* ' ); // Remove asterisk & spacing from start of line.
					$rowParts = explode( '|', $row );
					if ( !isset( $rowParts[ 1 ] ) ) {
						return true; // Invalid syntax, abort
					}

					$rowParts[1] = trim( $rowParts[1] );  // Clean whitespace that might break wikilinks

					// template names might have wikilinks in them to begin with, remove those
					$rowParts[1] = preg_replace( '/^\[\[/', '', $rowParts[1] );
					$rowParts[1] = preg_replace( '/\]\]$/', '', $rowParts[1] );

					$boilerplates .= "* [[$rowParts[1]|$rowParts[0]]]\n";
				}
			}

			if ( $boilerplates !== '' ) {
				if ( !$this->mIncluding ) {
					$output->addWikiMsg( 'multiboilerplate-special-define-in-interface' );
				}
				$output->addWikiTextAsInterface( $boilerplates );
			} else {
				// No boilerplates found in either configuration option!
				$output->wrapWikiMsg( "<div class=\"error\">$1</div>", 'multiboilerplate-special-no-boilerplates' );
			}
		}

		return true;
	}

	protected function getGroupName() {
		return 'pages';
	}
}
