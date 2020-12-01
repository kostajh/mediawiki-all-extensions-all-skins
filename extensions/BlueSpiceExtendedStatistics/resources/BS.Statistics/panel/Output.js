Ext.define( 'BS.Statistics.panel.Output', {
	extend: 'Ext.panel.Panel',
	requires: [ 'BS.Statistics.panel.Chart', 'BS.Statistics.panel.List' ],
	title: mw.message( 'bs-statistics-panel-title-result' ).plain(),

	applyFilterSettings: function( filterSettings ) {
		this.setLoading( true );
		var me = this;
		bs.api.tasks.execSilent( "statistics", "getData", filterSettings )
		.done( function( result ) {
			me.removeAll();
			var childPanel = null;

			if( filterSettings.mode == 'list' ) {
				childPanel = new BS.Statistics.panel.List( {
					bsPayload: result.payload
				} );
			}
			else {
				childPanel = new BS.Statistics.panel.Chart( {
					bsPayload: result.payload
				} );
			}

			me.add( childPanel );
			me.setLoading( false );
		} );
	}
} );