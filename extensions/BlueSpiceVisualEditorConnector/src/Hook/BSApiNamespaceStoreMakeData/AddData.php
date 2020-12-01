<?php

namespace BlueSpice\VisualEditorConnector\Hook\BSApiNamespaceStoreMakeData;

use BlueSpice\NamespaceManager\Hook\BSApiNamespaceStoreMakeData;

class AddData extends BSApiNamespaceStoreMakeData {

	protected function doProcess() {
		foreach ( $this->results as $key => &$result ) {

			$result['visualeditor'] = [
				'value' => $this->checkAvailability( $result ),
				'disabled' => false
			];

		}

		return true;
	}

	/**
	 *
	 * @param array $nsInfo
	 * @return bool
	 */
	protected function checkAvailability( $nsInfo ) {
		$enabledNamespaces = $this->getConfig()->get( 'VisualEditorAvailableNamespaces' );
		// OMG, this array mixes canonical names and ids. In the VisualEditor implementation, numeric
		// indices take precedence over named ones. See ApiVisualEditor::getAvailableNamespaceIds.
		// This behavior is reproduced here.

		if ( isset( $enabledNamespaces[$nsInfo['id']] ) && $enabledNamespaces[$nsInfo['id']] === true ) {
			return true;
		} elseif ( isset( $enabledNamespaces[$nsInfo['name']] ) &&
			$enabledNamespaces[$nsInfo['name']] === true ) {
			return true;
		}

		return false;
	}
}
