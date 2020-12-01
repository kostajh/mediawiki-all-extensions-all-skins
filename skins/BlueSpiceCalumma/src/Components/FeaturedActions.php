<?php

namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\Controls\SplitButtonDropdown;
use BlueSpice\SkinData;
use MediaWiki\MediaWikiServices;
use Skins\Chameleon\Components\Component;

class FeaturedActions extends Component {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		if ( $this->skipRendering() ) {
			return '';
		}

		$data = $this->getSkinTemplate()->get( SkinData::FEATURED_ACTIONS );
		$items = [];

		foreach ( $data as $dataKey => $dataDef ) {
			$elements = $this->sortFeaturedActions( $dataDef );
			$items += [
				$dataKey => $elements
			];
		}

		$html = '<div class="bs-featured-actions">';

		if ( empty( $items ) ) {
			$html .= \Html::closeElement( 'div' );
			return $html;
		}

		foreach ( $items as $itemId => $itemDefinition ) {
			$splitButton = new SplitButtonDropdown(
				$this->getSkinTemplate(),
				$this->makeSplitButtonDropDownData( $itemId, $itemDefinition )
			);
			$html .= $splitButton->getHtml();
		}
		$html .= \Html::closeElement( 'div' );

		return $html;
	}

	/**
	 *
	 * @param string $itemId
	 * @param array $itemDefinition
	 * @return bool
	 */
	protected function makeSplitButtonDropDownData( $itemId, $itemDefinition ) {
		$firstEntry = array_shift( $itemDefinition );

		$splitButtonData = $firstEntry;
		if ( !isset( $splitButtonData['classes'] ) ) {
			$splitButtonData['classes'] = [ 'btn-primary', "bs-fa-$itemId" ];
		} else {
			$splitButtonData['classes'] = array_merge(
					$splitButtonData['classes'],
					[ 'btn-primary', "bs-fa-$itemId" ]
				);
		}

		if ( !isset( $splitButtonData['href'] ) ) {
			$splitButtonData['classes'][] = 'disabled';
		}

		$canEdit = \MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan(
				'edit',
				$this->getSkin()->getUser(),
				$this->getSkin()->getTitle()
		);
		if ( !$canEdit && ( $firstEntry['id'] === 'ca-view' ) ) {
			return [
				'classes' => [
					0 => "btn-primary disabled bs-fa-$itemId"
				]
			];
		}

		$splitButtonData['hasItems'] = true;
		if ( count( $itemDefinition ) == 0 ) {
			$splitButtonData['hasItems'] = false;
			return $splitButtonData;
		}

		$splitButtonData['items'] = [];

		if ( isset( $firstEntry['id'] ) ) {
			unset( $firstEntry['id'] );
		}

		if ( !isset( $firstEntry['classes'] ) ) {
			$firstEntry['classes'] = [];
		}
		$splitButtonData['items'][] = [ 'link' => $firstEntry ];

		foreach ( $itemDefinition as $linkId => $linkDefinition ) {
			if ( !isset( $linkDefinition['classes'] ) ) {
				$linkDefinition['classes'] = [];
			}
			if ( isset( $linkDefinition['separator'] ) ) {
				$splitButtonData['items'][] = [ 'separator' => true ];
			} else {
				$splitButtonData['items'][] = [ 'link' => $linkDefinition ];
			}
		}

		return $splitButtonData;
	}

	private function sortFeaturedActions( $faArray ) {
		usort( $faArray, function ( $a, $b ) {
			if ( !isset( $a['position'] ) ) {
				return false;
			}
			if ( !isset( $b['position'] ) ) {
				return true;
			}
			return $a['position'] > $b['position'];
		} );

		return $faArray;
	}

	protected function skipRendering() {
		$hideIfNoRead = $this->getDomElement()->getAttribute( 'hide-if-noread' );
		$hideIfNoRead = strtolower( $hideIfNoRead ) === 'true' ? true : false;
		$userHasReadPermissionsAtAll = !MediaWikiServices::getInstance()
			->getPermissionManager()->userHasRight( $this->getSkin()->getUser(), 'read' );

		if ( $hideIfNoRead && $userHasReadPermissionsAtAll ) {
			return true;
		}

		return false;
	}
}
