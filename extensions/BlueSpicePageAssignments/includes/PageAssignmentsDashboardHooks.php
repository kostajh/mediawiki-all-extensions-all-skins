<?php

class PageAssignmentsDashboardHooks {

	/**
	 * Hook Handler for BSDashboardsUserDashboardPortalPortlets
	 *
	 * @param array &$aPortlets reference to array portlets
	 * @return bool always true to keep hook alive
	 */
	public static function onBSDashboardsUserDashboardPortalPortlets( &$aPortlets ) {
		$aPortlets[] = [
			'type'  => 'BS.PageAssignments.portlets.PageAssignmentsPortlet',
			'config' => [
				'title' => wfMessage(
					'bs-pageassignments-yourassignments'
				)->plain(),
			],
			'modules' => 'ext.bluespice.pageassignments.portlet',
			'title' => wfMessage(
				'bs-pageassignments-yourassignments'
			)->plain(),
			'description' => wfMessage(
				'bs-pageassignments-yourassignmentsdesc'
			)->plain(),
		];

		return true;
	}

	/**
	 * Hook Handler for BSDashboardsUserDashboardPortalConfig
	 *
	 * @param object $oCaller caller instance
	 * @param array &$aPortalConfig reference to array portlet configs
	 * @param bool $bIsDefault default
	 * @return bool always true to keep hook alive
	 */
	public static function onBSDashboardsUserDashboardPortalConfig( $oCaller,
		&$aPortalConfig, $bIsDefault ) {
		$aPortalConfig[0][] = [
			'type' => 'BS.PageAssignments.portlets.PageAssignmentsPortlet',
			'config' => [
				'title' => wfMessage(
					'bs-pageassignments-yourassignments'
				)->plain(),
			],
			'modules' => 'ext.bluespice.pageassignments.portlet'
		];

		return true;
	}
}
