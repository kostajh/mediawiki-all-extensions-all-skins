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
class File extends EntityAttachment {
	protected $sType = 'file';

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
		$this->mAttachment instanceof \File;
		$this->aArgs['class'] = 'bs-social-entityattachment-file breakheight';
		$this->aArgs['link'] = $this->mAttachment->getUrl();
		$this->aArgs['title'] = $this->mAttachment->getTitle()->getText();
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

		$this->aArgs['content'] = Html::element(
			'p',
			[ 'class' => 'attachment-name' ],
			$this->mAttachment->getTitle()->getText()
		);

		$this->makeExtensionArgs();
		return $this->aArgs;
	}

	protected function makeExtensionArgs() {
		$sExt = $this->mAttachment->getExtension();
		if ( isset( self::$aExtensionMapping[$sExt] ) ) {
			$this->aArgs['class'] .=
				" bs-social-entityattachment-" . self::$aExtensionMapping[$sExt];
		} else {
			$this->aArgs['class'] .= " bs-social-entityattachment-$sExt";
			$this->aArgs['class'] .= " bs-social-entityattachment-unknown";
		}
	}

	protected static $aExtensionMapping = [
		// docs
		"doc" => 'word',
		"dot" => 'word',
		"docx" => 'word',
		"dotx" => 'word',
		"docm" => 'word',
		"dotm" => 'word',
		// excel
		"xls" => 'excel',
		"xlt" => 'excel',
		"xla" => 'excel',
		"xlsx" => 'excel',
		"xltx" => 'excel',
		"xlsm" => 'excel',
		"xltm" => 'excel',
		"xlam" => 'excel',
		"xlsb" => 'excel',
		// pdf
		"pdf" => "pdf",
	];
}
