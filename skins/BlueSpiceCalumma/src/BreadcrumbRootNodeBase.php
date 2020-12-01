<?php

namespace BlueSpice\Calumma;

use Config;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;

abstract class BreadcrumbRootNodeBase implements IBreadcrumbRootNode {

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @var LinkRenderer
	 */
	protected $linkRenderer = null;

	/**
	 *
	 * @param Config $config
	 * @param LinkRenderer $linkRenderer
	 */
	public function __construct( $config, $linkRenderer ) {
		$this->config = $config;
		$this->linkRenderer = $linkRenderer;
	}

	/**
	 *
	 * @param Config $config
	 * @return IBreadCrumpRootNode
	 */
	public static function factory( $config ) {
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();

		return new static( $config, $linkRenderer );
	}

	/**
	 * Get the localized namespace text
	 *
	 * @param int $namespaceKey
	 * @return sting|null
	 */
	protected function getLocalizedNamespaceText( $namespaceKey ) {
		global $wgLang;

		$namespaces = $wgLang->getNamespaces();

		if ( array_key_exists( $namespaceKey, $namespaces ) ) {
			return $namespaces[$namespaceKey];
		}
		return null;
	}
}
