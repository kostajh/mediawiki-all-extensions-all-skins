<?php
namespace MultiMaps;

/**
 * Base class for collection of services
 *
 * @file BaseMapService.php
 * @ingroup MultiMaps
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @license GPL-2.0-or-later
 *
 * @property float $zoom Map scale
 * @property float $minzoom Minimum scale map
 * @property float $maxzoom Maximum scale map
 * @property string $center Center of the map
 * @property string $bounds The visible bounds of the map
 * @property string $width
 * @property string $height
 * @property-read string $classname Class name for tag "<div>" of map
 */
abstract class BaseMapService {

	/**
	 * class name for tag "<div>" of map
	 * @var string
	 */
	protected $classname = '';

	/**
	 * Array of the defined modules that be loaded during the output
	 * @var array
	 */
	protected $resourceModules;

	/**
	 * Text for adding to the "<head>" during the output
	 * @var string
	 */
	protected $headerItem;

	/**
	 * Map property "width" used for tag "<div>"
	 * @var string
	 */
	protected $width;

	/**
	 * Map property "height" used for tag "<div>"
	 * @var string
	 */
	protected $height;

	/**
	 * An array that is used to accumulate the error messages
	 * @var array
	 */
	protected $errormessages;

	/**
	 * Array of elements map marker
	 * @var Marker[]
	 */
	protected $markers;

	/**
	 * Array of elements map line
	 * @var Line[]
	 */
	protected $lines;

	/**
	 * Array of elements map polygon
	 * @var Polygon[]
	 */
	protected $polygons;

	/**
	 * Array of elements map rectangle
	 * @var Rectangle[]
	 */
	protected $rectangles;

	/**
	 * Array of elements map circle
	 * @var Circle[]
	 */
	protected $circles;

	/**
	 * The boundaries of the map elements
	 * @var Bounds
	 */
	protected $elementsBounds;

	/**
	 * Array of map properties
	 * @var array
	 */
	protected $properties;

	/**
	 * Array of map elements availables for adding
	 * @var array
	 */
	protected $availableMapElements = [
		'marker',
		'markers',
		'line',
		'lines',
		'polygon',
		'polygons',
		'rectangle',
		'rectangles',
		'circle',
		'circles',
	];

	/**
	 * Array of map properties available for definition
	 * @var array
	 */
	protected $availableMapProperties = [
		'width',
		'height',
		'zoom',
		'minzoom',
		'maxzoom',
		'center',
		'bounds',
		'title',
		'text',
		'icon',
		'color',
		'weight',
		'opacity',
		'fillcolor',
		'fillopacity',
		'fill',
	];

	/**
	 * Array of map properties definition of which should not cause an error
	 * @var array
	 */
	protected $ignoreProperties = [
		'service',
	];

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->resourceModules = [ 'ext.MultiMaps' ];
		$this->headerItem = '';

		$this->reset();
	}

	/**
	 * Returns html data for rendering map
	 * @return string
	 */
	public function render() {
		static $mapid = 0;

		$output = \Html::rawElement(
				'div',
				[
					'id' => 'multimaps_map' . $mapid++,
					'style' => 'width:' . htmlspecialchars( $this->width ) . '; height:' . htmlspecialchars( $this->height ) . ';',
					'class' => 'multimaps-map' . ( $this->classname != '' ? " multimaps-map-$this->classname" : '' ),
					],
				\Html::element( 'p', [], \wfMessage( 'multimaps-loading-map' )->escaped() ) .
				\Html::rawElement(
						'div',
						[ 'class' => 'multimaps-mapdata' ],
						\FormatJson::encode( $this->getMapData() )
						)
				);

		$errors = $this->getErrorMessages();
		if ( count( $errors ) > 0 ) {
			$output .= "\n" .
					\Html::rawElement(
						'div',
						[ 'class' => 'multimaps-errors' ],
						\wfMessage( 'multimaps-had-following-errors' )->escaped() .
						'<br />' .
						\implode( '<br />', $this->getErrorMessages() )
						);
		}

		return $output;
		// return array( $output, 'noparse' => true, 'isHTML' => true );
	}

	/**
	 * Returns array of map data
	 * @param array $param Optional, if sets - parse param before returns data of map
	 * @return array
	 */
	public function getMapData( array $param = [] ) {
		if ( count( $param ) != 0 ) {
			$this->parse( $param );
		}

		$calculatedProperties = [];

		if ( $this->bounds === null ) {
			if ( $this->center === null ) {
				$bounds = $this->elementsBounds;
				if ( $bounds->isValid() ) {
					if ( $bounds->ne == $bounds->sw ) {
						if ( $this->zoom === null ) {
							$calculatedProperties['zoom'] = $GLOBALS['egMultiMaps_DefaultZoom'];
						}
						$calculatedProperties['center'] = $bounds->getCenter()->getData();
					} elseif ( $bounds->isValid() ) {
						if ( $this->zoom === null ) {
							$calculatedProperties['bounds'] = $bounds->getData();
						} else {
							$calculatedProperties['center'] = $bounds->getCenter()->getData();
						}
					}
				}
			} else {
				// TODO
			}
		}

		$return = [];

		foreach ( $this->markers as $marker ) {
			$return['markers'][] = $marker->getData();
		}
		foreach ( $this->lines as $line ) {
			$return['lines'][] = $line->getData();
		}
		foreach ( $this->polygons as $polygon ) {
			$return['polygons'][] = $polygon->getData();
		}
		foreach ( $this->rectangles as $rectangle ) {
			$return['rectangles'][] = $rectangle->getData();
		}
		foreach ( $this->circles as $circle ) {
			$return['circles'][] = $circle->getData();
		}

		return array_merge( $return, $calculatedProperties, $this->properties );
	}

	/**
	 * Parse params and fill map data
	 * @param array $param
	 * @param bool $reset Reset service before parse data
	 */
	public function parse( array $param, $reset = true ) {
		if ( $reset ) {
			$this->reset();
		}

		$matches = [];
		foreach ( $param as $value ) {
			if ( preg_match( '/^\s*(\w+)\s*=\s*(.+)\s*$/s', $value, $matches ) ) {
				$name = strtolower( $matches[1] );
				if ( array_search( $name, $this->availableMapElements ) !== false ) {
					$this->addMapElement( $name, $matches[2] );
				} elseif ( array_search( $name, $this->availableMapProperties ) !== false ) {
					$this->setProperty( $name, $matches[2] );
					// TODO exception
				} else {
					if ( array_search( $name, $this->ignoreProperties ) === false ) {
						$this->errormessages[] = \wfMessage( 'multimaps-unknown-parameter', $matches[1] )->escaped();
					}
				}
				continue;
			} else {
				// Default map element = 'marker'
				$this->addMapElement( 'marker', $value );
			}
		}
	}

	/**
	 * Add new map element to map
	 * @param string $name
	 * @param string $value
	 * @return bool
	 */
	public function addMapElement( $name, $value ) {
		if ( trim( $value == '' ) ) {
			return;
		}
		$name = strtolower( $name );

		switch ( $name ) {
			case 'marker':
			case 'markers':
				return $this->addElementMarker( $value );
				break;
			case 'line':
			case 'lines':
				return $this->addElementLine( $value );
				break;
			case 'polygon':
			case 'polygons':
				return $this->addElementPolygon( $value );
				break;
			case 'rectangle':
			case 'rectangles':
				return $this->addElementRectangle( $value );
				break;
			case 'circle':
			case 'circles':
				return $this->addElementCircle( $value );
			default:
				break;
		}
	}

	/**
	 * Add marker to map
	 * @param string $value
	 * @return bool
	 */
	public function addElementMarker( $value ) {
		$return = true;
		$stringsmarker = explode( $GLOBALS['egMultiMaps_SeparatorItems'], $value );
		foreach ( $stringsmarker as $markervalue ) {
			if ( trim( $markervalue ) == '' ) {
				continue;
			}
			$marker = new Marker();
			if ( !$marker->parse( $markervalue, $this->classname ) ) {
				$return = false;
				$this->errormessages = array_merge( $this->errormessages, $marker->getErrorMessages() );
			}
			if ( !$marker->isValid() ) {
				continue;
			}
			$this->markers[] = $marker;
			$this->elementsBounds->extend( $marker->pos );
		}
		return $return;
	}

	/**
	 * Add line to map
	 * @param string $value
	 * @return bool
	 */
	public function addElementLine( $value ) {
		$return = true;
		$stringsline = explode( $GLOBALS['egMultiMaps_SeparatorItems'], $value );
		foreach ( $stringsline as $linevalue ) {
			if ( trim( $linevalue ) == '' ) {
				continue;
			}
			$line = new Line();
			if ( !$line->parse( $linevalue, $this->classname ) ) {
				$return = false;
				$this->errormessages = array_merge( $this->errormessages, $line->getErrorMessages() );
			}
			if ( !$line->isValid() ) {
				continue;
			}
			$this->lines[] = $line;
			$this->elementsBounds->extend( $line->pos );
		}
		return $return;
	}

	/**
	 * Add polygon to map
	 * @param string $value
	 * @return bool
	 */
	public function addElementPolygon( $value ) {
		$return = true;
		$stringspolygon = explode( $GLOBALS['egMultiMaps_SeparatorItems'], $value );
		foreach ( $stringspolygon as $polygonvalue ) {
			if ( trim( $polygonvalue ) == '' ) {
				continue;
			}
			$polygon = new Polygon();
			if ( !$polygon->parse( $polygonvalue, $this->classname ) ) {
				$return = false;
				$this->errormessages = array_merge( $this->errormessages, $polygon->getErrorMessages() );
			}
			if ( !$polygon->isValid() ) {
				continue;
			}
			$this->polygons[] = $polygon;
			$this->elementsBounds->extend( $polygon->pos );
		}
		return $return;
	}

	/**
	 * Add rectangle to map
	 * @param string $value
	 * @return bool
	 */
	public function addElementRectangle( $value ) {
		$return = true;
		$stringsrectangle = explode( $GLOBALS['egMultiMaps_SeparatorItems'], $value );
		foreach ( $stringsrectangle as $rectanglevalue ) {
			if ( trim( $rectanglevalue ) == '' ) {
				continue;
			}
			$rectangle = new Rectangle();
			if ( !$rectangle->parse( $rectanglevalue, $this->classname ) ) {
				$return = false;
				$this->errormessages = array_merge( $this->errormessages, $rectangle->getErrorMessages() );
			}
			if ( !$rectangle->isValid() ) {
				continue;
			}
			$this->rectangles[] = $rectangle;
			$this->elementsBounds->extend( $rectangle->pos );
		}
		return $return;
	}

	/**
	 * Add circle to map
	 * @param string $value
	 * @return bool
	 */
	public function addElementCircle( $value ) {
		$return = true;
		$stringscircle = explode( $GLOBALS['egMultiMaps_SeparatorItems'], $value );
		foreach ( $stringscircle as $circlevalue ) {
			if ( trim( $circlevalue ) == '' ) {
				continue;
			}
			$circle = new Circle();
			if ( !$circle->parse( $circlevalue, $this->classname ) ) {
				$return = false;
				$this->errormessages = array_merge( $this->errormessages, $circle->getErrorMessages() );
			}
			if ( !$circle->isValid() ) {
				continue;
			}
			$this->circles[] = $circle;
			$circlescount = count( $circle->pos );
			for ( $index = 0; $index < $circlescount; $index++ ) {
				$ne = new Point( $circle->pos[$index]->lat, $circle->pos[$index]->lon );
				$sw = new Point( $circle->pos[$index]->lat, $circle->pos[$index]->lon );
				$ne->move( $circle->radiuses[$index], $circle->radiuses[$index] );
				$sw->move( -$circle->radiuses[$index], -$circle->radiuses[$index] );
				$this->elementsBounds->extend( [ $ne, $sw ] );
			}
		}
		return $return;
	}

	public function __set( $name, $value ) {
		$this->setProperty( $name, $value );
	}

	public function setProperty( $name, $value ) {
		// TODO available properties
		$name = strtolower( $name );

		switch ( $name ) {
			case 'center':
				$center = \MultiMaps\GeoCoordinate::getLatLonFromString( $value );
				if ( $center ) {
					$this->properties['center'] = $center;
				} else {
					$this->errormessages[] = \wfMessage( 'multimaps-unable-parse-parameter', $name, $value )->escaped();
				}
				return true;
				break;
			case 'icon':
				$marker = new Marker();
				if ( $marker->setProperty( 'icon', $value ) ) {
					if ( $marker->icon !== null ) {
						$this->properties['icon'] = $marker->icon;
					}
					if ( $marker->size !== null ) {
						$this->properties['icon_size'] = $marker->size;
					}
					if ( $marker->anchor !== null ) {
						$this->properties['icon_anchor'] = $marker->anchor;
					}
					if ( $marker->shadow !== null ) {
						$this->properties['icon_shadow'] = $marker->shadow;
					}
					if ( $marker->sh_size !== null ) {
						$this->properties['icon_sh_size'] = $marker->sh_size;
					}
					if ( $marker->sh_anchor !== null ) {
						$this->properties['icon_sh_anchor'] = $marker->sh_anchor;
					}
				} else {
					$this->errormessages = array_merge( $this->errormessages, $marker->getErrorMessages() );
				}
				return true;
				break;
			case 'height':
				$this->height = $value;
				return true;
				break;
			case 'width':
				$this->width = $value;
				return true;
				break;
			case 'bounds':
				// TODO
				break;

			default:
				if ( is_string( $value ) ) {
					$this->properties[$name] = htmlspecialchars( $value, ENT_NOQUOTES );
				} else {
					$this->properties[$name] = $value;
				}
				return true;
				break;
		}
		return false;
	}

	public function __get( $name ) {
		return $this->getProperty( $name );
	}

	public function getProperty( $name ) {
		$name = strtolower( $name );

		switch ( $name ) {
			case 'classname':
				return $this->classname;
				break;
			default:
				return isset( $this->properties[$name] ) ? $this->properties[$name] : null;
		}
	}

	/**
	 * Add dependencies (resourceModules, headerItem) to Parser output
	 * @param \Parser &$parser
	 */
	public function addDependencies( \Parser &$parser ) {
		$output = $parser->getOutput();
		foreach ( $this->resourceModules as $modules ) {
			$output->addModules( $modules );
		}

		if ( $this->headerItem != '' ) {
			$output->addHeadItem( $this->headerItem, "multimaps_{$this->classname}" );
		}
	}

	/**
	 * Initializes the object again
	 */
	public function reset() {
		$this->elementsBounds = new Bounds();
		$this->width = $GLOBALS['egMultiMaps_Width'];
		$this->height = $GLOBALS['egMultiMaps_Height'];
		$this->properties = [];

		$this->markers = [];
		$this->lines = [];
		$this->polygons = [];
		$this->rectangles = [];
		$this->circles = [];

		$this->errormessages = [];
	}

	/**
	 * Returns an error messages
	 * @return array
	 */
	public function getErrorMessages() {
		return $this->errormessages;
	}

	/**
	 * Push error message into error messages
	 * @param string $string
	 */
	public function pushErrorMessage( $string ) {
		$this->errormessages[] = $string;
	}

}
