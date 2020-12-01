<?php

namespace BlueSpice\TagCloud\Tag;

use BlueSpice\Tag\Handler;
use BlueSpice\TagCloud\Context;
use BlueSpice\TagCloud\Renderer;
use MediaWiki\MediaWikiServices;

class TagCloudHandler extends Handler {

	public function handle() {
		$storeType = '';
		// backwards compatibility
		if ( isset( $this->processedArgs['type'] ) ) {
			$storeType = $this->processedArgs['type'];
		} elseif ( isset( $this->processedArgs['store'] ) ) {
			$storeType = $this->processedArgs['store'];
		} else {
			$storeType = $this->getFactory()->getDefaultStoreType();
		}

		$context = new Context(
			\RequestContext::getMain(),
			MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' ),
			$this->parser->getUser()
		);
		$store = $this->getFactory()->getStore( $storeType, $context );

		$readerParams = $store->makeReaderParams(
			$this->processedArgs
		);

		$result = $store->getReader()->read( $readerParams );

		$rendererType = '';
		// backwards compatibility
		if ( isset( $this->processedArgs['viewtype'] ) ) {
			$rendererType = $this->processedArgs['viewtype'];
		} elseif ( isset( $this->processedArgs['renderer'] ) ) {
			$rendererType = $this->processedArgs['renderer'];
		} else {
			$rendererType = $this->getFactory()->getDefaultRendererType();
		}

		$params = array_merge( $this->processedArgs, [
			Renderer::PARAM_RESULT => $result,
			Renderer::PARAM_CONTEXT => $context,
			Renderer::PARAM_STORE => $storeType,
			Renderer::PARAM_RENDERER => $rendererType,
		] );

		$renderer = $this->getFactory()->getRenderer(
			$rendererType,
			$params
		);
		return $renderer->render();
	}

	/**
	 *
	 * @return \BlueSpice\TagCloud\Factory
	 */
	protected function getFactory() {
		return MediaWikiServices::getInstance()->getService( 'BSTagCloudFactory' );
	}
}
