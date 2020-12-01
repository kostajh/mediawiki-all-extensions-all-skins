<?php
namespace BlueSpice\Social\Hook\ChangesListSpecialPageStructuredFilters;

use BlueSpice\Hook\ChangesListSpecialPageStructuredFilters;
use ChangesListBooleanFilterGroup;

class AddSocialEntityFilter extends ChangesListSpecialPageStructuredFilters {

	/**
	 * @return bool
	 */
	public function doProcess() {
		$socialActionsGroup = new ChangesListBooleanFilterGroup(
			$this->getSocialEntitiesFilterDefinition()
		);
		$this->specialPage->registerFilterGroup( $socialActionsGroup );
		return true;
	}

	/**
	 * @return array
	 */
	private function getSocialEntitiesFilterDefinition() {
		return [
			'name' => 'socialentities',
			'class' => ChangesListBooleanFilterGroup::class,
			'filters' => [
				[
					'name' => 'hidesocialentities',
					'showHide' => 'bs-social-specialpagelisting-socialentities',
					'default' => true,
					'queryCallable' => function (
						$specialClassName, $ctx, $dbr, &$tables, &$fields,
						&$conds, &$query_options, &$join_conds
					) {
						$conds[] = 'rc_namespace != ' . NS_SOCIALENTITY;
					}
				]
			]
		];
	}
}
