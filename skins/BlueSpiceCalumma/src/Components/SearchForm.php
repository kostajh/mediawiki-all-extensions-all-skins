<?php
namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\TemplateComponent;
use MediaWiki\MediaWikiServices;

class SearchForm extends TemplateComponent {

	/**
	 *
	 * @return string
	 */
	public function getHtml() {
		if ( $this->skipRendering() ) {
			return '';
		}

		return parent::getHtml();
	}

	/**
	 *
	 * @return string
	 */
	protected function getTemplatePathName() {
		return 'Calumma.Components.SearchForm';
	}

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$args['id'] = $this->getSkinTemplate()->get( 'bs_search_id' );
		$args['searchInput'] = $this->getSkinTemplate()->get( 'bs_search_input' );
		$args['target'] = $this->getSkinTemplate()->get( 'bs_search_target' );
		$args['hiddenFields'] = $this->getSkinTemplate()->get( 'bs_search_hidden_fields' );
		$args['action'] = $this->getSkinTemplate()->get( 'bs_search_action' );
		$args['method'] = $this->getSkinTemplate()->get( 'bs_search_method' );
		$args['btn-aria-label'] = wfMessage( 'bs-calumma-search-button-label' )->plain();
		return $args;
	}

	/**
	 *
	 * @return bool
	 */
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
