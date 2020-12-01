mw.libs.ve.addPlugin( function() {
	/**
	 * Unfortunately when `VisualeditorPluginModules`, that are registered at
	 * the serverside are loaded, the base classes like
	 * `ve.ui.MWLinkAnnotationInspector` are not available yet.
	 *
	 * Therefore we use this plugin registration method (which actually was
	 * meant for Gadgets). It allows us to return a `Promise` object and makes
	 * VE wait until it is being resolved. This way we can wait for all kinds of
	 * other modules!
	 */
	var dfd = $.Deferred();

	//Step 1: Load the tags classes
	mw.loader.using( 'ext.bluespice.visualEditorConnector.tags.classes' )
		.done( function() {
			mw.loader.using( 'ext.visualEditor.mwcore' ).done( function() {
				//Keep in sync with `extension.json/ResourceModules/ext.bluespice.visualEditorConnector.tags.classes/scripts`
				var tagRegistry = new bs.vec.util.tag.Registry();

				//Step 2: Load all plugin modules that may want to register to
				//those classes
				bs.vec.config.load().done( function( config ) {
					var bsvecTagDefinitions = config.get( 'TagDefinitions' );
					if( bsvecTagDefinitions.length === 0 ) {
						dfd.resolve();
					}

					mw.loader.using( bsvecTagDefinitions ).done( function() {
						//Step 3: There is no step three
						tagRegistry.initialize().done( function(){
							dfd.resolve();
						});
					} );
				} );
			} );
	} );

	return dfd.promise();
} );

(function( mw, $, bs ){
	var tagDefinitions = [];

	/**
	 *
	 * @param bs.vec.util.TagDefinition tagDefinition
	 * @returns undefined
	 */
	function registerTagDefinition( tagDefinition ) {
		tagDefinitions.push( tagDefinition );
	}

	/**
	 *
	 * @returns array of callbacks
	*/
	function getTagDefinitions() {
		return tagDefinitions;
	}

	bs.util.registerNamespace( 'bs.vec' );
	bs.vec.registerTagDefinition = registerTagDefinition;
	bs.vec.getTagDefinitions = getTagDefinitions;
})( mediaWiki, jQuery, blueSpice );
