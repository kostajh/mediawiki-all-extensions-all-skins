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

( function( $ ) {
	/**
	 * Gobal function to display a hierarchy.
	 *
	 * @param {string} divId Name of the div to render the hierarchy into
	 * @param {string} hierarchy A 2 dimensional array of the data
	 * - for each row in hierarchy, the first column is the level of indentation,
	 *	 the second column is the name of the page, and the third column is
	 *	 the URL of the page
	 * @param {string} collapsed A Boolean that indicates if the tree should
	 *  start collapsed
	 * @param {string} numbered A boolean that indicates if the hierarchy
	 *  should be auto-numbered
	 */
	window.renderHierarchy = function( divId, hierarchy, collapsed, numbered ) {
		/**
		 * @class RenderHierarchy
		 *
		 * Object literal implementing hierarchy rendering.
		 *
		 * This is actually an object literal which is defined and used by the
		 * global renderHierarchy function which contains all of the necessary
		 * functions for rendering a hierarchy.
		 */
		( {
			/**
			 * Render a hierarchy on a page.
			 *
			 * @param {string} divId Name of the div to render the hierarchy into
			 * @param {string} hierarchy A 2 dimensional array of the data
			 * - for each row in hierarchy, the first column is the level of
			 *   indentation, the second column is the name of the page, and the
			 *   third column is the URL of the page
			 * @param {string} collapsed A Boolean that indicates if the tree
			 *  should start collapsed
			 * @param {string} numbered A boolean that indicates if the hierarchy
			 *  should be auto-numbered
			 */
			render: function( divId, hierarchy, collapsed, numbered ) {
				if ( hierarchy.length < 1 ) {
					return;
				}

				if ( numbered ) {
					var $hierarchy = $( hierarchy );
					$hierarchy = this.numberHtml( $hierarchy );
					hierarchy = $hierarchy[ 0 ].outerHTML;
				}

				var jqDivId = "#" + divId;
				$( jqDivId )
					.html( hierarchy )
					.attr('dir', 'ltr');
				$( jqDivId + " > div > ul" )
					.unwrap();
				$( jqDivId + " * li" )
					.css( "list-style-image", "none" );
				$( jqDivId )
					.bind( "loaded.jstree", function( event, data ) {
						if ( collapsed ) {
							$( jqDivId )
								.jstree( "close_all" );
						} else {
							$( jqDivId )
								.jstree( "open_all" );
						}
					} );
				$( jqDivId )
					.bind( "refresh.jstree", function( event, data ) {
						if ( collapsed ) {
							$( jqDivId )
								.jstree( "close_all" );
						} else {
							$( jqDivId )
								.jstree( "open_all" );
						}
					} );
				$( jqDivId )
					.jstree( {
						"themes": {
							"theme": "apple",
							"dots": true,
							"icons": false
						},
						"plugins": [ "themes", "html_data" ]
					} );
			},

			/**
			 * Updates a hierarchy to include section numbers.
			 *
			 * This function takes an HTML hierarchy and applies section numbers
			 * to each row of the hierarchy. The resulting HTML hierarchy with
			 * section numbers applied is then returned.
			 *
			 * @param {Object} uListRoot A jquery object representing the top
			 *  level ul element of the entire HTML hierarchy.
			 */
			numberHtml: function( uListRoot ) {
				var list = uListRoot.clone();
				return this.numberHtmlHelper( list, "" );
			},

			/**
			 * Helper function for applying section numbers to a hierarchy.
			 *
			 * This is a helper function which recursively traverses a hierarchy
			 * and computes and applies section numbers to the beginning of each
			 * row in that hierarchy.
			 *
			 * @param {Object} uListRoot A jquery object representing the top
			 *  level ul element of the entire HTML hierarchy.
			 * @param {string} numberPrefix A string containing the immediate
			 *  parent's complete section number. (ex: 1.1) This is used to
			 *  construct the child's complete section number by using the
			 *  numberPrefix as a suffix. (ex: 1.1 is used to create 1.1.1)
			 *
			 * @return {Object} A jquery object representing the top level ul
			 *  element of the HTML hierarchy with section numbers applied.
			 */
			numberHtmlHelper: function( uListRoot, numberPrefix ) {
				var that = this;

				var $numberSuffix = 1; // this is the subsection number for a particular child. It starts at one because the first child is numbered 1.

				var cur = uListRoot[ 0 ];
				$( cur )
					.children( $( "li" ) )
					.each( function() {
						var $children = $( this )
							.contents();

						var childNumber = numberPrefix === "" ? $numberSuffix++ : numberPrefix + "." + $numberSuffix++;
						var numberedChild = childNumber + " " + $children.filter("a").first()
							.text();
						$children.filter("a").first()
							.text( numberedChild );

						var $sublist = $children.filter( "ul" );
						if ( $sublist.size() > 0 ) {
							that.numberHtmlHelper( $sublist, childNumber ); // recurse on the sublist
						}
					} );
				return uListRoot;
			}
		} )
		.render( divId, hierarchy, collapsed, numbered );
	};
}( jQuery ) );

$( function() {
	if ( mw.config.exists( 'HierarchyBuilderRender' ) ) {
		var hierarchies = mw.config.get( 'HierarchyBuilderRender' );
		for ( index in hierarchies ) {
			var hierarchy = hierarchies[index];
			renderHierarchy( hierarchy.div, hierarchy.hierarchy,
				hierarchy.collapsed, hierarchy.numbered );
		}
	}
});