/**
 * JavaScript for Google in the MultiMaps extension.
 *
 * @see https://www.mediawiki.org/wiki/Extension:MultiMaps
 *
 * @author Pavel Astakhov < pastakhov@yandex.ru >
 */

/* global google */
mediaWiki.MultiMapsGoogle = {
	/**
	 * Convert properties given from multimaps extension to options of map element
	 *
	 * @param {Object} properties Contains the fields lat, lon, title, text and icon
	 * @return {Object} options of map element
	 */
	convertPropertiesToOptions: function ( properties ) {
		var options = {}, text = false;

		if ( properties.icon !== undefined ) {
			var iconOptions = { url: properties.icon };
			if ( properties.size !== undefined ) {
				iconOptions.scaledSize = new google.maps.Size( properties.size[ 0 ], properties.size[ 1 ] );
			}
			if ( properties.anchor !== undefined ) {
				iconOptions.anchor = new google.maps.Point( properties.anchor[ 0 ], properties.anchor[ 1 ] );
			}
			options.icon = iconOptions;
			if ( properties.shadow !== undefined ) {
				var shadowOptions = { url: properties.shadow };
				if ( properties.sh_size !== undefined ) {
					shadowOptions.scaledSize = new google.maps.Size( properties.sh_size[ 0 ], properties.sh_size[ 1 ] );
				}
				if ( properties.sh_anchor !== undefined ) {
					shadowOptions.anchor = new google.maps.Point( properties.sh_anchor[ 0 ], properties.sh_anchor[ 1 ] );
				}
				options.shadow = shadowOptions;
			}
		}
		if ( properties.color !== undefined ) {
			options.strokeColor = properties.color;
		}
		if ( properties.weight !== undefined ) {
			options.strokeWeight = properties.weight;
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
			options.title = properties.title;
			text = '<strong>' + properties.title + '</strong><hr />' + properties.text;
		} else if ( properties.title !== undefined ) {
			options.title = properties.title;
			text = '<strong>' + properties.title + '</strong>';
		} else if ( properties.text !== undefined ) {
			text = properties.text;
		}

		return { options: options, text: text };
	},

	/**
	 * Creates a new marker with the provided data,
	 * adds it to the map, and returns it.
	 *
	 * @param {Object} map
	 * @param {Object} properties Contains the fields lat, lon, title, text and icon
	 */
	addMarker: function ( map, properties ) {
		var marker, infowindow, value = this.convertPropertiesToOptions( properties );

		value.options.position = new google.maps.LatLng( properties.pos[ 0 ].lat, properties.pos[ 0 ].lon );
		value.options.map = map;

		marker = new google.maps.Marker( value.options );

		if ( value.text ) {
			infowindow = new google.maps.InfoWindow( { content: value.text } );
			google.maps.event.addListener( marker, 'click', function () {
				infowindow.open( marker.get( 'map' ), marker );
			} );
		}
	},

	addLine: function ( map, properties ) {
		var x, polyline, infowindow, latlngs = [], value = this.convertPropertiesToOptions( properties );

		for ( x = 0; x < properties.pos.length; x++ ) {
			latlngs.push( new google.maps.LatLng( properties.pos[ x ].lat, properties.pos[ x ].lon ) );
		}
		value.options.path = latlngs;
		value.options.map = map;

		polyline = new google.maps.Polyline( value.options );
		if ( value.text ) {
			infowindow = new google.maps.InfoWindow( { content: value.text } );
			google.maps.event.addListener( polyline, 'click', function () {
				infowindow.open( polyline.get( 'map' ), polyline );
			} );
		}
	},

	addPolygon: function ( map, properties ) {
		var latlngs, x, polygon, infowindow, value = this.convertPropertiesToOptions( properties );

		latlngs = [];
		for ( x = 0; x < properties.pos.length; x++ ) {
			latlngs.push( new google.maps.LatLng( properties.pos[ x ].lat, properties.pos[ x ].lon ) );
		}
		value.options.paths = latlngs;
		value.options.map = map;

		polygon = new google.maps.Polygon( value.options );
		if ( value.text ) {
			infowindow = new google.maps.InfoWindow( { content: value.text } );
			google.maps.event.addListener( polygon, 'click', function () {
				infowindow.open( polygon.get( 'map' ), polygon );
			} );
		}
	},

	addCircle: function ( map, properties ) {
		var circle, infowindow, value = this.convertPropertiesToOptions( properties );

		value.options.center = new google.maps.LatLng( properties.pos[ 0 ].lat, properties.pos[ 0 ].lon );
		value.options.radius = properties.radius[ 0 ];
		value.options.map = map;
		circle = new google.maps.Circle( value.options );
		if ( value.text ) {
			infowindow = new google.maps.InfoWindow( { content: value.text } );
			google.maps.event.addListener( circle, 'click', function () {
				infowindow.open( circle.get( 'map' ), circle );
			} );
		}
	},

	addRectangle: function ( map, properties ) {
		var bounds, rectangle, infowindow, value = this.convertPropertiesToOptions( properties );

		bounds = new google.maps.LatLngBounds();
		bounds.extend( new google.maps.LatLng( properties.pos[ 0 ].lat, properties.pos[ 0 ].lon ) );
		bounds.extend( new google.maps.LatLng( properties.pos[ 1 ].lat, properties.pos[ 1 ].lon ) );
		value.options.bounds = bounds;
		value.options.map = map;

		rectangle = new google.maps.Rectangle( value.options );
		if ( value.text ) {
			infowindow = new google.maps.InfoWindow( { content: value.text } );
			google.maps.event.addListener( rectangle, 'click', function () {
				infowindow.open( rectangle.get( 'map' ), rectangle );
			} );
		}
	},

	setup: function ( element, options ) {
		var map, i, mapOptions = {
			center: new google.maps.LatLng( 0, 0 ),
			zoom: 1,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		if ( options.minzoom !== false ) {
			mapOptions.minZoom = options.minzoom;
		}
		if ( options.maxzoom !== false ) {
			mapOptions.maxZoom = options.maxzoom;
		}

		map = new google.maps.Map( element, mapOptions ); // .fitWorld();

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
			map.fitBounds( new google.maps.LatLngBounds(
				new google.maps.LatLng( options.bounds.sw.lat, options.bounds.sw.lon ),
				new google.maps.LatLng( options.bounds.ne.lat, options.bounds.ne.lon )
			) );
		} else {
			if ( options.center ) {
				map.setCenter( new google.maps.LatLng( options.center.lat, options.center.lon ) );
				map.setZoom( parseInt( options.zoom, 10 ) );
			} else if ( options.zoom ) {
				map.setZoom( parseInt( options.zoom, 10 ) );
			}
		}
	}
};

( function ( $, mw ) {

	$( document ).ready( function () {
		mw.loader.using( 'ext.MultiMaps', function () {
			$( '.multimaps-map-google' ).each( function () {
				var $this = $( this );
				mw.MultiMapsGoogle.setup( $this.get( 0 ), JSON.parse( $this.find( 'div' ).text() ) );
			} );
		} );
	} );

}( jQuery, mediaWiki ) );
