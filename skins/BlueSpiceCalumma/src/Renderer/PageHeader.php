<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BlueSpice\Calumma\Renderer;

use BlueSpice\Renderer;
use BlueSpice\Renderer\Params;
use Config;
use Exception;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;
use QuickTemplate;

/**
 * Description of PageHeader
 *
 * @author Sir-Lagg-A-Lot
 */
abstract class PageHeader extends Renderer {

	public const SKIN_TEMPLATE = 'skintemplate';

	/**
	 *
	 * @var QuickTemplate
	 */
	protected $skinTemplate = null;

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 * @param QuickTemplate|null $skinTemplate
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '', QuickTemplate $skinTemplate = null ) {
		parent::__construct( $config, $params, $linkRenderer, $context, $name );
		$this->skinTemplate = $skinTemplate;
	}

	/**
	 *
	 * @param string $name
	 * @param MediaWikiServices $services
	 * @param Config $config
	 * @param Params $params
	 * @param IContextSource|null $context
	 * @param LinkRenderer|null $linkRenderer
	 * @param QuickTemplate|null $skinTemplate
	 * @return Renderer
	 */
	public static function factory( $name, MediaWikiServices $services, Config $config,
		Params $params, IContextSource $context = null, LinkRenderer $linkRenderer = null,
		QuickTemplate $skinTemplate = null ) {
		if ( !$context ) {
			$context = $params->get(
				static::PARAM_CONTEXT,
				false
			);
			if ( !$context instanceof IContextSource ) {
				$context = \RequestContext::getMain();
			}
		}
		if ( !$linkRenderer ) {
			$linkRenderer = $services->getLinkRenderer();
		}
		if ( !$skinTemplate ) {
			$skinTemplate = $params->get( static::SKIN_TEMPLATE, null );
		}
		if ( !$skinTemplate ) {
			throw new Exception(
				'Param "' . static::SKIN_TEMPLATE . '" must be an instance of '
				. QuickTemplate::class
			);
		}

		return new static( $config, $params, $linkRenderer, $context, $name, $skinTemplate );
	}
}
