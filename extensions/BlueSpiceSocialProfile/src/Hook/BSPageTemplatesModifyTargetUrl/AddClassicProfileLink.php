<?php
namespace BlueSpice\Social\Profile\Hook\BSPageTemplatesModifyTargetUrl;

class AddClassicProfileLink extends \BlueSpice\PageTemplates\Hook\BSPageTemplatesModifyTargetUrl {

	protected function skipProcessing() {
		if ( $this->getContext()->getRequest()->getBool( 'classicprofile', false ) === false ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$this->targetUrl .= "&classicprofile=true";
		return true;
	}

}
