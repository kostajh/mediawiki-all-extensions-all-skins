mw.loader.using( "ext.visualEditor.desktopArticleTarget.init" )
	.done( function() {
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
			//Step 1: Load the override classes
			mw.loader.using( 'ext.bluespice.visualEditorConnector.overrides.classes' )
				.done( function() {
					mw.loader.using( 'ext.visualEditor.mwcore' ).done( function() {
						ve.ui.windowFactory.register( bs.vec.ui.MWLinkAnnotationInspector );
						ve.ui.windowFactory.register( bs.vec.ui.MWMediaDialog );
						ve.ui.windowFactory.register( bs.vec.ui.MWSaveDialog );
						ve.ui.windowFactory.register( bs.vec.ui.MWTableDialog );
						ve.ui.windowFactory.register( bs.vec.ui.MWMetaDialog );
						ve.ui.toolFactory.register( bs.vec.ui.MWHelpPopupTool );

						//Step 2: Load all plugin modules that may want to register to
						//those classes
						bs.vec.config.load().done( function( config ) {
							var bsvecPluginModules = config.get( 'PluginModules' );
							if( bsvecPluginModules.length === 0 ) {
								dfd.resolve();
							}
							mw.loader.using( bsvecPluginModules ).done( function() {
								//Step 3: There is no step three
								dfd.resolve();
							} );
						} );
					} );
				} );

			return dfd.promise();
		} );
	} );


mw.loader.using( "ext.bluespice" )
	.done( function() {
		var bs = blueSpice;
		var componentPlugins = {};

		/**
		 *
		 * @param string componentKey
		 * @param callable pluginCallback
		 * @returns undefined
		 */
		function registerComponentPlugin( componentKey, pluginCallback ) {
			if( !componentPlugins[componentKey] ) {
				componentPlugins[componentKey] = [];
			}
			componentPlugins[componentKey].push( pluginCallback );
		}

		/**
		 *
		 * @param string componentKey
		 * @returns array of callbacks
		 */
		function getComponentPlugins( componentKey ) {
			if( !componentPlugins[componentKey] ) {
				componentPlugins[componentKey] = [];
			}

			return componentPlugins[componentKey];
		}

		bs.util.registerNamespace( 'bs.vec' );
		bs.vec.registerComponentPlugin = registerComponentPlugin;
		bs.vec.getComponentPlugins = getComponentPlugins;
		bs.vec.components = {
			LINK_ANNOTATION_INSPECTOR: 'link-annotation-inspector',
			MEDIA_DIALOG: 'media-dialog',
			SAVE_DIALOG: 'save-dialog',
			TABLE_DIALOG: 'table-dialog',
			META_DIALOG: 'meta-dialog'
		};
	} );
