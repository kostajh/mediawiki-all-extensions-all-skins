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

use BlueSpice\Social\EntityAttachment;
use Html;

/**
 * This view renders the a single item.
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
class Image extends EntityAttachment {
	protected $sType = 'image';

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
		if ( !$this->mAttachment instanceof \File ) {
			return [];
		}

		$this->aArgs['class'] = 'bs-social-entityattachment-image breakheight';
		$this->aArgs['link'] = $this->mAttachment->getUrl();
		$this->aArgs['title'] = $this->mAttachment->getTitle()->getText();
		$sThumb = $this->getThumb();
		$link = \Linker::makeThumbLink2(
			$this->mAttachment->getTitle(),
			$this->mAttachment
		);
		$match = [];
		preg_match_all(
			'#(data-[0-9A-Za-z\s+-]+)=?\"([0-9A-Za-z\s+-:_]+)\"?#s',
			$link,
			$match,
			PREG_SET_ORDER
		);
		$this->aArgs['attribs'] = '';
		foreach ( $match as $parts ) {
			$this->aArgs['attribs'] .= ' ' . trim( $parts[0] );
		}

		$this->aArgs['content'] = Html::rawElement( 'img', [
			'src' => $sThumb,
			'title' => $this->mAttachment->getTitle()->getText(),
			'alt' => $this->mAttachment->getTitle()->getText(),
			],
			$this->aArgs['content']
		);
		$this->aArgs['content'] .= Html::element(
			'p',
			[ 'class' => 'attachment-name' ],
			$this->mAttachment->getTitle()->getText()
		);
		return $this->aArgs;
	}

	/**
	 *
	 * @return string
	 */
	protected function getThumb() {
		return $this->mAttachment->createThumb( 128 );
	}
}
