flexiskin.ui.ColorPresetOptionWidget = function ( cfg ) {
	flexiskin.ui.ColorPresetOptionWidget.parent.call( this, cfg );

	this.palette = new flexiskin.ui.ColorPalleteWidget( cfg );

	this.$element.addClass( 'flexiskin-color-preset-option' ).append(
		this.palette.$element
	);

	if ( cfg.editable ) {
		this.editButton = new OO.ui.ButtonWidget( {
			title: mw.message( 'flexiskin-ui-plugin-color-presets-customize-label' ).text(),
			icon: 'edit',
			framed: false
		} );

		this.editButton.connect( this, {
			click: function () {
				this.emit( 'customize', this.palette.getColors() );
			}
		} );

		this.$element.append( this.editButton.$element );
	}

};

OO.inheritClass( flexiskin.ui.ColorPresetOptionWidget, OO.ui.RadioOptionWidget );
