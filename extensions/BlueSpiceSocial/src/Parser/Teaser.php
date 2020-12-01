<?php
/**
 * Teaser class for BlueSpiceSocial
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
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

namespace BlueSpice\Social\Parser;

/**
 * Teaser class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
class Teaser extends Input {
	protected $iMinWordCount = 20;
	protected $iMaxWordCount = 40;
	protected $aShortenOnChars = [
		'.',
		':',
		"\n",
		",",
	];

	/**
	 * @param string $sText
	 * @return string
	 */
	public function parse( $sText ) {
		$sText = parent::parse( $sText );
		if ( empty( $sText ) ) {
			return $sText;
		}
		$sText = $this->shortenByWordCount( $sText );
		return $sText;
	}

	/**
	 *
	 * @param string $sText
	 * @return string
	 */
	protected function shortenByWordCount( $sText ) {
		$aText = explode( ' ', $sText );
		if ( count( $aText ) <= $this->getMinWordCount() ) {
			return $sText;
		}
		// $aNewText = array_splice( $aText, 0, $this->getMinWordCount() );
		$iCut = isset( $aText[$this->getMaxWordCount()] )
			? $this->getMaxWordCount()
			: count( $aText ) - 1;

		$iNewCut = false;
		foreach ( $this->getShortenOnChars() as $sChar ) {
			foreach ( $aText as $iKey => $sTextFragment ) {
				if ( $iKey < $this->getMinWordCount() ) {
					continue;
				}
				if ( $iKey > $iCut ) {
					break;
				}
				$iPos = strpos( $sTextFragment, $sChar );
				if ( $iPos === false ) {
					continue;
				}
				$iNewCut = $iKey;
				$aText[$iKey] = substr( $sTextFragment, 0, $iPos + 1 );
				break;
			}
			if ( $iNewCut ) {
				$iCut = $iNewCut;
				break;
			}
		}

		return implode( ' ', array_splice( $aText, 0, $iCut + 1 ) );
	}

	/**
	 *
	 * @return int
	 */
	public function getMinWordCount() {
		return $this->iMinWordCount;
	}

	/**
	 *
	 * @param int $iMinWordCount
	 * @return Teaser
	 */
	public function setMinWordCount( $iMinWordCount ) {
		$this->iMinWordCount = $iMinWordCount;
		return $this;
	}

	/**
	 *
	 * @return int
	 */
	public function getMaxWordCount() {
		return $this->iMaxWordCount;
	}

	/**
	 *
	 * @param int $iMaxWordCount
	 * @return Teaser
	 */
	public function setMaxWordCount( $iMaxWordCount ) {
		$this->iMaxWordCount = $iMaxWordCount;
		return $this;
	}

	/**
	 *
	 * @return array
	 */
	public function getShortenOnChars() {
		return $this->aShortenOnChars;
	}

	/**
	 *
	 * @param array $aShortenOnChars
	 * @return Teaser
	 */
	public function setShortenOnChars( $aShortenOnChars ) {
		$this->aShortenOnChars = $aShortenOnChars;
		return $this;
	}
}
