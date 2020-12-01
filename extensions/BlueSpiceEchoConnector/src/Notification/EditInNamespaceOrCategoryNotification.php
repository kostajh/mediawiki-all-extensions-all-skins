<?php

namespace BlueSpice\EchoConnector\Notification;

class EditInNamespaceOrCategoryNotification extends EditNotification {

	/**
	 * @inheritDoc
	 */
	public function __construct(
		$agent, $title, $revision, $summary, $key = 'bs-page-in-namespace-category-edit'
	) {
		parent::__construct( $agent, $title, $revision, $summary, $key );
	}
}
