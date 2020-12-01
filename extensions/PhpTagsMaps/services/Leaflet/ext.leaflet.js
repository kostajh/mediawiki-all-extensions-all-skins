/**
 * JavaScript for Leaflet in the MultiMaps extension.
 *
 * @see https://www.mediawiki.org/wiki/Extension:MultiMaps
 *
 * @author Pavel Astakhov < pastakhov@yandex.ru >
 */

/* global L */
mediaWiki.MultiMapsLeaflet = {
	/**
	 * Convert properties given from multimaps extension to options of map element
	 *
	 * @param {Object} properties Contains the fields lat, lon, title, text and icon
	 * @return {Object} options of map element
	 */
	convertPropertiesToOptions: function ( properties ) {
		var options = {}, text = false;

		if ( properties.icon !== undefined ) {
			var iconOptions = { iconUrl: properties.icon };
			if ( properties.size !== undefined ) {
				iconOptions.iconSize = properties.size;
			}
			if ( properties.anchor !== undefined ) {
				iconOptions.iconAnchor = properties.anchor;
			}
			if ( properties.shadow !== undefined ) {
				iconOptions.shadowUrl = properties.shadow;
			}
			if ( properties.sh_size !== undefined ) {
				iconOptions.shadowSize = properties.sh_size;
			}
			if ( properties.sh_anchor !== undefined ) {
				iconOptions.shadowAnchor = properties.sh_anchor;
			}
			options.icon = new L.Icon( iconOptions );
		}
		if ( properties.color !== undefined ) {
			options.color = properties.color;
		}
		if ( properties.weight !== undefined ) {
			options.weight = properties.weight;
		}
		if ( properties.opacity !== undefined ) {
			options.opacity = properties.opacity;
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
		var marker, value = this.convertPropertiesToOptions( properties );

		marker = L.marker( [ properties.pos[ 0 ].lat, properties.pos[ 0 ].lon ], value.options ).addTo( map );
		if ( value.text ) {
			marker.bindPopup( value.text );
		}
	},

	addLine: function ( map, properties ) {
		var x, polyline, latlngs = [], value = this.convertPropertiesToOptions( properties );

		for ( x = 0; x < properties.pos.length; x++ ) {
			latlngs.push( [ properties.pos[ x ].lat, properties.pos[ x ].lon ] );
		}

		polyline = L.polyline( latlngs, value.options ).addTo( map );
		if ( value.text ) {
			polyline.bindPopup( value.text );
		}
	},

	addPolygon: function ( map, properties ) {
		var x, polygon, latlngs = [], value = this.convertPropertiesToOptions( properties );

		for ( x = 0; x < properties.pos.length; x++ ) {
			latlngs.push( [ properties.pos[ x ].lat, properties.pos[ x ].lon ] );
		}

		polygon = L.polygon( latlngs, value.options ).addTo( map );
		if ( value.text ) {
			polygon.bindPopup( value.text );
		}
	},

	addCircle: function ( map, properties ) {
		var circle, value = this.convertPropertiesToOptions( properties );

		circle = L.circle( [ properties.pos[ 0 ].lat, properties.pos[ 0 ].lon ], properties.radius[ 0 ], value.options ).addTo( map );
		if ( value.text ) {
			circle.bindPopup( value.text );
		}
	},

	addRectangle: function ( map, properties ) {
		var bounds, rectangle, value = this.convertPropertiesToOptions( properties );

		bounds = [ [ properties.pos[ 0 ].lat, properties.pos[ 0 ].lon ], [ properties.pos[ 1 ].lat, properties.pos[ 1 ].lon ] ];

		rectangle = L.rectangle( bounds, value.options ).addTo( map );
		if ( value.text ) {
			rectangle.bindPopup( value.text );
		}
	},

	setup: function ( element, options ) {
		var map, i, mapOptions = {};

		if ( options.minzoom !== false ) {
			mapOptions.minZoom = options.minzoom;
		}
		if ( options.maxzoom !== false ) {
			mapOptions.maxZoom = options.maxzoom;
		}

		map = L.map( element, mapOptions ).fitWorld();

		// add an OpenStreetMap tile layer
		L.tileLayer( 'http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
		} ).addTo( map );

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
			map.fitBounds( [
				[ options.bounds.sw.lat, options.bounds.sw.lon ],
				[ options.bounds.ne.lat, options.bounds.ne.lon ]
			] );
		} else {
			if ( options.center ) {
				map.setView( [ options.center.lat, options.center.lon ], options.zoom );
			} else if ( options.zoom ) {
				map.setZoom( options.zoom );
			}
		}
	}
};

( function ( $, mw ) {

	$( document ).ready( function () {
		mw.loader.using( 'ext.MultiMaps', function () {
			$( '.multimaps-map-leaflet' ).each( function () {
				var $this = $( this );
				mw.MultiMapsLeaflet.setup( $this.get( 0 ), JSON.parse( $this.find( 'div' ).text() ) );
			} );
		} );
	} );

}( jQuery, mediaWiki ) );
