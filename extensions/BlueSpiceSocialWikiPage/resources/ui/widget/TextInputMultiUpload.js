bs = bs || {};
bs.ui = bs.ui || {};
bs.ui.widget = bs.ui.widget || {};

bs.ui.widget.TextInputMultiUpload = function ( config ) {
	OO.ui.InputWidget.call( this, config );
	var me = this;
	me.$element.find( 'input' ).remove();
	me.$element.addClass( 'bs-social-entity-input-multiupload' );
};
OO.initClass( bs.ui.widget.TextInputMultiUpload );
OO.inheritClass( bs.ui.widget.TextInputMultiUpload, OO.ui.InputWidget );

bs.ui.widget.TextInputMultiUpload.prototype.init = function() {
	var me = this;
	var id = 'bs-textinput-multiupload-' + bs.social.generateUniqueId();
	var $field = $(
		'<div class="uploadfield" id="' + id + '" />'
	);
	me.$element.append( $field );
	mw.loader.using( [ 'ext.bluespice.upload' ] ).done( function () {
		function _showDialog( upldr, files ) {
			upldr.disableBrowse( true );
			mw.loader.using( 'ext.bluespice.extjs.upload', function() {
				Ext.require( 'BS.dialog.MultiUpload', function () {
					var mud = new BS.dialog.MultiUpload( {
						uploader: upldr,
						files: files
					} );
					mud.on( 'uploadcomplete', me.onUploadComplete, me ),
					mud.show();
				} );
			} );
		};

		var uploader = bs.upload.makeUploader( {
			drop_element: id,
			browse_button: id,
			dragdrop: true
		} );
		uploader.bind( 'FilesAdded', _showDialog );

		$( '#' + id ).html(
			mw.message( 'bs-uploader-drop-or-click' ).text()
		);
	} );

	bs.ui.widget.TextInputMultiUpload.prototype.onUploadComplete = function( uploader, data ) {
		var files = [];
		for( var i = 0; i < data.length; i++ ) {
			//TODO: check status somehow!
			files.push( data[i].uploadApiMeta.filename );
		}
		this.emit( 'change', this, { files: files } );
	};
};
