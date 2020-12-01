<?php

namespace BlueSpice\Dashboards\Hook\PersonalUrls;

use BlueSpice\Hook\PersonalUrls;

class AddDashboardUrls extends PersonalUrls {

	protected function skipProcessing() {
		$user = $this->getContext()->getUser();
		return !$user->isLoggedIn();
	}

	protected function doProcess() {
		$user = $this->getContext()->getUser();
		$spFactory = \MediaWiki\MediaWikiServices::getInstance()->getSpecialPageFactory();

		$this->personal_urls['userdashboard'] = [
			'href' => \SpecialPage::getTitleFor( 'UserDashboard' )->getLocalURL(),
			'text' => $spFactory->getPage( 'UserDashboard' )->getDescription()
		];

		if ( in_array( 'sysop', $user->getGroups() ) ) {
			$this->personal_urls['admindashboard'] = [
				'href' => \SpecialPage::getTitleFor( 'AdminDashboard' )->getLocalURL(),
				'text' => $spFactory->getPage( 'AdminDashboard' )->getDescription()
			];
		}
		return true;
	}

}
