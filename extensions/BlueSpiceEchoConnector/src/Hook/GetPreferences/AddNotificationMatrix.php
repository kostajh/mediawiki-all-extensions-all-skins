<?php

namespace BlueSpice\EchoConnector\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;
use MediaWiki\MediaWikiServices;
use Message;

class AddNotificationMatrix extends GetPreferences {

	/** @var array */
	private $actions = [ 'create', 'edit' ];
	/** @var array|null */
	protected $categories = null;

	protected function doProcess() {
		$contLang = $this->getContext()->getLanguage();

		// Add namespace selection matrix
		$arrNamespaces = [];

		foreach ( $contLang->getFormattedNamespaces() as $ns => $title ) {
			if ( is_numeric( $title ) ) {
				// whenever the namespace text comes out to be numeric, php will
				// turn it into an integer when using it as an array key. This is
				// somewhat of an ugly workaround, that is not spotted in the ui
				// It is needed due to the HTMLCheckboxMatric throwing an exception
				// whenever the label is an integer.
				// This label has no other function than show the namespaces
				// display text.
				$title = "$title ";
			}
			if ( $ns > 0 ) {
				$arrNamespaces[ $title ] = $ns;
			} elseif ( $ns == 0 ) {
				$arrNamespaces[ Message::newFromKey( 'bs-ns_main' )->text() ] = $ns;
			}
		}

		$this->preferences[ 'notify-namespace-selection' ] = [
			'type' => 'checkmatrix',
			'section' => 'echo/namespace-notifications',
			// a system message (optional)
			'help-message' => 'tog-help-notify-namespace-selection',
			'rows' => $arrNamespaces,
			'columns' => $this->getColumns()
		];

		$cats = $this->getAvailableCategories();
		if ( count( $cats ) < 1 ) {
			return true;
		}
		$rows = [];
		foreach ( $cats as $cat ) {
			$rows[str_replace( '_', ' ', $cat )] = $cat;
		}

		$this->preferences[ 'notify-category-selection' ] = [
			'type' => 'checkmatrix',
			'section' => 'echo/category-notifications',
			'rows' => $rows,
			'columns' => $this->getColumns(),
			'id' => 'bs-echoconnector-notify-category-selection-matrix'
		];
		$this->getContext()->getOutput()->addModules(
			'ext.bluespice.echoConnector.preferences'
		);

		return true;
	}

	/**
	 * @return array
	 */
	private function getColumns() {
		$columns = [];
		foreach ( $this->actions as $action ) {
			$columns[ wfMessage( $action )->text() ] = "page-" . $action;
		}
		return $columns;
	}

	/**
	 * @return array|null
	 */
	private function getAvailableCategories() {
		if ( $this->categories === null ) {
			$db = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection(
				DB_REPLICA
			);
			$res = $db->select(
				'category',
				'cat_title',
				[],
				__METHOD__
			);
			$this->categories = [];
			foreach ( $res as $row ) {
				$this->categories[] = $row->cat_title;
			}
		}

		return $this->categories;
	}
}
