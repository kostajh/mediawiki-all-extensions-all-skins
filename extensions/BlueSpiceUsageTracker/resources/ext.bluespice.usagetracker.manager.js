( function( mw, $, bs, d, undefined ){
	function _renderGrid() {
		Ext.onReady(function(){
			Ext.Loader.setPath(
				'BS.UsageTracker',
				mw.config.get( "wgScriptPath" ) + '/extensions/BlueSpiceUsageTracker/resources/BS.UsageTracker'
			);
			Ext.create( 'BS.UsageTracker.panel.Manager', {
				renderTo: 'bs-usagetracker-manager'
			});
		});
	}

	var deps = mw.config.get( 'bsUsageTrackerDeps', false );
	if( deps ) {
		mw.loader.using( deps, _renderGrid );
	}
	else {
		_renderGrid();
	}

})( mediaWiki, jQuery, blueSpice, document );