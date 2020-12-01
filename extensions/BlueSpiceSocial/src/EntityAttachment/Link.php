<?php
/**
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Patric Wirth
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */
namespace BlueSpice\Social\EntityAttachment;

use BlueSpice\DynamicFileDispatcher\ArticlePreviewImage;
use BlueSpice\DynamicFileDispatcher\Params as DFDParams;
use BlueSpice\Social\EntityAttachment;
use Html;
use MediaWiki\MediaWikiServices;

/**
 * This view renders the a single item.
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
class Link extends EntityAttachment {
	protected $sType = 'link';

	/**
	 *
	 * @return string
	 */
	public function getTemplateName() {
		return 'BlueSpiceSocial.Entity.attachment.Default';
	}

	/**
	 * Default given attachment data is an array with params
	 * Overwrite this in your Attachment class
	 * @return array
	 */
	public function getArgs() {
		if ( !$this->mAttachment instanceof \Title ) {
			return [];
		}

		$this->aArgs['class'] = 'bs-social-entityattachment-link breakheight';
		$this->aArgs['link'] = $this->mAttachment->getFullUrl();
		$this->aArgs['title'] = $this->mAttachment->getFullText();

		$this->aArgs['content'] = Html::rawElement( 'img', [
			'src' => $this->getThumb(),
			'title' => $this->mAttachment->getText(),
			'alt' => $this->mAttachment->getText(),
			],
			$this->aArgs['content']
		);
		$this->aArgs['content'] .= Html::element(
			'p',
			[ 'class' => 'attachment-name' ],
			$this->mAttachment->getText()
		);
		return $this->aArgs;
	}

	/**
	 *
	 * @return string
	 */
	protected function getThumb() {
		$params = [
			DFDParams::MODULE => 'articlepreviewimage',
			ArticlePreviewImage::WIDTH => 100,
			ArticlePreviewImage::TITLETEXT => $this->mAttachment->getFullText(),
		];
		$dfdUrlBuilder = MediaWikiServices::getInstance()
			->getService( 'BSDynamicFileDispatcherUrlBuilder' );
		return $dfdUrlBuilder->build(
			new DFDParams( $params )
		);
	}
}
