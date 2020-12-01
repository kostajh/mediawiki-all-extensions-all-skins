<?php

namespace BlueSpice\Rating\Data\Item\ArticleLike;

class Store extends \BlueSpice\Rating\Data\Store {

	/**
	 *
	 * @return Reader
	 */
	public function getReader() {
		return new Reader( $this->loadBalancer, $this->context );
	}

	/**
	 *
	 * @throws \MWException
	 */
	public function getWriter() {
		throw new \MWException( 'Write is not supported' );
	}
}
