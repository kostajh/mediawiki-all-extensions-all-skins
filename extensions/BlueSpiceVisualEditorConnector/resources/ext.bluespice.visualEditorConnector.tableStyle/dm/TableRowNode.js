bs.util.registerNamespace( 'bs.vec.dm' );

bs.vec.dm.TableRowNode = function() {
	bs.vec.dm.TableRowNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( bs.vec.dm.TableRowNode, ve.dm.TableRowNode );

/* Override default data creation */
ve.dm.TableRowNode.static.createData = function ( options ) {
	var i, cellCount,
		data = [];

	options = options || {};

	cellCount = options.cellCount || 1;

	data.push( $.extend( {
		type: 'tableRow'
	}, options.styles || {} ) );
	for ( i = 0; i < cellCount; i++ ) {
		data = data.concat( ve.dm.TableCellNode.static.createData( options ) );
	}
	data.push( { type: '/tableRow' } );
	return data;
};

ve.dm.TableRowNode.static.applyCellStyling = function ( rowNode, cell, cellIndex, styles ) {
	var data = [];
	data.push( rowNode.element, styles );

	data.push( { type: '/tableRow' } );
	return data;
};

ve.dm.TableRowNode.static.applyStylings = function ( rowNode, styles ) {
	var data = [];
	data.push( $.extend( rowNode.element, styles || {} ) );
	data.push( { type: '/tableRow' } );
	return data;
};

bs.vec.dm.TableRowNode.static.toDataElement = function ( domElements ) {
	var result = {};

	this.runTableStyles( 'toDataElement', [ domElements[0], result ] );

	result = $.extend( result, {
		type: this.name
	} );
	return result;
};

bs.vec.dm.TableRowNode.static.toDomElements = function ( dataElement, doc ) {
	var	domElement = doc.createElement( 'tr' );

	this.runTableStyles( 'toDomElements', [ dataElement, domElement ] );

	return [ domElement ];
};

bs.vec.dm.TableRowNode.static.runTableStyles = function( func, params ) {
	var registry = bs.vec.registry.TableStyle.registry,
		callback = $.noop,
		scope = null,
		callbackParams = [ 'row' ].concat( params ),
		tableStyleKey;

	for( tableStyleKey in registry ) {
		if ( !registry.hasOwnProperty( tableStyleKey ) ) {
			continue;
		}
		scope = registry[tableStyleKey];
		callback = scope[func];
		callback.apply( scope, callbackParams );
	}
};

ve.dm.modelRegistry.register( bs.vec.dm.TableRowNode );
