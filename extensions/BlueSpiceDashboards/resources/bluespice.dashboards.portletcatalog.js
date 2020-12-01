if ( mw.config.get('bsPortalConfigLocation') !== null ) {
	var imageUrl = mw.config.get( "wgScriptPath" ) + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-btn_add.png';

	$origHeading = $( 'h1.firstHeading' );
	$addButton = $( '<span>' )
		.attr( 'id', 'bs-dashboard-add' )
		.attr( 'style', 'background-image: url(' + imageUrl + ')' )
		.on( 'click', function() {
			Ext.require( 'BS.Dashboards.PortletCatalog', function() {
				BS.Dashboards.PortletCatalog.show();
			} );
		} );
	$origHeading.append( $addButton );
}