<?php

/**
 * Input class for BlueSpiceSocial
 *
 * add desc
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
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Patric Wirth
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */
namespace BlueSpice\Social\Parser;

/**
 * Input class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
class Input {
	protected $aAllowedTags = [];

	public function __construct() {
	}

	/**
	 *
	 * @param string $sText
	 * @return string
	 */
	public function parse( $sText ) {
		if ( empty( $sText ) ) {
			return '';
		}
		if ( !is_scalar( $sText ) ) {
			if ( is_callable( [ $sText, 'toString' ] ) ) {
				$sText = (string)$sText;
			} else {
				return '';
			}
		}
		$sText = str_ireplace( [ "<br />", "<br>", "<br/>" ], "\n", $sText );
		$sText = str_replace( "\r\n", "\n", $sText );
		$sText = $this->stripTags( $sText );
		$sText = $this->trimLines( $sText );
		$sText = $this->trimEnd( $sText );
		return $sText;
	}

	/**
	 *
	 * @param string $sText
	 * @return string
	 */
	protected function stripTags( $sText ) {
		$sAllowedTags = '';
		foreach ( $this->getAllowedTags() as $sTag ) {
			$sAllowedTags .= "<$sTag><$sTag/>";
		}
		return strip_tags( $sText, $sAllowedTags );
	}

	/**
	 *
	 * @param string $sText
	 * @return string
	 */
	protected function trim( $sText ) {
		return trim( $sText );
	}

	/**
	 *
	 * @param string $sText
	 * @return string
	 */
	protected function trimLines( $sText ) {
		$sNewText = '';
		foreach ( explode( "\n", $sText ) as $sLine ) {
			$sNewText .= $this->trim( $sLine ) . "\n";
		}
		return $sNewText;
	}

	/**
	 *
	 * @param string $sText
	 * @return string
	 */
	protected function trimEnd( $sText ) {
		return rtrim( $sText );
	}

	/**
	 *
	 * @return array
	 */
	protected function getAllowedTags() {
		return $this->aAllowedTags;
	}

	/**
	 *
	 * @param array $aAllowedTags
	 * @return Input
	 */
	protected function setAllowedTags( $aAllowedTags = [] ) {
		$this->aAllowedTags = $aAllowedTags;
		return $this;
	}
}
