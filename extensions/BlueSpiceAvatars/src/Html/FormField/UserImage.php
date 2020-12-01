<?php

namespace BlueSpice\Avatars\Html\FormField;

use BlueSpice\Renderer\Params;
use BlueSpice\Renderer\UserImage as DFDImage;
use MediaWiki\MediaWikiServices;
use OOUI\ButtonInputWidget;

class UserImage extends \HTMLTextField {

	/**
	 *
	 * @return string
	 */
	public function getLabel() {
		return $this->msg( 'bs-avatars-pref-userimage' )->parse();
	}

	/**
	 *
	 * @param string $value
	 * @return string
	 */
	public function getInputHTML( $value ) {
		$this->mParent->getOutput()->addModules( 'ext.bluespice.avatars.js' );
		$this->mParent->getOutput()->addModuleStyles(
			'ext.bluespice.avatars.preferences.styles'
		);

		$factory = MediaWikiServices::getInstance()->getService( 'BSRendererFactory' );
		$params = [
			DFDImage::PARAM_WIDTH => 128,
			DFDImage::PARAM_HEIGHT => 128,
			DFDImage::PARAM_USER => $this->mParent->getUser(),
			DFDImage::PARAM_CLASS => 'bs-avatars-userimage-pref',
		];
		$renderer = $factory->get( 'userimage', new Params( $params ) );
		$button = new ButtonInputWidget( [
			'label' => $this->msg( 'bs-avatars-upload-title' )->plain(),
			'classes' => [ 'bs-avatars-userimage-pref-btn' ]
		] );
		$html = parent::getInputHTML( $value ) . $renderer->render() . $button;

		return $html;
	}

	/**
	 * Same as getInputHTML, but returns an OOUI object.
	 * Defaults to false, which getOOUI will interpret as "use the HTML version"
	 *
	 * @param string $value
	 * @return OOUI\Widget|false
	 */
	public function getInputOOUI( $value ) {
		return false;
	}
}
