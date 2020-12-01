<?php

use BlueSpice\ContextMenu\IMenuItem;
use BlueSpice\ExtensionAttributeBasedRegistry;

class BSApiContextMenuTasks extends BSApiTasksBase {

	protected $aTasks = [
		'getMenuItems' => [
			'examples' => [
				[
					'title' => 'Main_page'
				]
			],
			'params' => [
				'title' => [
					'desc' => 'Valid Title value',
					'type' => 'string',
					'required' => true
				]
			]
		]
	];

	/**
	 *
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [ 'getMenuItems' => [ 'read' ] ];
	}

	/**
	 *
	 * @param type $oData
	 * @param type $aParams
	 * @return type
	 * @throws Exception
	 */
	protected function task_getMenuItems( $oData, $aParams ) {
		$oResult = $this->makeStandardReturn();

		if ( !isset( $oData->title ) || empty( $oData->title ) ) {
			return $oResult;
		}

		$oTitle = Title::newFromText( $oData->title );

		$itemsObjects = [];
		$itemFactories = new ExtensionAttributeBasedRegistry( 'BlueSpiceContextMenuItemFactories' );
		foreach ( $itemFactories->getAllKeys() as $itemId ) {
			$factoryCallback = $itemFactories->getValue( $itemId );
			if ( !is_callable( $factoryCallback ) ) {
				throw new Exception( "Callback for '$itemId' invalid!" );
			}
			$item = call_user_func_array( $factoryCallback, [ $oTitle ] );
			if ( $item instanceof BlueSpice\ContextMenu\IMenuItem === false ) {
				throw new Exception( "Callback for '$itemId' returned no IMenuItem!" );
			}
			$itemsObjects[$itemId] = $item;
		}

		$items = $this->convertItemObjectsToArray( $itemsObjects );
		$this->getServices()->getHookContainer()->run( 'BsContextMenuGetItems', [
			&$items,
			$oTitle
		] );

		$return = $this->returnItems( $oResult, $items );
		return $return;
	}

	/**
	 *
	 * @param type &$oResult
	 * @param array $aItems
	 * @return type
	 */
	protected function returnItems( &$oResult, $aItems ) {
		$oResult->success = true;
		$oResult->payload_count = count( $aItems );
		$oResult->payload = [ 'items' => $aItems ];
		return $oResult;
	}

	/**
	 *
	 * @return bool
	 */
	public function isWriteMode() {
		return false;
	}

	/**
	 *
	 * @param IMenuItem[] $itemsObjects
	 * @return array
	 */
	private function convertItemObjectsToArray( $itemsObjects ) {
		$items = [];

		usort( $itemsObjects, function ( $a, $b ) {
			return $a->getPosition() > $b->getPosition();
		} );

		foreach ( $itemsObjects as $item ) {
			if ( !$item->shouldList( $this->getContext() ) ) {
				continue;
			}
			$itemArray = [
				'text' => $item->getLabelMessage()->text(),
				'href' => $item->getUrl(),
				'id' => $item->getId(),
				'iconCls' => $item->getIconClass()
			];
			$items[] = $itemArray;
		}

		return $items;
	}

}
