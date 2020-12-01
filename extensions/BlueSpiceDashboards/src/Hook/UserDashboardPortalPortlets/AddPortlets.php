<?php

namespace BlueSpice\Dashboards\Hook\UserDashboardPortalPortlets;

use BlueSpice\Dashboards\Hook\BSDashboardsUserDashboardPortalPortlets;

class AddPortlets extends BSDashboardsUserDashboardPortalPortlets {

	protected function doProcess() {
		$this->portlets[] = [
			'type' => 'BS.Dashboards.CalendarPortlet',
			'config' => [ 'title' => wfMessage( 'bs-dashboard-userportlet-calendar-title' )->plain() ],
			'title' => wfMessage( 'bs-dashboard-userportlet-calendar-title' )->plain(),
			'description' => wfMessage( 'bs-dashboard-userportlet-calendar-description' )->plain()
		];
		$this->portlets[] = [
			'type' => 'BS.Dashboards.WikiPagePortlet',
			'config' => [ 'title' => wfMessage( 'bs-dashboard-userportlet-wikipage-title' )->plain() ],
			'title' => wfMessage( 'bs-dashboard-userportlet-wikipage-title' )->plain(),
			'description' => wfMessage( 'bs-dashboard-userportlet-wikipage-description' )->plain()
		];
	}

}
