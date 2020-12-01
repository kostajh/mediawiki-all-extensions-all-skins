<?php

namespace MediaWiki\Extension\FlexiSkin;

use MWStake\MediaWiki\Component\CommonUserInterface\LessVars;

abstract class PluginBase implements IPlugin {
	/**
	 * @return IPlugin
	 */
	public static function factory() {
		return new static();
	}

	/**
	 * @param IFlexiSkin $skin
	 * @return array
	 */
	public function getLessVars( IFlexiSkin $skin ): array {
		$vars = [];

		foreach ( $this->getLessVarsMap() as $var => $key ) {
			$value = $skin->getValueForPath( $key );
			if ( $value !== null ) {
				$vars[$var] = $value;
			}
		}

		return $vars;
	}

	/**
	 * @inheritDoc
	 */
	public function adaptConfiguration( &$config ) {
		// STUB
	}

	/**
	 * @inheritDoc
	 */
	public function setDefaults( IFlexiSkin $skin, &$config ) {
		$lessVars = LessVars::getInstance()->getAllVars();

		foreach ( $this->getLessVarsMap() as $var => $key ) {
			if ( !isset( $lessVars[$var] ) ) {
				continue;
			}
			if ( $skin->getValueForPath( $key ) !== null ) {
				continue;
			}
			$keyBits = explode( '/', $key );
			$data = $lessVars[$var];
			if ( $data === null ) {
				continue;
			}
			// Do one-level substitute of referenced variables
			if ( substr( $data, 0, 1 ) === '@' ) {
				$refVar = substr( $data, 1, strlen( $data ) );
				if ( !isset( $lessVars[$refVar] ) ) {
					continue;
				}
				$data = $lessVars[$refVar];
			}
			// Set value to correct path of $config
			foreach ( array_reverse( $keyBits ) as $bit ) {
				$data = [ $bit => $data ];
			}
			// Apply new value to $config
			$config = array_merge_recursive( $config, $data );
		}
	}

	/**
	 * @return array
	 */
	public function getLessVarsMap(): array {
		return [];
	}
}
