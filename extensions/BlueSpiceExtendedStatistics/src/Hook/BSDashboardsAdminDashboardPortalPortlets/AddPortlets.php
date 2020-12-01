<?php

namespace BlueSpice\ExtendedStatistics\Hook\BSDashboardsAdminDashboardPortalPortlets;

use BlueSpice\Dashboards\Hook\BSDashboardsAdminDashboardPortalPortlets;

class AddPortlets extends BSDashboardsAdminDashboardPortalPortlets {

	protected function doProcess() {
		$this->portlets[] = [
			'type' => 'BS.ExtendedStatistics.RSSPortlet',
			'config' => [
				'title' => $this->msg( 'bs-rssfeeder-rss' )->plain(),
				'height' => 660,
				'rssurl' => 'https://blog.bluespice.com/feed/'
			],
			'title' => $this->msg( 'bs-rssfeeder-rss' )->plain(),
			'description' => $this->msg( 'bs-rssfeeder-rss-desc' )->plain(),
			'modules' => 'ext.bluespice.rssFeeder',
		];
		$this->portlets[] = [
			'type'  => 'BS.Statistics.StatisticsPortletNumberOfUsers',
			'config' => [
				'title' => $this->msg( 'bs-statistics-portlet-numberofusers' )->plain(),
				'inputPeriod' => 'week',
			],
			'title' => $this->msg( 'bs-statistics-portlet-numberofusers' )->plain(),
			'description' => $this->msg( 'bs-statistics-portlet-numberofusersdesc' )->plain()
		];
		$this->portlets[] = [
			'type'  => 'BS.Statistics.StatisticsPortletNumberOfEdits',
			'config' => [
				'title' => $this->msg( 'bs-statistics-portlet-numberofedits' )->plain(),
				'inputPeriod' => 'week',
			],
			'title' => $this->msg( 'bs-statistics-portlet-numberofedits' )->plain(),
			'description' => $this->msg( 'bs-statistics-portlet-numberofeditsdesc' )->plain()
		];
		$this->portlets[] = [
			'type'  => 'BS.Statistics.StatisticsPortletNumberOfArticles',
			'config' => [
				'title' => $this->msg( 'bs-statistics-portlet-numberofarticles' )->plain(),
				'inputPeriod' => 'week',
			],
			'title' => $this->msg( 'bs-statistics-portlet-numberofarticles' )->plain(),
			'description' => $this->msg( 'bs-statistics-portlet-numberofarticlesdesc' )->plain()
		];
		$this->portlets[] = [
			'type'  => 'BS.Statistics.StatisticsPortletNumberOfPages',
			'config' => [
				'title' => $this->msg( 'bs-statistics-portlet-numberofpages' )->plain(),
				'inputPeriod' => 'week',
			],
			'title' => $this->msg( 'bs-statistics-portlet-numberofpages' )->plain(),
			'description' => $this->msg( 'bs-statistics-portlet-numberofpagesdesc' )->plain()
		];
	}

}
