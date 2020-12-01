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
	window.renderHierarchySelected = function( divId, hierarchy, collapsed, numbered, selectedComponents ) {
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
			render: function( divId, hierarchy, collapsed, numbered, selectedComponents ) {
				if ( hierarchy.length < 1 ) {
					return;
				}

				if ( numbered ) {
					var $hierarchy = $( hierarchy );
					$hierarchy = this.numberHtml( $hierarchy );
					hierarchy = $hierarchy[ 0 ].outerHTML;
				}

				if ( selectedComponents && selectedComponents.length > 0) {
					for (var i = 0; i < selectedComponents.length; i++) {
						selectedComponents[i] = selectedComponents[i].replace("%20", " ");
					}
				}

				var obj = this;

				var jqDivId = "#" + divId;
				$( jqDivId )
					.html( hierarchy )
					.attr('dir', 'ltr');
				$( jqDivId + " * li" )
					.css( "list-style-image", "none" );
				$( jqDivId )
					.bind( "loaded.jstree", function( event, data ) {
						obj.initializeTree( jqDivId, selectedComponents, true, collapsed );
					} );
				$( jqDivId )
					.bind( "refresh.jstree", function( event, data ) {
						obj.initializeTree( jqDivId, selectedComponents, true, collapsed );
					} );

				$( jqDivId )
					.jstree( {
						"themes": {
							"theme": "apple",
							"dots": true,
							"icons": false
						},
						"checkbox": {
							"two_state": true
						},
						"types": {
							"types": {
								"disabled": {
									"check_node": false,
									"uncheck_node": false
								}
							}
						},
						"plugins": [ "themes", "html_data", "checkbox", "types" ]
					} );
			},

			/**
			 * Initializes the displayed hierarchy to display the properly and
			 * have the correct behaviors.
			 *
			 * @param {string} jqDivId The id of the div containing the hierarchy
			 *  to be initialized.
			 * @param {Array} selectedComponents A list of the rows which should
			 *  be displayed as selected to begin with.
			 * @param {boolean} init A boolean to indicate whether the hierarchy
			 *  is being initialized or refreshed.
			 * @param {boolean} collapsed A boolean to indicate whether the
			 *  hierarchy should start out in collapsed or expanded form.
			 */
			initializeTree: function( jqDivId, selectedComponents, init, collapsed ) {
				var obj = this;

				$( jqDivId + "* li" )
					.each( function() {
						var parent = $( this );
						$( this )
							.children( "a" )
							.each( function() {
								var $element = $( this );
								var elementName = $( this )
									//.children( "span:first" )
									.text()
									.trim();
								if ( obj.isSelectedHierarchyComponent( elementName,
									selectedComponents ) ) {
									$( jqDivId )
										.jstree( "check_node", parent );
									$( this ).attr("class", "selectedHierarchyRow");
								}
							} );
					} );

				$( jqDivId + "* li" )
					.each( function() {
						$.jstree._reference( jqDivId )
							.set_type( "disabled",
								$( this ) );
					} );

				if ( collapsed ) {
					$( jqDivId )
						.jstree( "close_all" );
				} else {
					$( jqDivId )
						.jstree( "open_all" );
				}

			},

			/**
			 * Determine if a row has been selected or not.
			 *
			 * @param {string} elementName The row who's selected status is being
			 *  determined.
			 * @param {Array} selectedComponents The list of currently seleccted
			 *  hierarchy rows.
			 *
			 * @return {boolean} True if elementName is included in the array
			 *  selectedComponents. False otherwise.
			 */
			isSelectedHierarchyComponent: function( elementName,
				selectedComponents ) {
				if ( selectedComponents && selectedComponents.length > 0 ) {
					var pageName = elementName;
					var index = $.inArray( pageName, selectedComponents );
					if ( index !== -1 ) {
						return true;
					}
				}
				return false;
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
						var numberedChild = childNumber + " " + $children.first()
							.text();
						$children.first()
							.text( numberedChild );

						var $sublist = $children.filter( "ul" );
						if ( $sublist.size() > 0 ) {
							that.numberHtmlHelper( $sublist, childNumber ); // recurse on the sublist
						}
					} );
				return uListRoot;
			}
		} )
		.render( divId, hierarchy, collapsed, numbered, selectedComponents );
	};
}( jQuery ) );


$( function() {
	if ( mw.config.exists( 'HierarchyBuilderRenderSelected' ) ) {
		var hierarchies = mw.config.get( 'HierarchyBuilderRenderSelected' );
		for ( index in hierarchies ) {
			var hierarchy = hierarchies[index];
			renderHierarchySelected( hierarchy.div, hierarchy.hierarchy,
				hierarchy.collapsed, hierarchy.numbered,
				hierarchy.selectedPages );
		}
	}
});