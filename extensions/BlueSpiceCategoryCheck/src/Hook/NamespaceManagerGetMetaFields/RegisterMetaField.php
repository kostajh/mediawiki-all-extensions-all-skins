<?php

namespace BlueSpice\CategoryCheck\Hook\NamespaceManagerGetMetaFields;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerGetMetaFields;

class RegisterMetaField extends NamespaceManagerGetMetaFields {

	protected function doProcess() {
		$this->metaFields[] = [
			'name' => 'categorycheck',
			'type' => 'boolean',
			'label' => wfMessage( 'bs-categorycheck-nsm-label-categorycheck' )->plain(),
			'filter' => [
				'type' => 'boolean'
			]
		];

		return true;
	}

}
