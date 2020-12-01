<?php
namespace MultiMaps;

/**
 * Base class for collection of map elements
 *
 * @file BaseService.php
 * @ingroup MultiMaps
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @license GPL-2.0-or-later
 * @property-read array $pos Array of geographic coordinates
 * @property string $title Title of element
 * @property string $text Popup text of element
 */
abstract class BaseMapElement {

	/**
	 * Geographic coordinates
	 * @var Point[]
	 */
	protected $coordinates;

	/**
	 * @todo Description
	 * @var bool
	 */
	protected $isValid;

	/**
	 * An array that is used to accumulate the error messages
	 * @var array
	 */
	protected $errormessages;

	/**
	 * Array of properties available for this element
	 * @var array
	 */
	protected $availableProperties;

	/**
	 * Array of element properties
	 * @var array
	 */
	protected $properties;

	/**
	 * Returns element name
	 * return string Element name
	 */
	abstract public function getElementName();

	function __construct() {
		$this->availableProperties = [
			'title',
			'text',
		];

		$this->reset();
	}

	public function __get( $name ) {
		return $this->getProperty( $name );
	}

	/**
	 * Get element property by name
	 * @param string $name
	 * @return mixed
	 */
	public function getProperty( $name ) {
		$name = strtolower( $name );

		switch ( $name ) {
			case 'pos':
				return $this->coordinates;
				break;
			default:
				if ( isset( $this->properties[$name] ) ) {
					return $this->properties[$name];
				}
				break;
		}
		return null;
	}

	public function __set( $name, $value ) {
		$this->setProperty( $name, $value );
	}

	/**
	 * Set element property by name
	 * @param string $name
	 * @param mixed $value
	 * @return bool
	 */
	public function setProperty( $name, $value ) {
		$name = strtolower( $name );

		if ( array_search( $name, $this->availableProperties ) === false ) {
			return false;
		}

		if ( is_string( $value ) ) {
			$value = trim( $value );
			$this->properties[$name] = htmlspecialchars( $value, ENT_NOQUOTES );
		} else {
			$this->properties[$name] = $value;
		}
		return true;
	}

	/**
	 * Unset element property by name
	 * @param string $name
	 */
	public function unsetProperty( $name ) {
		$name = strtolower( $name );

		if ( isset( $this->properties[$name] ) ) {
			unset( $this->properties[$name] );
		}
	}

	/**
	 * Filling properties of the object according to the obtained data
	 * @global string $egMultiMaps_DelimiterParam
	 * @param string $param
	 * @param string|null $service Name of map service
	 * @return bool returns false if there were errors during parsing, it does not mean that the item was not added. Check with isValid()
	 */
	public function parse( $param, $service = null ) {
		global $egMultiMaps_DelimiterParam;
		$this->reset();

		$arrayparam = explode( $egMultiMaps_DelimiterParam, $param );

		// The first parameter should always be coordinates
		$coordinates = array_shift( $arrayparam );
		if ( $this->parseCoordinates( $coordinates, $service ) === false ) {
			$this->errormessages[] = \wfMessage( 'multimaps-unable-create-element', $this->getElementName() )->escaped();
			return false;
		}

		// These parameters are optional
		$this->isValid = true;
		return $this->parseProperties( $arrayparam );
	}

	/**
	 * Filling property 'coordinates'
	 * @global string $egMultiMaps_CoordinatesSeparator
	 * @param string $coordinates
	 * @param string|null $service Name of map service
	 * @return bool
	 */
	protected function parseCoordinates( $coordinates, $service = null ) {
		global $egMultiMaps_CoordinatesSeparator;

		$array = explode( $egMultiMaps_CoordinatesSeparator, $coordinates );
		foreach ( $array as $value ) {
			$point = new Point();
			if ( $point->parse( $value, $service ) ) {
				$this->coordinates[] = $point;
			} else {
				$this->errormessages[] = \wfMessage( 'multimaps-unable-parse-coordinates', $value )->escaped();
				return false;
			}
		}
		return true;
	}

	/**
	 *
	 * @param array $param
	 * @return bool false if there were errors during parsing
	 */
	protected function parseProperties( array $param ) {
		$return = true;
		// filling properties with the names
		$matches = [];
		$properties = implode( '|', $this->availableProperties );
		foreach ( $param as $key => $paramvalue ) {
			if ( preg_match( "/^\s*($properties)\s*=(.+)$/si", $paramvalue, $matches ) ) {
				if ( !$this->setProperty( $matches[1], $matches[2] ) ) {
					$return = false;
				}
				unset( $param[$key] );
			}
		}

		// filling properties without the names
		reset( $param );
		$value = current( $param );
		if ( $value === false ) {
			return $return;
		}
		foreach ( $this->availableProperties as $name ) {
			if ( $this->getProperty( $name ) === null ) {
				if ( preg_match( '/^\s*$/s', $value ) == false ) { // Ignore empty values
					if ( !$this->setProperty( $name, $value ) ) {
						$return = false;
					}
				}
				$value = next( $param );
				if ( $value === false ) {
					return $return;
				}
			}
		}

		$this->errormessages[] = \wfMessage( 'multimaps-element-more-parameters', $this->getElementName() )->escaped();
		$notprocessed = [ $value ];
		while ( $value = next( $param ) ) {
			$notprocessed[] = $value;
		}
		$this->errormessages[] = \wfMessage( 'multimaps-element-parameters-not-processed', '"' . implode( '", "', $notprocessed ) . '"' )->escaped();
		return false;
	}

	/**
	 * Checks if the object is valid
	 * @return bool
	 */
	public function isValid() {
		return $this->isValid;
	}

	/**
	 * Initializes the object again, and makes it invalid
	 */
	public function reset() {
		$this->isValid = false;
		$this->coordinates = [];
		$this->errormessages = [];
		$this->properties = [];
	}

	/**
	 * Returns an error messages
	 * @return array
	 */
	public function getErrorMessages() {
		return $this->errormessages;
	}

	/**
	 * Returns an array of data
	 * @return array
	 */
	public function getData() {
		if ( $this->isValid() ) {
			$ret = [];
			foreach ( $this->coordinates as $pos ) {
				$ret['pos'][] = $pos->getData();
			}
			return array_merge( $ret, $this->properties );
		}
	}
}
