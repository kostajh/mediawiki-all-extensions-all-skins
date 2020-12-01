<?php

namespace NotesLink;

class NDLLinkRenderer {

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @var NDLParser
	 */
	protected $parser = null;

	/**
	 * @param NDLParser $parser
	 * @param \Config $config
	 * @return NDLLinkRenderer
	 */
	public function __construct( $parser, $config ) {
		$this->config = $config;
		$this->parser = $parser;
	}

	public function render() {
		$href = 'notes://';
		$content = '';

		if ( $this->config->get( 'DocumentHost' ) !== '' ) {
			$href .= $this->config->get( 'DocumentHost' );
		} elseif ( $this->parser->get( NDLParser::HINT, false ) ) {
			$href .= $this->parser->get( NDLParser::HINT );
		}
		$href = rtrim( $href, '/' );

		if ( $this->parser->get( NDLParser::REPLICA, false ) ) {
			$href .= "/{$this->parser->get( NDLParser::REPLICA )}";
			$content = $this->parser->get( NDLParser::REPLICA );
		}
		if ( $this->parser->get( NDLParser::VIEW, false ) ) {
			$href .= "/{$this->parser->get( NDLParser::VIEW )}";
			$content = $this->parser->get( NDLParser::VIEW );
		}
		if ( $this->parser->get( NDLParser::NOTE, false ) ) {
			$href .= "/{$this->parser->get( NDLParser::NOTE )}";
			$content = $this->parser->get( NDLParser::NOTE );
		}
		if ( $this->parser->get( NDLParser::REM, false ) ) {
			$content = $this->parser->get( NDLParser::REM );
		}
		return \Html::element( 'a', [
			'href' => $href,
			'class' => 'external noteslink',
			'title' => strip_tags( $content ),
		], $content );
	}
}
