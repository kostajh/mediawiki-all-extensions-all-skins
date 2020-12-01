<?php
/**
 * @author Ike Hecht <tosfos@yahoo.com>
 */
class ShowMe {
	private $type;
	private $name;
	private $options;
	protected static $num = 0;

	/**
	 * @param string $type The type of input field, currently must be 'dropdown'
	 * @param string $name The name and ID to be assigned to the input field
	 * @param string[] $options Use the form label => value
	 * @param ParserOutput $out
	 */
	function __construct( $type, $name, array $options, ParserOutput $out ) {
		$this->type = $type;
		$this->name = $name;
		$this->options = $options;

		// Add this $name to he array of ShowMe field IDs that exist on this page
		$configVars = $out->getJsConfigVars();
		$configVars['wgShowMeDropdownIDs'][] = $name;
		$out->addJsConfigVars( 'wgShowMeDropdownIDs', $configVars['wgShowMeDropdownIDs'] );
	}

	/**
	 * Get the output HTML
	 *
	 * @return string
	 */
	public function getHTML() {
		// Theoretically, other types may be added in the future, such as radio buttons.
		if ( $this->type == 'dropdown' ) {
			return $this->getDropdownHTML();
		}
		// invalid type
		return ''; // Throw error?
	}

	/**
	 * Get the output HTML for a dropdown
	 *
	 * @return string
	 */
	protected function getDropdownHTML() {
		$select = new XmlSelect( $this->name, $this->name );
		$select->addOptions( $this->options );
		return $select->getHTML();
	}
}
