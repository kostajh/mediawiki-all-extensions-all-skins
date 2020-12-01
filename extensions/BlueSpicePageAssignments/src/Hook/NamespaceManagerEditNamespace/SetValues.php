<?php

namespace BlueSpice\PageAssignments\Hook\NamespaceManagerEditNamespace;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerEditNamespace;

class SetValues extends NamespaceManagerEditNamespace {

	protected function doProcess() {
		$this->namespaceDefinition[$this->nsId]['pageassignments-secure']
			= false;
		if ( $this->useInternalDefaults ) {
			return true;
		}
		if ( isset( $this->additionalSettings['pageassignments-secure'] ) ) {
			$this->namespaceDefinition[$this->nsId]['pageassignments-secure']
				= $this->additionalSettings['pageassignments-secure'];
		}
		return true;
	}

}
