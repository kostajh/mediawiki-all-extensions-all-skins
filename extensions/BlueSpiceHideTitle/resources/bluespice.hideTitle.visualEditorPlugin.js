bs.vec.registerComponentPlugin(
	bs.vec.components.META_DIALOG,
	function( component ) {
		return new bs.hidetitle.ui.plugin.MWMetaDialog( component );
	}
);