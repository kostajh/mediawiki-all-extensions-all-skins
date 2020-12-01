<?php

namespace BlueSpice\SmartList\Hook\BSDashboardsAdminDashboardPortalPortlets;

use BlueSpice\Dashboards\Hook\BSDashboardsAdminDashboardPortalPortlets;

class AddPortlets extends BSDashboardsAdminDashboardPortalPortlets {

	protected function doProcess() {
		$this->portlets[] = [
			'type'  => 'BS.SmartList.MostEditedPortlet',
			'config' => [
				'title' => $this->msg( 'bs-smartlist-mosteditedpages' )->plain()
			],
			'title' => $this->msg( 'bs-smartlist-mosteditedpages' )->plain(),
			'description' => $this->msg( 'bs-smartlist-mosteditedpagesdesc' )->plain()
		];
		$this->portlets[] = [
			'type'  => 'BS.SmartList.MostVisitedPortlet',
			'config' => [
				'title' => $this->msg( 'bs-smartlist-mostvisitedpages' )->plain()
			],
			'title' => $this->msg( 'bs-smartlist-mostvisitedpages' )->plain(),
			'description' => $this->msg( 'bs-smartlist-mostvisitedpagesdesc' )->plain()
		];
		$this->portlets[] = [
			'type'  => 'BS.SmartList.MostActivePortlet',
			'config' => [
				'title' => $this->msg( 'bs-smartlist-mostactiveusers' )->plain()
			],
			'title' => $this->msg( 'bs-smartlist-mostactiveusers' )->plain(),
			'description' => $this->msg( 'bs-smartlist-mostactiveusersdesc' )->plain()
		];
	}

}
