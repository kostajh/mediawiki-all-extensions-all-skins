var orig = ve.ui.TableAction.prototype.create;

ve.ui.TableAction.prototype.create = function ( options ) {
	var optionsDialog = new bs.vec.ui.dialog.CreateTableOptions();

	var windowManager = new OO.ui.WindowManager();
	$( document.body ).append( windowManager.$element );
	windowManager.addWindows( [ optionsDialog ] );
	windowManager.openWindow( optionsDialog ).closed.done( function( data ) {
		if ( !data ) {
			return;
		}
		if ( !data.hasOwnProperty( 'action' ) ) {
			return;
		}
		if ( data.action !== 'save' ) {
			return;
		}
		options.cols = parseInt( data.cols );
		options.rows = parseInt( data.rows );
		return orig.call( this, options );
	}.bind( this ) );
};