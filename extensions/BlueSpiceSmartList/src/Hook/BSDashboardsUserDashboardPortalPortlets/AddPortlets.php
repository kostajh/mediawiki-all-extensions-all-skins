<?php

namespace BlueSpice\SmartList\Hook\BSDashboardsUserDashboardPortalPortlets;

use BlueSpice\Dashboards\Hook\BSDashboardsUserDashboardPortalPortlets;

class AddPortlets extends BSDashboardsUserDashboardPortalPortlets {

	protected function doProcess() {
		$this->portlets[] = [
			'type'  => 'BS.SmartList.YourEditsPortlet',
			'config' => [
				'title' => $this->msg( 'bs-smartlist-lastedits' )->plain()
			],
			'title' => $this->msg( 'bs-smartlist-lastedits' )->plain(),
			'description' => $this->msg( 'bs-smartlist-lasteditsdesc' )->plain()
		];
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
