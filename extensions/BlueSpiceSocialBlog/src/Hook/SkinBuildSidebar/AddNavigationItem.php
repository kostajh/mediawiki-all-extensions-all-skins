<?php

namespace BlueSpice\Social\Blog\Hook\SkinBuildSidebar;

use BlueSpice\Hook\SkinBuildSidebar;
use SpecialPage;
use Title;

class AddNavigationItem extends SkinBuildSidebar {

	protected function skipProcessing() {
		if ( !SpecialPage::getTitleFor( 'Blog' ) ) {
			return true;
		}
		if ( Title::makeTitle( NS_MEDIAWIKI, 'Sidebar' )->exists() ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$title = SpecialPage::getTitleFor( 'Blog' );
		$this->bar['navigation'][] = [
			'href' => $title->getLocalURL(),
			'text' => $this->skin->msg( 'bs-socialblog-special-blog-heading' ),
			'title' => $this->skin->msg( 'bs-socialblog-special-blog-heading' ),
			'id' => 'bs-social-special-blog',
			'iconClass' => 'icon-bs-social-entity-blog',
		];
		return true;
	}

}
