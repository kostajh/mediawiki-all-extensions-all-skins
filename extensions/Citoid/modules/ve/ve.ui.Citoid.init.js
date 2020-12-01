( function () {
	var name, toolClass, toolGroups, map, requireMappings, origRenderBody,
		missingMappings = [];

	// Don't create tool unless the configuration message is present
	try {
		map = JSON.parse( mw.message( 'citoid-template-type-map.json' ).plain() );
	} catch ( e ) {}

	// Check map has all required keys
	if ( map ) {
		requireMappings = [
			'artwork',
			'audioRecording',
			'bill',
			'blogPost',
			'book',
			'bookSection',
			'case',
			'computerProgram',
			'conferencePaper',
			'dictionaryEntry',
			'document',
			'email',
			'encyclopediaArticle',
			'film',
			'forumPost',
			'hearing',
			'instantMessage',
			'interview',
			'journalArticle',
			'letter',
			'magazineArticle',
			'manuscript',
			'map',
			'newspaperArticle',
			'patent',
			'podcast',
			'presentation',
			'radioBroadcast',
			'report',
			'statute',
			'thesis',
			'tvBroadcast',
			'videoRecording',
			'webpage'
		];

		requireMappings.forEach( function ( key ) {
			if ( !map[ key ] ) {
				missingMappings.push( key );
			}
		} );
		if ( missingMappings.length ) {
			mw.log.warn( 'Mapping(s) missing from citoid-template-type-map.json: ' + missingMappings.join( ', ' ) );
			map = undefined;
		}
	}

	// Expose
	ve.ui.mwCitoidMap = map;

	// If there is no template map ("auto") or citation tools ("manual")
	// don't bother registering Citoid at all.
	if ( !( ve.ui.mwCitoidMap || ve.ui.mwCitationTools.length ) ) {
		// Unregister the tool
		ve.ui.toolFactory.unregister( ve.ui.CitoidInspectorTool );
		return;
	}

	/* Command */
	ve.ui.commandRegistry.register(
		new ve.ui.Command(
			'citoid', 'citoid', 'open', { supportedSelections: [ 'linear' ] }
		)
	);

	/* Sequence */
	ve.ui.sequenceRegistry.register(
		new ve.ui.Sequence( 'wikitextRef', 'citoid', '<ref', 4 )
	);

	/* Trigger */
	// Unregister Cite's trigger
	ve.ui.triggerRegistry.unregister( 'reference' );
	ve.ui.triggerRegistry.register(
		'citoid', { mac: new ve.ui.Trigger( 'cmd+shift+k' ), pc: new ve.ui.Trigger( 'ctrl+shift+k' ) }
	);

	/* Command help */
	// This will replace Cite's trigger on insert/ref
	// "register" on commandHelpRegistry is more of an "update", so we don't need to provide label/sequence.
	ve.ui.commandHelpRegistry.register( 'insert', 'ref', {
		trigger: 'citoid'
	} );

	/* Setup tools and toolbars */

	// HACK: Find the position of the current citation toolbar definition
	// and manipulate it.

	// Unregister regular citation tools so they don't end up in catch-all groups
	for ( name in ve.ui.toolFactory.registry ) {
		toolClass = ve.ui.toolFactory.lookup( name );
		if (
			name === 'reference' || name.indexOf( 'reference/' ) === 0 ||
			toolClass.prototype instanceof ve.ui.MWCitationDialogTool
		) {
			ve.ui.toolFactory.unregister( toolClass );
		}
	}

	function fixTarget( target ) {
		var i, iLen;
		toolGroups = target.static.toolbarGroups;
		// Instead of using the rigid position of the group,
		// downgrade this hack from horrific to somewhat less horrific by
		// looking through the object to find what we actually need
		// to replace. This way, if toolbarGroups are changed in VE code
		// we won't have to manually change the index here.
		for ( i = 0, iLen = toolGroups.length; i < iLen; i++ ) {
			// Replace the previous cite group with the citoid tool.
			// If there is no cite group, citoid will appear in the catch-all group
			if ( toolGroups[ i ].name === 'cite' ) {
				toolGroups[ i ] = {
					name: 'citoid',
					include: [ 'citoid' ]
				};
				break;
			}
		}
	}

	for ( name in ve.init.mw.targetFactory.registry ) {
		fixTarget( ve.init.mw.targetFactory.lookup( name ) );
	}

	ve.init.mw.targetFactory.on( 'register', function ( n, target ) {
		fixTarget( target );
	} );

	/**
	 * HACK: Override MWReferenceContextItem methods directly instead of inheriting,
	 * as the context relies on the generated citation types (ref, book, ...) inheriting
	 * directly from MWReferenceContextItem.
	 *
	 * This should be a subclass, e.g. CitoidReferenceContextItem
	 */

	/**
	 * Get the href associated with this reference if it is a plain link reference
	 *
	 * @param {ve.dm.InternalItemNode} itemNode Reference item node
	 * @return {string|null} Href, or null if this isn't a plain link reference
	 */
	ve.ui.MWReferenceContextItem.static.getConvertibleHref = function ( itemNode ) {
		var annotation, contentNode,
			doc = itemNode.getRoot().getDocument(),
			range = itemNode.getRange(),
			// Get covering annotations
			annotations = doc.data.getAnnotationsFromRange( range, false );

		// The reference consists of one single external link so
		// offer the user a conversion to citoid-generated reference
		if (
			annotations.getLength() === 1 &&
			( annotation = annotations.get( 0 ) ) instanceof ve.dm.MWExternalLinkAnnotation
		) {
			return annotation.getHref();
		} else if ( range.getLength() === 4 ) {
			contentNode = ve.getProp( itemNode, 'children', 0, 'children', 0 );
			if ( contentNode instanceof ve.dm.MWNumberedExternalLinkNode ) {
				return contentNode.getHref();
			}
		}
		return null;
	};

	origRenderBody = ve.ui.MWReferenceContextItem.prototype.renderBody;

	/**
	 * @inheritdoc
	 */
	ve.ui.MWReferenceContextItem.prototype.renderBody = function () {
		var convertButton, convertibleHref,
			contextItem = this,
			refNode = this.getReferenceNode();

		origRenderBody.call( this );

		if ( !refNode || this.isReadOnly() || !ve.ui.mwCitoidMap ) {
			return;
		}

		convertibleHref = this.constructor.static.getConvertibleHref( refNode );

		if ( convertibleHref ) {
			convertButton = new OO.ui.ButtonWidget( {
				label: ve.msg( 'citoid-referencecontextitem-convert-button' )
			} ).on( 'click', function () {
				var action = ve.ui.actionFactory.create( 'citoid', contextItem.context.getSurface() );
				action.open( true, convertibleHref );
			} );

			this.$body.append(
				$( '<div>' )
					.addClass( 've-ui-citoidReferenceContextItem-convert ve-ui-mwReferenceContextItem-muted' )
					.text( ve.msg( 'citoid-referencecontextitem-convert-message' ) )
			);

			if ( this.$foot ) {
				this.$foot.prepend( convertButton.$element );
			} else {
				this.$body.append( convertButton.$element );
			}
		}
	};

	// Add a "Replace reference" action to reference and citation dialogs
	function extendDialog( dialogClass ) {
		var getActionProcess = dialogClass.prototype.getActionProcess;
		dialogClass.prototype.getActionProcess = function ( action ) {
			if ( action === 'replace' ) {
				return new OO.ui.Process( function () {
					this.close( { action: action } ).closed.then( function () {
						var surface = this.getManager().getSurface();
						surface.execute( 'citoid', 'open', true );
					}.bind( this ) );
				}, this );
			}
			return getActionProcess.call( this, action );
		};
		// Clone the array, so that we don't add this action to some unrelated parent class
		dialogClass.static.actions = dialogClass.static.actions.concat( {
			action: 'replace',
			label: OO.ui.deferMsg( 'citoid-action-replace' ),
			icon: 'quotes',
			modes: [ 'edit' ]
		} );
	}
	extendDialog( ve.ui.MWReferenceDialog );
	extendDialog( ve.ui.MWCitationDialog );

}() );
