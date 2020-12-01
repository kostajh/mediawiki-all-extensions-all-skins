<?php

/**
 * A list item to be played
 */
class MultimediaPlayerItem {

	/**
	 * Source to load
	 *
	 * @var string
	 */
	private $source;

	/**
	 * ID for this item
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Link text to display
	 *
	 * @var string
	 */
	private $linkText;

	public function getSource() {
		return $this->source;
	}

	public function getId() {
		return $this->id;
	}

	public function getLinkText() {
		return $this->linkText;
	}

	public function setSource( $source ) {
		return wfSetVar( $this->source, $source );
	}

	/**
	 * Adds a prefix to the ID and sets member variable
	 *
	 * @param string $id HTML id
	 * @return string Previous value
	 */
	public function setId( $id ) {
		$id = self::getItemIdPrefix() . $id; //ID's can't begin with a number
		$id = Sanitizer::escapeIdForAttribute( $id );
		return wfSetVar( $this->id, $id );
	}

	public function setLinkText( $linkText ) {
		return wfSetVar( $this->linkText, $linkText );
	}

	function __construct( $source, $id, $linkText ) {
		$this->setSource( $source );
		$this->setId( $id );
		$this->setLinkText( $linkText );
	}

	/**
	 * @return string Prefix added to ID of the HTML element that surrounds the MultimediaPlayerItem
	 */
	public static function getItemIdPrefix() {
		return 'mm-';
	}

	/**
	 *
	 * @return string Suffix added to each MultimediaPlayerItem of all items of this source
	 */
	public static function getItemClassSuffix() {
		return '-item';
	}

	/**
	 * Generate the HTML for this item
	 *
	 * @return string HTML
	 */
	public function getOutput() {
		$id = $this->getId();

		$source = Sanitizer::escapeClass( $this->getSource() );
		$classes = array( $source . self::getItemClassSuffix(), 'plainlinks' );

		$link = Linker::makeExternalLink( '#', $this->getLinkText(), false );

		return Html::rawElement( 'span', array( 'id' => $id, 'class' => $classes ), $link );
	}
}
