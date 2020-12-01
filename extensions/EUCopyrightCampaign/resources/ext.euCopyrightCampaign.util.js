( function() {

	/**
	 *
	 * @param object data an object like
	 * {
	 *     "email": "a.somebody@somewhere.tld",
	 *     "firstname": "Amy",
	 *     "lastname": "Somebody",
	 *     "freetext": "I frequently post about ...",
	 *     "options": [
	 *         "protect-public-domain",
	 *         "freedom-of-panorama",
	 *         "exception-text-data-mining"
	 *     ],
	 *     "newsletter": false
	 * }
	 * @returns {undefined}
	 */
	function _storeAction( data ) {
		var api = new mw.Api();
		api.postWithEditToken( {
			action: 'eucc-store-action',
			data: JSON.stringify( data )
		} );
		//No need to handle response as this is only for "statistics/analytics"
	}

	window.eucc = {
		ui: {
			dialog: {}
		},
		storeAction: _storeAction
	};

} )();
