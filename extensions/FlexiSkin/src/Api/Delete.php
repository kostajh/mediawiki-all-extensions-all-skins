<?php

namespace MediaWiki\Extension\FlexiSkin\Api;

use ApiUsageException;
use MediaWiki\Extension\FlexiSkin\IFlexiSkin;

class Delete extends FlexiSkinOperation {
	/**
	 * @return bool
	 * @throws ApiUsageException
	 */
	protected function executeAction() {
		return $this->executeOperationOnSkin( $this->getFlexiSkin() );
	}

	/**
	 * @param IFlexiSkin $flexiSkin
	 * @return bool
	 */
	protected function executeOperationOnSkin( IFlexiSkin $flexiSkin ) {
		return $this->flexiSkinManager->delete();
	}

	/**
	 * @return bool
	 */
	protected function mustExist(): bool {
		return true;
	}
}
