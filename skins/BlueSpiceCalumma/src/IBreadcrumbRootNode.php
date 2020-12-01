<?php

namespace BlueSpice\Calumma;

use Title;

interface IBreadcrumbRootNode {

	/**
	 * @param Title $title
	 * @return string
	 */
	public function getHtml( $title );
}
