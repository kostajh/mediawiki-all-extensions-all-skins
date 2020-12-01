<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\SkinData;
use Skins\Chameleon\IdRegistry;

class Toolbox extends StandardSkinDataLinkList {

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return new \Message( 'bs-sitetools-toolbox' );
	}

	/**
	 *
	 * @param string $linkKey
	 * @return bool
	 */
	protected function skipLink( $linkKey ) {
		$blacklist = $this->skintemplate->data[SkinData::TOOLBOX_BLACKLIST];
		return in_array( $linkKey, $blacklist );
	}

	/**
	 *
	 * @return array
	 */
	protected function getStandardSkinDataLinkListDefinition() {
		$toolbox = $this->skintemplate->getToolbox();
		$sorter = 0;
		$sortKeys = [];
		// split multilink toolbox items such as feeds into single links as the link
		// panels currently do not support multiple links in one toolbox item
		foreach ( $toolbox as $key => $item ) {
			if ( !isset( $item['links'] ) ) {
				$sortKeys[$key] = $sorter;
				$sorter++;
				continue;
			}
			foreach ( $item['links'] as $name => $link ) {
				$toolbox[$name] = $link;
				$sortKeys[$name] = $sorter;
			}
			unset( $toolbox[$key] );
			$sorter++;
		}
		uksort( $toolbox, function ( $a, $b ) use ( $sortKeys ) {
			if ( $sortKeys[$a] === $sortKeys[$b] ) {
				return 0;
			}
			return $sortKeys[$a] < $sortKeys[$b] ? -1 : 1;
		} );
		return $toolbox;
	}

	/**
	 *
	 * @var string
	 */
	protected $htmlId = null;

	/**
	 * The HTML ID for thie component
	 * @return string
	 */
	public function getHtmlId() {
		if ( $this->htmlId === null ) {
			$this->htmlId = IdRegistry::getRegistry()->getId( 'bs-toolbox-panel' );
		}
		return $this->htmlId;
	}

}
