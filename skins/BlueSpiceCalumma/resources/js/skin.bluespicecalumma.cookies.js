/**
 * Serves as a wrapper for all cookies used in Calumma.
 * This is done to encapsulate all the cookies with dynamic names
 * into a single cookie, while keeping the flexibility of the current implementation
 *
 * @constructor
 */
var BlueSpiceCalummaCookieHandler = function() {
	// This name has to be kept in sync with \BlueSpice\Calumma\CookieHandler::$cookieName
	this.cookieName = 'Calumma_state';
};

/**
 * Get a single cookie value from the unified cookie
 *
 * @param {string} name
 * @returns {null|*}
 */
BlueSpiceCalummaCookieHandler.prototype.get = function( name ) {
	var parsed = this.parse();
	if ( parsed.hasOwnProperty( name ) ) {
		return parsed[name];
	}

	return null;
};

/**
 * Set value of a single cookie that is a part of the unified cookie
 *
 * @param {string} name
 * @param {*} value
 */
BlueSpiceCalummaCookieHandler.prototype.set = function( name, value ) {
	var parsed = this.parse();
	parsed[name] = value;

	this.setInternally( parsed );
};

/**
 * Combine all single-cookie values and store then into the unified cookie
 *
 * @param {*} values
 */
BlueSpiceCalummaCookieHandler.prototype.setInternally = function( values ) {
	mw.cookie.set( this.cookieName, JSON.stringify( values ) );
};

/**
 * Get single-cookie values from the unified cookie
 *
 * @returns {array}
 */
BlueSpiceCalummaCookieHandler.prototype.parse = function() {
	var value = mw.cookie.get( this.cookieName );
	if ( !value ) {
		return {};
	}

	return JSON.parse( value );
};

( function( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.calumma' );
	bs.calumma.cookie = new BlueSpiceCalummaCookieHandler();
})( mediaWiki, jQuery, blueSpice );
