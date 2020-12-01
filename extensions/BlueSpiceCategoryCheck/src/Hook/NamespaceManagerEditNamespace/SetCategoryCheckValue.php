<?php

namespace BlueSpice\CategoryCheck\Hook\NamespaceManagerEditNamespace;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerEditNamespace;

class SetCategoryCheckValue extends NamespaceManagerEditNamespace {

	protected function doProcess() {
		if ( !$this->useInternalDefaults
			&& isset( $this->additionalSettings['categorycheck'] ) ) {
			$this->namespaceDefinition[$this->nsId][ 'categorycheck' ]
				= $this->additionalSettings['categorycheck'];
		} else {
			$this->namespaceDefinition[$this->nsId][ 'categorycheck' ] = false;
		}
		return true;
	}

}
