/**
 * JavaScript for Yandex in the MultiMaps extension.
 *
 * @see https://www.mediawiki.org/wiki/Extension:MultiMaps
 *
 * @author Pavel Astakhov < pastakhov@yandex.ru >
 */

/* global ymaps */
mediaWiki.MultiMapsYandex = {
	/**
	 * Convert properties given from multimaps extension to options of map element
	 *
	 * @param {Object} properties Contains the fields lat, lon, title, text and icon
	 * @return {Object} options of map element
	 */
	convertPropertiesToOptions: function ( properties ) {
		var prop = {}, options = {};

		if ( properties.icon !== undefined ) {
			options.iconImageHref = properties.icon;
			if ( properties.size !== undefined ) {
				options.iconImageSize = properties.size;
			}
			if ( properties.anchor !== undefined ) {
				options.iconImageOffset = [ -properties.anchor[ 0 ], -properties.anchor[ 1 ] ];
			}
			if ( properties.shadow !== undefined ) {
				options.iconShadow = true;
				options.iconShadowImageHref = properties.shadow;
			}
			if ( properties.sh_size !== undefined ) {
				options.iconShadowImageSize = properties.sh_size;
			}
			if ( properties.sh_anchor !== undefined ) {
				options.iconShadowImageOffset = [ -properties.sh_anchor[ 0 ], -properties.sh_anchor[ 1 ] ];
			}
		}
		if ( properties.color !== undefined ) {
			options.strokeColor = properties.color;
		}
		if ( properties.weight !== undefined ) {
			options.strokeWidth = properties.weight;
		}
		if ( properties.opacity !== undefined ) {
			options.strokeOpacity = properties.opacity;
		}
		if ( properties.fill !== undefined ) {
			options.fill = properties.fill;
		}
		if ( properties.fillcolor !== undefined ) {
			options.fillColor = properties.fillcolor;
		}
		if ( properties.fillopacity !== undefined ) {
			options.fillOpacity = properties.fillopacity;
		}

		if ( properties.title !== undefined && properties.text !== undefined ) {
			prop.hintContent = properties.title;
			prop.balloonContent = '<strong>' + properties.title + '</strong><hr />' + properties.text;
		} else if ( properties.title !== undefined ) {
			prop.hintContent = properties.title;
			prop.balloonContent = '<strong>' + properties.title + '</strong>';
		} else if ( properties.text !== undefined ) {
			prop.balloonContent = properties.text;
		}

		return { properties: prop, options: options };
	},

	/**
	 * Creates a new marker with the provided data,
	 * adds it to the map, and returns it.
	 *
	 * @param {Object} map
	 * @param {Object} properties Contains the fields lat, lon, title, text and icon
	 */
	addMarker: function ( map, properties ) {
		var marker, value = this.convertPropertiesToOptions( properties );

		marker = new ymaps.Placemark( [ properties.pos[ 0 ].lat, properties.pos[ 0 ].lon ], value.properties, value.options );
		map.geoObjects.add( marker );
	},

	addLine: function ( map, properties ) {
		var x, polyline, latlngs = [], value = this.convertPropertiesToOptions( properties );

		for ( x = 0; x < properties.pos.length; x++ ) {
			latlngs.push( [ properties.pos[ x ].lat, properties.pos[ x ].lon ] );
		}

		polyline = new ymaps.Polyline( latlngs, value.properties, value.options );
		map.geoObjects.add( polyline );
	},

	addPolygon: function ( map, properties ) {
		var x, polygon, latlngs = [], value = this.convertPropertiesToOptions( properties );

		for ( x = 0; x < properties.pos.length; x++ ) {
			latlngs.push( [ properties.pos[ x ].lat, properties.pos[ x ].lon ] );
		}
		latlngs.push( [ properties.pos[ 0 ].lat, properties.pos[ 0 ].lon ] );

		polygon = new ymaps.Polygon( [ latlngs ], value.properties, value.options );
		map.geoObjects.add( polygon );
	},

	addCircle: function ( map, properties ) {
		var circle, value = this.convertPropertiesToOptions( properties );

		circle = new ymaps.Circle( [ [ properties.pos[ 0 ].lat, properties.pos[ 0 ].lon ], properties.radius[ 0 ] ], value.properties, value.options );
		map.geoObjects.add( circle );
	},

	addRectangle: function ( map, properties ) {
		var bounds, rectangle, value = this.convertPropertiesToOptions( properties );

		bounds = [ [ properties.pos[ 0 ].lat, properties.pos[ 0 ].lon ], [ properties.pos[ 1 ].lat, properties.pos[ 1 ].lon ] ];

		rectangle = new ymaps.Rectangle( bounds, value.properties, value.options );
		map.geoObjects.add( rectangle );
	},

	setup: function ( element, options ) {
		var map, i, mapState, mapOptions = {};
		if ( options.minzoom !== false ) {
			mapOptions.minZoom = options.minzoom;
		}
		if ( options.maxzoom !== false ) {
			mapOptions.maxZoom = options.maxzoom;
		}
		mapState = {
			center: [ 0, 0 ],
			zoom: 1
		};

		map = new ymaps.Map( element, mapState, mapOptions );
		map.controls
			.add( 'zoomControl' )
			.add( 'typeSelector' );

		// Add the markers.
		if ( options.markers !== undefined ) {
			for ( i = 0; i < options.markers.length; i++ ) {
				this.addMarker( map, mediaWiki.MultiMaps.fillByGlobalOptions( options, 'marker', options.markers[ i ] ) );
			}
		}

		// Add lines
		if ( options.lines !== undefined ) {
			for ( i = 0; i < options.lines.length; i++ ) {
				this.addLine( map, mediaWiki.MultiMaps.fillByGlobalOptions( options, 'line', options.lines[ i ] ) );
			}
		}

		// Add polygons
		if ( options.polygons !== undefined ) {
			for ( i = 0; i < options.polygons.length; i++ ) {
				this.addPolygon( map, mediaWiki.MultiMaps.fillByGlobalOptions( options, 'polygon', options.polygons[ i ] ) );
			}
		}

		// Add circles
		if ( options.circles !== undefined ) {
			for ( i = 0; i < options.circles.length; i++ ) {
				this.addCircle( map, mediaWiki.MultiMaps.fillByGlobalOptions( options, 'circle', options.circles[ i ] ) );
			}
		}

		// Add rectangles
		if ( options.rectangles !== undefined ) {
			for ( i = 0; i < options.rectangles.length; i++ ) {
				this.addRectangle( map, mediaWiki.MultiMaps.fillByGlobalOptions( options, 'rectangle', options.rectangles[ i ] ) );
			}
		}

		// Set map position (centre and zoom)
		if ( options.bounds ) {
			map.setBounds( [
				[ options.bounds.sw.lat, options.bounds.sw.lon ],
				[ options.bounds.ne.lat, options.bounds.ne.lon ]
			] );
		} else {
			if ( options.center ) {
				map.setCenter( [ options.center.lat, options.center.lon ], options.zoom );
			} else if ( options.zoom ) {
				map.setZoom( options.zoom );
			}
		}
	}
};

( function ( $, mw ) {

	ymaps.ready( function () {
		mw.loader.using( 'ext.MultiMaps', function () {
			$( '.multimaps-map-yandex' ).each( function () {
				var $this = $( this );
				$this.find( 'p' ).remove();
				mw.MultiMapsYandex.setup( $this.get( 0 ), JSON.parse( $this.find( 'div' ).text() ) );
			} );
		} );
	} );

}( jQuery, mediaWiki ) );
