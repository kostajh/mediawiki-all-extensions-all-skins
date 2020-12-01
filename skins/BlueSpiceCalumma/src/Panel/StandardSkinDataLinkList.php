<?php

namespace BlueSpice\Calumma\Panel;

abstract class StandardSkinDataLinkList extends BasePanel {

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		$originalDefinitions = $this->getStandardSkinDataLinkListDefinition();
		$linkDefs = $this->makeLinkDefs( $originalDefinitions );

		$links = new LinkList( 'dummy-id', [
			'content' => $linkDefs
		] );

		return $links->getBody();
	}

	private function makeLinkDefs( $links ) {
		$linkDefs = [];

		foreach ( $links as $linkKey => $linkDesc ) {
			if ( $this->skipLink( $linkKey ) ) {
				continue;
			}

			if ( empty( $linkDesc['text'] ) ) {
				$linkDesc['text'] = wfMessage( $linkKey )->text();
			}

			if ( empty( $linkDesc['title'] ) ) {
				$linkDesc['title'] = $linkDesc['text'];
			}

			if ( empty( $linkDesc['href'] ) ) {
				$linkDesc['href'] = '#';
			}
			if ( empty( $linkDesc['data'] ) ) {
				$linkDesc['data'] = [];
			}
			$reformattedData = [];
			foreach ( $linkDesc['data'] as $dataName => $dataValue ) {
				$reformattedData[] = [
					'key' => $dataName,
					'value' => $dataValue
				];
			}
			$linkDesc['data'] = $reformattedData;

			$linkDef = [
				'id' => isset( $linkDesc['id'] ) ? $linkDesc['id'] : '',
				'title' => $linkDesc['title'],
				'text' => $linkDesc['text'],
				'href' => $linkDesc['href'],
				'data' => $linkDesc['data']
			];

			$linkDefs[] = $linkDef;
		}
		return $linkDefs;
	}

	/**
	 *
	 * @param string $linkKey
	 * @return bool
	 */
	protected function skipLink( $linkKey ) {
		return false;
	}

	/**
	 * @return array
	 */
	abstract protected function getStandardSkinDataLinkListDefinition();
}
