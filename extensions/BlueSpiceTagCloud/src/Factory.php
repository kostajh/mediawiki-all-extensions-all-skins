<?php

namespace BlueSpice\TagCloud;

use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\Renderer;
use BlueSpice\Renderer\Params;
use BlueSpice\RendererFactory;
use BlueSpice\TagCloud\Data\TagCloud\IStore;
use Config;
use Message;
use MWException;

class Factory {

	/**
	 *
	 * @var ExtensionAttributeBasedRegistry
	 */
	protected $storeRegistry = null;

	/**
	 *
	 * @var ExtensionAttributeBasedRegistry
	 */
	protected $rendererRegistry = null;

	/**
	 *
	 * @var RendererFactory
	 */
	protected $rendererFactory = null;

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 * @param ExtensionAttributeBasedRegistry $storeRegistry
	 * @param ExtensionAttributeBasedRegistry $rendererRegistry
	 * @param RendererFactory $rendererFactory
	 * @param Config $config
	 */
	public function __construct( $storeRegistry, $rendererRegistry, $rendererFactory, $config ) {
		$this->storeRegistry = $storeRegistry;
		$this->rendererRegistry = $rendererRegistry;
		$this->rendererFactory = $rendererFactory;
		$this->config = $config;
	}

	/**
	 *
	 * @param string $type
	 * @param Context $context
	 * @return IStore
	 * @throws MWException
	 */
	public function getStore( $type, Context $context ) {
		// backwards compatibillity
		if ( $type === 'categories' ) {
			$type = 'category';
		}
		$store = $this->storeRegistry->getValue( $type, false );
		if ( !$store ) {
			$msg = Message::newFromKey( 'bs-tagcloud-error-tagtype' );
			throw new MWException( $msg->params( $type )->text() );
		}

		return new $store( $context );
	}

	/**
	 *
	 * @return string
	 */
	public function getDefaultStoreType() {
		return 'category';
	}

	/**
	 *
	 * @param string $type
	 * @param array $data
	 * @return Renderer
	 * @throws MWException
	 */
	public function getRenderer( $type, array $data = [] ) {
		$renderer = $this->rendererRegistry->getValue( $type, false );
		if ( !$renderer ) {
			$msg = Message::newFromKey( 'bs-tagcloud-error-tagrenderer' );
			throw new MWException( $msg->params( $type )->text() );
		}
		return $this->rendererFactory->get(
			$renderer,
			new Params( $data )
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getDefaultRendererType() {
		return 'text';
	}
}
