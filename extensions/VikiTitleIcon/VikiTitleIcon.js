/*
 * Copyright (c) 2014 The MITRE Corporation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

window.VIKI = ( function( mw, my ) {
	/**
	 * @class VikiTitleIcon
	 *
	 * Create VikiTitleIcon, a plugin to VIKI to handle pages using the Title Icon extension.
	 * These pages will show up with their title icons rather than wiki logos in VIKI.
	 *
	 */
	my.VikiTitleIcon = {
		hookName: "",

		/**
		 * Hook function to check this page for the existence of a title icon.
		 *
		 * This is the hook function registered with VIKI to check for title icon usage.
		 * It calls queryForTitleIcon to execute the actual query.
		 *
 		 * @param {Object} vikiObject reference to the VIKI object that this is a plugin to
		 * @param {Array} parameters all VIKI hook calls come with parameters
		 * @param {string} hookName name of the hook this function was registered with
		 */
		checkForTitleIcon: function( vikiObject, parameters, hookName ) {
			this.hookName = hookName;
			var node = parameters[ 0 ];

			this.queryForTitleIcon( vikiObject, node );
		},

		/**
		 * Query this page for usage of a title icon.
		 *
 		 * @param {Object} vikiObject reference to the VIKI object that this is a plugin to
		 * @param {Object} node node to check for title icon
		 */
		queryForTitleIcon: function( vikiObject, node ) {
			var self = this;
			jQuery.ajax( {
				url: node.apiURL,
				dataType: node.sameServer ? 'json' : 'jsonp',
				data: {
					action: 'getTitleIcons',
					format: 'json',
					pageTitle: node.pageTitle
				},
				beforeSend: function( ) {},
				success: function( data ) {
					self.titleIconSuccessHandler( vikiObject, data, node );
				},
				error: function( ) {
					vikiObject.showError( mw.message( 'vikititleicon-error-fetch', node.pageTitle )
						.text() );
					vikiObject.hookCompletion( self.hookName );
				}
			} );
		},

		/**
		 * Process query result from queryForTitleIcon.
		 *
		 * This method is called from queryForTitleIcon to process the data returned from the query.
		 * The node's hook icon URL is set to the URL of the title icon so it will render with
		 * the title icon, rather than the wiki logo.
		 *
		 * @param {Object} vikiObject reference to the VIKI object that this is a plugin to
		 * @param {Object} data data returned from the query
		 * @param {Object} node node to check for title icon
		 */
		titleIconSuccessHandler: function( vikiObject, data, node ) {
			if ( data.error && data.error.code && data.error.code === "unknown_action" ) {
				vikiObject.hookCompletion( my.hookName );
				return;
			}

			var titleIconURLs = data.getTitleIcons.titleIcons;
			if ( titleIconURLs.length === 0 || titleIconURLs[ 0 ] === null ) {
				vikiObject.hookCompletion( my.hookName );
				return;
			}

			node.hookIconURL = titleIconURLs[ 0 ];
			vikiObject.hookCompletion( my.hookName, {
				"redrawNode": true,
				"node": node
			} );

		}

	};

	return my;
}( mediaWiki, window.VIKI || {} ) );
