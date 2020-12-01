<?php

namespace BlueSpice\CustomMenu\Hook\OutputPageBodyAttributes;

use BlueSpice\Hook\OutputPageBodyAttributes;

class AddOutputPageBodyClass extends OutputPageBodyAttributes {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		foreach ( explode( ' ', $this->bodyAttrs[ 'class' ] ) as $class ) {
			if ( empty( $class ) ) {
				continue;
			}
			if ( trim( $class ) !== 'bs-cusom-menu-active' ) {
				continue;
			}
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->bodyAttrs[ 'class' ] .= ' bs-cusom-menu-active ';
		return true;
	}

}
