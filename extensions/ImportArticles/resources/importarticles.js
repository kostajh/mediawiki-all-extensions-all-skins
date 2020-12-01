/*!
 * Import JavaScript and Stylesheet articles.
 *
 * @version 1.3
 * @licence GPL-3.0-only http://www.gnu.org/licenses/gpl.html
 * @author Kris Blair (Cblair91)
 */

/**
 * Call the api and send the data then output the return
 *
 * @param {Array} data The data to use.
 * @param {string} method The method to use: either 'GET' or 'POST'.
 * @param {Function} callback The function to call back to.
 */
function callAPI( data, method, callback ) {
	data.format = 'json';
	$.ajax( {
		data: data,
		dataType: 'json',
		url: '/api.php',
		type: method,
		success: function ( response ) {
			if ( response.error ) {
				mw.error( 'API error: ' + response.error.info );
				return false;
			} else if ( response.warnings ) {
				mw.error( 'API Warning: ' + response.warnings.main['*'] );
			} else {
				callback( response );
			}
		},
		error: function ( xhr, error ) {
			mw.error( 'AJAX error: ' + error );
		}
	} );
}

/**
 *
 * >> Examples:
 *
 * // Importing multiple JavaScript files
 *  importArticles(
 *      'script', [
 *          'Mediawiki:MyCustomJavaScript.js',
 *          'Mediawiki:MyCustomJavaScript.js2',
 *      ]
 *  });
 *
 * @param {string} type The type to load: Either 'script' or 'style'.
 * @param {string|Array} pages The file(s) to load.
 * @return {boolean} Whether success or fail
 */
function importArticles( type, pages ) {
	var page,
		outputText = '',
		query = {
			action: 'query',
			prop: 'revisions',
			rvprop: 'content',
			indexpageids: 'true'
		};
	if ( Array.isArray( pages ) ) {
		query.titles = pages.join( '|' );
	} else {
		query.titles = pages;
	}
	if ( type === 'script' ) {
		window.output = document.createElement( 'script' );
		window.output.type = 'text/javascript';
	} else if ( type === 'style' ) {
		window.output = document.createElement( 'style' );
	} else {
		mw.error( 'Invalid article type: ' + type );
		return false;
	}
	callAPI(
		query,
		'GET',
		function ( response ) {
			var i;
			for ( i = 0; i < response.query.pageids.length; i++ ) {
				page = response.query.pages[response.query.pageids[i]];
				if ( response.query.pageids[i] === '-1' ) {
					mw.error( 'The page does not exist: ' + page.title );
				} else {
					outputText = outputText + '\n' + page.revisions[0]['*'];
				}
			}
			try {
				window.output.appendChild( document.createTextNode( outputText ) );
			} catch ( e ) {
				window.output.text = outputText;
			}
			document.body.appendChild( window.output );
		}
	);
}
