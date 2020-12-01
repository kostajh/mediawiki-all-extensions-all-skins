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
namespace BlueSpice\Social;

use BlueSpice\TemplateFactory;
use MediaWiki\MediaWikiServices;

/**
 * This view renders the a single item.
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
class EntityAttachment {
	protected static $aAttachments = [
		'default' => "\\BlueSpice\\Social\\EntityAttachment",
		'file' => "\\BlueSpice\\Social\\EntityAttachment\\File",
		'image' => "\\BlueSpice\\Social\\EntityAttachment\\Image",
		'link' => "\\BlueSpice\\Social\\EntityAttachment\\Link",
		// 'entity' TODO!
	];
	protected static $bAttachmentsRegistered = false;

	/**
	 *
	 * @var Entity
	 */
	protected $oEntity = null;

	/**
	 *
	 * @var mixed
	 */
	protected $mAttachment = null;

	/**
	 *
	 * @var array
	 */
	protected $aArgs = [];

	/**
	 *
	 * @var string
	 */
	protected $sType = 'default';

	/**
	 *
	 * @var TemplateFactory
	 */
	protected $templateFactory = null;

	/**
	 * @param \Entity $oEntity
	 * @param mixed|null $mAttachment
	 * @param type $sType
	 * @param TemplateFactory|null $templateFactory
	 * @return \EntityAttachment
	 */
	public static function factory( Entity $oEntity, $mAttachment = null,
		$sType = 'default', TemplateFactory $templateFactory = null ) {
		if ( empty( $mAttachment ) ) {
			return null;
		}
		$aRegisteredAttachments = self::getRegisteredAttachments();
		if ( !isset( $aRegisteredAttachments[$sType] ) ) {
			return null;
		}
		if ( !$templateFactory ) {
			$templateFactory = MediaWikiServices::getInstance()->getService(
				'BSTemplateFactory'
			);
		}

		$oInstance = new $aRegisteredAttachments[$sType](
			$oEntity,
			$mAttachment,
			$templateFactory
		);
		return $oInstance;
	}

	/**
	 *
	 * @param bool $bForceReload
	 * @return bool
	 */
	protected static function runRegister( $bForceReload = false ) {
		if ( static::$bAttachmentsRegistered && !$bForceReload ) {
			return true;
		}

		$b = MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSEntityAttachmentsRegister',
			[
				&self::$aAttachments,
			]
		);

		static::$bAttachmentsRegistered = $b;
		return $b;
	}

	/**
	 * Returns all registered entities ( type => EntityConfigClass )
	 * @return array
	 */
	public static function getRegisteredAttachments() {
		if ( !self::runRegister() ) {
			return [];
		}
		return self::$aAttachments;
	}

	/**
	 *
	 * @param Entity $oEntity
	 * @param mixed $mAttachment
	 * @param TemplateFactory $templateFactory
	 */
	protected function __construct( Entity $oEntity, $mAttachment,
		TemplateFactory $templateFactory ) {
		$this->oEntity = $oEntity;
		$this->mAttachment = $mAttachment;
		$this->aArgs['type'] = $this->sType;
		$this->aArgs['content'] = "&nbsp;";
		$this->templateFactory = $templateFactory;
	}

	/**
	 * @return string
	 */
	public function render() {
		if ( !$this->mAttachment ) {
			return '';
		}
		return $this->templateFactory->get( $this->getTemplateName() )->process(
			$this->getArgs()
		);
	}

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
		if ( !$this->mAttachment ) {
			return [];
		}
		$this->aArgs = $this->mAttachment;
		return $this->aArgs;
	}

	/**
	 * @return Entity
	 */
	public function getEntity() {
		return $this->oEntity;
	}

	/**
	 * @return mixed
	 */
	public function getAttachment() {
		return $this->mAttachment;
	}
}
