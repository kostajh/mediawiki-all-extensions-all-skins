bs.util.registerNamespace( 'bs.vec.util.tag' );
bs.util.registerNamespace( 'bs.vec.ui' );
bs.util.registerNamespace( 'bs.vec.ce' );
bs.util.registerNamespace( 'bs.vec.dm' );

bs.vec.util.tag.Registry = function BsVecUtilTagRegistry() {};

OO.initClass( bs.vec.util.tag.Registry );

bs.vec.util.tag.Registry.prototype.initialize = function() {
	var dfd = $.Deferred();
	this.initTagDefinitions();
	for( var i = 0; i < this.definitions.length; i++ ) {
		this.register( this.definitions[i] );
	}
	dfd.resolve();
	return dfd;
};

bs.vec.util.tag.Registry.prototype.register = function( definition ) {
	var cfg = definition.getCfg();
	this.createCeForTag( cfg );
	this.createDmForTag( cfg );
	this.createCommandForTag( cfg );
//	if ( cfg.toolGroup && cfg.toolGroup !== 'none' ) {
		this.createToolForTag( cfg );
//	}
	this.createInspectorForTag( cfg );
};

bs.vec.util.tag.Registry.prototype.createCeForTag = function( cfg ) {
	var classname = cfg.classname + 'Node';
	bs.vec.ce[classname] = function() {
		bs.vec.ce[classname].super.apply( this, arguments );
	};

	OO.inheritClass( bs.vec.ce[classname], ve.ce.MWInlineExtensionNode );

	bs.vec.ce[classname].static.name = cfg.name;
	bs.vec.ce[classname].static.primaryCommandName = cfg.tagname;

	bs.vec.ce[classname].static.rendersEmpty = cfg.rendersEmpty;

	bs.vec.ce[classname].prototype.validateGeneratedContents = function ( $element ) {
		if ( $element.is( 'span' ) && $element.children( '.bsErrorFieldset' ).length > 0 ) {
			return false;
		}
		return true;
	};

	ve.ce.nodeFactory.register( bs.vec.ce[classname] );
};

bs.vec.util.tag.Registry.prototype.createDmForTag = function( cfg ) {
	var classname = cfg.classname + 'Node';
	bs.vec.dm[classname] = function() {
		bs.vec.dm[classname].super.apply( this, arguments );
	};

	OO.inheritClass( bs.vec.dm[classname], ve.dm.MWInlineExtensionNode );

	bs.vec.dm[classname].static.name = cfg.name;
	bs.vec.dm[classname].static.tagName = cfg.tagname;

	// Name of the parser tag
	bs.vec.dm[classname].static.extensionName = cfg.tagname;

	// This tag renders without content
	if ( cfg.rendersEmpty ) {
		bs.vec.dm[classname].static.childNodeTypes = [];
		bs.vec.dm[classname].static.isContent = true;
	}

	ve.dm.modelRegistry.register( bs.vec.dm[classname] );
};

bs.vec.util.tag.Registry.prototype.createCommandForTag = function( cfg ) {
	ve.ui.commandRegistry.register(
		new ve.ui.Command(
			cfg.name + 'Command', 'window', 'open',
			{ args: [ cfg.name + 'Inspector' ] }
		)
	);
}

bs.vec.util.tag.Registry.prototype.createToolForTag = function( cfg ) {
	var classname = cfg.classname + 'InspectorTool';
	bs.vec.ui[classname] = function( toolGroup, config ) {
		bs.vec.ui[classname].super.call( this, toolGroup, config );
	};
	OO.inheritClass( bs.vec.ui[classname], ve.ui.FragmentInspectorTool );

	bs.vec.ui[classname].static.name = cfg.name + 'Tool';
	bs.vec.ui[classname].static.group = cfg.toolGroup;
	bs.vec.ui[classname].static.autoAddToCatchall = false;
	bs.vec.ui[classname].static.icon = cfg.icon;
	bs.vec.ui[classname].static.title = OO.ui.deferMsg(
		cfg.menuItemMsg
	);
	bs.vec.ui[classname].static.modelClasses = [ bs.vec.dm[cfg.classname + 'Node'] ];
	bs.vec.ui[classname].static.commandName = cfg.name + 'Command';
	ve.ui.toolFactory.register( bs.vec.ui[classname] );
};

bs.vec.util.tag.Registry.prototype.createInspectorForTag = function( cfg ){
	var classname = cfg.classname + 'Inspector';
	bs.vec.ui[classname] = function( config ) {
		bs.vec.ui[classname].super.call( this, ve.extendObject( {padded: false, expanded: true, scrollable: false }, config ) );
	};

	OO.inheritClass( bs.vec.ui[classname], ve.ui.MWLiveExtensionInspector );

	bs.vec.ui[classname].static.name = cfg.name + 'Inspector';
	bs.vec.ui[classname].static.title = OO.ui.deferMsg( cfg.menuItemMsg );
	bs.vec.ui[classname].static.modelClasses = [ bs.vec.dm[ cfg.classname + 'Node' ]];
	if ( cfg.rendersEmpty ) {
		bs.vec.ui[classname].static.allowedEmpty = true;
		bs.vec.ui[classname].static.selfCloseEmptyBody = true;
	}

	bs.vec.ui[classname].prototype.initialize = function() {
		bs.vec.ui[classname].super.prototype.initialize.call( this );
		if ( cfg.tabbed === true ) {
			this.indexLayout = new OO.ui.IndexLayout( {
				expanded: false,
				scrollable: false,
				padded: true
			});
			for( var i = 0; i < cfg.tabs.length; i++ ) {
				var tabName = cfg.tabs[i].name;
				var tabClassName = "tab" + tabName;
				ve.ui[tabClassName] = function( name, config ) {
					ve.ui[tabClassName].parent.call( this, name, config );
				}
				OO.inheritClass( ve.ui[tabClassName], OO.ui.TabPanelLayout );

				ve.ui[tabClassName].prototype.setupTabItem = function() {
					this.tabItem.setLabel( mw.message( this.data.labelMsg ).plain() );
				};

				this[ tabClassName ] =
					new ve.ui[tabClassName]( tabName, { expanded:false, data: cfg.tabs[i] } );
			}
		} else {
			this.indexLayout = new OO.ui.PanelLayout( {
				scrollable: false,
				expanded: false,
				padded: true
			} );
		}

		cfg.inspector.methods.createFields( this, cfg );

		this.$content.addClass( 've-ui-' + cfg.name + '-inspector-content' );
		if ( cfg.tabbed === true ) {
			var pages = [];
			for( var i = 0; i < cfg.tabs.length; i++ ) {
				pages.push( this[ "tab" + cfg.tabs[i].name ] );
			};
			this.indexLayout.addTabPanels( pages );
		};

		if ( cfg.hideMainInput === true ) {
			this.input.$element.remove();
		}

		// Add description field
		this.descriptionField = new OO.ui.LabelWidget( {
			label: $( '<div class="bs-vec-inspector-desc">' + mw.message( cfg.descriptionMsg ).plain() + '</div>' )
		} );
		this.descriptionLayout = new OO.ui.PanelLayout( { padded:true, expanded:false, scrollable:false } );
		this.descriptionLayout.$element.append( this.descriptionField.$element );
		this.form.$element.prepend( this.descriptionLayout.$element );

		// This is needed because tab panels have absolute position and no size.
		this.indexLayout.$element.append(
			$( '<div style="height:0px; width:100%;"></div>' )
		);

		this.indexLayout.$element.append(
			this.generatedContentsError.$element
		);

		// Add all other fields
		this.form.$element.append(
			this.indexLayout.$element
		);
	};

	bs.vec.ui[classname].prototype.getSetupProcess = function ( data ) {
		return bs.vec.ui[classname].super.prototype.getSetupProcess.call( this, data )
			.next( function () {
				var attributes = this.selectedNode.getAttribute( 'mw' ).attrs;

				cfg.inspector.methods.setValues( this, attributes, cfg );

				this.actions.setAbilities( {done: true} );

			}, this );
	};

	bs.vec.ui[classname].prototype.updateMwData = function ( mwData ) {
		bs.vec.ui[classname].super.prototype.updateMwData.call( this, mwData );

		mwData = cfg.inspector.methods.updateMwData( this, mwData, cfg );
	};

	bs.vec.ui[classname].prototype.getNewElement = function () {
		var element = bs.vec.ui[classname].super.prototype.getNewElement.call( this );

		return cfg.inspector.methods.getNewElement( this, element );
	};

	ve.ui.windowFactory.register( bs.vec.ui[classname] );
}

bs.vec.util.tag.Registry.prototype.initTagDefinitions = function() {
	this.definitions = bs.vec.getTagDefinitions();
};
