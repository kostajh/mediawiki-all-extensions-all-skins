<?php
namespace MultiMaps;

/**
 * Polygon class for collection of map elements
 *
 * @file Polygon.php
 * @ingroup MultiMaps
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @license GPL-2.0-or-later
 * @property boolean $fill
 * @property string $fillcolor
 * @property string $fillopacity
 */
class Polygon extends Line {

	/**
	 * @var array Values for set fill to true
	 */
	protected static $fill_true = [ 'yes', '1', 'true' ];

	/**
	 * @var array Values for set fill to false
	 */
	protected static $fill_false = [ 'no', '0', 'false' ];

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();

		$this->availableProperties = array_merge(
			$this->availableProperties,
			[ 'fillcolor', 'fillopacity', 'fill' ]
		);
	}

	/**
	 * Returns element name
	 * return string Element name
	 */
	public function getElementName() {
		return 'Polygon'; // TODO i18n?
	}

	/**
	 * Set element property by name
	 * @param string $name
	 * @param mixed $value
	 * @return bool
	 */
	public function setProperty( $name, $value ) {
		$name = strtolower( $name );
		$value = trim( $value );

		switch ( $name ) {
			case 'fill':
				if ( $value === true || array_search( $value, self::$fill_true ) !== false ) {
					$this->properties['fill'] = true;
					return true;
				} elseif ( $value == false || array_search( $value, self::$fill_false ) !== false ) {
					$this->properties['fill'] = false;
					$this->unsetProperty( 'fillcolor' );
					$this->unsetProperty( 'fillopacity' );
					return true;
				} else {
					$this->errormessages[] = \wfMessage( 'multimaps-element-illegal-value', $name, $value, '"' . implode( '", "', self::getPropertyValidValues( $name ) ) . '"' )->escaped();
					return false;
				}
				break;
			case 'fillcolor':
			case 'fillopacity':
				$this->fill = true;
				return parent::setProperty( $name, $value );
				break;
			default:
				return parent::setProperty( $name, $value );
				break;
		}
	}

	/**
	 * Returns array of valid values for property
	 * This function helps test code
	 * @param string $name
	 * @return array
	 */
	public static function getPropertyValidValues( $name ) {
		$name = strtolower( $name );

		switch ( $name ) {
			case 'fill':
				return array_merge( self::$fill_true, self::$fill_false );
				break;
		}
	}

}
