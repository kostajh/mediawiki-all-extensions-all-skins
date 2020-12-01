<?php

namespace BlueSpice\Social\Topics\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddNoDiscussionSwitch extends BSInsertMagicAjaxGetData {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		return $this->type !== 'switches';
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->response->result[] = (object)[
			'id' => 'bs:nodiscussion',
			'type' => 'switch',
			'name' => 'NODISCUSSION',
			'desc' => \Message::newFromKey(
				'bs-socialtopics-switch-nodiscussion-description'
			)->plain(),
			'code' => $this->getCode(),
			'previewable' => false,
			'helplink' => $this->getHelpLink(),
		];
		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getCode() {
		return '__NODISCUSSION__';
	}

	/**
	 *
	 * @return string
	 */
	protected function getHelpLink() {
		$extensions = \ExtensionRegistry::getInstance()->getAllThings();
		if ( !isset( $extensions['BlueSpiceSocialTopics'] ) ) {
			return '';
		}
		return $extensions['BlueSpiceSocialTopics']['url'];
	}
}
