bs.vec.registerComponentPlugin(
	bs.vec.components.MEDIA_DIALOG,
	function( component ) {
		var options = {
			currentNamespace: mw.config.get( 'wgNamespaceNumber' ),
			invalidNamespaces: bs.nsfrc.getInvalidFileNamespacesForEditing(),
			formattedNamespaces: mw.config.get( 'wgFormattedNamespaces' )
		}

		return new bs.nsfrc.ui.plugin.MWMediaDialog( component, options );
	}
);