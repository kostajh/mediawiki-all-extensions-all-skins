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
	 * Global function to display the hierarchy.
	 *
	 * @param {string} inputId The id of a hidden form field in the
	 *  document that is used to pass the possibly modified list of selected
	 *  hierarchy rows back when the form is saved.
	 * @param {Object} params Object containing the following named
	 *  parameters necessary for initializing and customizing the edit
	 *  hierarchy interface.
	 *  The params object contains the following fields:
	 *
	 *   - divId - is the name of the div to render the hierarchy into
	 *   - hierarchy - is a 2 dimensional array of the data. For each row
	 *      in hierarchy, the first column is the level of indentation,
	 *      the second column is the name of the page, and the third
	 *      column is the URL of the page
	 *   - isDisabled - indicates whether editing should be disabled
	 *   - isMandatory - indicates whether it is mandatory for a value to
	 *      be returned (currently not implemented)
	 */
	window.selectFromHierarchyInit = function( inputId, params ) {
		/**
		 * @class SelectFromHierarchy
		 *
		 * Object literal implementing Select From Hierarchy.
		 *
		 * This is actually an object literal which is defined and used by the
		 * global selectFromHierarchyInit function which contains all of the
		 * necessary functions for implementing the select from Hierarchy
		 * functionality of the HierarchyBuilder extension.
		 */
		( {
			/**
			 * Initialize the select from hierarchy user interface.
			 *
			 * @param {string} inputId The id of a hidden form field in the
			 *  document that is used to pass the possibly modified list of
			 *  selected hierarchy rows back when the form is saved.
			 * @param {Object} params Object containing the following named
			 *  parameters necessary for initializing and customizing the edit
			 *  hierarchy interface.
			 *  The params object contains the following fields:
			 *
			 *   - divId - is the name of the div to render the hierarchy into
			 *   - hierarchy - is a 2 dimensional array of the data. For each row
			 *      in hierarchy, the first column is the level of indentation,
			 *      the second column is the name of the page, and the third
			 *      column is the URL of the page
			 *   - isDisabled - indicates whether editing should be disabled
			 *   - isMandatory - indicates whether it is mandatory for a value to
			 *      be returned (currently not implemented)
			 */
			init: function( inputId, params ) {
				if ( params.hierarchy.length < 1 ) {
					return;
				}
				
				var twostate = params.threestate ? false : true;

				var selectedComponents = params.selectedItems;
				var test = [];
				selectedComponents = selectedComponents.map( function( cur ) {
						if ( cur.indexOf( "[[" ) === 0 && cur.lastIndexOf( "]]" ) == cur.length - 2 ) {
							return cur.substring( 2, cur.length - 2 );
						} else {
							return cur;
						}
					} );

				var hierarchy = params.hierarchy;
				var html = hierarchy;
				//html = this.parseWikiTextToHtml( html );
				
				var jqDivId = "#" + params.divId;
				$( jqDivId )
					.html( html )
					.attr( 'dir', 'ltr' )
					.attr( 'class', 'scrollableHierarchy' );

				if ( params.width !== "" ) {
					$( jqDivId )
						.css( 'max-width', params.width );
				}
				if ( params.height !== "" ) {
					$( jqDivId )
						.css( 'max-height', params.height );
				}

				$( jqDivId + " * li" )
					.css( "list-style-image", "none" );

				var updatedSelectedComponents = [];
				var obj = this;
				$( jqDivId + "* li" )
					.each( function() {
						$( this )
							.children( "a" )
							.each( function() {
								var elementName = $( this )
									.children( "span:first" )
									.text();

								if ( obj.isSelectedHierarchyComponent( elementName, selectedComponents ) && $.inArray( elementName, updatedSelectedComponents ) === -1 ) {
										updatedSelectedComponents.push( elementName );
									}

							} );
					} );
				selectedComponents = updatedSelectedComponents;

				$( "#" + inputId )
					.val( selectedComponents.join( "," ) );

				$( jqDivId )
					.bind( "loaded.jstree", function( event, data ) {
						obj.initializeTree( jqDivId, params.isDisabled,
							selectedComponents, true, inputId, params.collapsed, params.threestate );
						/*$( jqDivId )
							.jstree( "open_all" );*/
					} );

				$( jqDivId )
					.bind( "refresh.jstree", function( event, data ) {
						obj.initializeTree( jqDivId, params.isDisabled,
							selectedComponents, false, inputId, params.collapsed, params.threestate );
					} );

				$( jqDivId )
					.jstree( {
						"themes": {
							"theme": "apple",
							"dots": true,
							"icons": false
						},
						"checkbox": {
							"two_state": twostate,
							"three_state": params.threestate
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

				/*$html = $( jqDivId ).html();
				$( jqDivId )
					.html( "<div width= height='100px'>" + $html + "</div>" );*/
			},

			/**
			 * Returns a list of the selected hierarchy rows so far.
			 *
			 * @param {string} inputId The id of the hidden form field used to
			 *  store possibly updated list of selected hierarchy rows.
			 *
			 * @return {Array} Array containing the selected hierarchy rows.
			 */
			getSelectedHierarchyComponents: function( inputId ) {
				var curValue = $( "#" + inputId )
					.val();
				curValue = $.trim( curValue );
				if ( curValue.length > 0 ) {
					return curValue.split( "," );
				} else {
					return [];
				}
			},

			/**
			 * Initializes the displayed hierarchy to display the properly and
			 * have the correct behaviors.
			 *
			 * @param {string} jqDivId The id of the div containing the hierarchy
			 *  to be initialized.
			 * @param {boolean} isDisabled A boolean indicating whether editing
			 *  should be disabled.
			 * @param {Array} selectedComponents A list of the rows which should
			 *  be displayed as selected to begin with.
			 * @param {string} inputId The id of the hidden form field used to
			 *  store the possibly updated list of selected hierarchy rows.
			 * @param {boolean} collapsed A boolean to indicate whether the
			 *  hierarchy should start out in collapsed or expanded form.
			 */
			initializeTree: function( jqDivId, isDisabled, selectedComponents,
				init, inputId, collapsed, threestate ) {
				var obj = this;
				if ( collapsed ) {
					$( jqDivId )
						.jstree( "close_all" );
				} else {
					$( jqDivId )
						.jstree( "open_all" );
				}
				$( jqDivId + "* li" )
					.each( function() {
						var parent = $( this );
						$( this )
							.children( "a" )
							.each( function() {
								var $element = $( this );
								var elementName = $( this )
									.children( "span:first" )
									.text();
								if ( obj.isSelectedHierarchyComponent( elementName,
									selectedComponents ) ) {
									$( jqDivId )
										.jstree( "check_node", parent );

									$( this )
										.attr( "class", "selectedHierarchyRow" );
								}
							} );
					} );
				if ( isDisabled ) {
					$( jqDivId + "* li" )
						.each( function() {
							$.jstree._reference( jqDivId )
								.set_type( "disabled",
									$( this ) );
						} );
				}
				if ( init ) {
					$( jqDivId )
						.bind( "check_node.jstree", function( event, data ) {
							data.rslt.obj.children( "a" )
								.each( function() {
									var elementName =
										$( this )
										.children( "span:first" )
										.text();
									obj.checkNode( jqDivId, elementName, inputId, threestate );
									obj.processDups( jqDivId, elementName, "check" );

									$( this )
										.attr( "class", "selectedHierarchyRow" );
								} );
						} );
					$( jqDivId )
						.bind( "uncheck_node.jstree", function( event, data ) {
							data.rslt.obj.children( "a" )
								.each( function() {
									var elementName =
										$( this )
										.children( "span:first" )
										.text();
									obj.uncheckNode( jqDivId, elementName, inputId, threestate );
									obj.processDups( jqDivId, elementName, "uncheck" );

									$( this )
										.attr( "class", "unselectedHierarchyRow" );
								} );
						} );
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
			 * Mark the specified hierarchy row as selected.
			 *
			 * @param {string} elementName The row which is being marked as
			 *  having been selected.
			 * @param {string} inputId The id of the hidden form field used to
			 *  store the possibly updated list of selected hierarchy rows.
			 */
			checkNode: function( jqDivId, elementName, inputId, threestate ) {
				if ( threestate ) {
					var selectedComponents = [];
					this.computeConciseSelectedComponents( $( jqDivId ), selectedComponents );
					selectedComponents = $.unique( selectedComponents );
					selectedComponents.sort();

					var curValue = selectedComponents.join( "," );
					$( "#" + inputId )
						.val( curValue );
				} else {
					var selectedComponents =
						this.getSelectedHierarchyComponents( inputId );
					var pageName = elementName;
					var curValue = pageName;
					if ( selectedComponents.length > 0 ) {
						var index = $.inArray( pageName, selectedComponents );
						if ( index === -1 ) {
							selectedComponents.push( pageName );
							selectedComponents.sort();
						}
						curValue = selectedComponents.join( "," );
					}
					$( "#" + inputId )
						.val( curValue );
				}

			},

			/**
			 * Mark the specified hierarchy row as unselected.
			 *
			 * @param {string} elementName The row which is being marked as
			 *  having been unselected.
			 * @param {string} inputId The id of the hidden form field used to
			 *  store the possibly updated list of selected hierarchy rows.
			 */
			uncheckNode: function( jqDivId, elementName, inputId, threestate ) {
				if ( threestate ) {
					var selectedComponents = [];
					this.computeConciseSelectedComponents( $( jqDivId ), selectedComponents );
					selectedComponents = $.unique( selectedComponents );
					selectedComponents.sort();

					var curValue = selectedComponents.join( "," );
					$( "#" + inputId )
						.val( curValue );
				} else {
					var selectedComponents =
						this.getSelectedHierarchyComponents( inputId );
					var pageName = elementName;
					var curValue = "";
					if ( selectedComponents.length > 0 ) {
						var index = $.inArray( pageName, selectedComponents );
						if ( index !== -1 ) {
							selectedComponents.splice( index, 1 );
						}
						curValue = selectedComponents.join( "," );
					}
					$( "#" + inputId )
						.val( curValue );
				}
			},

			/**
			 * Recursively processes a jstree hierarchy to find the most concise
			 * description of the selected pages when in three-state mode.
			 *
			 * This function recursively traverses the tree from top to bottom.
			 * If a node is selected, then all the children MUST be selected so
			 * this node is added to the list of selected items and we return.
			 * Otherwise, this node is not selected and we recurse on each child.
			 * The result is a list of only the most "senior" nodes in the
			 * hierarchy that have been selected.
			 *
			 * @param {jquery} $root The jquery object representing the root of
			 *  the current level within the jstree hierarchy.
			 * @param {array} selected The list that will contain the concise
			 *  set of selected nodes.
			 */
			computeConciseSelectedComponents: function( $root, selected ) {
				var that = this;

				var $cur = $root;
				var $children = $root.children( 'ul' )
					.children( 'li' );

				if ( $cur.hasClass( 'jstree-checked' ) ) {
					// TODO: handle legacy mode
					var elementName = $cur.children( 'a' )
						.children( 'span:first' )
						.text();
					var pageName = elementName;
					selected.push( elementName );
				} else {
					$children.each( function() {
						that.computeConciseSelectedComponents( $( this ), selected );
					} );
				}
			},

			/**
			 * Applies the check or uncheck operation to any duplicates of the
			 * current target.
			 *
			 * This function will run through all the other unselected hierarchy
			 * elements searching for duplicates of the given element. If any
			 * are found then we check those elements too.
			 *
			 * @param {string} jqDivId The divId that contains the hierarchy.
			 * @param {string} elementName The name of the element who's duplicates
			 *  we're trying to handle.
			 * @param {string} inputId The id of the hidden form field used to
			 *  store the possibly updated list of selected hierarcy rows.
			 * @param {string} action A string who's value is either "check" or
			 *  "uncheck" which indicates how duplicate rows should be processed.
			 */
			processDups: function( jqDivId, elementName, action ) {
				action = action === "check" ? "check_node" : "uncheck_node";
				var status = action === "check_node" ? "jstree-unchecked" : "jstree-checked";
				$( jqDivId + "* li" )
					.each( function() {
						var parent = $( this );
						$( this )
							.children( "a" )
							.each( function() {
								var curElementName = $( this )
									.children( "span:first" )
									.text();
								if ( curElementName === elementName && parent.hasClass( status ) ) {
									$( jqDivId )
										.jstree( action, parent );
								}
							} );
					} );
			},

			/**
			 * Convert a wikitext formatted hierarchy into HTML.
			 *
			 * Note: the given hierarchy must be well-formed.
			 *
			 * @param {string} wikiTextHierarchy is a string containing a
			 *  hierarchy in WikiText format.
			 *
			 * @return {string} HTML formatted hierarchy.
			 */
			parseWikiTextToHtml: function( wikiTextHierarchy ) {
				var hierarchyHtml = this.parseWikiTextToHtmlHelper( wikiTextHierarchy, "" );
				return hierarchyHtml;
			},

			/**
			 * Recursive helper method for converting a wikitext formatted
			 * hierarchy into HTML.
			 *
			 * @param {string} wikiTextHierarchy A hierarchy in modified wikitext
			 *  format. Specifically, the root node has no leading *s.
			 * @param {string} depth A string composed of * characters denoting
			 *  the current depth within the hierarchy.
			 *
			 * @return {string} HTML formatted hierarchy.
			 */
			parseWikiTextToHtmlHelper: function( wikiTextHierarchy, depth ) {
				// split the hierarchy into a list with the root and each child hierarchy in a list
				// this constructs a regular expression to search for lines with exactly depth+1 leading *s
				var nextDepth = "^" + depth + "*";
				var r1 = new RegExp( "\\*", "g" );
				var regex = nextDepth.replace( r1, "\\*" ) + "(?!\\*)";
				var r2 = new RegExp( regex, "mi" );
				// actually split the hierarchy into root and children
				var rootAndChildren = wikiTextHierarchy.split( r2 );

				var root = rootAndChildren[ 0 ]; // this is just the root row of this hierarchy
				var children = rootAndChildren.slice( 1 ); // this is a list of direct children hierarchies of the root. It might be an empty list though

				// take the root eleent and make a list item for it but don't close the list item yet incase there are nested kids
				var html = "";
				if ( depth !== "" ) {
					html = "<li>";

					var pageLinkRegex = new RegExp( "(\\[\\[)(.*)(\\]\\])", "g" ); // regex to find a link ([[pageName | displayName]])
					var pageLinkMatches = pageLinkRegex.exec( root );
					var pageLink = ( pageLinkMatches.length > 0 ? pageLinkMatches[ 2 ] : "" );
					var pageName = pageLink.split( " | " )[ 0 ];
					var displayName = pageLink.split( " | " )[ 1 ] ? pageLink.split( " | " )[ 1 ] : pageName;
					var rootRow = "<a>" +
						displayName +
						"<span style=display:none>" + pageName + "</span>" +
						"</a>";
					html += rootRow;
				}

				// if there are children, add an unordered-list element to contain them and recurse on each child
				if ( children.length > 0 ) {
					html += "<ul>";
					// add the html for each child to our string
					for ( var i = 0; i < children.length; i++ ) {
						html += this.parseWikiTextToHtmlHelper( children[ i ], depth + "*" );
					}
					html += "</ul>";
				}

				if ( depth !== "" ) {
					html += "</li>";
				}

				// now that our html has the root and the list with the children in html format we can finally return it.
				return html;
			}
		} )
		.init( inputId, params );
	};
}( jQuery ) );
