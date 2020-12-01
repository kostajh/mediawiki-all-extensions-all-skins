bs.util.registerNamespace( 'bs.vec.util' );

bs.vec.util.StyleAttributeParser = function( value ) {
	if ( typeof value !== 'undefined') {
		// Parse even if empty style is passed
		this.setStyle( value );
	}
};

OO.initClass( bs.vec.util.StyleAttributeParser );

/**
 * This class enabled operations on style attribute of a DOM element
 *
 * @param string style
 */
bs.vec.util.StyleAttributeParser.prototype.setStyle = function( style ) {
	this.value = style;
	this.formatted = {};
	this.parseStyle();
};

bs.vec.util.StyleAttributeParser.prototype.parseStyle = function() {
	var pairs, pair, pairBits, attr, value, i;
	if ( typeof this.value !== 'string' ) {
		return {};
	}
	pairs = this.value.split( ';' );
	for( i = 0; i < pairs.length; i++ ) {
		pair = pairs[i];
		if ( pair.trim() === '' ) {
			continue;
		}
		pairBits = pair.split( ':' );
		if ( pairBits.length < 2 ) {
			continue;
		}
		value = pairBits.pop().trim();
		attr = pairBits.pop().trim();
		this.formatted[attr] = value;
	}
	return this.formatted;
};

bs.vec.util.StyleAttributeParser.prototype.addToStyle = function( attr, value ) {
	this.formatted[attr] = value;
};

bs.vec.util.StyleAttributeParser.prototype.removeFromStyle = function( attr ) {
	if ( this.formatted.hasOwnProperty( attr ) ) {
		delete( this.formatted[attr] );
	}
};

bs.vec.util.StyleAttributeParser.prototype.getValueForAttr = function( attr ) {
	if ( this.formatted.hasOwnProperty( attr ) ) {
		return this.formatted[attr];
	}
	return null;
};

bs.vec.util.StyleAttributeParser.prototype.toString = function() {
	var attr, str = '';
	for( attr in this.formatted ) {
		if ( !this.formatted.hasOwnProperty( attr ) ) {
			continue;
		}
		str += attr + ':' + this.formatted[attr] + ';';
	}
	return str;
};
