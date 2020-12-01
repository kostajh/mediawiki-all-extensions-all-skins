<?php

namespace BlueSpice\VisualEditorConnector\Hook\NamespaceManagerEditNamespace;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerEditNamespace;

class SetVisualEditorValues extends NamespaceManagerEditNamespace {

	protected function doProcess() {
		if ( !$this->useInternalDefaults && isset( $this->additionalSettings['visualeditor'] ) ) {
			$this->namespaceDefinition[$this->nsId][ 'visualeditor' ] =
				$this->additionalSettings['visualeditor'];
		} else {
			$this->namespaceDefinition[$this->nsId][ 'visualeditor' ] = false;
		}

		return true;
	}

}
