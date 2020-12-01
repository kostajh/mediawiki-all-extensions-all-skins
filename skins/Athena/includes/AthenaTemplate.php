<?php

class AthenaTemplate extends BaseTemplate {

	/**
	 * Merge together the views, actions and variants
	 * and remove the current action, per useless and confusing
	 */
	function getActions() {
		global $mediaWiki;
		$actions = array_merge(
			$this->data['content_navigation']['views'],
			$this->data['content_navigation']['actions'],
			$this->data['content_navigation']['variants']
		);
		$action = $mediaWiki->getAction();
		unset( $actions[ $action ] ); // Remove the current action (doesn't work with Move)
		return $actions;
	}

	/**
	 * Output the page
	 */
	function execute() {
		include 'Athena.phtml';
	}
}
