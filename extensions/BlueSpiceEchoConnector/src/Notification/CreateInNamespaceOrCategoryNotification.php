<?php

namespace BlueSpice\EchoConnector\Notification;

class CreateInNamespaceOrCategoryNotification extends CreateNotification {

	/**
	 * @inheritDoc
	 */
	public function __construct(
		$agent, $title, $summary, $key = 'bs-page-in-namespace-category-create'
	) {
		parent::__construct( $agent, $title, $summary, $key );
	}
}
