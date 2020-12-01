<?php
/**
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Patric Wirth
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocialProfile
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */
namespace BlueSpice\Social\Profile\Renderer\Entity;

use BlueSpice\Context;
use BlueSpice\Renderer\Params;
use BlueSpice\Renderer\UserImage;
use BlueSpice\Social\Profile\EntityListContext\UserProfile;
use BlueSpice\Social\Profile\IField;
use BlueSpice\Utility\CacheHelper;
use Config;
use Html;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;

class Profile extends \BlueSpice\Social\Renderer\Entity\Page {

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 * @param CacheHelper|null $cacheHelper
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '', CacheHelper $cacheHelper = null ) {
		parent::__construct(
			$config,
			$params,
			$linkRenderer,
			$context,
			$name,
			$cacheHelper
		);

		$this->args['basetitlecontent'] = '';
		$this->args['profilefields'] = '';
		$this->args['profilecustomfields'] = '';
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	protected function render_children( $val ) {
		if ( $this->renderType !== static::RENDER_TYPE_PAGE ) {
			return '';
		}

		if ( !$this->getEntity()->exists() ) {
			return '';
		}
		$context = new UserProfile(
			new Context(
				$this->getContext(),
				$this->getEntity()->getConfig()
			),
			$this->getEntity()->getConfig(),
			$this->getContext()->getUser(),
			$this->getEntity(),
			$this->getEntity()->getOwner()
		);
		$renderer = $this->getServices()->getService( 'BSRendererFactory' )->get(
			'entitylist',
			new Params( [ 'context' => $context ] )
		);

		return $renderer->render();
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	public function render_userimage( $val ) {
		if ( $this->renderType !== static::RENDER_TYPE_PAGE ) {
			return parent::render_userimage( $val );
		}

		$factory = $this->getServices()->getService( 'BSRendererFactory' );
		$image = $factory->get( 'userimage', new Params( [
			UserImage::PARAM_USER => $this->getEntity()->getOwner(),
			UserImage::PARAM_WIDTH => 200,
			UserImage::PARAM_HEIGHT => 200,
		] ) );
		return $image->render();
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	public function render_basetitlecontent( $val ) {
		if ( $this->renderType !== static::RENDER_TYPE_PAGE ) {
			return '';
		}

		return $this->getEntity()->getBaseTitleContent();
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	public function render_profilefields( $val ) {
		$out = '';
		$factory = $this->getServices()->getService(
			'BSSocialProfileFieldsFactory'
		);
		if ( empty( $factory->getFieldDefinitions() ) ) {
			return $out;
		}
		$out .= Html::openElement( 'table' );
		foreach ( $factory->getFieldDefinitions() as $name => $definition ) {
			$field = $factory->factory( $name, $this->getEntity()->getOwner() );
			if ( !$field instanceof IField ) {
				continue;
			}
			if ( empty( $field->getValue() ) ) {
				continue;
			}
			if ( $field->isHidden() ) {
				continue;
			}
			$out .= Html::openElement( 'tr' );
			$out .= Html::openElement( 'td' );
			$out .= $field->getLabel();
			$out .= Html::closeElement( 'td' );
			$out .= Html::openElement( 'td' );
			$out .= $field->getValue();
			$out .= Html::closeElement( 'td' );
			$out .= Html::closeElement( 'tr' );
		}
		$out .= Html::closeElement( 'table' );
		return $out;
	}

	/**
	 *
	 * @param mixed $val
	 * @param string $sType
	 * @return string
	 */
	public function render_profilecustomfields( $val, $sType = 'Default' ) {
		$out = '';
		$factory = $this->getServices()->getService(
			'BSSocialProfileCustomFieldsFactory'
		);
		if ( empty( $factory->getFieldDefinitions() ) ) {
			return $out;
		}
		$out .= Html::openElement( 'table' );
		foreach ( $factory->getFieldDefinitions() as $name => $definition ) {
			$field = $factory->factory( $name, $this->getEntity()->getOwner() );
			if ( !$field instanceof IField ) {
				continue;
			}
			if ( empty( $field->getValue() ) ) {
				continue;
			}
			if ( $field->isHidden() ) {
				continue;
			}
			$out .= Html::openElement( 'tr' );
			$out .= Html::openElement( 'td' );
			$out .= $field->getLabel();
			$out .= Html::closeElement( 'td' );
			$out .= Html::openElement( 'td' );
			$out .= $field->getValue();
			$out .= Html::closeElement( 'td' );
			$out .= Html::closeElement( 'tr' );
		}
		$out .= Html::closeElement( 'table' );
		return $out;
	}
}
