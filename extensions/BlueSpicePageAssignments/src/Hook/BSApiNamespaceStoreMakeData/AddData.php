<?php

namespace BlueSpice\PageAssignments\Hook\BSApiNamespaceStoreMakeData;

use BlueSpice\NamespaceManager\Hook\BSApiNamespaceStoreMakeData;

class AddData extends BSApiNamespaceStoreMakeData {

	protected function doProcess() {
		$enabledNamespaces = $this->getConfig()->get(
			'PageAssignmentsSecureEnabledNamespaces'
		);
		foreach ( $this->results as &$result ) {

			$result['pageassignments-secure'] = [
				'value' => in_array( $result[ 'id' ], $enabledNamespaces ),
				'disabled' => $result['isTalkNS']
			];

		}

		return true;
	}
}
