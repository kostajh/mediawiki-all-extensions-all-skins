<?php

namespace BlueSpice\Dashboards\Hook\UserDashboardPortalConfig;

use BlueSpice\Dashboards\Hook\BSDashboardsUserDashboardPortalConfig;

class AddConfig extends BSDashboardsUserDashboardPortalConfig {

	protected function doProcess() {
		$this->portalConfig[0][] = [
			'type' => 'BS.Dashboards.CalendarPortlet',
			'config' => [ 'title' => wfMessage( 'bs-dashboard-userportlet-calendar-title' )->plain() ]
		];
		$this->portalConfig[0][] = [
			'type' => 'BS.Dashboards.WikiPagePortlet',
			'config' => [ 'title' => wfMessage( 'bs-dashboard-userportlet-wikipage-title' )->plain() ]
		];
	}

}
