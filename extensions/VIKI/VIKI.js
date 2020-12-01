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


window.VIKI = ( function( mw, $, vex, Spinner, d3, my ) {
	/**
	 * @class VikiJS
	 *
	 * Create VikiJS, the core VIKI module.
	 *
	 */
	my.VikiJS = function() {

		/*
		 * Global Constants. Note: all colors except "LIGHT_BLUE" are from flatuicolors.com
		 */
		this.ID = null;
		this.WIKI_PAGE_TYPE = 0;
		this.EXTERNAL_PAGE_TYPE = 1;
		this.MAX_BAR_WIDTH = 60;
		this.BAR_HEIGHT = 6;
		this.UNSELECTED_IMAGE_DIMENSION = 25;
		this.THIS_WIKI = "THIS WIKI";
		this.MIN_SCALE = 0.2;
		this.MAX_SCALE = 5;
		this.GRAVITY = 0.2;
		this.LINK_STRENGTH = 1.25;
		this.LINK_OPACITY = 0.2;
		this.HUB_LINK_LENGTH = 400;
		this.LEAF_LINK_LENGTH = 150;
		this.AMETHYST_COLOR = "#9b59b6";
		this.PETER_RIVER_COLOR = "#3498db";
		this.EMERALD_COLOR = "#2ecc71";
		this.SUNFLOWER_COLOR = "#f1c40f";
		this.LIGHT_BLUE = "#23a4ff";
		this.INCOMING_LINK_COLOR = this.PETER_RIVER_COLOR;
		this.OUTGOING_LINK_COLOR = this.SUNFLOWER_COLOR;
		this.BIDIRECTIONAL_LINK_COLOR = this.EMERALD_COLOR;
		this.HIDE_HUB = 0;
		this.HIDE_INCOMING = 1;
		this.HIDE_OUTGOING = 2;
		this.ELABORATION_TYPE_NORMAL = 0;
		this.ELABORATION_TYPE_SECOND_ORDER_LINKS = 1;
		this.ELABORATION_TYPE_INITIAL_POPULATION = 2;

		/*
		 * Mutable Global Variables
		 */
		this.CURRENT_IDENTIFIER = 0;
		this.loadingView = null;
		this.progressbarView = null;
		this.searchableCount = 0;
		this.contentNamespacesFetched = 0;
		this.initialPageTitles = null;
		this.Hooks = null;
		this.hasHooks = false;
		this.GraphDiv = null;
		this.SubDetailsDiv = null;
		this.ErrorsDiv = null;
		this.SliderDiv = null;
		this.HamburgerSpan = null;
		this.SelectedNodeIndex = null;
		this.Nodes = [];
		this.Links = [];
		this.LinkMap = {};
		this.HiddenNodes = [];
		this.HiddenLinks = [];
		this.Force = null;
		this.LinkSelection = null;
		this.NodeSelection = null;
		this.ImagePath = null;
		this.Zoompos = 1; // to store values for zoom scale
		this.serverURL = mw.config.get( "wgServer" );
		this.myApiURL = this.serverURL + mw.config.get( "wgScriptPath" ) + "/api.php";
		this.allWikis = [];
		this.HiddenCategories = [];
		this.NoCategoryNodesHidden = false;

		this.ongoingElaborations = 0;
		this.TempLinks = [];
		this.TempHiddenLinks = [];
		this.TempNodes = [];
		this.OngoingElaborationNodes = [];
		this.showSecondOrderLinks = false;
		this.slideoverVisible = false;
		this.allCategories = {};

		var self = this;

		/**
		 * Initialize the VIKI graph.
		 *
		 * Call this method from VikiJS.php to initialize the VIKI graph.
		 *
		 * @param {Array} pageTitles Array of initial page titles to render in the graph.
		 * @param {Array} divs Array of divs to draw the graph components in order:
		 * - main graph div, where the graph lives
		 * - details div, where the page title and slider live
		 * - slider div, where the slider lives (inside details div)
		 * - errors div, where errors are shown
		 * @param {Object} parameters Other parameters passed into the VIKI graph, including:
		 * - graph width
		 * - graph height
		 * - hook functions to be called
		 * - file path to the images directory for VIKI images
		 * - hidden categories
		 */
		my.VikiJS.prototype.initialize = function( pageTitles, divs, parameters ) {
			var self = this;

			// Parse passed in parameters and initialize div settings.
			// this.ID = this graph div's ID (support for multiple VIKI graphs on one page in the future)
			var allDivs = jQuery.parseJSON( divs );
			var allParameters = jQuery.parseJSON( parameters );

			this.GraphDiv = allDivs[ 0 ];
			this.SubDetailsDiv = allDivs[ 1 ];
			this.SliderDiv = allDivs[ 2 ];
			this.ErrorsDiv = allDivs[ 3 ];
			this.HamburgerSpan = allDivs[ 4 ];

			this.ID = this.GraphDiv.match( new RegExp( "[0-9]", 'g' ) )[ 0 ];
			this.INITIAL_WIDTH = allParameters.width;
			this.INITIAL_HEIGHT = allParameters.height;
			this.showSecondOrderLinks = allParameters.showSecondOrderLinks;
			this.height = self.INITIAL_HEIGHT;
			this.width = self.INITIAL_WIDTH;
			this.Hooks = allParameters.hooks;
			this.hasHooks = ( self.Hooks !== null );
			this.serverURL = mw.config.get( "wgServer" );
			this.ImagePath = allParameters.imagePath;
			this.initialPageTitles = jQuery.parseJSON( pageTitles );
			this.HiddenCategories = allParameters.hiddenCategories || [];
			if ( this.initialPageTitles === null ) {
				self.showError( mw.message( 'viki-error-missing-pageTitle' )
					.text() );
				return;
			}
			$( '#' + self.GraphDiv ).css('height', this.INITIAL_HEIGHT+'px');

			var myLogoURL = allParameters.logoURL;

			// Add this wiki's data to self.allWikis first.
			var thisWikiData = {
				wikiTitle: this.THIS_WIKI,
				apiURL: this.myApiURL,
				contentURL: mw.config.get( "wgServer" ) + mw.config.get( "wgArticlePath" ),
				logoURL: myLogoURL,
				searchableWiki: true
			};

			self.allWikis.push( thisWikiData );

			// Show a placeholder that says "No Node Selected"
			self.displayNoNodeSelected();

			// Set up the slider, error divs and slideover menu.
			self.initializeSliderDiv();
			self.initializeErrorDiv();
			self.initializeSlideover();

			// Set up the loading spinner
			self.initializeLoadingSpinner();

			// Set up the context menu.
			self.initializeContextMenu();

			// Initialize the D3 graph.
			self.initializeGraph();

			// End of initialization; call the InitializationCompleteHook, then GetAllWikis hook.

			this.callHooks( "InitializationCompleteHook", [] );

			var calledGetAllWikisHook = this.callHooks( "GetAllWikisHook", [] );
			if ( !calledGetAllWikisHook )
				self.hookCompletion( "GetAllWikisHook" );
		};

		/**
		 * Initialize the slider div.
		 *
		 * This method is called internally by initialize().
		 */
		my.VikiJS.prototype.initializeSliderDiv = function() {
			// The details div gets 3/5 of the space, while the slider gets 2/5.
			// The detail-panel width is equal to the input width - size of paddings.
			var margin = 10;
			$( "#" + self.SubDetailsDiv )
				.width( ( self.width - margin ) * 3 / 5 );
			$( "#" + self.SliderDiv )
				.width( ( ( self.width - margin ) * 2 / 5 ) - 8*margin );
			$( ".vikijs-detail-panel" )
				.width( self.width - margin );
			// create a new zoom slider
			$( "#" + self.SliderDiv )
				.slider( {
					orientation: "horizontal", //make the slider horizontal
					min: self.MIN_SCALE, // set the lowest value
					max: self.MAX_SCALE, // set the highest value
					step: 0.001, // set the value for each individual increment
					value: self.Zoompos, // set the starting value
					slide: function( event, ui ) {
						// set the zoom scale equal to the current value of the slider
						// which is the current position
						self.Zoompos = ui.value;
						// call the slide function to zoom/pan using the slider
						self.slide();
					}
				} );
		};

		/**
		 * Initialize the error div.
		 *
		 * This method is called internally by initialize().
		 */
		my.VikiJS.prototype.initializeErrorDiv = function() {
			$( "#" + self.ErrorsDiv )
				.append( "<p><strong>" + mw.message( 'viki-error-title' )
					.text() + "</strong></p>" );
		};

		/**
		 * Initialize the hamburger button.
		 *
		 * This method is called internally by initialize().
		 *
		 */
		my.VikiJS.prototype.initializeSlideover = function() {
			var menuHtml = '\
			<div class="menu-container" id="nav-'+ self.ID +'">\
				<ul class="slide-menu" id="menu-'+ self.ID +'">\
					<li class="nav-item"><a href="#">Home</a></li>\
					<li class="nav-item"><a href="#">Portfolio</a></li>\
					<li class="nav-item"><a href="#">About</a></li>\
					<li class="nav-item"><a href="#">Blog</a></li>\
					<li class="nav-item"><a href="#">Contact</a></li>\
				</ul>\
			</div>';
			$( "#" + self.GraphDiv ).append(menuHtml);
			$( "#menu-" + self.ID ).css( "height", self.INITIAL_HEIGHT );
			$( "#nav-" + self.ID ).css( "height", self.INITIAL_HEIGHT );
			$( "#nav-" + self.ID ).css( "pointer-events", "none" );
			$( "#" + self.HamburgerSpan ).html("&equiv;");
			$( "#" + self.HamburgerSpan ).on( "click", function() {
				if(self.slideoverVisible)
					self.hideSlideover();
				else
					self.showSlideover();
				self.slideoverVisible = !self.slideoverVisible;
			});
		}

		/**
		 * Initialize the loading spinner.
		 *
		 * This method is called internally by initialize().
		 */
		my.VikiJS.prototype.initializeLoadingSpinner = function() {
			vex.defaultOptions.className = 'vex-theme-default';

			var loadingContent = '\
<div id="loadingDiv">\
	<div id="textDiv">Loading...</div>\
	<div id="spinnerDiv"></div>\
</div>';

			var loadingStyle = '\
<style>\
	#textDiv {\
		text-align: center;\
	}\
	#spinnerDiv {\
		height: 75px;\
	}\
</style>';

			var opts = {
				lines: 11, // The number of lines to draw
				length: 8, // The length of each line
				width: 4, // The line thickness
				radius: 8, // The radius of the inner circle
				corners: 1, // Corner roundness (0..1)
				rotate: 0, // The rotation offset
				direction: 1, // 1: clockwise, -1: counterclockwise
				color: '#000', // #rgb or #rrggbb or array of colors
				speed: 1, // Rounds per second
				trail: 60, // Afterglow percentage
				shadow: false, // Whether to render a shadow
				hwaccel: false, // Whether to use hardware acceleration
				className: 'spinner', // The CSS class to assign to the spinner
				zIndex: 2e9, // The z-index (defaults to 2000000000)
				top: '60%', // Top position relative to parent
				left: '50%' // Left position relative to parent
			};

			self.loadingView = vex.open( {
				content: loadingContent,
				css: {
					position: "absolute",
					top: "auto",
					left: "auto",
					right: "auto",
					bottom: "auto",
					overflow: "hidden",
					"padding-top":0,
					"padding-bottom":0,
					height: self.INITIAL_HEIGHT+"px",
					width: self.INITIAL_WIDTH+"px",
				},
				contentCSS: {
					width: '150px',
					margin: '0',
  					left: '50%',
  					top: '50%',
  					transform: 'translate(-50%,-50%)'
				},
				appendLocation: '#VIKI_'+ self.ID +'_overlay',
				afterOpen: function( $vexContent ) {
					$vexContent.append( loadingStyle );
					new Spinner( opts )
						.spin( document.getElementById( 'spinnerDiv' ) );
				},
				showCloseButton: false,
				overlayClosesOnClick: false,
				escapeButtonCloses: false
			} );
		};

		/**
		 * Initialize the context menu.
		 *
		 * This method is called internally by initialize().
		 */
		my.VikiJS.prototype.initializeContextMenu = function() {
			// Ensure the background graph div doesn't trigger context menu - only nodes and the covering rect.

			$( '#' + self.GraphDiv )
				.on( 'contextmenu', function() {
					return false;
				} );

			$( 'body' )
				.append(
					"<div class=\"contextMenu\" id=\"viki_menu-" + self.ID + "\"><ul>" +
					// the dynamic menu title (node name)
					"<li id=\"viki_name-" + self.ID + "\"  class=\"header\" style=\"text-align: center; font-weight: bold;\">Options</li>" +
					"<hr>" + // separator
					// actual navigable menu
					"<div class=\"options\" >" +
					"<li id=\"freeze\" class=\"viki_freeze-" + self.ID + "\">Freeze</li>" +
					"<li id=\"getinfo\" >Visit Page</li>" +
					"<li id=\"elaborate\" class=\"viki_elaborate-" + self.ID + "\">Elaborate</li>" +
					"<li id=\"categories\">Show Categories</li>" +
					"<hr>" + // separator
					"<li id=\"hide\">Hide Node</li>" +
					"<li id=\"hideHub\">Hide Hub</li>" +
					"<li id=\"hideByCategory\">Hide By Category</li>" +
					"<li id=\"hideIncoming\">Hide Incoming Links</li>" +
					"<li id=\"hideOutgoing\">Hide Outgoing Links</li>" +
					"<hr>" + // separator
					"<li id=\"showall\">Show All</li>" +
					"</ul></div></div>"
				);

			$( 'body' )
				.append(
					"<div class=\"contextMenu\" id=\"viki_backgroundMenu-" + self.ID + "\"><ul>" +
					"<li id=\"viki_backgroundMenu-" + self.ID + "\" class=\"header\" style=\"text-align: center; font-weight: bold;\">Options</li>" +
					"<hr>" +
					"<div class=\"options\">" +
					"<li id=\"showall\">Show All</li>" +
					"</ul></div></div>"
				);

			$( "#name" )
				.css( "text-align", "center" );
		};

		/**
		 * Initialize D3 graph.
		 *
		 * This method is called internally by initialize().
		 */
		my.VikiJS.prototype.initializeGraph = function() {
			function tick() {

				// Explicit detection for IE10 and IE11, which requires this patch to fix SVG markers.
				// See:
				// http://stackoverflow.com/questions/15588478/internet-explorer-10-not-showing-svg-path-d3-js-graph
				// http://stackoverflow.com/questions/17447373/how-can-i-target-only-internet-explorer-11-with-javascript
				if ( ( navigator.appVersion.indexOf( "MSIE 10" ) !== -1 ) ||
					( !!navigator.userAgent.match( /Trident.*rv[ :]*11\./ ) ) )
					self.LinkSelection.each( function() {
						this.parentNode.insertBefore( this, this );
					} );

				self.NodeSelection.attr( "transform", function( d ) {
					return "translate(" + d.x + "," + d.y + ")";
				} );

				// rather than return the (x,y) of the source and target node directly,
				// which would cause the links to stab through the node text,
				// we create an imaginary parabola around the node (a, b = node width, height)
				// and make the links connect to points on this parabola which would extend
				// the line into the center of the node, if possible.
				// (x,y) depend on (r, theta) and because this is an ellipse, r is a function of
				// a, b, and theta.

				self.LinkSelection.attr( "x1", function( d ) {

					var dy = d.target.y - d.source.y;
					var dx = d.target.x - d.source.x;
					var angle = Math.atan2( dy, dx );
					var width = d.source.nodeWidth;
					var height = d.source.nodeHeight;

					var a = width / 2;
					var b = height / 2;

					// value of r is from wikipedia article on ellipses: r as a function of theta, a, b.
					var r = a * b / Math.sqrt( ( b * b * Math.cos( angle ) * Math.cos( angle ) ) + ( a * a * Math.sin( angle ) * Math.sin( angle ) ) );

					return d.source.x + r * Math.cos( angle );
				} );

				self.LinkSelection.attr( "y1", function( d ) {

					var dy = d.target.y - d.source.y;
					var dx = d.target.x - d.source.x;
					var angle = Math.atan2( dy, dx );
					var width = d.source.nodeWidth;
					var height = d.source.nodeHeight;

					var a = width / 2;
					var b = height / 2;

					var r = a * b / Math.sqrt( ( b * b * Math.cos( angle ) * Math.cos( angle ) ) + ( a * a * Math.sin( angle ) * Math.sin( angle ) ) );

					return d.source.y + r * Math.sin( angle );
				} );

				self.LinkSelection.attr( "x2", function( d ) {

					var dy = d.target.y - d.source.y;
					var dx = d.target.x - d.source.x;
					var angle = Math.atan2( dy, dx );
					var width = d.target.nodeWidth;
					var height = d.target.nodeHeight;

					var a = width / 2;
					var b = height / 2;

					var r = a * b / Math.sqrt( ( b * b * Math.cos( Math.PI + angle ) * Math.cos( Math.PI + angle ) ) + ( a * a * Math.sin( Math.PI + angle ) * Math.sin( Math.PI + angle ) ) );

					return d.target.x + r * Math.cos( Math.PI + angle );
				} );

				self.LinkSelection.attr( "y2", function( d ) {

					var dy = d.target.y - d.source.y;
					var dx = d.target.x - d.source.x;
					var angle = Math.atan2( dy, dx );
					var width = d.target.nodeWidth;
					var height = d.target.nodeHeight;

					var a = width / 2;
					var b = height / 2;

					var r = a * b / Math.sqrt( ( b * b * Math.cos( Math.PI + angle ) * Math.cos( Math.PI + angle ) ) + ( a * a * Math.sin( Math.PI + angle ) * Math.sin( Math.PI + angle ) ) );
					return d.target.y + r * Math.sin( Math.PI + angle );
				} );
			}

			self.zoom = d3.behavior.zoom()
				.on( "zoom", self.redrawZoom )
				.scaleExtent( [ self.MIN_SCALE, self.MAX_SCALE ] );

			var svg = d3.select( "#" + self.GraphDiv )
				.append( "svg:svg" )
				.attr( "width", self.width )
				.attr( "height", self.height )
				.attr( "id", "viki_" + self.ID )
				.attr( "pointer-events", "all" )
				.append( "svg:g" )
				.call( self.zoom )
				.on( "dblclick.zoom", null );

			svg.append( "svg:rect" )
				.attr( "id", "viki_rect-" + self.ID )
				.attr( "width", self.width )
				.attr( "height", self.height )
				.attr( "fill", "white" );

			svg.append( "svg:g" )
				.attr( "id", "viki_moveable-" + self.ID );

			var defs = svg.append( "defs" );

			defs.append( "marker" )
				.attr( "id", "arrowHeadOutgoing" )
				.attr( "viewBox", "0 -8 20 20" )
				.attr( "refX", 16 )
				.attr( "refY", 0 )
				.attr( "markerWidth", 12 )
				.attr( "markerHeight", 12 )
				.attr( "markerUnits", "userSpaceOnUse" )
				.attr( "orient", "auto" )
				.attr( "fill", self.OUTGOING_LINK_COLOR )
				.attr( "stroke-width", "2" )
				.append( "path" )
				.attr( "d", "M0,-8L20,0L0,8" );

			defs.append( "marker" )
				.attr( "id", "arrowHeadIncoming" )
				.attr( "viewBox", "0 -8 20 20" )
				.attr( "refX", 16 )
				.attr( "refY", 0 )
				.attr( "markerWidth", 12 )
				.attr( "markerHeight", 12 )
				.attr( "markerUnits", "userSpaceOnUse" )
				.attr( "orient", "auto" )
				.attr( "fill", self.INCOMING_LINK_COLOR )
				.attr( "stroke-width", "2" )
				.append( "path" )
				.attr( "d", "M0,-8L20,0L0,8" );

			defs.append( "marker" )
				.attr( "id", "arrowHeadBidirectional" )
				.attr( "viewBox", "0 -8 20 20" )
				.attr( "refX", 16 )
				.attr( "refY", 0 )
				.attr( "markerWidth", 12 )
				.attr( "markerHeight", 12 )
				.attr( "markerUnits", "userSpaceOnUse" )
				.attr( "orient", "auto" )
				.attr( "fill", self.BIDIRECTIONAL_LINK_COLOR )
				.attr( "stroke-width", "2" )
				.append( "path" )
				.attr( "d", "M0,-8L20,0L0,8" );

			defs.append( "marker" )
				.attr( "id", "arrowHeadBlack" )
				.attr( "viewBox", "0 -8 20 20" )
				.attr( "refX", 16 )
				.attr( "refY", 0 )
				.attr( "markerWidth", 12 )
				.attr( "markerHeight", 12 )
				.attr( "markerUnits", "userSpaceOnUse" )
				.attr( "orient", "auto" )
				.attr( "fill", "black" )
				.attr( "stroke-width", "2" )
				.append( "path" )
				.attr( "d", "M0,-8L20,0L0,8" );

			defs.append( "marker" )
				.attr( "id", "backArrowHeadBidirectional" )
				.attr( "viewBox", "-20 -8 20 20" )
				.attr( "refX", -16 )
				.attr( "refY", 0 )
				.attr( "markerWidth", 12 )
				.attr( "markerHeight", 12 )
				.attr( "markerUnits", "userSpaceOnUse" )
				.attr( "orient", "auto" )
				.attr( "fill", self.BIDIRECTIONAL_LINK_COLOR )
				.attr( "stroke-width", "2" )
				.append( "path" )
				.attr( "d", "M0,-8L-20,0L0,8" );

			defs.append( "marker" )
				.attr( "id", "backArrowHeadBlack" )
				.attr( "viewBox", "-20 -8 20 20" )
				.attr( "refX", -16 )
				.attr( "refY", 0 )
				.attr( "markerWidth", 12 )
				.attr( "markerHeight", 12 )
				.attr( "markerUnits", "userSpaceOnUse" )
				.attr( "orient", "auto" )
				.attr( "fill", "black" )
				.attr( "stroke-width", "2" )
				.append( "path" )
				.attr( "d", "M0,-8L-20,0L0,8" );

			d3.select( "#viki_moveable-" + self.ID )
				.append( "svg:g" )
				.attr( "id", "viki_links-" + self.ID );
			d3.select( "#viki_moveable-" + self.ID )
				.append( "svg:g" )
				.attr( "id", "viki_nodes-" + self.ID );

			self.Force = d3.layout.force();
			self.Force.gravity( self.GRAVITY );
			self.Force.linkStrength( self.LINK_STRENGTH );
			// link distance was made dynamic in respect to the increase in charge. As the nodes form a cluster, the edges are less likely to cross.
			// The edge between to clusters is stretched from the polarity between the adjacent clusters.
			self.Force.linkDistance(
				function( n ) {
					// if the source and target has been elaborated, set the variable child to true
					var child = ( n.source.elaborated && n.target.elaborated );
					if ( child ) {
						return self.HUB_LINK_LENGTH;
					} // if this node is the parent or the center of a cluster of nodes
					else {
						return self.LEAF_LINK_LENGTH;
					} // if this node is the child or the outer edge of a cluster of nodes

				}
			);
			// Original value of charge was -3000. Increasing the charge maximizes polarity between nodes causing each node to repel.
			// This will decrease edge crossings for the nodes.
			self.Force.charge( -7500 );
			self.Force.friction( 0.675 );
			self.Force.size( [ self.width, self.height ] );
			self.Force.on( "tick", tick );

			self.LinkSelection =
				svg.select( "#viki_links-" + self.ID )
				.selectAll( ".viki_link-" + self.ID );

			self.NodeSelection =
				svg.select( "#viki_nodes-" + self.ID )
				.selectAll( ".viki_node-" + self.ID );

		};

		/**
		 * Fetch content namespaces for all wikis.
		 *
		 * This method is called internally at the end of VikiJS.initialize() to fetch content namespaces for all wikis in this.allWikis.
		 */
		my.VikiJS.prototype.fetchContentNamespaces = function() {
			var self = this;

			var actuallySearchableWikis = self.allWikis.filter( function( wiki ) {
				return wiki.searchableWiki;
			} );

			self.searchableCount = actuallySearchableWikis.length;

			if ( self.searchableCount === 0 ) {
				self.populateInitialGraph();
			} else
				for ( var i = 0; i < actuallySearchableWikis.length; i++ ) {
					self.getContentNamespacesForWikiAtIndex( actuallySearchableWikis, i );
				}
		};

		/**
		 * Get content namespaces for wiki at index for this.allWikis.
		 *
		 * This method is called internally by fetchContentNamespaces.
		 *
		 * @param {Array} actuallySearchableWikis Array of searchable wikis.
		 * @param {number} index index of wiki in actuallySearchableWikis.
		 */
		my.VikiJS.prototype.getContentNamespacesForWikiAtIndex = function( actuallySearchableWikis, index ) {
			var self = this;
			var wiki = actuallySearchableWikis[ index ];
			var wikiTitle = wiki.wikiTitle;

			var sameServer = wiki.contentURL.indexOf( self.serverURL ) > -1;
			if(document.location.protocol == 'https:' && wiki.contentURL.substring(0, wiki.contentURL.indexOf(':')) == 'http') {
				self.showError( mw.message( 'viki-error-https-namespace', wikiTitle ) );
				self.contentNamespacesFetched++;
				if ( self.contentNamespacesFetched === self.searchableCount ) {
					self.populateInitialGraph();
				}
				return;
			}
			jQuery.ajax( {
				url: wiki.apiURL,
				dataType: sameServer ? 'json' : 'jsonp',
				data: {
					action: 'getContentNamespaces',
					format: 'json',
					redirects: 'true'
				},
				timeout: 5000,
				success: function( data ) {
					if ( data.error && data.error.code && data.error.code === "unknown_action" ) {
						actuallySearchableWikis[ index ].contentNamespaces = [ 0 ];
					} else {
						actuallySearchableWikis[ index ].contentNamespaces = data.getContentNamespaces;
					}

					self.contentNamespacesFetched++;
					if ( self.contentNamespacesFetched === self.searchableCount ) {
						self.populateInitialGraph();
					}

				},
				error: function( jqXHR, textStatus, errorThrown ) {
					if ( errorThrown === 'timeout' ) {
						// do something about this error, but then increment contentNamespacesFetched so it can continue to work.
						// default to just NS 0 (main).
						self.showError( mw.message( 'viki-timeout-content-namespace', wikiTitle )
							.text() );
					} else {
						self.showError( mw.message( 'viki-error-content-namespace', wikiTitle )
							.text() );
					}
					actuallySearchableWikis[ index ].contentNamespaces = [ 0 ];
					self.contentNamespacesFetched++;
					if ( self.contentNamespacesFetched === self.searchableCount ) {
						self.populateInitialGraph();
					}
				}
			} );

		};

		/**
		 * Perform initial graph population.
		 *
		 * This method is called internally after all content namespaces are fetched.
		 * It adds all pages from the initial pageTitles array, and then elaborates these pages.
		 */
		my.VikiJS.prototype.populateInitialGraph = function() {
			var self = this;

			vex.close( self.loadingView.data().vex.id );

			var initialNodeCount = self.initialPageTitles.length;
			var warningMessage;
			if(initialNodeCount > 50) {
				warningMessage = "You are about to start a graph with "+initialNodeCount+" nodes and try to elaborate them all. \
				The graph is unlikely to successfully handle this number of nodes and may freeze or crash. This is not recommended. Are you sure you want to continue?";
			}
			else if(initialNodeCount > 10) {
				warningMessage = "You are about to start a graph with "+initialNodeCount+" nodes and try to elaborate them all.\
				This may take a very long time and result in a sluggish graph. Are you sure you want to continue?";
			}
			else {
				warningMessage = null;
			}
			if(warningMessage) {
				self.showWarningDialog( warningMessage, function() { startElaboratingNodes(); }, function() { });
			}
			else
				startElaboratingNodes();

			function startElaboratingNodes() {
				self.ongoingElaborations = self.initialPageTitles.length;

				var nodesElaborated = 0, totalElaborateCount = self.initialPageTitles.length, nodesToHide = [];
				self.showProgressOverlay( "Elaborating initial nodes... 0%", totalElaborateCount );
				for ( var i = 0; i < self.initialPageTitles.length; i++ ) {
					var node = self.createWikiNodeFromWiki( self.initialPageTitles[ i ], self.THIS_WIKI );
					self.addNode( node );
					self.visitNode( node, function(isHidden, thisNode) {
						if( isHidden ) {
							nodesToHide.push( thisNode );
						}
						self.elaborateWikiNode( thisNode, function( thisNode ) {
							nodesElaborated++;
							self.updateProgressOverlay( "Elaborating initial nodes... "+ +(nodesElaborated / totalElaborateCount * 100).toPrecision(4) + "%", nodesElaborated );
							if( nodesElaborated == totalElaborateCount ) {
								self.closeProgressOverlay();
								self.TempNodes.forEach(function(node) {
									self.addNode(node);
								});
								self.TempNodes = [];

								self.addTempLinksToGraph();

								nodesToHide.forEach( function(node) {
									self.hideNode( node, false );
								} );
								self.log("redraw() - from populateInitialGraph() after all elaborations complete");
								self.redraw( true );
							}
						}, self.ELABORATION_TYPE_INITIAL_POPULATION );
					} );
				}

				self.Force.nodes( self.Nodes );
				self.Force.links( self.Links );
				self.log("redraw() - from populateInitialGraph()");
				self.redraw(true);

				// after initial population, by default select the first node.
				self.SelectedNodeIndex = 0;
				self.displayNodeInfo( self.Nodes[ 0 ] );
				self.redrawNode( self.Nodes[ 0 ] );
			}
		};

		/*
		 * Graph Display and D3 Functions
		 */

		/**
		 * Redraw the graph.
		 *
		 * Call this method in response to any changes to the underlying data in the graph,
		 * for example new pages being added, or plugin functions which modify the page title.
		 *
		 * @param {boolean} restartGraph determine whether to reheat the D3 graph, causing the nodes to re-position.
		 */
		my.VikiJS.prototype.redraw = function( restartGraph ) {
			var self = this;

			self.NodeSelection =
				self.NodeSelection.data( self.Nodes, function( d ) {
					return d.identifier;
				} );
			self.LinkSelection =
				self.LinkSelection.data( self.Links );

			var newNodes = self.NodeSelection.enter()
				.append( "svg:g" );

			self.NodeSelection.exit()
				.remove();
			self.LinkSelection.exit()
				.remove();

			newNodes.attr( "class", "vikijs-node viki_node-" + this.ID );

			newNodes.on( "click", function( d ) {
				self.SelectedNodeIndex = d.index;
				self.displayNodeInfo( d );
				self.log("redraw() - from click");
				self.redraw( false );
			} );
			newNodes.on( "dblclick", function( d ) {
				d.fixed = !d.fixed;
			} );

			newNodes.on( "contextmenu", function( d ) {
				self.SelectedNodeIndex = d.index;
				self.log("redraw() - from contextmenu");
				self.redraw( false );
			} );

			self.prepareContextMenus();

			self.Force.drag()
				.on( "dragstart", function() {
					d3.event.sourceEvent.stopPropagation();
				} );

			newNodes.call( self.Force.drag );

			var newToolTips = newNodes.append( "svg:title" );
			newToolTips.attr( "class", "tooltip" );
			var allToolTips = d3.selectAll( ".tooltip" );
			allToolTips.text( function( d ) {
				return d.fullDisplayName;
			} );

			var newLabels = newNodes.append( "svg:text" );
			newLabels.text( function( d ) {
					return d.displayName;
				} )
				.attr( "text-anchor", "middle" )
				.attr( "dy", ".25em" ) // see bost.ocks.org/mike/d3/workshop/#114
				.attr( "dx", 1 * self.UNSELECTED_IMAGE_DIMENSION / 2 )
				.each( function() {
					var textbox = this.getBBox();

					var node = d3.select( this.parentNode )
						.datum();
					node.nodeWidth = textbox.width + self.UNSELECTED_IMAGE_DIMENSION + 10; // the 2 is a magic number to improve appearance
					node.nodeHeight = Math.max( textbox.height, self.UNSELECTED_IMAGE_DIMENSION ) + 5;
				} );

			var texts = self.NodeSelection.select( "text" );
			texts.text( function( d ) {
				return d.displayName;
			} );

			texts.each( function() {
				var textbox = this.getBBox();
				var node = d3.select( this.parentNode )
					.datum();
				node.nodeWidth = textbox.width + self.UNSELECTED_IMAGE_DIMENSION + 10;
				node.nodeHeight = Math.max( textbox.height, self.UNSELECTED_IMAGE_DIMENSION ) + 5;
			} );

			texts.attr( "font-weight", function( d ) {
				return d.index === self.SelectedNodeIndex ? "bold" : "normal";
			} );
			texts.attr( "fill", function( d ) {
				if ( d.nonexistentPage )
					return "red";
				else if ( !d.searchable )
					return "grey";
				else
					return "black";
			} );

			var newImages = newNodes.append( "svg:image" );
			newImages.attr( "class", "vikijs-icon icon" );

			var allImages = self.NodeSelection.selectAll( ".icon" );

			allImages.attr( "xlink:href", function( d ) {
				// go through the hierarchy of possible icons in order of preference
				// Hook Icons > Site Logo Icons > External Node Icons > info.png

				if ( d.hookIconURL )
					return d.hookIconURL;
				else if ( d.logoURL )
					return d.logoURL;
				else if ( d.externalNodeIconURL )
					return d.externalNodeIconURL;
				else
					return self.ImagePath + "info.png";
			} );
			allImages
				.attr( "x", function() {
					var textbox = d3.select( this.parentNode )
						.select( "text" )
						.node()
						.getBBox();
					var nodeWidth = textbox.width + self.UNSELECTED_IMAGE_DIMENSION;
					return -1 * nodeWidth / 2 - 2; // this -2 is a magic number
				} )
				.attr( "y", function() {
					return -1 * self.UNSELECTED_IMAGE_DIMENSION / 2;
				} )
				.attr( "width", self.UNSELECTED_IMAGE_DIMENSION )
				.attr( "height", self.UNSELECTED_IMAGE_DIMENSION );

			var newLinks = self.LinkSelection.enter()
				.append( "svg:line" );
			newLinks.attr( "class", "viki_link-" + this.ID );
			self.LinkSelection.style( "stroke-width", function( d ) {
				if ( typeof d.source.index !== 'undefined' ) {
					return d.source.index === self.SelectedNodeIndex ||
						d.target.index === self.SelectedNodeIndex ? 2 : 1;
				} else {
					return d.source === self.Nodes[ self.SelectedNodeIndex ] ||
						d.target === self.Nodes[ self.SelectedNodeIndex ] ? 2 : 1;
				}
			} );
			self.LinkSelection.style( "opacity", function( d ) {
				if ( typeof d.source.index !== 'undefined' ) {
					return d.source.index === self.SelectedNodeIndex ||
						d.target.index === self.SelectedNodeIndex ? 1 : self.LINK_OPACITY;
				} else {
					return d.source === self.Nodes[ self.SelectedNodeIndex ] ||
						d.target === self.Nodes[ self.SelectedNodeIndex ] ? 1 : self.LINK_OPACITY;
				}
			} );
			self.LinkSelection.style( "stroke", function( d ) {
				if ( typeof d.source.index !== 'undefined' ) {
					if ( d.source.index === self.SelectedNodeIndex )
						return d.bidirectional ? self.BIDIRECTIONAL_LINK_COLOR : self.OUTGOING_LINK_COLOR;
					else if ( d.target.index === self.SelectedNodeIndex )
						return d.bidirectional ? self.BIDIRECTIONAL_LINK_COLOR : self.INCOMING_LINK_COLOR;
					else return "black";
				} else {
					if ( d.source === self.Nodes[ self.SelectedNodeIndex ] )
						return d.bidirectional ? self.BIDIRECTIONAL_LINK_COLOR : self.OUTGOING_LINK_COLOR;
					else if ( d.target === self.Nodes[ self.SelectedNodeIndex ] )
						return d.bidirectional ? self.BIDIRECTIONAL_LINK_COLOR : self.INCOMING_LINK_COLOR;
					else return "black";
				}

			} );
			self.LinkSelection.attr( "marker-end", function( d ) {
				if ( typeof d.source.index !== 'undefined' ) {
					if ( d.source.index === self.SelectedNodeIndex )
						return d.bidirectional ? "url(#arrowHeadBidirectional)" : "url(#arrowHeadOutgoing)";
					else if ( d.target.index === self.SelectedNodeIndex )
						return d.bidirectional ? "url(#arrowHeadBidirectional)" : "url(#arrowHeadIncoming)";
					else return "url(#arrowHeadBlack)";
				} else {
					if ( d.source === self.Nodes[ self.SelectedNodeIndex ] )
						return d.bidirectional ? "url(#arrowHeadBidirectional)" : "url(#arrowHeadOutgoing)";
					else if ( d.target === self.Nodes[ self.SelectedNodeIndex ] )
						return d.bidirectional ? "url(#arrowHeadBidirectional)" : "url(#arrowHeadIncoming)";
					else return d.bidirectional ? "url(#arrowHeadBidirectional)" : "url(#arrowHeadBlack)";
				}
			} );

			self.LinkSelection.attr( "marker-start", function( d ) {
				if ( d.bidirectional ) {
					if ( typeof d.source.index !== 'undefined' ) {
						return d.source.index === self.SelectedNodeIndex || d.target.index === self.SelectedNodeIndex ? "url(#backArrowHeadBidirectional)" : "url(#backArrowHeadBlack)";
					} else {
						return d.source === self.Nodes[ self.SelectedNodeIndex ] || d.target === self.Nodes[ self.SelectedNodeIndex ] ? "url(#backArrowHeadBidirectional)" : "url(#backArrowHeadBlack)";
					}
				}
			} );

			if ( restartGraph ) {
				self.Force.start();
			}

		};

		/**
		 * Redraw an individual node.
		 *
		 * Call this method in response to changes to the underlying data of one single node in the graph,
		 * for example plugin functions which modify the page title. This method has better performance
		 * than redraw for large graphs.
		 *
		 * @param {Object} node the node to be redrawn.
		 */
		my.VikiJS.prototype.redrawNode = function( node ) {
			// nodeSelection is a selection of one, but it's easier to still behave as a selection.

			var nodeSelection = self.NodeSelection.filter( function( d ) {
				return d === node;
			} );

			var toolTip = nodeSelection.select( ".tooltip" );
			toolTip.text( node.fullDisplayName );

			var text = nodeSelection.select( "text" );
			text.text( node.displayName );

			text.each( function() {
				var textbox = this.getBBox();
				node.nodeWidth = textbox.width + self.UNSELECTED_IMAGE_DIMENSION + 10;
				node.nodeHeight = Math.max( textbox.height, self.UNSELECTED_IMAGE_DIMENSION ) + 5;
			} );

			text.attr( "font-weight", function( d ) {
				return d.index === self.SelectedNodeIndex ? "bold" : "normal";
			} );

			text.attr( "fill", function( d ) {
				if ( d.nonexistentPage )
					return "red";
				else if ( !d.searchable )
					return "grey";
				else
					return "black";
			} );

			var image = nodeSelection.select( ".icon" );
			image.attr( "xlink:href", function( d ) {
					// go through the hierarchy of possible icons in order of preference
					// Hook Icons > Site Logo Icons > External Node Icons > info.png

					if ( d.hookIconURL )
						return d.hookIconURL;
					else if ( d.logoURL )
						return d.logoURL;
					else if ( d.externalNodeIconURL )
						return d.externalNodeIconURL;
					else
						return self.ImagePath + "info.png";
				} )
				.attr( "x", function() {
					var textbox = d3.select( this.parentNode )
						.select( "text" )
						.node()
						.getBBox();
					var nodeWidth = textbox.width + self.UNSELECTED_IMAGE_DIMENSION;
					return -1 * nodeWidth / 2 - 2; // this -2 is a magic number
				} )
				.attr( "y", function() {
					return -1 * self.UNSELECTED_IMAGE_DIMENSION / 2;
				} )
				.attr( "width", self.UNSELECTED_IMAGE_DIMENSION )
				.attr( "height", self.UNSELECTED_IMAGE_DIMENSION );
		};

		/**
		 * Set up the context menu for nodes.
		 *
		 * This method is called in redraw() to prepare the context menu for newly added nodes.
		 */
		my.VikiJS.prototype.prepareContextMenus = function() {
			var self = this;

			$( '#viki_rect-' + self.ID )
				.contextMenu( 'viki_backgroundMenu-' + this.ID, {
					onShowMenu: function( e, menu ) {
						self.Force.stop();
						return menu;
					},
					onExitMenu: function() {
						self.Force.start();
					},
					itemStyle: {
						fontFamily: 'sans serif',
						fontSize: '13px',
						backgroundColor: '#FFFFFF'
					},
					menuStyle: {
						width: '80px'
					},
					bindings: {
						'showall': function() {
							self.showAllNodes();
						}
					}
				} );

			$( '.viki_node-' + self.ID )
				.contextMenu( 'viki_menu-' + this.ID, {
					// activate before the menu shows
					onShowMenu: function( e, menu ) {
						self.Force.stop();
						// find the node according to the index and set it locally
						var node = self.findNode( 'index', self.SelectedNodeIndex );
						if ( typeof node.fix === 'undefined' )
							node.fix = false;
						// create a json object to store the variable settings
						var freeze = {
							toggle: "",
							fix: false
						};

						// if the node has been fixed, then display "unfreeze" as a menu
						// option and if unfreeze is selected, unfreeze the node
						// note: the weird syntax here is due to some strange issue with
						// node.fixed taking on integer values instead of true/false
						// after they have been moused over at some point.

						freeze.fix = node.fix ? false : true;
						freeze.toggle = node.fix ? "Unfreeze" : "Freeze";

						// set the title of the menu to the name
						var safeDisplayName = node.displayName.replace(/&/g, '&amp;')
																 .replace(/</g, '&lt;')
																 .replace(/>/g, '&gt;')
																 .replace(/"/g, '&quot;')
																 .replace(/\'/g, '&#039;');
						$( '#viki_name-' + self.ID )
							.html( safeDisplayName );
						// toggle the menu option between freeze and unfreeze
						$( '.viki_freeze-' + self.ID )
							.html( node.fix ? 'Unfreeze' : 'Freeze' );
						// the actual menu code

						if ( node.elaborated || node.type === self.EXTERNAL_PAGE_TYPE || node.nonexistentPage || ( node.type === self.WIKI_PAGE_TYPE && !node.searchable ) ) {
							$( '.viki_elaborate-' + self.ID, menu )
								.remove();
						}
						if ( !node.elaborated ) {
							$( '#hideHub', menu )
								.remove();
							$( '#hideIncoming', menu )
								.remove();
							$( '#hideOutgoing', menu )
								.remove();
						}
						if ( node.nonexistentPage ) {
							$( '#getinfo', menu )
								.remove();
							$( '#categories', menu )
								.remove();
							$( '#hideByCategory', menu )
								.remove();
						}
						if ( node.type === self.EXTERNAL_PAGE_TYPE ) {
							$( '#categories', menu )
								.remove();
							$( '#hideByCategory', menu )
								.remove();
						}
						if ( node.hidingOutgoing ) {
							$( '#hideOutgoing', menu )
								.remove();
						}
						if ( node.hidingIncoming ) {
							$( '#hideIncoming', menu )
								.remove();
						}
						return menu;
					},
					// activate after the menu shows
					onExitMenu: function() {
						self.Force.start();
					},
					// style the menu
					itemStyle: {
						fontFamily: 'sans serif',
						fontSize: '13px',
						backgroundColor: '#FFFFFF'
					},
					bindings: {
						'freeze': function( t ) {
							var node = d3.select( t )
								.datum();

							if ( typeof node.fix === 'undefined' )
								node.fix = false;

							node.fixed = !node.fix;
							node.fix = !node.fix;
						},
						'getinfo': function( t ) {
							var node = d3.select( t )
								.datum();
							window.open( node.URL, "_blank" );
						},
						'elaborate': function() {
							self.elaborateNodeAtIndex( self.SelectedNodeIndex );
						},
						'categories': function( t ) {
							var node = d3.select( t )
								.datum();
							self.showCategoriesModal( node.categories, false );
						},
						'hideByCategory': function( t ) {
							var node = d3.select( t )
								.datum();
							self.showCategoriesModal( node.categories, true );
						},
						'hideIncoming': function( t ) {
							var node = d3.select( t )
								.datum();
							self.hideIncomingLinks( node );
						},
						'hideOutgoing': function( t ) {
							var node = d3.select( t )
								.datum();
							self.hideOutgoingLinks( node );
						},
						'hide': function( t ) {
							var node = d3.select( t )
								.datum();
							self.hideNodeAndRedraw( node );
						},
						'hideHub': function( t ) {
							var node = d3.select( t )
								.datum();
							self.hideHub( node );
						},
						'showall': function() {
							self.showAllNodes();
						}
					}
				} );

		};

		/**
		 * Slide event for the zoom slider.
		 *
		 * This method is called internally in response to a zoom slider event.
		 */
		my.VikiJS.prototype.slide = function() {

			// set targetZoom to the logged zoom position
			var targetZoom = this.Zoompos;
			// calculate the center of the graph by dividing the width and height by two
			var center = [ this.width / 2, this.height / 2 ];
			// set the scale extent
			var extent = this.zoom.scaleExtent();
			// and the translation vectors
			var translate = this.zoom.translate();
			var translation = [];
			var l = [];
			// setup a json object with the translation x and y values with the zoom scale
			var view = {
				x: translate[ 0 ],
				y: translate[ 1 ],
				k: this.zoom.scale()
			};

			if ( targetZoom < extent[ 0 ] || targetZoom > extent[ 1 ] ) {
				return false;
			}

			translation = [ ( center[ 0 ] - view.x ) / view.k, ( center[ 1 ] - view.y ) / view.k ];
			view.k = targetZoom;
			// generate the translation calculations by multiplying a transition value with the zoom value
			// and adding the appropriate view value
			l = [ translation[ 0 ] * view.k + view.x, translation[ 1 ] * view.k + view.y ];
			// set the view x and y values ( the pan x and pan y) equal to the center values
			// minus the transition calculations
			view.x += center[ 0 ] - l[ 0 ];
			view.y += center[ 1 ] - l[ 1 ];
			// now that the values have been calculated, call the controls and zoom
			this.interpolateZoom( [ view.x, view.y ], view.k );

		};

		/**
		 * Set the zoom setting in response to the zoom slider.
		 *
		 * This method is called internally from slide().
		 *
		 * @param {Array} translate the translation array
		 * @param {number} scale the zoom scale
		 */
		my.VikiJS.prototype.interpolateZoom = function( translate, scale ) {
			var self = this;
			// zoom with the set scale and translation values
			return d3.transition()
				.duration( 50 )
				.tween( "zoom", function() {
					var iTranslate = d3.interpolate( self.zoom.translate(), translate ),
						iScale = d3.interpolate( self.zoom.scale(), scale );
					return function( t ) {
						self.zoom
							.scale( iScale( t ) )
							.translate( iTranslate( t ) );
						self.zoomed();
					};
				} );
		};
		/**
		 * Redraw the graph in response to a slider event.
		 *
		 * This method is called internally from interpolateZoom() after a zoom event.
		 */
		my.VikiJS.prototype.zoomed = function() {
			var self = this;
			// access the element movable and move to the scale and translation vectors
			d3.select( "#viki_moveable-" + this.ID )
				.attr( "transform",
					"translate(" + self.zoom.translate() + ")" +
					"scale(" + self.zoom.scale() + ")"
				);
		};

		/**
		 * Redraw the graph in response to a mousewheel event.
		 *
		 * This method is called internally.
		 *
		 */
		my.VikiJS.prototype.redrawZoom = function() {
			self.Zoompos = d3.event.scale;
			d3.select( "#viki_moveable-" + self.ID )
				.attr( "transform", "translate(" + d3.event.translate + ")" + " scale(" + self.Zoompos + ")" );
			// if you scroll via a scrollwheel inside the graph, then set the slider to the current scale
			$( "#" + self.SliderDiv )
				.slider( "value", self.Zoompos );
		};

		/**
		 * Display the node title and any other relevant information.
		 *
		 * This method is called internally whenever a different node is selected.
		 *
		 * @param {Object} node The node whose information is to be displayed.
		 */
		my.VikiJS.prototype.displayNodeInfo = function( node ) {
			var self = this;

			if ( self.SelectedNodeIndex !== node.index ) {
				return;
			}

			var info = "<h4 id='vikijs-header'>";

			info += node.fullDisplayName.replace(/&/g, '&amp;')
											 .replace(/</g, '&lt;')
											 .replace(/>/g, '&gt;')
											 .replace(/"/g, '&quot;')
											 .replace(/\'/g, '&#039;');

			if ( node.nonexistentPage )
				info += " (Page Does Not Exist)";
			if ( node.type === self.WIKI_PAGE_TYPE && !node.searchable )
				info += " (Page Cannot Be Elaborated)";

			info += "</h4>";

			jQuery( "#" + self.SubDetailsDiv )
				.html( info );
		};

		/**
		 * Display "No Node Selected" in the detail section when no node is selected.
		 *
		 * This method is called internally whenever no node is selected.
		 */
		my.VikiJS.prototype.displayNoNodeSelected = function() {
			var self = this;

			var info = "<h4 id='vikijs-header'>(No Node Selected)</h4>";
			jQuery( "#" + self.SubDetailsDiv )
				.html( info );
		};

		my.VikiJS.prototype.visitNodeBatch = function( nodesToVisit ) {
			var thisContext = this;

			var nodesVisited = 0, nodesToHide = [], totalVisitCount = nodesToVisit.length;
			nodesToVisit.forEach(function(node) {
				self.visitNode(node, function(shouldHide) { 
					nodesVisited++; 
					if(shouldHide) {
						nodesToHide.push(node);
					}
					if(nodesVisited === totalVisitCount) {
						nodesToHide.forEach(function(node) {
							self.hideNode(node, false);
						});
						self.log("redraw() - from visitNodeBatch");
						self.redraw( true );
						self.OngoingElaborationNodes = [];
					}
				});
			});
		}
		/**
		 * Make an API call to get information about this node.
		 *
		 * This method is called internally whenever a new node is added to the graph,
		 * in order to verify its existence and collect other information about this page.
		 *
		 * @param {Object} intraNode The node being visited.
		 * @param {Object} callback Function to be called after the visit is complete.
		 * This function should accept two parameters - boolean for whether the node belongs
		 * to a currently-hidden category or not, and a reference to the node itself (for scope reasons)
		 */
		my.VikiJS.prototype.visitNode = function( intraNode, callback ) {
			var self = this;
			// note: beyond modularity, this is a separate function to preserve the scope of intraNode for the ajax call.
			self.callHooks( "BeforeVisitNodeHook", [ intraNode ] );

			if ( intraNode.visited ) {
				if( callback )
					callback( false, intraNode );
				return;
			}

			jQuery.ajax( {
				url: intraNode.apiURL,
				dataType: intraNode.sameServer ? 'json' : 'jsonp',
				data: {
					action: 'query',
					prop: 'categories',
					titles: intraNode.pageTitle,
					format: 'json',
					redirects: 'true'
				},
				success: function( data, textStatus, jqXHR ) {
					wikiPageCheckSuccessHandler( data, textStatus, jqXHR, intraNode, callback );
				},
				error: function() {
					self.showError( mw.message( 'viki-error-visit-node', intraNode.pageTitle )
						.text() );
					if( callback )
						callback( false, intraNode );
				}
			} );
			intraNode.visited = true;

			function wikiPageCheckSuccessHandler( data, textStatus, jqXHR, originNode ) {

				if ( data.query.pages[ "-1" ] ) {
					// check if the page is nonexistent
					originNode.nonexistentPage = true;
					self.redrawNode( originNode );
					if(callback)
						callback( false, originNode );
				} else {
					// if originNode doesn't already have a categories array, make one
					if ( !originNode.categories )
						originNode.categories = [];

					// get the categories
					var page = data.query.pages[ Object.keys( data.query.pages )[ 0 ] ];
					if ( page.categories ) {

						for ( var i = 0; i < page.categories.length; i++ ) {
							var categoryTitle = page.categories[ i ].title;
							// the category title is of the form "Category:Foo" so must remove the "Category:" part
							categoryTitle = categoryTitle.replace( "Category:", "" );
							originNode.categories.push( categoryTitle );

							if( !self.allCategories.hasOwnProperty( categoryTitle ) ) {
								self.allCategories[categoryTitle] = false;
							}
						}
					}

					if(callback)
						callback(self.nodeHasHiddenCategory(originNode), originNode);
				}

				self.callHooks( "AfterVisitNodeHook", [ originNode ] );
			}
		};

		/**
		 * Display categories for the given node modally.
		 *
		 * This method doubles as the UI for hiding nodes by category. It is called internally
		 * when the user chooses to either display categories for a node, or to hide nodes by
		 * a category.
		 *
		 * @param {Array} categories List of categories to display.
		 * @param {boolean} hideByCategory Whether to display checkboxes to allow nodes to be hidden.
		 */
		my.VikiJS.prototype.showCategoriesModal = function( categories, hideByCategory ) {
			var self = this;
			var categoriesHTML;
			if ( hideByCategory ) { // Hide By Category
				categoriesHTML = "\
				<div id='categoryDiv-'"+ self.ID +">\
					<fieldset>\
						<legend>Categories</legend>\
						<table id='categoryContainer'>\
							<tbody>\
					";

				for ( var i = 0; i < categories.length; i++ ) {
					categoriesHTML += "<tr><td><input type='checkbox' id='" + categories[ i ] + "-"+self.ID+ "' name='" + categories[ i ] + "' value=false><label for='" + categories[ i ] + "-"+self.ID+ "'>" + categories[ i ] + "</label></td></tr>";
				}

				categoriesHTML += "</tbody></table></fieldset></div>";

				vex.dialog.buttons.YES.text = 'OK';
				vex.dialog.buttons.NO.text = 'Cancel';
				vex.dialog.open( {
					message: "Select categories to hide:",
					input: categoriesHTML,
					css: {
						position: "absolute",
						top: "auto",
						left: "auto",
						right: "auto",
						bottom: "auto",
						overflow: "hidden",
						"padding-top": 0,
						"padding-bottom": 0,
						height: self.INITIAL_HEIGHT+"px",
						width: self.INITIAL_WIDTH+"px"
					},
					contentCSS: {
						"min-width": '250px',
						"width": "auto",
						"display": "table",
						margin: '0',
						left: '50%',
						top: '50%',
						transform: 'translate(-50%, -50%)'
					},
					appendLocation: '#VIKI_'+ self.ID +'_overlay',
					afterOpen: function() {
						self.Force.stop();
						$( ".categoryCheckbox" )
							.each( function() {
								var checkbox = $( this );
								checkbox.click( function() {
									var value = checkbox.prop( 'checked' );
									checkbox.prop( 'value', value );
								} );
							} );
					},
					callback: function( data ) {
						if ( data ) {
							self.hideByCategories( Object.keys( data ) );
						}
					},
					showCloseButton: true
				} );

			} else { // Show Categories
				categoriesHTML = "\
				<div id='categoryDiv-'"+ self.ID +">\
					<fieldset>\
						<legend>Categories</legend>\
						<ul id='categoryContainer'>\
					";

				if ( categories.length === 0 )
					categories.push( "No categories" );

				for ( var j = 0; j < categories.length; j++ ) {
					categoriesHTML += "<li>" + categories[ j ] + "</li>";
				}

				categoriesHTML += "</ul>";

				vex.open( {
					content: categoriesHTML,
					css: {
						position: "absolute",
						top : "auto",
						left : "auto",
						right : "auto",
						bottom : "auto",
						overflow : "hidden",
						"padding-top":0,
						"padding-bottom":0,
						height : self.INITIAL_HEIGHT+"px",
						width : self.INITIAL_WIDTH+"px"
					},
					appendLocation: '#VIKI_'+ self.ID +'_overlay',
					contentCSS: {
						"min-width" : '150px',
						width : "auto",
						display : "table",
						margin : '0',
						left : '50%',
						top : '50%',
						transform: 'translate(-50%, -50%)'
					},
					afterOpen: function() {
						self.Force.stop();
					}
				} );
			}
		};

		/*
		 * Node Management Methods
		 */

		/**
		 * Creates a new external page node to be added to the graph.
		 *
		 * This method is called internally when new external page information is returned.
		 *
		 * @param {string} url URL of the external page to be added to the graph.
		 *
		 * @return {Object} newly created node
		 */
		my.VikiJS.prototype.createExternalNode = function( url ) {
			var self = this;

			var node = self.newNode();
			var shortURL = url.replace( "http://", "" )
				.replace( "https://", "" )
				.replace( "www.", "" );
			node.displayName = ( shortURL.length < 20 ? shortURL : shortURL.substring( 0, 20 ) + "..." );
			node.fullDisplayName = url;
			node.type = self.EXTERNAL_PAGE_TYPE;
			node.URL = url;
			node.externalNodeIconURL = self.ImagePath + "internet.png";

			return node;
		};

		/**
		 * Create a new wiki node to be added to the graph.
		 *
		 * This method is called internally when an internal link to a wiki page is discovered
		 * in the node elaboration process.
		 * It calls createWikiNode to complete the wiki node creation.
		 *
		 * @param {string} pageTitle title of the internal wiki page
		 * @param {string} wikiTitle title of the wiki this page belongs to
		 *
		 * @return {Object} newly created node
		 */
		my.VikiJS.prototype.createWikiNodeFromWiki = function( pageTitle, wikiTitle ) {
			var self = this;

			var index = self.searchableWikiIndexForName( wikiTitle );
			var wiki = self.allWikis[ index ];
			var url = wiki.contentURL.substring( 0, wiki.contentURL.indexOf( "$1" ) ) + ( pageTitle.split( " " )
				.join( "_" ) );
			return self.createWikiNode( pageTitle, url, wiki, index );
		};

		/**
		 * Create a new wiki node from an external URL to a wiki page.
		 *
		 * This method is called internally when an external link to a wiki page is discovered
		 * in the node elaboration process.
		 * It calls createWikiNode to complete the wiki node creation.
		 *
		 * @param {string} url external URL of the wiki page.
		 * @param {number} wikiIndex index of the wiki this page belongs to.
		 *
		 * @return {Object} newly created node
		 */
		my.VikiJS.prototype.createWikiNodeFromExternalLink = function( url, wikiIndex ) {
			var self = this;
			var strippedContentURL = self.allWikis[ wikiIndex ].contentURL.substring( 0, self.allWikis[ wikiIndex ].contentURL.indexOf( "$1" ) );
			var pageTitle = url.replace( strippedContentURL, "" )
				.split( "_" )
				.join( " " );
			var wiki = self.allWikis[ wikiIndex ];

			return self.createWikiNode( pageTitle, url, wiki, wikiIndex );
		};

		/**
		 * Create a new wiki node to add to the graph.
		 *
		 * This method is called internally from createWikiNodeFromWiki or createWikiNodeFromExternalLink
		 * to complete the wiki node creation.
		 *
		 * @param {string} pageTitle title of the wiki page
		 * @param {string} url URL of the wiki page
		 * @param {Object} wiki Object representing the wiki this page belongs to
		 * @param {number} index index of this.allWikis that this wiki corresponds to
		 *
		 * @return {Object} newly created node
		 */

		my.VikiJS.prototype.createWikiNode = function( pageTitle, url, wiki, index ) {
			var node = self.newNode();
			node.pageTitle = pageTitle;
			node.displayName = pageTitle.length < 50 ? pageTitle : pageTitle.substring( 0, 50 ) + "...";
			node.fullDisplayName = pageTitle;
			node.type = self.WIKI_PAGE_TYPE;
			node.URL = url;
			node.wikiIndex = index;
			node.apiURL = wiki.apiURL;
			node.contentURL = wiki.contentURL;
			node.logoURL = wiki.logoURL;
			node.searchable = wiki.searchableWiki;
			node.sameServer = node.contentURL.indexOf( self.serverURL ) > -1; // if the node's content URL contains my server, it should have the same server
			node.wikiTitle = wiki.wikiTitle;

			return node;
		};

		/**
		 * Create a new node.
		 *
		 * @return {Object} newly created node
		 */

		my.VikiJS.prototype.newNode = function() {

			var node = {
				elaborated: false,
				fixed: false,
				hidden: false,
				visited: false,
				explicitlyHidden: false,
				hidingHub: false,
				hidingIncoming: false,
				hidingOutgoing: false
			};
			return node;
		};

		/**
		 * Find this node in the active VIKI graph, if it exists.
		 *
		 * @param {string} property property type to search for
		 * @param {string} value value of the property to search for
		 *
		 * @return {Object} node, if it exists, else null
		 */

		my.VikiJS.prototype.findNode = function( property, value ) {
			var self = this;

			var ret = findInList( self.Nodes );
			if(!ret)
				ret = findInList( self.HiddenNodes );
			if(!ret)
				ret = findInList( self.TempNodes );
			return ret;

			function findInList( list ) {
				var oldString, newString, newValue;

				for ( var i = 0; i < list.length; i++ ) {
					if ( property === 'pageTitle' ) {
						// a specific check for page titles - the first letter is case insensitive
						oldString = list[ i ][ property ];
						if ( oldString ) {
							newString = self.replaceAt( oldString, oldString.indexOf( ":" ) + 1,
								oldString.charAt( oldString.indexOf( ":" ) + 1 )
								.toLowerCase() );
							newValue = self.replaceAt( value, value.indexOf( ":" ) + 1,
								value.charAt( value.indexOf( ":" ) + 1 )
								.toLowerCase() );
							if ( newString === newValue )
								return list[ i ];
						}
					} else if ( typeof list[ i ][ property ] !== 'undefined' && list[ i ][ property ] === value ) {
						return list[ i ];
					}
				}
				return null;
			}
		};

		/**
		 * Add a new node to the graph.
		 *
		 * This method is called internally when a newly created node is added to the VIKI graph.
		 *
		 * @param {Object} node to be added to the graph.
		 */
		my.VikiJS.prototype.addNode = function( node ) {
			var self = this;
			node.identifier = self.CURRENT_IDENTIFIER;
			self.CURRENT_IDENTIFIER++;
			self.Nodes.push( node );
			if ( self.Nodes.length === 1 ) {
				self.SelectedNodeIndex = 0;
			}

			if ( node.type === self.WIKI_PAGE_TYPE )
				self.callHooks( "NewWikiNodeAddedHook", [ node ] );
			else
				self.callHooks( "NewExternalNodeAddedHook", [ node ] );
		};

		/**
		 * Add a new link to the graph.
		 *
		 * This helper method is called internally when a new node has been added to the graph,
		 * and it must be linked to other nodes.
		 *
		 * @param {Object} node1 first node in the link
		 * @param {Object} node2 second node in the link
		 * @param {Boolean} hidden whether this link should be hidden (not immediately added to graph)
		 */
		my.VikiJS.prototype.addLink = function( node1, node2, hidden ) {
			var self = this;

			var link = {
				source: node1,
				target: node2,
				bidirectional: false
			};
			if(hidden) {
				self.HiddenLinks.push( link );
			}
			else {
				self.Links.push( link );
				self.LinkMap[ node1.identifier + "," + node2.identifier ] = link;
				self.LinkMap[ node2.identifier + "," + node1.identifier ] = link;
			}
			return link;
		};

		my.VikiJS.prototype.addTempLink = function( node1, node2 ) {
			var self = this;
			var link = {
				source: node1,
				target: node2,
				bidirectional: false
			};
			self.TempLinks.push( link );
			return link;
		}

		my.VikiJS.prototype.addSecondOrderLink = function( node1, node2, hidden) {
			var self = this;

			var link = {
				source: node1,
				target: node2,
				bidirectional: false
			};
			if(hidden) {
				self.TempHiddenLinks.push( link );
			}
			else {
				self.TempLinks.push( link );
			}
			return link;
		}

		my.VikiJS.prototype.addTempLinksToGraph = function() {
			for( var i = 0; i < self.TempLinks.length; i++ ) {
				var link = self.TempLinks[ i ];
				var alreadyFound = self.findLink( link.source.identifier, link.target.identifier );
				if( alreadyFound ) {
					// If this link was found, check if it's a duplicate (source and targets are same in both) or bidirectional (source and targets are reversed)
					if(link.source.identifier === alreadyFound.target.identifier && link.source.identifier === alreadyFound.target.identifier)
						alreadyFound.bidirectional = true;
					continue;
				}
				self.Links.push( link );
				self.LinkMap[ link.source.identifier + "," + link.target.identifier ] = link;
				self.LinkMap[ link.target.identifier + "," + link.source.identifier ] = link;
			}
			self.TempLinks = [];
		}

		my.VikiJS.prototype.addHiddenLinksToGraph = function() {
			for ( var i = 0; i < self.HiddenLinks.length; i++ ) {
				var link = self.HiddenLinks[ i ];
				var alreadyFound = self.findLink( link.source.identifier, link.target.identifier );
				if( alreadyFound ) {
					// If this link was found, check if it's a duplicate (source and targets are same in both) or bidirectional (source and targets are reversed)
					if(link.source.identifier === alreadyFound.target.identifier && link.source.identifier === alreadyFound.target.identifier)
						alreadyFound.bidirectional = true;
					continue;
				}
				self.Links.push( link );
				self.LinkMap[ link.source.identifier + "," + link.target.identifier ] = link;
				self.LinkMap[ link.target.identifier + "," + link.source.identifier ] = link;
			}

			self.HiddenLinks = [];
		}

		/**
		 * Find link in the active VIKI graph, if it exists.
		 *
		 * @param {number} from identifier of the first node in the link
		 * @param {string} to identifier of the second node in the link
		 *
		 * @return {Object} link if it exists, else null
		 */
		my.VikiJS.prototype.findLink = function( from, to ) {
			var self = this;
			var link = self.LinkMap[ from + "," + to ];
			if ( typeof link === 'undefined' ) {
				return null;
			}
			return link;
		};

		/**
		 * Find link in the list of hidden links, if it exists.
		 *
		 * @param {number} from identifier of the first node in the link
		 * @param {string} to identifier of the second node in the link
		 *
		 * @return {Object} link if it exists, else null
		 */
		my.VikiJS.prototype.findHiddenLink = function( from, to ) {
			var self = this;
			for( var i = 0; i < self.HiddenLinks.length; i++ ) {
				hiddenSourceIdentifier = self.HiddenLinks[i].source.identifier;
				hiddenTargetIdentifier = self.HiddenLinks[i].target.identifier;
				if( self.HiddenLinks[ i ].source.identifier === from.identifier && self.HiddenLinks[ i ].target.identifier === to.identifier )
					return self.HiddenLinks[ i ];
			}
			return null;
		}

		/*
		 * Graph Modification Methods
		 */

		/**
		 * Create a new wiki node from an external URL to a wiki page.
		 *
		 * This method is called internally when an external link to a wiki page is discovered
		 * in the node elaboration process.
		 * It calls createWikiNode to complete the wiki node creation.
		 *
		 * @param {string} url external URL of the wiki page.
		 * @param {number} wikiIndex index of the wiki this page belongs to.
		 */
		my.VikiJS.prototype.elaborateNodeAtIndex = function( index ) {
			var self = this;
			var node = self.Nodes[ index ];
			if ( node.type === self.WIKI_PAGE_TYPE ) {
				self.ongoingElaborations++;
				self.elaborateWikiNode( node, null, self.ELABORATION_TYPE_NORMAL );
			}
		};

		/**
		 * Elaborate this wiki node.
		 *
		 * Node elaboration involves making MediaWiki API queries to find all
		 * external links out of this page, as well as all intra-wiki links
		 * into and out of this page. Each found node is then added to the graph. This method
		 * is called internally whenever the user chooses to elaborate a node.
		 *
		 * @param {Object} node node to elaborate
		 * @param {Object} completionHandler code block to execute at the end of elaboration
		 * @param {Integer} elaborationType type of elaboration: normal, 2nd order links, initial population
		 */
		my.VikiJS.prototype.elaborateWikiNode = function( node, completionHandler, elaborationType ) {
			var self = this;

			if(document.location.protocol == 'https:' && node.apiURL.substring(0, node.apiURL.indexOf(':')) == 'http') {
				abandonElaboration();
				return;
			}

			// 1. Get external links OUT from page.

			var externalElaborationData, intraOutElaborationData, intraInElaborationData;
			jQuery.ajax( {
				url: node.apiURL,
				dataType: node.sameServer ? 'json' : 'jsonp',
				data: {
					action: 'query',
					prop: 'extlinks',
					titles: node.pageTitle,
					ellimit: 'max',
					format: 'json',
					redirects: 'true'
				},
				success: function( data, textStatus, jqXHR ) {
					externalElaborationData = data;
					checkElaborationQueriesComplete( node, completionHandler, elaborationType );
				},
				error: function() {
					self.showError( mw.message( 'viki-error-external-links', node.pageTitle )
						.text() );
					externalElaborationData = {};
					checkElaborationQueriesComplete( node, completionHandler, elaborationType );
				}
			} );

			// 2. Get intra-wiki links OUT from page.
			jQuery.ajax( {
				url: node.apiURL,
				dataType: node.sameServer ? 'json' : 'jsonp',
				data: {
					action: 'query',
					prop: 'links',
					titles: node.pageTitle,
					pllimit: 'max',
					format: 'json',
					redirects: 'true'
				},
				success: function( data, textStatus, jqXHR ) {
					intraOutElaborationData = data;
					checkElaborationQueriesComplete( node, completionHandler, elaborationType );
				},
				error: function() {
					self.showError( mw.message( 'viki-error-intrawiki-out', node.pageTitle )
						.text() );
					intraOutElaborationData = {};
					checkElaborationQueriesComplete( node, completionHandler, elaborationType );
				}
			} );
			// 3. Get intra-wiki links IN to this page.
			jQuery.ajax( {
				url: node.apiURL,
				dataType: node.sameServer ? 'json' : 'jsonp',
				data: {
					action: 'query',
					list: 'backlinks',
					bltitle: node.pageTitle,
					bllimit: 'max',
					format: 'json',
					redirects: 'true'
				},
				success: function( data, textStatus, jqXHR ) {
					intraInElaborationData = data;
					checkElaborationQueriesComplete( node, completionHandler, elaborationType );
				},
				error: function() {
					self.showError( mw.message( 'viki-error-intrawiki-in', node.pageTitle )
						.text() );
					intraInElaborationData = {};
					checkElaborationQueriesComplete( node, completionHandler, elaborationType );
				}
			} );
			if( elaborationType !== self.ELABORATION_TYPE_SECOND_ORDER_LINKS ) {
				node.elaborated = true;
				self.displayNodeInfo( node );
			}

			function checkElaborationQueriesComplete( originNode, completionHandler, elaborationType ) {
				if(externalElaborationData !== undefined && intraOutElaborationData !== undefined && intraInElaborationData !== undefined) {
					var externalLinksList = externalElaborationData.query.pages[ Object.keys( externalElaborationData.query.pages )[ 0 ] ].extlinks;
					var intraLinksOutList = intraOutElaborationData.query.pages[ Object.keys( intraOutElaborationData.query.pages )[ 0 ] ].links;
					var intraLinksInList = intraInElaborationData.query.backlinks;

					var externalLinksCount = externalLinksList ? externalLinksList.length : 0;
					var intraLinksOutCount = intraLinksOutList ? intraLinksOutList.length : 0;
					var intraLinksInCount = intraLinksInList ? intraLinksInList.length : 0;

					var sum = externalLinksCount + intraLinksOutCount + intraLinksInCount;

					if(sum > 50 && elaborationType === self.ELABORATION_TYPE_NORMAL) {
						self.showWarningDialog("About to add "+sum+" nodes... this may take a very long time and result in a sluggish graph. Are you sure you want to?", 
							function() { 
								completeElaboration(); 
							}, 
							function() { 
								node.elaborated = false;
								self.ongoingElaborations--;
							} 
						);
					}
					else {
						completeElaboration();
					}
				}

				function completeElaboration( ) {
					var newExternalNodes = self.externalLinksSuccessHandler( self, externalElaborationData, node, elaborationType );
					var newIntraOutNodes = self.intraWikiOutSuccessHandler( self, intraOutElaborationData, node, elaborationType );
					var newIntraInNodes = self.intraWikiInSuccessHandler( self, intraInElaborationData, node, elaborationType );

					if( elaborationType === self.ELABORATION_TYPE_NORMAL ) {
						self.log("redraw() - from completeElaboration()");
						self.redraw( true );
					}
					
					if( elaborationType !== self.ELABORATION_TYPE_SECOND_ORDER_LINKS ) {
						self.OngoingElaborationNodes = self.OngoingElaborationNodes.concat(newExternalNodes);
						self.OngoingElaborationNodes = self.OngoingElaborationNodes.concat(newIntraOutNodes);
						self.OngoingElaborationNodes = self.OngoingElaborationNodes.concat(newIntraInNodes);
					}

					if( completionHandler ) {
						completionHandler( originNode );
					}

					if( elaborationType !== self.ELABORATION_TYPE_SECOND_ORDER_LINKS ) {
						if( self.showSecondOrderLinks ) {
							self.ongoingElaborations--;
							if( self.ongoingElaborations == 0 ) {
								// call findSecondOrderLinks() after a brief delay to avoid animation hiccups
								setTimeout(findSecondOrderLinks, 400);
							}
							self.callHooks( "NodeElaborationCompleteHook", [ originNode ] );
						}
						else {
							self.ongoingElaborations--;
							self.log("self.ongoingElaborations = " + self.ongoingElaborations);
							if(self.ongoingElaborations == 0) {
								self.visitNodeBatch(self.OngoingElaborationNodes.filter( function( node ) { return node.apiURL !== null && node.apiURL !== undefined; } ) );
							}
						}
					}
				}
			}

			function findSecondOrderLinks( ) {
				// We must check all of the newly-added (unvisited) nodes (self.OngoingElaborationNodes), 
				// as well as all "external wiki" nodes currently in the graph (e.g. wiki nodes from another wiki).
				// The other nodes in the graph from "this wiki" we already know about, as their links to 
				// newly-added nodes would already be accounted for with the intra-in and intra-out APIs.
				var nodesToCheck = self.OngoingElaborationNodes.filter( function( node ) { 
					return !node.visited; 
				} ).concat( 
					self.Nodes.filter( function( node ) { 
						return node.type === self.WIKI_PAGE_TYPE && node.wikiTitle !== self.THIS_WIKI; 
					} )
				);

				var nodesChecked = 0, totalCheckCount = nodesToCheck.length;
				if(totalCheckCount <= 0) {
					return;
				}
				self.showProgressOverlay("Finding 2nd order links... 0%", nodesToCheck.length );
					
				for( var i = 0; i < nodesToCheck.length; i++ ) {
					var node = nodesToCheck[i];
					if( node.type === self.WIKI_PAGE_TYPE && node.searchable ) {
						self.elaborateWikiNode( node, function( node ) { 
							nodesChecked++;
							self.updateProgressOverlay( "Finding 2nd order links..."+ +(nodesChecked / totalCheckCount * 100).toPrecision(4) + "%", nodesChecked);
							if(nodesChecked < totalCheckCount) {
								return;
							}

							self.closeProgressOverlay( );

							self.addTempLinksToGraph();

							for(var i = 0; i < self.TempHiddenLinks.length; i++) {
								var link = self.TempHiddenLinks[ i ];
								if( !self.findHiddenLink( link.source, link.target ) ) {
									self.HiddenLinks.push( link );
								}
							}
							self.TempHiddenLinks = [];
							self.log("redraw() - from findSecondOrderLinks");
							self.redraw( true );
						
							self.visitNodeBatch(self.OngoingElaborationNodes.filter( function( node ) { return node.apiURL !== null && node.apiURL !== undefined; } ) );
						}, self.ELABORATION_TYPE_SECOND_ORDER_LINKS );
					}
					else {
						nodesChecked++;
						self.updateProgressOverlay( "Finding 2nd order links..."+ +(nodesChecked / totalCheckCount * 100).toPrecision(4) + "%", nodesChecked);
						if( nodesChecked === totalCheckCount )
							self.closeProgressOverlay( );
					}
				}
			}

			function abandonElaboration() {
				self.showError( mw.message( 'viki-error-https-node', originNode.pageTitle ) );
				if( completionHandler ) {
					completionHandler( originNode );
				}

				if( elaborationType !== self.ELABORATION_TYPE_SECOND_ORDER_LINKS ) {
					if( self.showSecondOrderLinks ) {
						self.ongoingElaborations--;
						if( self.ongoingElaborations == 0 ) {
							// call findSecondOrderLinks() after a brief delay to avoid animation hiccups
							setTimeout(findSecondOrderLinks, 400);
						}
						self.callHooks( "NodeElaborationCompleteHook", [ originNode ] );
					}
					else {
						self.ongoingElaborations--;
						self.log("self.ongoingElaborations = " + self.ongoingElaborations);
						if(self.ongoingElaborations == 0) {
							self.visitNodeBatch(self.OngoingElaborationNodes.filter( function( node ) { return node.apiURL !== null && node.apiURL !== undefined; } ) );
						}
					}
				}
			}
		};

	my.VikiJS.prototype.externalLinksSuccessHandler = function( context, data, originNode, elaborationType ) {

			if ( data.error ) {
				context.showError( mw.message( 'viki-error-external-links', node.pageTitle )
					.text() );
				return;
			}

			var externalLinks = data.query.pages[ Object.keys( data.query.pages )[ 0 ] ].extlinks;
			var newExternalNodes = [];
			if ( externalLinks ) {
				// some of these external links are actually links to other searchable wikis.
				// these should be recognized as wiki nodes, not just external nodes.

				for ( var i = 0; i < externalLinks.length; i++ ) {
					var thisURL = externalLinks[ i ][ "*" ];

					// index of the searchable wiki in list of searchable wikis, or -1 if this is not a searchable wiki page.
					var index = context.indexOfWikiForURL( externalLinks[ i ][ "*" ] );
					// handle the case where the URL has the form "index.php?title=..." rather than "index.php/..."
					var alternativeTitleFormatIndex = context.indexOfWikiForURL( thisURL.replace( "?title=", "/" ) );

					var isWikiPage = ( index !== -1 || alternativeTitleFormatIndex !== -1 );
					var link;
					var externalNode;
					if ( isWikiPage ) {
						// if "index.php?title=..." form was used, swap it with "index.php/..." form.
						if ( alternativeTitleFormatIndex !== -1 ) {
							thisURL = thisURL.replace( "?title=", "/" );
							index = alternativeTitleFormatIndex;
						}

						var externalWikiNode = context.findNode( "URL", thisURL );
						if ( !externalWikiNode ) {
							if( elaborationType === self.ELABORATION_TYPE_SECOND_ORDER_LINKS )
								continue;

							externalWikiNode = context.createWikiNodeFromExternalLink( thisURL, index );
							context.callHooks( "NewWikiNodeCreatedHook", [ externalWikiNode, originNode ] );
							if ( externalWikiNode.unadded )
								continue;
							else {
								if(elaborationType === self.ELABORATION_TYPE_INITIAL_POPULATION)
									context.TempNodes.push( externalWikiNode );
								else
									context.addNode( externalWikiNode );
							}
						}
						if(elaborationType === self.ELABORATION_TYPE_SECOND_ORDER_LINKS) {
							link = context.findLink( originNode.identifier, externalWikiNode.identifier );
							if ( !link ) {
								// If the link doesn't exist, add it - hidden, if necessary.
								link = context.addSecondOrderLink( originNode, externalWikiNode, externalWikiNode.hidden );
							}
							else {
								// if the found link has this originNode as the SOURCE, this is an already known link OUT; disregard.
								// if the found link has this originNode as the TARGET, this is a NEW link out; set as bidirectional.
								if ( !link.bidirectional && link.target.identifier === originNode.identifier )
									link.bidirectional = true;
							}
						}
						else {
							var hasHiddenCategory = context.nodeHasHiddenCategory( externalWikiNode );
							if ( externalWikiNode.hidden ) {
								if( !hasHiddenCategory ) {
									context.unhideNode( externalWikiNode.identifier );
								}
							}
							link = context.findLink( originNode.identifier, externalWikiNode.identifier );
							if ( !link ) {
								if(elaborationType === self.ELABORATION_TYPE_INITIAL_POPULATION)
									link = context.addTempLink( originNode, externalWikiNode );
								else
									link = context.addLink( originNode, externalWikiNode, hasHiddenCategory );
							}
								
							else {
								link.bidirectional = true;
							}
						}
						externalNode = externalWikiNode;
					} else {
						externalNode = context.findNode( "URL", thisURL );
						if ( !externalNode ) {
							if( elaborationType === self.ELABORATION_TYPE_SECOND_ORDER_LINKS )
								continue;

							externalNode = context.createExternalNode( thisURL );
							context.callHooks( "NewExternalNodeCreatedHook", [ externalNode, originNode ] );
							if ( externalNode.unadded )
								continue;
							else {
								if(elaborationType === self.ELABORATION_TYPE_INITIAL_POPULATION)
									context.TempNodes.push( externalNode );
								else
									context.addNode( externalNode );
							}
						}
						if(elaborationType === self.ELABORATION_TYPE_SECOND_ORDER_LINKS) {
							link = context.findLink( originNode.identifier, externalNode.identifier );
							if ( !link ) {
								link = context.addSecondOrderLink( originNode, externalNode, externalNode.hidden );
							}
						}
						else {
							var hasHiddenCategory = context.nodeHasHiddenCategory( externalNode );
							if ( externalNode.hidden ) {
								if( !hasHiddenCategory ) {
									context.unhideNode( externalNode.identifier );
								}
							}
							link = context.findLink( originNode.identifier, externalNode.identifier );
							if ( !link ) {
								if(elaborationType === self.ELABORATION_TYPE_INITIAL_POPULATION)
									link = context.addTempLink( originNode, externalNode );
								else
									link = context.addLink( originNode, externalNode, hasHiddenCategory );
							}
							else {
								link.bidirectional = true;
							}
						}
					}
					if ( externalNode )
						newExternalNodes.push( externalNode );
				}

				if( elaborationType !== self.ELABORATION_TYPE_SECOND_ORDER_LINKS )
					context.callHooks( "ExternalNodeHook", [ newExternalNodes.filter(function(node) { return node.type === self.EXTERNAL_PAGE_TYPE; } ) ] );
			}
			return newExternalNodes;
		}

		my.VikiJS.prototype.intraWikiOutSuccessHandler = function( context, data, originNode, elaborationType ) {
			var thisContext = this;

			if ( data.error ) {
				context.showError( mw.message( 'viki-error-intrawiki-out', node.pageTitle )
					.text() );
				return;
			}
			var intraLinks = data.query.pages[ Object.keys( data.query.pages )[ 0 ] ].links;
			var newIntraOutNodes = [];
			if ( intraLinks ) {
				// get list of namespaces, or fetch with AJAX if required.

				var wiki = context.allWikis[ originNode.wikiIndex ];

				var contentNamespaces = wiki.contentNamespaces;
				var intraNode;
				for ( var i = 0; i < intraLinks.length; i++ ) {
					intraNode = context.findNode( "pageTitle", intraLinks[ i ].title );
					if ( !intraNode || ( intraNode.apiURL !== originNode.apiURL ) ) {
						if( elaborationType === self.ELABORATION_TYPE_SECOND_ORDER_LINKS )
							continue;

						// add the node to the graph immediately if it is within the wiki's content namespaces.

						if ( contentNamespaces.indexOf( intraLinks[ i ].ns ) > -1 ) {
							intraNode = context.createWikiNodeFromWiki( intraLinks[ i ].title, originNode.wikiTitle );
							context.callHooks( "NewWikiNodeCreatedHook", [ intraNode, originNode ] );
							if ( intraNode.unadded )
								continue;
							else {
								if(elaborationType === self.ELABORATION_TYPE_INITIAL_POPULATION)
									context.TempNodes.push( intraNode );
								else
									context.addNode( intraNode );
							}
						} else
							continue;

					}
					if ( intraNode ) {
						if(elaborationType === self.ELABORATION_TYPE_SECOND_ORDER_LINKS) {
							var link = context.findLink( originNode.identifier, intraNode.identifier );
							if ( !link ) {
								// If the link doesn't exist, add it - hidden, if necessary.
								link = context.addSecondOrderLink( originNode, intraNode, intraNode.hidden );
							} else {
								// if the found link has this originNode as the SOURCE, this is an already known link OUT; disregard.
								// if the found link has this originNode as the TARGET, this is a NEW link out; set as bidirectional.
								if ( !link.bidirectional && link.target.identifier === originNode.identifier )
									link.bidirectional = true;
							}
						}
						else {
							var hasHiddenCategory = context.nodeHasHiddenCategory( intraNode );
							if ( intraNode.hidden ) {
								if( !hasHiddenCategory ) {
									context.unhideNode( intraNode.identifier );
								}
							}
							var link = context.findLink( originNode.identifier, intraNode.identifier );
							if ( !link ) {
								if(elaborationType === self.ELABORATION_TYPE_INITIAL_POPULATION)
									link = context.addTempLink( originNode, intraNode );
								else
									link = context.addLink( originNode, intraNode, hasHiddenCategory );
							} 
							else {
								// if the found link has this originNode as the SOURCE, this is an already known link OUT; disregard.
								// if the found link has this originNode as the TARGET, this is a NEW link out; set as bidirectional.
								if ( !link.bidirectional && link.target.identifier === originNode.identifier )
									link.bidirectional = true;
							}
						}
					}
					newIntraOutNodes.push( intraNode );
				}

				if( elaborationType !== self.ELABORATION_TYPE_SECOND_ORDER_LINKS )
					context.callHooks( "IntraOutNodeHook", [ newIntraOutNodes ] );
			}
			return newIntraOutNodes;
		}

		my.VikiJS.prototype.intraWikiInSuccessHandler = function( context, data, originNode, elaborationType ) {

			if ( data.error ) {
				context.showError( mw.message( 'viki-error-intrawiki-in', node.pageTitle )
					.text() );
				return;
			}

			var intraLinks = data.query.backlinks;
			var newIntraInNodes = [];
			if ( intraLinks ) {
				// get list of namespaces, or fetch with AJAX if required.

				var wiki = context.allWikis[ originNode.wikiIndex ];
				var contentNamespaces = wiki.contentNamespaces;
				var intraNode;

				for ( var i = 0; i < intraLinks.length; i++ ) {
					intraNode = context.findNode( "pageTitle", intraLinks[ i ].title );
					if ( !intraNode || ( intraNode.apiURL !== originNode.apiURL ) ) {
						if( elaborationType === self.ELABORATION_TYPE_SECOND_ORDER_LINKS )
							continue;
						
						// add the node to the graph immediately if it is within the wiki's content namespaces.

						if ( contentNamespaces.indexOf( intraLinks[ i ].ns ) > -1 ) {
							intraNode = context.createWikiNodeFromWiki( intraLinks[ i ].title, originNode.wikiTitle );
							context.callHooks( "NewWikiNodeCreatedHook", [ intraNode, originNode ] );
							if ( intraNode.unadded )
								continue;
							else {
								if(elaborationType === self.ELABORATION_TYPE_INITIAL_POPULATION)
									context.TempNodes.push( intraNode );
								else
									context.addNode( intraNode );
							}
						} else
							continue;

					}
					if ( intraNode ) {
						if(elaborationType === self.ELABORATION_TYPE_SECOND_ORDER_LINKS) {
							var link = context.findLink( intraNode.identifier, originNode.identifier );
							if ( !link )
								// If the link doesn't exist, add it - hidden, if necessary.
								link = context.addSecondOrderLink( intraNode, originNode, intraNode.hidden ); // opposite order because these are pages coming IN
							else {
								// if the found link has this originNode as the TARGET, this is an already known link IN; disregard.
								// if the found link has this originNode as the SOURCE, this is a NEW link in; set as bidirectional.
								if ( !link.bidirectional && link.source.identifier === originNode.identifier )
									link.bidirectional = true;
							}
						}
						else {
							var hasHiddenCategory = context.nodeHasHiddenCategory( intraNode );
							if ( intraNode.hidden ) {
								if( !hasHiddenCategory ) {
									context.unhideNode( intraNode.identifier );
								}
							}
							var link = context.findLink( intraNode.identifier, originNode.identifier );
							if ( !link ) {
								if(elaborationType === self.ELABORATION_TYPE_INITIAL_POPULATION)
									link = context.addTempLink( intraNode, originNode ); // opposite order because these are pages coming IN
								else
									link = context.addLink( intraNode, originNode, hasHiddenCategory ); // opposite order because these are pages coming IN
							}
							else {
								// if the found link has this originNode as the TARGET, this is an already known link IN; disregard.
								// if the found link has this originNode as the SOURCE, this is a NEW link in; set as bidirectional.
								if ( !link.bidirectional && link.source.identifier === originNode.identifier )
									link.bidirectional = true;
							}
						}
					}

					newIntraInNodes.push( intraNode );
				}

				if( elaborationType !== self.ELABORATION_TYPE_SECOND_ORDER_LINKS )
					context.callHooks( "IntraInNodeHook", [ newIntraInNodes ] );

			}
			return newIntraInNodes;
		}

		/**
		 * Show a progress bar overlay over the graph.
		 *
		 * @param {String} text text to show above the progress bar
		 * @param {Integer} maxValue max value of the progress bar
		 */
		my.VikiJS.prototype.showProgressOverlay = function( text, maxValue ) {
			// Set up the progress bar view for calculating 2nd order links
			var secondOrderContent = '\
			<div id="overlayDiv">\
				<div id="textDiv">'+text+'</div>\
				<div class="progressbarDiv"></div>\
			</div>';

			var loadingStyle = '\
			<style>\
				#textDiv {\
					text-align: center;\
				}\
			</style>';

			self.progressbarView = vex.open( {
				content: secondOrderContent,
				css: {
					position: "absolute",
					top: "auto",
					left: "auto",
					right: "auto",
					bottom: "auto",
					overflow: "hidden",
					"padding-top": 0,
					"padding-bottom": 0,
					height: self.INITIAL_HEIGHT+"px",
					width: self.INITIAL_WIDTH+"px"
				},
				contentCSS: {
					width: '300px',
					margin: '0',
					left: '50%',
					top: '50%',
					transform: 'translate(-50%, -50%)'
				},
				appendLocation: '#VIKI_'+ self.ID +'_overlay',
				afterOpen: function( $vexContent ) {
					$vexContent.append( loadingStyle );
					$('.progressbarDiv').progressbar({ max: maxValue, value: 0 });
				},
				showCloseButton: false,
				overlayClosesOnClick: false,
				escapeButtonCloses: false
			} );
		}

		/**
		 * Update the progress overlay.
		 *
		 * @param {String} text text to show above the progress bar
		 * @param {Integer} value current value of the progress bar
		 */
		my.VikiJS.prototype.updateProgressOverlay = function( text, value ) {
			$('#textDiv').text(text);
			$('.progressbarDiv').progressbar("value", value);
		}

		/**
		 * Close the progress overlay.
		 */
		my.VikiJS.prototype.closeProgressOverlay = function( ) {
			vex.close( self.progressbarView.data().vex.id );
			self.progressbarView = null;
		}

		my.VikiJS.prototype.showWarningDialog = function( text, yesCompletion, noCompletion ) {
			vex.dialog.buttons.YES.text = 'Yes';
			vex.dialog.buttons.NO.text = 'No';

			vex.dialog.confirm({
				css: {
					position: "absolute",
					top: "auto",
					left: "auto",
					right: "auto",
					bottom: "auto",
					overflow: "hidden",
					"padding-top":0,
					"padding-bottom":0,
					height: self.INITIAL_HEIGHT+"px",
					width: self.INITIAL_WIDTH+"px"
				},
				contentCSS: {
					margin: '0',
					left: '50%',
					top: '50%',
					transform: 'translate(-50%, -50%)'
				},
				appendLocation: '#VIKI_'+ self.ID +'_overlay',
				message: text,
				callback: function(value) {
					if( value )
						if(yesCompletion) yesCompletion();
					else
						if(noCompletion) noCompletion();
				}
			});
		}

		my.VikiJS.prototype.showSlideover = function() {
			var html = '<div class="vex-overlay vikijs-overlay" style="height: '+self.INITIAL_HEIGHT+'px; width: '+self.INITIAL_WIDTH+'px;"></div>';
			$('#VIKI_'+ self.ID +'_overlay').append(html);

			var categoriesHTML = "\
				<div class='slideover-categoryDiv' id='categoryDiv-'"+ self.ID +">\
					<fieldset>\
						<legend>Hide By Category:</legend>\
						<table class='categoryContainer'>\
							<tbody>\
				";

			Object.keys( self.allCategories ).sort().forEach(function(category) {
				categoriesHTML += "<tr><td><input type='checkbox' class='categoryCheckbox' id='" + category + "-"+ self.ID + "' name='" + category + "' value=false><label class='categoryCheckboxLabel' for='" + category + "-"+ self.ID + "'>" + category + "</label></td></tr>";
			});
			categoriesHTML += "</tbody></table>";
			categoriesHTML +="<table class='categoryContainer'><tbody>";
			categoriesHTML += "<tr><td><input type='checkbox' class='noCategoryCheckbox' id='no-category-" + self.ID + "' value=false><label class='categoryCheckboxLabel' for='no-category-" + self.ID + "'><i>No Category</i></label></td></tr>";
			categoriesHTML += "</tbody></table></fieldset></div>";
				
			$( "#menu-"+ self.ID ).html(categoriesHTML);
			$( ".categoryCheckbox" )
							.each( function() {
								var checkbox = $( this );
								checkbox.prop( 'checked', self.HiddenCategories.indexOf( checkbox.prop( 'name' ) ) != -1 );

								checkbox.click( function() {
									var value = checkbox.prop( 'checked' );
									checkbox.prop( 'value', value );

									if(value) {
										// hide category
										var category = checkbox.prop( 'name' );
										self.hideByCategories( [ category ] );
									}
									else {
										// unhide category
										var category = checkbox.prop( 'name' );
										self.unhideByCategories( [ category ] );
									}
								} );
							} );

			$( ".noCategoryCheckbox" ).each( function() {
				var checkbox = $( this );
				checkbox.prop( 'checked', self.NoCategoryNodesHidden );

				checkbox.click( function() {
					var value = checkbox.prop( 'checked' );
					checkbox.prop( 'value', value );

					if(value) {
						self.hideByNoCategories();
					}
					else {
						self.unhideByNoCategories();
					}
				} );
			});

			$('#VIKI_'+ self.ID +'_overlay > .vex-overlay').click(function() { 
				self.slideoverVisible = !self.slideoverVisible;
				self.hideSlideover(); 
			});
			$( "#nav-" + self.ID ).css( "pointer-events", "auto" );
			$( "#menu-"+ self.ID ).css("transform", "translate3d(0, 0, 0)");
		}

		my.VikiJS.prototype.hideSlideover = function() {
			$('#VIKI_'+ self.ID +'_overlay > .vex-overlay').fadeOut(400, function(){ $(this).remove(); });
			$( "#nav-" + self.ID ).css( "pointer-events", "none" );
			$( "#menu-"+ self.ID ).css("transform", "translate3d(200px, 0, 0)");
		}

		/**
		 * Hide this node and all associated links from the graph.
		 *
		 * @param {Object} node node to hide from the graph
		 */
		my.VikiJS.prototype.hideNode = function( node, deselect ) {
			var recentHiddenLinks = Array();

			// 1. Remove node from Nodes array and store into hidden nodes array.
			node.hidden = true;
			self.HiddenNodes.push( node );
			self.Nodes.splice( node.index, 1 );
			for ( var i = node.index; i < self.Nodes.length; i++ )
				self.Nodes[ i ].index--;

			// 2. Remove any associated links from Links array and store into hidden links array.
			// Also store into recentHiddenLinks so we can remove them from LinkMap.
			for ( var j = self.Links.length - 1; j >= 0; j-- ) {
				var link = self.Links[ j ];
				if ( link.source === node || link.target === node ) {
					self.HiddenLinks.push( link );
					recentHiddenLinks.push( link );
					self.Links.splice( j, 1 );
				}
			}

			// 3. Remove links from LinkMap.
			for ( var k = 0; k < recentHiddenLinks.length; k++ ) {
				var key = recentHiddenLinks[ k ].source.identifier + "," + recentHiddenLinks[ k ].target.identifier;
				var reverse = recentHiddenLinks[ k ].target.identifier + "," + recentHiddenLinks[ k ].source.identifier;
				if ( self.LinkMap[ key ] )
					delete self.LinkMap[ key ];
				if ( self.LinkMap[ reverse ] )
					delete self.LinkMap[ reverse ];
			}

			// 4. If relevant, set selected node to none and display "No Node Selected" since the old selected node is now hidden.
			if(deselect) {
				self.SelectedNodeIndex = -1;
				self.displayNoNodeSelected();
			}

		};

		/**
		 * Hide this node and redraw the graph.
		 * This is a convenience method called internally to hide a node in the graph and then redraw the graph.
		 *
		 * @param {Object} node node to hide from the graph
		 */
		my.VikiJS.prototype.hideNodeAndRedraw = function( node ) {
			node.explicitlyHidden = true;
			self.hideNode( node, true );
			self.log("redraw() - from hideNodeAndRedraw");
			self.redraw( true );
		};

		/**
		 * Hide this hub from the graph.
		 *
		 * This method is called when the user elects to hide an elaborated node from the graph,
		 * along with all leaf nodes attached to it (i.e. nodes not attached to other nodes).
		 *
		 * @param {Object} node central node of the hub to be hidden
		 */
		my.VikiJS.prototype.hideHub = function( node ) {
			if ( !node.elaborated )
				return;

			node.hidingHub = true;
			self.hideCluster( node, self.HIDE_HUB );
		};

		/**
		 * Hide this cluster from the graph.
		 *
		 * This is the generic method called internally by hideHub, hideIncomingLinks and hideOutgoingLinks.
		 *
		 * @param {Object} node central node of the node collection to be hidden
		 * @param {number} hideType type of collection to be hidden:
		 * - hub
		 * - incoming links
		 * - outgoing links
		 */
		my.VikiJS.prototype.hideCluster = function( node, hideType ) {
			// hideCluster is the same call for hideHub, hideIncomingLinks and hideOutgoingLinks.
			// We iterate links to check all nodes participating in those links which are connected to the passed-in node.
			// If hideHub was the caller, identify all nodes connected to this node which aren't connected to any others (i.e. leaf nodes).
			// If hideIncomingLinks or hideOutgoingLinks was the caller, just identify all connected nodes.

			var i;

			var nodesToRemove = [];
			if ( hideType === self.HIDE_HUB )
				nodesToRemove.push( node );

			for ( i = 0; i < self.Links.length; i++ ) {
				var link = self.Links[ i ];

				// hideHub doesn't care about bidirectionality of links, but hideIncomingLinks and hideOutgoingLinks do.
				if ( link.bidirectional && ( link.source === node || link.target === node ) && hideType !== self.HIDE_HUB ) {
					var thisNode = link.source === node ? link.target : link.source;
					// Only want to hide bidirectional links if BOTH hideIncoming and hideOutgoing will be the case for the passed-in node.
					// Thus, if hideType is HIDE_INCOMING and the node is already hidingOutgoing, hide this bidirectional link.
					// Same if hideType is HIDE_OUTGOING and the node is already hidingIncoming.
					if ( ( hideType === self.HIDE_INCOMING && node.hidingOutgoing ) || ( hideType === self.HIDE_OUTGOING && node.hidingIncoming ) ) {
						nodesToRemove.push( thisNode );
					}
				} else if ( link.source === node ) {
					if ( hideType === self.HIDE_OUTGOING || ( hideType === self.HIDE_HUB && self.numberOfConnections( link.target ) === 1 ) )
						nodesToRemove.push( link.target );
				} else if ( link.target === node ) {
					if ( hideType === self.HIDE_INCOMING || ( hideType === self.HIDE_HUB && self.numberOfConnections( link.source ) === 1 ) )
						nodesToRemove.push( link.source );
				}
			}

			for ( i = 0; i < nodesToRemove.length; i++ ) {
				self.hideNode( nodesToRemove[ i ], true );
			}

			if ( hideType === self.HIDE_INCOMING || hideType === self.HIDE_OUTGOING ) {
				self.SelectedNodeIndex = node.index;
				self.displayNodeInfo( self.Nodes[ self.SelectedNodeIndex ] );
			}
			self.log("redraw() - from hideCluster");
			self.redraw( true );
		};

		/**
		 * Hide incoming links to this node from the graph.
		 *
		 * This method is called the user elects to hide incoming links to a given node.
		 *
		 * @param {Object} node node whose incoming links are to be hidden.
		 */
		my.VikiJS.prototype.hideIncomingLinks = function( node ) {
			node.hidingIncoming = true;
			self.hideCluster( node, self.HIDE_INCOMING );
		};

		/**
		 * Hide outgoing links from this node from the graph.
		 *
		 * This method is called the user elects to hide outgoing links from a given node.
		 *
		 * @param {Object} node node whose outgoing links are to be hidden.
		 */
		my.VikiJS.prototype.hideOutgoingLinks = function( node ) {
			node.hidingOutgoing = true;
			self.hideCluster( node, self.HIDE_OUTGOING );
		};

		/**
		 * Hide nodes in the graph by the categories their pages belong to.
		 *
		 * This method is called when the user elects to hide nodes based on categories.
		 *
		 * @param {Array} categories list of categories to hide nodes by
		 */
		my.VikiJS.prototype.hideByCategories = function( categories ) {
			var self = this;
			categories.forEach( function( category ) {
				var nodesInThisCategory = self.Nodes.filter( function( node ) {
					return node.categories ? node.categories.indexOf( category ) !== -1 : false;
				} );

				self.HiddenCategories.push(category);
				nodesInThisCategory.forEach( function( node ) {
					self.hideNode( node, true );
				} );
			} );

			self.log("redraw() - from hideByCategories");
			self.redraw( true );
		};

		my.VikiJS.prototype.hideByNoCategories = function() {
			var noCategoryNodes = self.Nodes.filter( function( node ) {
				return !node.categories || node.categories.length == 0;
			} );

			noCategoryNodes.forEach( function( node ) {
				self.hideNode( node, true );
			} )

			self.NoCategoryNodesHidden = true;

			self.log("redraw() - from hideByNoCategories");
			self.redraw( true );
		}

		/**
		 * Unhide node from the graph.
		 *
		 * This method is called internally when a new elaboration occurs, and this
		 * previously hidden node is connected to a new node and should be un-hidden.
		 *
		 * @param {number} identifier identifier for the node to be unhidden
		 */
		my.VikiJS.prototype.unhideNode = function( identifier ) {
			var index = -1;
			for ( var i = 0; i < self.HiddenNodes.length; i++ ) {
				if ( self.HiddenNodes[ i ].identifier === identifier ) {
					index = i;
					break;
				}
			}

			if ( index === -1 )
				return;

			self.Nodes.push( self.HiddenNodes[ index ] );
			self.HiddenNodes.splice( index, 1 );

			self.log("redraw() - from unhideNode");
			self.redraw( true );

		};

		/**
		 * Unhide nodes in the given categories.
		 *
		 * This method is called when the user elects to unhide a category.
		 *
		 * @param {categories} list of categories to unhide
		 */
		my.VikiJS.prototype.unhideByCategories = function( categories ) {
			categories.forEach(function(category) {
				var nodesInThisCategory = self.HiddenNodes.filter( function( node ) {
					return node.categories ? node.categories.indexOf( category ) !== -1 : false;
				} );

				// Remove category from HiddenCategories.
				self.HiddenCategories.splice( self.HiddenCategories.indexOf( category ), 1 );

				self.unhideNodesIfValid( nodesInThisCategory );
			});
		}

		/**
		* Unhide nodes which have no categories.
		*
		* This method is called when the user elects to unhide nodes which aren't in a category.
		*/
		my.VikiJS.prototype.unhideByNoCategories = function() {

			var noCategoryNodes = self.HiddenNodes.filter( function( node ) {
				return !node.categories || node.categories.length == 0;
			} );

			self.NoCategoryNodesHidden = false;
			self.unhideNodesIfValid( noCategoryNodes );
		}

		/**
		 * Generic method for unhiding a set of nodes IF all hidden conditions are clear. 
		 * Unhides these nodes and their corresponding links, except links to other nodes 
		 * that are still hidden.
		 * 
		 * This method is called internally when a group of nodes should be unhidden,
		 * such as unhiding a category.
		 */
		my.VikiJS.prototype.unhideNodesIfValid = function( nodes ) {
			var self = this;
			// 0. Filter list to nodes that have all hidden conditions clear.
			var nodesToUnhide = nodes.filter( function( node ) { return !self.nodeShouldBeHidden( node ); });
			self.log("nodes to unhide:");
			self.log(nodesToUnhide);

			// 1. Add these hidden nodes back into main Nodes array.
			nodesToUnhide.forEach( function ( node ) {
				self.HiddenNodes.splice( self.HiddenNodes.indexOf( node ), 1 );
				self.Nodes.push( node );
				node.index = self.Nodes.length - 1;
				node.hidden = false;
			});

			// 2. Find the hidden links that correspond to these nodesToUnhide.
			var theseHiddenLinks = self.HiddenLinks.filter( function( link ) {
				var associated = false;
				nodesToUnhide.forEach(function( node ) {
					if( link.source.identifier === node.identifier || link.target.identifier === node.identifier )
						associated = true;
				});
				return associated;
			});

			// 3. Add their corresponding hidden links back into main Links array and LinkMap, but not if
			// the other end of a link is still a hidden node.
			theseHiddenLinks.forEach( function( link, index, array ) {
				// TODO: possibly need to check for alreadyFound, duplicate vs bidirectional, etc?
				// Reference code from addHiddenLinksToGraph()

				// If either the source or target of this link is still hidden, keep it hidden
				if( link.source.hidden || link.target.hidden ) {
					return;
				}
				self.Links.push( link );
				self.LinkMap[ link.source.identifier + "," + link.target.identifier ] = link;
				self.LinkMap[ link.target.identifier + "," + link.source.identifier ] = link;
				self.HiddenLinks.splice( self.HiddenLinks.indexOf( link ), 1 );
			});

			self.redraw( true );
		}

		/**
		 * Show all nodes in the graph, re-adding any hidden nodes.
		 *
		 * This method is called when the user elects to show all nodes in the graph.
		 *
		 */
		my.VikiJS.prototype.showAllNodes = function() {
			// 1. Add all hidden nodes back into main Nodes array, then destroy hidden nodes array.
			var i;

			for ( i = 0; i < self.HiddenNodes.length; i++ ) {
				self.Nodes.push( self.HiddenNodes[ i ] );
				self.HiddenNodes[ i ].index = self.Nodes.length - 1;
				self.HiddenNodes[ i ].hidden = false;
			}
			self.HiddenNodes = [];

			// 2. Add all hidden links back into main Links array. Also add all hidden links back into the LinkMap.
			self.addHiddenLinksToGraph();

			self.Nodes.forEach( function( node ) {
				node.hidingIncoming = false;
				node.hidingOutgoing = false;
			} );

			self.HiddenCategories = [];

			self.log("redraw() - from showAllNodes");
			self.redraw( true );

		};

		/*
		 * Helper Methods
		 */

		/**
		 * Determine if a node has a category which is currently hidden on the graph.
		 *
		 * @param {node} node object to check
		 *
		 * @return {hasHiddenCategory} true if the node has a hidden category, false if not
		 */
		 my.VikiJS.prototype.nodeHasHiddenCategory = function( node ) {
		 	if( node.type === self.EXTERNAL_PAGE_TYPE )
		 		return false;
		 	if( !node.categories || node.categories.length == 0 )
		 		return self.NoCategoryNodesHidden;

			for(var i = 0; i < self.HiddenCategories.length; i++) {
				var STOP = false;
				for(var j = 0; j < node.categories.length; j++) {
					if(self.HiddenCategories[i] === node.categories[j]) {
						return true;
					}
				}
			}
			return false;
		 }

		/**
		 * Determine if a node is hidden because it is an incoming link to another node
		 * which has "hide incoming links" active.
		 *
		 * @param {node} node object to check
		 *
		 * @return {isHiddenIncomingLink} true if the node is an incoming link to another
		 * "hide incoming links" node, otherwise false
		 */
		 my.VikiJS.prototype.nodeIsHiddenIncomingLink = function( node ) {
		 	var self = this;
		 	// Check this node's *outgoing* nodes - if any of them has "hideIncoming" true,
		 	// then this node is being hidden because it is an incoming link to that node.
		 	var outgoingNodes = self.allOutgoingNodes( node );
		 	var isHiddenIncoming = false;
		 	outgoingNodes.forEach( function( outgoingNode ) {
		 		if( outgoingNode.hidingIncoming ) {
		 			isHiddenIncoming = true;
		 		}
		 	});
		 	return isHiddenIncoming;
		 }

		/**
		 * Determine if a node is hidden because it is an outgoing link from another node
		 * which has "hide outgoing links" active.
		 *
		 * @param {node} node object to check
		 *
		 * @return {isHiddenIncomingLink} true if the node is an outgoing link from another
		 * "hide outgoing links" node, otherwise false
		 */
		 my.VikiJS.prototype.nodeIsHiddenOutgoingLink = function( node ) {
		 	var self = this;
		 	// Check this node's *incoming* nodes - if any of them has "hideOutgoing" true,
		 	// then this node is being hidden because it is an outgoing link from that node.
		 	var incomingNodes = self.allIncomingNodes( node );
		 	var isHiddenOutgoing = false;
		 	incomingNodes.forEach( function( incomingNode ) {
		 		if( incomingNode.hidingOutgoing ) {
		 			isHiddenOutgoing = true;
		 		}
		 	});
		 	return isHiddenOutgoing;
		 }

		/**
		 * Determine if a node is hidden because it is part of a hidden hub.
		 *
		 * @param {node} node object to check
		 *
		 * @return {isPartOfHiddenHub} true if the node is part of a hidden hub.
		 */
		 my.VikiJS.prototype.nodeIsPartOfHiddenHub = function( node ) {
		 	var self = this;
		 	// This node can only be part of a hidden hub if it has only 1 connection.
		 	if( self.numberOfConnections( node ) > 1 )
		 		return false;

		 	var allRelatedNodes = self.allIncomingNodes( node ).concat( self.allOutgoingNodes( node ) ).concat( self.allBidirectionalNodes( node ) );
		 	var isPartOfHiddenHub = false;
		 	allRelatedNodes.forEach(function ( node ) {
		 		if( node.hidingHub )
		 			isPartOfHiddenHub = true;
		 	});
		 	return isPartOfHiddenHub;
		 }

		/**
		 * Determine if a node should be hidden based on multiple factors.
		 * Currently these include: hidden category, explicitly hidden, 
		 * hidden hub, is incoming or outgoing link.
		 *
		 * @param {node} node object to check hidden status
		 *
		 * @return {shouldBeHidden} true if node sould be hidden, false if not
		 */
		 my.VikiJS.prototype.nodeShouldBeHidden = function( node ) {
		 	var self = this;
		 	var hiddenBecauseCategory = self.nodeHasHiddenCategory( node );
		 	var hiddenBecauseExplicitlyHidden = node.explicitlyHidden;
		 	var hiddenBecauseHubHidden = self.nodeIsPartOfHiddenHub( node );
		 	var hiddenBecauseIsAnIncomingLink = self.nodeIsHiddenIncomingLink( node );
		 	var hiddenBecauseIsAnOutgoingLink = self.nodeIsHiddenOutgoingLink( node );

		 	return hiddenBecauseCategory || hiddenBecauseExplicitlyHidden || 
		 	    hiddenBecauseHubHidden || hiddenBecauseIsAnIncomingLink || hiddenBecauseIsAnOutgoingLink;
		 }

		/**
		 * Get the index of a wiki from URL to a page in that wiki.
		 *
		 * This helper method is called internally.
		 *
		 * @param {string} url URL of the page whose wiki is to be determined.
		 *
		 * @return {number} index of the wiki, or -1 if it was not found.
		 */
		my.VikiJS.prototype.indexOfWikiForURL = function( url ) {
			var self = this;
			for ( var i = 0; i < self.allWikis.length; i++ ) {
				var strippedContentURL = self.allWikis[ i ].contentURL.substring( 0, self.allWikis[ i ].contentURL.indexOf( "$1" ) );
				if ( url.indexOf( strippedContentURL ) !== -1 )
					return i;
			}
			return -1;
		};

		/*
		 * Get the index of a wiki from the wiki name.
		 *
		 * This helper method is called internally.
		 *
		 * @param {string} wikiTitle name of the wiki
		 *
		 * @return {number} index of the wiki, or -1 if it was not found.
		 */
		my.VikiJS.prototype.searchableWikiIndexForName = function( wikiTitle ) {
			var self = this;

			for ( var i = 0; i < self.allWikis.length; i++ )
				if ( self.allWikis[ i ].wikiTitle === wikiTitle )
					return i;

			return -1;
		};

		/*
		 * Replace the character at index of a string with the new specified sequence.
		 *
		 * This helper method is called internally.
		 *
		 * @param {string} string string to be modified
		 * @param {number} index index of the character to be replaced
		 * @param {string} character new character to be inserted
		 *
		 * @return {string} newly modified string
		 */
		my.VikiJS.prototype.replaceAt = function( string, index, character ) {
			return string.substr( 0, index ) + character + string.substr( index + character.length );
		};

		/*
		 * Get the number of nodes connected to this node.
		 *
		 * This helper method is called internally to help determine whether nodes are leaf nodes.
		 *
		 * @param {Object} node node to be examined
		 *
		 * @return {number} number of connections to this node
		 */
		my.VikiJS.prototype.numberOfConnections = function( node ) {
			var connections = self.Links.filter( function( link ) {
				return link.source.identifier === node.identifier || link.target.identifier === node.identifier;
			} );

			return connections.length;
		};

		/*
		 * Get a list of all incoming nodes to this node, currently hidden or not. Excludes bidirectional.
		 *
		 * @param {Object} node node to be examined
		 *
		 * @return {nodes} all incoming nodes
		 */		
		my.VikiJS.prototype.allIncomingNodes = function( node ) {
			var self = this;
			var allLinks = self.Links.concat(self.HiddenLinks);
			var allIncomingNodes = [];

			allLinks.forEach(function(link) {
				if(link.target.identifier === node.identifier && link.source.identifier !== node.identifier )
					allIncomingNodes.push(link.source);
			});

			return allIncomingNodes;
		}

		/*
		 * Get a list of all outgoing nodes from this node, currently hidden or not. Excludes bidirectional.
		 *
		 * @param {Object} node node to be examined
		 *
		 * @return {nodes} all outgoing nodes
		 */		
		my.VikiJS.prototype.allOutgoingNodes = function( node ) {
			var self = this;
			var allLinks = self.Links.concat(self.HiddenLinks);
			var allOutgoingNodes = [];

			allLinks.forEach(function(link) {
				if(link.source.identifier === node.identifier && link.target.identifier !== node.identifier )
					allOutgoingNodes.push(link.target);
			});

			return allOutgoingNodes;
		}

		/*
		 * Get a list of all nodes with a bidirectional relationship to this node.
		 *
		 * @param {Object} node node to be examined
		 *
		 * @return {nodes} all bidirectionally-related nodes
		 */		
		my.VikiJS.prototype.allBidirectionalNodes = function( node ) {
			var self = this;
			var allLinks = self.Links.concat(self.HiddenLinks);
			var allBidirectionalNodes = [];

			allLinks.forEach(function(link) {
				if(link.source.identifier === node.identifier && link.target.identifier === node.identifier )
					allBidirectionalNodes.push(link.source);
			});

			return allBidirectionalNodes;	
		}

		/*
		 * Log statement to the console.
		 *
		 * This helper method is called internally as a replacement for console.log, because
		 * Internet Explorer doesn't define console.log unless the console is active.
		 *
		 * @param {string} text text to be logged
		 */
		my.VikiJS.prototype.log = function( text ) {
			if ( ( window.console !== undefined ) )
				window.console.log( text );
		};

		/*
		 * Display this error message in the error div.
		 *
		 * Call this helper method from VIKI plugins whenever an error has occurred which the user
		 * should know about. This method is also called internally by VIKI for errors. If no errors
		 * are being displayed, then the error div is hidden; once showError is called, the error
		 * div is displayed with any errors that have been called.
		 *
		 * @param {string} errorText text of the error to be displayed
		 */
		my.VikiJS.prototype.showError = function( errorText ) {
			$( "#" + self.ErrorsDiv )
				.css( "visibility", "visible" );
			var text = $( "#" + self.ErrorsDiv ).text();
			if( text.indexOf( errorText ) == -1 ) {
				$( "#" + self.ErrorsDiv )
					.append( "<p>" + errorText + "</p>" );
			}
		};

		/*
		 * VikiJS Hook Structure Methods
		 */

		/*
		 * Call hooks for the given hook name.
		 *
		 * This method is central to the VIKI hook structure. Any plugins which have registered function hooks
		 * with defined hook points will have the function hooks called through this function at the time
		 * of the hook event. This method should not be called externally by VIKI plugins; instead, register
		 * hook functions with $wgVIKI_Function_Hooks in the plugin's PHP file. See VIKI extension documentation
		 * for more details.
		 *
		 * @param {string} hookName name of the hook event
		 * @return {parameters} parameters that are to be passed to the hook functions. These are well defined
		 * based on the hook name. See VIKI extension documentation for more details.
		 */
		my.VikiJS.prototype.callHooks = function( hookName, parameters ) {
			var self = this;
			if ( this.hasHooks ) {
				if ( this.Hooks[ hookName ] ) {
					for ( var i = 0; i < self.Hooks[ hookName ].length; i++ ) {
						// Determine appropriate scope and call function.
						// http://stackoverflow.com/questions/912596/how-to-turn-a-string-into-a-javascript-function-call

						var hookFunction = self.Hooks[ hookName ][ i ];

						var scope = window;
						var scopeSplit = hookFunction.split( '.' );
						for ( var j = 0; j < scopeSplit.length - 1; j++ ) {
							scope = scope[ scopeSplit[ j ] ];

							if ( scope === undefined ) return false;
						}

						scope[ scopeSplit[ scopeSplit.length - 1 ] ]( self, parameters, hookName );
					}

					return true;
				}
			}
			return false;
		};

		/*
		 * Completion handler for a hook function call.
		 *
		 * Call this method at the completion of your hook function for a VIKI plugin to let VIKI know
		 * that a hook function has completed executing. Include in the method call parameters specifying
		 * whether VIKI should redraw the entire graph in response to your hook function, or just redraw a
		 * single node; if the latter, also specify the node to be redrawn. As of VIKI 1.0, this method
		 * only serves to ensure content namespaces are fetched at the end of GetAllWikisHook and to allow
		 * VIKI to redraw the graph or a node; in the future, other functions may be added to this method.
		 *
		 * @param {string} hookName name of the hook event
		 * @param {Array} parameters parameters to be passed to VIKI, such as whether the graph should be
		 * redrawn or a single node should be redrawn
		 */
		my.VikiJS.prototype.hookCompletion = function( hookName, parameters ) {
			var self = this;
			// let VikiJS know that the hook was completed, so VikiJS can perform actions if needed.

			parameters = parameters || {};
			if ( hookName === "GetAllWikisHook" ) {
				self.fetchContentNamespaces();
			}
			if ( parameters.redraw && parameters.redraw === true ) {
				self.log("redraw() - from hookCompletion ("+hookName+")");
				self.redraw( true );
			}

			if ( parameters.redrawNode && parameters.redrawNode === true && parameters.node ) {
				self.redrawNode( parameters.node );
				if( self.SelectedNodeIndex !== -1 ) {
					self.displayNodeInfo( self.Nodes[ self.SelectedNodeIndex ] );
				}
			}
		};
	};

	return my;
}( mediaWiki, jQuery, vex, Spinner, d3, window.VIKI || {} ) );
