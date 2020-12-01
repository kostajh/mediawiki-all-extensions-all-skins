bs.vec.registerComponentPlugin(
	bs.vec.components.MEDIA_DIALOG,
	function( component ) {
		return new bs.insertfile.ui.plugin.MWMediaDialog( component );
	}
);