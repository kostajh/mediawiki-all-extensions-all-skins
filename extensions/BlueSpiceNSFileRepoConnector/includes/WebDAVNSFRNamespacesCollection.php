<?php

class WebDAVNSFRNamespacesCollection extends WebDAVPagesCollection {
	/**
	 *
	 * @return array
	 */
	public function getChildren() {
		$children = [];
		$aNamespaces = NSFileRepoConnectorNamespaceHelper::getPossibleNamespaces();

		foreach ( $aNamespaces as $iNsId => $sNsName ) {
			$children[] = new WebDAVNSFRFilesCollection( $this, $sNsName, $iNsId );
		}

		return $children;
	}
}
