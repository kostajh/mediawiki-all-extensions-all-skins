flexiskin.ui.dialog.PreviewLoading = function ( cfg ) {
	flexiskin.ui.dialog.PreviewLoading.super.call( this, cfg );
};

OO.inheritClass( flexiskin.ui.dialog.PreviewLoading, OO.ui.Dialog );

flexiskin.ui.dialog.PreviewLoading.static.name = 'fsPreviewLoading';

flexiskin.ui.dialog.PreviewLoading.prototype.initialize = function () {
	flexiskin.ui.dialog.PreviewLoading.parent.prototype.initialize.call( this );
	this.panel = new OO.ui.PanelLayout( {
		padded: true
	} );
	var label = new OO.ui.LabelWidget( {
			label: mw.message( 'flexiskin-ui-preview-loading-message' ).text()
		} ),
	 progress = new OO.ui.ProgressBarWidget();

	this.panel.$element.append( label.$element, progress.$element );

	this.$body.append( this.panel.$element );
};

flexiskin.ui.dialog.PreviewLoading.prototype.getBodyHeight = function () {
	return 100;
};
