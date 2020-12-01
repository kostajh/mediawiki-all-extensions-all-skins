( function( mw, $ ) {
	eucc.ui.CheckboxMultioptionTextWidget = function( cfg ) {
		eucc.ui.CheckboxMultioptionTextWidget.parent.call( this, cfg );

		this.text = cfg.contentText;

		this.$text = $( '<span>' )
			.addClass( 'eucc-checkbox-multioption-text-widget-text' )
			.html( this.text );

		this.$element
			.addClass( 'eucc-checkbox-multioption-text-widget' )
			.append( this.$text );
	};

	OO.inheritClass( eucc.ui.CheckboxMultioptionTextWidget, OO.ui.CheckboxMultioptionWidget );

	eucc.ui.CheckboxMultioptionTextWidget.prototype.getText = function() {
		return this.text;
	};

} ) ( mediaWiki, jQuery );
