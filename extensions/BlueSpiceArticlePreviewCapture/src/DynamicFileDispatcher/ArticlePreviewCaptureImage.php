<?php

namespace BlueSpice\ArticlePreviewCapture\DynamicFileDispatcher;

use BlueSpice\DynamicFileDispatcher\ArticlePreviewImage;
use BlueSpice\DynamicFileDispatcher\Params;
use MediaWiki\MediaWikiServices;

class ArticlePreviewCaptureImage extends ArticlePreviewImage {
	const SCREENSHOT = 'screenshot';

	/**
	 *
	 * @return array
	 */
	public function getParamDefinition() {
		return array_merge( parent::getParamDefinition(), [
			static::SCREENSHOT => [
				Params::PARAM_TYPE => Params::TYPE_BOOL,
				Params::PARAM_DEFAULT => false,
			],
		] );
	}

	/**
	 * @return File
	 */
	public function getFile() {
		$revision = null;
		if ( $this->params[static::REVISION] > 0 ) {
			$store = MediaWikiServices::getInstance()->getRevisionStore();
			$revision = $store->getRevisionById(
				$this->params[static::REVISION]
			);
		}
		return new ArticlePreviewCaptureImage\Image(
			$this,
			\Title::newFromText( $this->params[static::TITLETEXT] ),
			$revision,
			$this->params[static::SCREENSHOT]
		);
	}

	/**
	 *
	 * @param Params $params
	 */
	protected function extractParams( $params ) {
		$this->params[static::SCREENSHOT] = $this->context->getRequest()->getBool(
			static::SCREENSHOT,
			false
		);
		parent::extractParams( $params );

		$this->titleText = $this->params[static::TITLETEXT];
	}

	/**
	 *
	 * @return bool
	 */
	protected function isTitleRequired() {
		return true;
	}

}
