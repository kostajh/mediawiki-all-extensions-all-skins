<?php
/**
 * Copyright (C) 2017-2020 Kunal Mehta <legoktm@member.fsf.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace AutoGallery;

use File;
use MediaWiki\MediaWikiServices;
use NolinesImageGallery;

class AutoGallery extends NolinesImageGallery {
	public function __construct( $mode = 'traditional', $context = null ) {
		parent::__construct( $mode, $context );
		// Output CSS 'nolines' class
		$this->mMode = 'nolines';
	}

	public function add( $title, $html = '', $alt = '', $link = '', $handlerOpts = [] ) {
		if ( $title instanceof File ) {
			// Old calling convention
			$title = $title->getTitle();
		}

		if ( !strlen( $html ) ) {
			// The default caption should be the filename without namespace and extension
			$text = $title->getRootText();
			$exp = explode( '.', $text );
			if ( count( $exp ) > 1 ) {
				array_pop( $exp );
				$html = htmlspecialchars( implode( '.', $exp ) );
			} else {
				$html = htmlspecialchars( $text );
			}
		}

		// If there's no link, default to the absolute URL of the file
		if ( !$link ) {
			if ( method_exists( MediaWikiServices::class, 'getRepoGroup' ) ) {
				// MediaWiki 1.34+
				$link = MediaWikiServices::getInstance()->getRepoGroup()
					->findFile( $title )->getUrl();
			} else {
				$link = wfFindFile( $title )->getUrl();
			}
		}

		$this->mImages[] = [ $title, $html, $alt, $link, $handlerOpts ];
	}
}
