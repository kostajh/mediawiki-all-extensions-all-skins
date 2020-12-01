<?php

namespace BlueSpice\NamespaceCSS\Data;

use IContextSource;
use Wikimedia\Rdbms\LoadBalancer;

class Store implements \BlueSpice\Data\IStore {

	/**
	 * @var IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @param IContextSource $context
	 * @param LoadBalancer $loadBalancer
	 */
	public function __construct( $context, $loadBalancer ) {
		$this->loadBalancer = $loadBalancer;
		$this->context = $context;
	}

	/**
	 *
	 * @return Reader
	 */
	public function getReader() {
		return new Reader( $this->loadBalancer, $this->context );
	}

	/**
	 *
	 * @throws \Exception
	 */
	public function getWriter() {
		throw new Exception( 'This store does not support writing!' );
	}

}
