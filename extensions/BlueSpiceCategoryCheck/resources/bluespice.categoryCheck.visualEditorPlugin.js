bs.vec.registerComponentPlugin(
	bs.vec.components.SAVE_DIALOG,
	function( component ) {
		var bsgCategoryCheckNamespaces =
			mw.config.get( 'bsgCategoryCheckNamespaces', [] );
		var currentNamespace = mw.config.get( 'wgNamespaceNumber' );

		if( bsgCategoryCheckNamespaces.indexOf( currentNamespace ) === -1 ) {

			//Base class that does nothing
			return new bs.vec.ui.plugin.MWSaveDialog( component );
		}

		return new bs.catgrychk.ui.plugin.MWSaveDialog ( component );
	}
);
