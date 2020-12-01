<?php

namespace BlueSpice\Rating\Hook\NamespaceManagerWriteNamespaceConfiguration;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerWriteNamespaceConfiguration;

class WriteToConfiguration extends NamespaceManagerWriteNamespaceConfiguration {
	protected function skipProcessing() {
		if ( $this->ns === null ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$this->writeConfiguration( "RatingArticleEnabledNamespaces", "rating" );
		$this->writeConfiguration( "RatingArticleLikeEnabledNamespaces", "recommendations" );

		return true;
	}

}
