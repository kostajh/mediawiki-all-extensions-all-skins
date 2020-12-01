<?php

namespace MediaWiki\Extension\FlexiSkin\Api;

use MediaWiki\Extension\FlexiSkin\IFlexiSkin;
use Wikimedia\ParamValidator\ParamValidator;

class Activation extends FlexiSkinOperation {
	/**
	 * @inheritDoc
	 */
	protected function getAllowedParams() {
		return parent::getAllowedParams() + [
			'active' => [
				static::PARAM_TYPE => 'integer',
				static::PARAM_REQUIRED => false,
				static::PARAM_HELP_MSG => 'apihelp-flexiskin-activation-param-active',
				ParamValidator::PARAM_DEFAULT => 1
			]
		];
	}

	protected function executeOperationOnSkin( IFlexiSkin $flexiSkin ) {
		$active = (bool)$this->getParameter( 'active' );
		return $this->flexiSkinManager->setActive( $active );
	}

	/**
	 * @return bool
	 */
	protected function mustExist(): bool {
		return true;
	}
}
