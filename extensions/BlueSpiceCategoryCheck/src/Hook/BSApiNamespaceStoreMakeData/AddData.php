<?php

namespace BlueSpice\CategoryCheck\Hook\BSApiNamespaceStoreMakeData;

use BlueSpice\NamespaceManager\Hook\BSApiNamespaceStoreMakeData;

class AddData extends BSApiNamespaceStoreMakeData {

	protected function doProcess() {
		$enabledNamespace = $this->getConfig()->get( 'CategoryCheckNamespaces' );

		foreach ( $this->results as $key => &$result ) {
			$result['categorycheck'] =
				in_array( $result[ 'id' ], $enabledNamespace );
		}

		return true;
	}

}
