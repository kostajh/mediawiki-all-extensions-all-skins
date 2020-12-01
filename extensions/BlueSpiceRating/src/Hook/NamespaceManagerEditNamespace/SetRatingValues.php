<?php

namespace BlueSpice\Rating\Hook\NamespaceManagerEditNamespace;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerEditNamespace;

class SetRatingValues extends NamespaceManagerEditNamespace {

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		if ( !$this->useInternalDefaults && isset( $this->additionalSettings['rating'] ) ) {
			$this->namespaceDefinition[$this->nsId][ 'rating' ] = $this->additionalSettings['rating'];
		} else {
			$this->namespaceDefinition[$this->nsId][ 'rating' ] = false;
		}

		if ( !$this->useInternalDefaults && isset( $this->additionalSettings['recommendations'] ) ) {
			$this->namespaceDefinition[$this->nsId][ 'recommendations' ]
				= $this->additionalSettings['recommendations'];
		} else {
			$this->namespaceDefinition[$this->nsId][ 'recommendations' ] = false;
		}
		return true;
	}

}
