<?php

namespace BlueSpice\VisualEditorConnector\Hook\NamespaceManagerGetMetaFields;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerGetMetaFields;

class RegisterMetaFields extends NamespaceManagerGetMetaFields {

	protected function doProcess() {
		$this->metaFields[] = [
			'name' => 'visualeditor',
			'type' => 'boolean',
			'label' => wfMessage( 'bs-visualeditor-nsm-label' )->plain(),
			'filter' => [
				'type' => 'boolean'
			]
		];

		return true;
	}
}
