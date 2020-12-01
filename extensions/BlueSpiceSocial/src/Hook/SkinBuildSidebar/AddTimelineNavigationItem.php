<?php

namespace BlueSpice\Social\Hook\SkinBuildSidebar;

use BlueSpice\Hook\SkinBuildSidebar;

class AddTimelineNavigationItem extends SkinBuildSidebar {

	protected function skipProcessing() {
		if ( !\SpecialPage::getTitleFor( 'Timeline' ) ) {
			return true;
		}
		if ( \Title::makeTitle( NS_MEDIAWIKI, 'Sidebar' )->exists() ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$title = \SpecialPage::getTitleFor( 'Timeline' );
		$this->bar['navigation'][] = [
			'href' => $title->getLocalURL(),
			'text' => $this->skin->msg( 'bs-social-special-timeline-heading' ),
			'title' => $this->skin->msg( 'bs-social-special-timeline-heading' ),
			'id' => 'bs-social-special-timeline',
			'iconClass' => 'icon-bs-social-timeline',
		];
		return true;
	}

}
