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

window.VIKI = ( function ( mw, my ) {
	/**
	 * @class VikiSemanticTitle
	 *
	 * Create VikiSemanticTitle, a plugin to VIKI to handle pages using the Semantic Title extension.
	 *
	 */
	my.VikiSemanticTitle = {
		displayNames: {},
		hookName: '',

		/**
		 * Hook function to check this page for the usage of a semantic title.
		 *
		 * This is the hook function registered with 'AfterVisitNodeHook' to check for the usage
		 * of a semantic title. It calls queryForSemanticTitle to execute the actual query.
		 *
		 * @param {Object} vikiObject reference to the VIKI object that this is a plugin to
		 * @param {Array} parameters all VIKI hook calls come with parameters
		 * @param {string} hookName name of the hook this function was registered with
		 */
		checkForSemanticTitle: function ( vikiObject, parameters, hookName ) {
			this.hookName = hookName;
			var node = parameters[ 0 ];
			node.semanticTitle = node.pageTitle;
			if ( !node.semanticQueried && !node.dynamicPage ) {
				this.queryForSemanticTitle( vikiObject, node );
			}

		},

		/**
		 * Query this page for the usage of a semantic title.
		 *
		 * This method is called from checkForSemanticTitle to query the MediaWiki API
		 * for the semantic title (display title) of this page (an API extension also packaged in VikiSemanticTitle).
		 *
		 * @param {Object} vikiObject reference to the VIKI object that this is a plugin to
		 * @param {Object} node node to check for display name
		 */
		queryForSemanticTitle: function ( vikiObject, node ) {
			var self = this;
			jQuery.ajax( {
				url: node.apiURL,
				dataType: node.sameServer ? 'json' : 'jsonp',
				data: {
					action: 'query',
					prop: 'pageprops',
					titles: node.semanticTitle,
					ppprop: 'displaytitle',
					format: 'json'
				},
				success: function ( data ) {
					self.processDisplayTitle( vikiObject, data, node );

				},
				error: function () {
					vikiObject.showError( mw.message( 'vikisemantictitle-error-displaytitle-fetch', node.pageTitle )
						.text() );
					vikiObject.hookCompletion( self.hookName );
				}
			} );
		},

		/**
		 * Process query result from queryForSemanticTitle.
		 *
		 * This method is called from queryForSemanticTitle to process the data returned from the query.
		 * The node's display name is set to the display title if it exists.
		 *
		 * @param {Object} vikiObject reference to the VIKI object that this is a plugin to
		 * @param {Object} data data returned from the query
		 * @param {Object} node node to check for display name
		 */
		processDisplayTitle: function ( vikiObject, data, node ) {
			data = data.query.pages[ Object.keys( data.query.pages )[ 0 ] ];
			if ( data.pageprops && data.pageprops.displaytitle ) {
				var semanticTitle = data.pageprops.displaytitle;
				semanticTitleStripped = this.stripTags( semanticTitle ).trim();
				if ( semanticTitleStripped.length === 0 ) {
					vikiObject.hookCompletion( my.hookName );
				} else {
					semanticTitle = semanticTitle.replace( /&amp;/g, '&' )
						.replace( /&lt;/g, '<' )
						.replace( /&gt;/g, '>' )
						.replace( /&quot;/g, '"' )
						.replace( /&#039;/g, '\'' );
					if ( node.semanticTitle !== semanticTitle ) {

						node.displayName = semanticTitle.length < 20 ? semanticTitle : semanticTitle.substring( 0, 20 ) + '...';
						node.fullDisplayName = semanticTitle + ' (' + node.pageTitle + ')';
					}
					node.semanticQueried = true;

					vikiObject.hookCompletion( my.hookName, {
						redrawNode: true,
						node: node
					} );
				}
			} else {
				vikiObject.hookCompletion( my.hookName );
			}
		},
		stripTags: function ( html ) {
			var tmp = document.createElement( 'DIV' );
			tmp.innerHTML = html;
			return tmp.textContent || tmp.innerText || '';
		}
	};

	return my;
}( window.VIKI || {} ) );
