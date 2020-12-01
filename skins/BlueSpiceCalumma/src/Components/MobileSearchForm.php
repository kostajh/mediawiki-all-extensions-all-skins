<?php
namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\TemplateComponent;

class MobileSearchForm extends TemplateComponent {

	/**
	 *
	 * @return string
	 */
	protected function getTemplatePathName() {
		return 'Calumma.Components.MobileSearchForm';
	}

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$args['id'] = $this->getSkinTemplate()->get( 'bs_search_mobile_id' );
		$args['searchInput'] = $this->getSkinTemplate()->get( 'bs_search_mobile_input' );
		$args['target'] = $this->getSkinTemplate()->get( 'bs_search_target' );
		$args['hiddenFields'] = $this->getSkinTemplate()->get( 'bs_search_hidden_fields' );
		$args['action'] = $this->getSkinTemplate()->get( 'bs_search_action' );
		$args['method'] = $this->getSkinTemplate()->get( 'bs_search_method' );
		$args['btn-aria-label'] = wfMessage( 'bs-calumma-search-button-label' )->plain();
		return $args;
	}
}
