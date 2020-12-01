<?php

namespace NotesLink;

class NDLParser {

	const REPLICA = 'replica';
	const VIEW = 'replica';
	const NOTE = 'note';
	const HINT = 'hint';
	const REM = 'rem';

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @var string
	 */
	protected $input = '';

	/**
	 *
	 * @var \Title
	 */
	protected $title = null;

	/**
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * @param string $input
	 * @param \Config $config
	 * @param \Title $title
	 * @return NDLParser
	 */
	public function __construct( $input, $config, $title ) {
		$this->config = $config;
		$this->input = $input;
		$this->title = $title;
		$this->parse();
	}

	protected function parse() {
		$patterns = $this->config->get( 'ParserPatterns' );
		foreach ( $this->getKeys() as $key ) {
			if ( empty( $patterns[$key] ) ) {
				continue;
			}
			if ( !is_array( $patterns[$key] ) ) {
				$patterns[$key] = [ $patterns[$key] ];
			}
			foreach ( $patterns[$key] as $pattern ) {
				$matches = [];
				preg_match( $pattern, $this->input, $matches );
				if ( empty( $matches[1] ) ) {
					continue;
				}
				break;
			}
			if ( empty( $matches[1] ) ) {
				continue;
			}
			if ( in_array( $key, [ static::REPLICA, static::VIEW, static::NOTE ] ) ) {
				$this->data[$key] = str_replace( [ ':', '-' ], '', $matches[1] );
				continue;
			}
			$this->data[$key] = trim( $matches[1] );
		}
	}

	/**
	 *
	 * @return array
	 */
	public function getKeys() {
		return [
			static::REPLICA,
			static::VIEW,
			static::NOTE,
			static::HINT,
			static::REM,
		];
	}

	/**
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return string | $default
	 */
	public function get( $key, $default = '' ) {
		if ( isset( $this->data[$key] ) ) {
			return $this->data[$key];
		}
		return $default;
	}
}
