<?php

namespace BlueSpice\ArticlePreviewCapture\DynamicFileDispatcher\ArticlePreviewCaptureImage;

use BlueSpice\ArticlePreviewCapture\Generator;
use BlueSpice\DynamicFileDispatcher\Module;
use MediaWiki\Storage\RevisionRecord;

class Image extends \BlueSpice\DynamicFileDispatcher\ArticlePreviewImage\Image {

	protected $screenshot = false;

	/**
	 *
	 * @param Module $dfd
	 * @param \Title $title
	 * @param RevisionRecord|null $revision
	 * @param type $screenshot
	 */
	public function __construct( Module $dfd, \Title $title, RevisionRecord $revision = null,
			$screenshot = false ) {
		parent::__construct( $dfd, $title, $revision );
		$this->screenshot = $screenshot;
	}

	/**
	 *
	 * @return string | bool
	 * @throws \MWException
	 */
	protected function getSourcePath() {
		if ( $this->screenshot || !$this->title->exists() ) {
			return parent::getSourcePath();
		}

		$generator = new Generator( $this->dfd->getConfig(), $this->dfd->getContext()->getRequest() );
		$thumb = $generator->generate(
			$this->revision,
			$this->dfd->getParams()
		);
		if ( !$thumb instanceof \MediaTransformOutput
			|| $thumb->isError()
			|| empty( $thumb->getLocalCopyPath() ) ) {
			throw new \MWException(
				'FATAL: ArticlePreviewCapture not found!'
			);
		}

		return $thumb->getLocalCopyPath();
	}

}
