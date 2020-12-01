<?php

namespace BS\PageVersion\Variable;

abstract class Base {

	/**
	 * @var \Parser
	 */
	protected $parser = null;

	/**
	 * @var \ParserCache
	 */
	protected $cache = null;

	/**
	 * Base constructor.
	 * @param \Parser &$parser
	 * @param \ParserCache &$cache
	 */
	function __construct( &$parser, &$cache ) {
		$this->parser = $parser;
		$this->cache = $cache;
	}

	/**
	 * @return string
	 */
	abstract public function getValue();
}
