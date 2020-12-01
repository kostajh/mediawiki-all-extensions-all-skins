<?php
/*
 * Copyright (C) 2013, Miguel Peláez
 *
 * This file is part of the UpdateMediaWiki extension.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @file
 * @ingroup Extensions
 * @author Miguel PelÃ¡ez <miguel2706outlook.com>
 * @license https://www.gnu.org/copyleft/gpl.html GPL-2.0-or-later
 * @link https://www.mediawiki.org/wiki/Extension:UpdateMediaWiki Documentation
 */
class SpecialUpdateMediaWiki extends SpecialPage {

	function __construct() {
		parent::__construct( 'UpdateMediaWiki', 'updatecore' );
	}

	function execute( $par ) {
		// ...
		if ( !$this->userCanExecute( $this->getUser() ) ) {
			$this->displayRestrictionError();
			return;
		}
		// ...
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();

		global $wgVersion, $IP;
		$getVersion = json_decode( file_get_contents( 'https://www.mediawiki.org/w/api.php?action=parse&format=json&text=%7B%7B%23invoke%3AVersion%7Cget%7Cstable%7Cversion%7D%7D&prop=text&disablelimitreport=1&disableeditsection=1&preview=1&disabletoc=1&contentmodel=wikitext' ), true ) or die( 'ERROR' );
		$aV = $getVersion["parse"]["text"]["*"];
		$updated = false;
		$found = false;
		if ( !empty( $aV ) ) {
			// If we managed to access that file, then lets break up those release versions into an array.
			$output->addWikiTextAsInterface( "== " . $this->msg( 'updatemediawiki-current', $wgVersion )->text() . " ==" );
			$aV = str_replace( "<p>", "", $aV );
			$aV = str_replace( "</p>", "", $aV );
			$aV = str_replace( "\n", "", $aV );
			$mainVersion = substr( $aV, 0, 4 );
			if ( $aV > $wgVersion ) {
				$output->addWikiTextAsInterface( "<p>" . $this->msg( 'updatemediawiki-update-found', "v{$aV}" )->text() . "</p>" );
				$found = true;
				 // Download The File If We Do Not Have It
				if ( !is_file( __DIR__ . "/../Updates/mediawiki-{$aV}.tar.gz" ) ) {
					$output->addWikiTextAsInterface( "<p>" . wfMessage( 'updatemediawiki-update-downloading' )->text() . "</p>" );
					$newUpdate = file_get_contents( "https://download.wikimedia.org/mediawiki/{$mainVersion}/mediawiki-{$aV}.tar.gz" );
					if ( !is_dir( __DIR__ . '/../Updates' ) ) {
						mkdir( __DIR__ . "/../Updates", 0755 );
					}
					$dlHandler = fopen( __DIR__ . "/../Updates/mediawiki-{$aV}.tar.gz", 'w' );
					if ( !fwrite( $dlHandler, $newUpdate ) ) {
						$output->addWikiTextAsInterface( "<p>" . $this->msg( 'updatemediawiki-update-aborted' )->text() . "</p>" );
						exit();
					}
					fclose( $dlHandler );
					$output->addWikiTextAsInterface( "<p>" . $this->msg( 'updatemediawiki-update-saved' )->text() . "</p>" );
				} else {
					$output->addWikiTextAsInterface( "<p>" . $this->msg( 'updatemediawiki-update-exist' )->text() . "</p>" );
				}

				if ( $par == "doUpdate" ) {
					// Open The File And Do Stuff
					$gz = new PharData( "/../Updates/mediawiki-{$aV}.tar.gz" );
					$output->addWikiTextAsInterface( "<ul>" );

					// Make the directory if we need to...
					if ( !is_dir( __DIR__ . "/../Updates{$aV}" ) ) {
						mkdir( __DIR__ . "/../Updates{$aV}", 0755 );
					}

					// Overwrite the file

					$gz = decompress(); // creates files.tar
					// unarchive from the tar
					$phar = new PharData( "/../Updates/mediawiki-{$aV}.tar" );
					$phar->extractTo( $IP );
					$output->addWikiTextAsInterface( "<p>" . $this->msg( 'updatemediawiki-update-ready' )->text() . " [$IP/mw-config " . wfMessage( 'updatemediawiki-update-database' )->text() . "]</p>" );

					$output->addWikiTextAsInterface( "</ul>" );
					$updated = true;
				} else {
					$output->addWikiTextAsInterface( "<p>" . $this->msg( 'updatemediawiki-update-updateready' )->text() . " [[Special:UpdateMediaWiki/doUpdate|" . $this->msg( 'updatemediawiki-update-install' )->text() . "]]</p>" );
				}
			}
		}

		if ( $updated === true ) {
			$output->addWikiTextAsInterface( '<p class="success">&raquo; ' . $this->msg( 'updatemediawiki-update-updated', $aV )->text() . '</p>' );
		} elseif ( $found !== true ) {
			$output->addWikiTextAsInterface( '<p>' . $this->msg( 'updatemediawiki-update-nofound' )->text() . '</p>' );
		} else {
			$output->addWikiTextAsInterface( '<p>' . $this->msg( 'updatemediawiki-update-error' )->text() . '</p>' );
		}
	}

	protected function getGroupName() {
		return 'wiki';
	}

}
