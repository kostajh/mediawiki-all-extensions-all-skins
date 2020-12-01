<?php

namespace BlueSpice\Social\WikiPage\Renderer\Entity;

use BlueSpice\DynamicFileDispatcher\ArticlePreviewImage;
use BlueSpice\DynamicFileDispatcher\Params as DFDParams;
use BlueSpice\Renderer\Params;
use Config;
use Html;
use MediaWiki\Linker\LinkRenderer;

class WikiPage extends \BlueSpice\Social\Renderer\Entity\Page {

	/**
	 *
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 */
	public function __construct( Config $config, Params $params, LinkRenderer $linkRenderer = null ) {
		parent::__construct( $config, $params, $linkRenderer );
		$this->args['basetitlecontent'] = '';
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	protected function render_children( $val ) {
		return '';
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	public function render_userimage( $val ) {
		$size = 200;
		if ( $this->renderType !== static::RENDER_TYPE_PAGE ) {
			$size = 50;
		}

		$title = $this->getEntity()->getRelatedTitle();
		$params = [
			DFDParams::MODULE => 'articlepreviewimage',
			ArticlePreviewImage::WIDTH => $size,
			ArticlePreviewImage::TITLETEXT => $title->getFullText(),
		];
		$dfdUrlBuilder = $this->getServices()->getService(
			'BSDynamicFileDispatcherUrlBuilder'
		);
		$url = $dfdUrlBuilder->build(
			new DFDParams( $params )
		);

		$out = Html::openElement( 'a', [
			'href' => $title->getLocalURL()
		] );
		$out .= Html::element( 'img', [
			'src' => $url,
			'alt' => $title->getFullText()
		] );
		$out .= Html::closeElement( 'a' );
		return $out;
	}
}
