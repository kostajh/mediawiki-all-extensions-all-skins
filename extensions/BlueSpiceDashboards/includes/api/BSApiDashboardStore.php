<?php

class BSApiDashboardStore extends BSApiExtJSStoreBase {

	/**
	 *
	 * @param string $sQuery
	 * @return \stdClass[]
	 */
	protected function makeData( $sQuery = '' ) {
		$aPortlets = [];

		$this->getServices()->getHookContainer()->run(
			'BSDashboardsUserDashboardPortalPortlets',
			[
				&$aPortlets
			]
		);

		for ( $i = 0; $i < count( $aPortlets ); $i++ ) {
			if ( !isset( $aPortlets[$i]['group'] ) ) {
				$aPortlets[$i]['groups'] = [ 'UserDashboard' ];
			}
		}

		$aReturnArray = $aPortlets;

		$aPortlets = [];

		$this->getServices()->getHookContainer()->run(
			'BSDashboardsAdminDashboardPortalPortlets',
			[
				&$aPortlets
			]
		);

		for ( $i = 0; $i < count( $aPortlets ); $i++ ) {
			if ( !isset( $aPortlets[$i]['groups'] ) ) {
				$aPortlets[$i]['groups'] = [ 'AdminDashboard' ];
			}
		}

		$aReturnArray = array_merge( $aReturnArray, $aPortlets );

		$aPortlets = [];

		$this->getServices()->getHookContainer()->run( 'BSDashboardsGetPortlets', [
			&$aPortlets
		] );

		for ( $i = 0; $i < count( $aPortlets ); $i++ ) {
			if ( !isset( $aPortlets[$i]['groups'] ) ) {
				$aPortlets[$i]['groups'] = [ 'UserDashboard', 'AdminDashboard' ];
			}
		}

		$aReturnArray = array_merge( $aReturnArray, $aPortlets );

		// make sure to return objects, not arrays
		$aReturnObjects = [];
		foreach ( $aReturnArray as $aReturn ) {
			$aReturnObjects[] = (object)$aReturn;
		}

		return $aReturnObjects;
	}

	/**
	 *
	 * @param array $aDataSet
	 * @return bool
	 */
	public function filterCallback( $aDataSet ) {
		$aFilter = $this->getParameter( 'filter' );

		foreach ( $aFilter as $oFilter ) {
			if ( $oFilter->type == 'group' ) {
				$bFilterApplies = $this->filterGroup( $oFilter, $aDataSet );
				if ( !$bFilterApplies ) {
					return false;
				}
			}
		}

		return parent::filterCallback( $aDataSet );
	}

	/**
	 *
	 * @param \stdClass $oFilter
	 * @param array $aDataSet
	 * @return bool
	 */
	public function filterGroup( $oFilter, $aDataSet ) {
		if ( !is_string( $oFilter->value ) ) {
			// TODO: Warning
			return true;
		}
		$sFieldValue = $aDataSet->groups;
		$sFilterValue = $oFilter->value;

		return in_array( $oFilter->value,  $aDataSet->groups );
	}

}
