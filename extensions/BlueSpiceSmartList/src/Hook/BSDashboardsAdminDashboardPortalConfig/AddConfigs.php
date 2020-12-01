<?php

namespace BlueSpice\SmartList\Hook\BSDashboardsAdminDashboardPortalConfig;

use BlueSpice\Dashboards\Hook\BSDashboardsAdminDashboardPortalConfig;

class AddConfigs extends BSDashboardsAdminDashboardPortalConfig {

	protected function doProcess() {
		$this->portalConfig[0][] = [
			'type'  => 'BS.SmartList.MostVisitedPortlet',
			'config' => [
				'title' => $this->msg( 'bs-smartlist-mostvisitedpages' )->plain()
			]
		];
		$this->portalConfig[1][] = [
			'type'  => 'BS.SmartList.MostEditedPortlet',
			'config' => [
				'title' => $this->msg( 'bs-smartlist-mosteditedpages' )->plain()
			]
		];
		$this->portalConfig[1][] = [
			'type'  => 'BS.SmartList.MostActivePortlet',
			'config' => [
				'title' => $this->msg( 'bs-smartlist-mostactiveusers' )->plain()
			]
		];
	}

}
