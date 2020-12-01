<?php

namespace BlueSpice\Calumma\Hook\MessageCacheGet;

use BlueSpice\Hook\MessageCacheGet;

class ReplaceMWSidebar extends MessageCacheGet {

	protected function skipProcessing() {
		if ( $this->lckey !== 'sidebar' ) {
			return true;
		}
		$sidebarTitle = \Title::makeTitle( NS_MEDIAWIKI, 'Sidebar' );
		if ( $sidebarTitle instanceof \Title && $sidebarTitle->exists() ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$this->lckey = 'bs-sidebar-override';
		return true;
	}
}
