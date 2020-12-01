<?php

namespace BlueSpice\PageAssignments\Hook\NamespaceManagerWriteNamespaceConfiguration;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerWriteNamespaceConfiguration;

class WriteToConfiguration extends NamespaceManagerWriteNamespaceConfiguration {

	protected function doProcess() {
		$this->writeConfiguration(
			"PageAssignmentsSecureEnabledNamespaces",
			"pageassignments-secure"
		);

		return true;
	}

}
