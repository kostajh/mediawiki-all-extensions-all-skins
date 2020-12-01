bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.ColorStyleTool = function( config ) {
	var pickerCfg, customColors;

	this.value = {};
	pickerCfg = {
		framed: false,
		icon: this.getIcon()
	};
	customColors = config.customColors ||  bs.vec.config.get( 'ColorPickerColors' );
	if ( customColors && customColors.length > 0 ) {
		pickerCfg.colors = customColors;
	}
	this.colorPicker = new OOJSPlus.ui.widget.ColorPickerWidget( pickerCfg );
	bs.vec.ui.ColorStyleTool.parent.call( this, config );

	this.colorPicker.connect( this, {
		clear: 'onPickerClear',
		colorSelected: 'onPickerColorSelected',
		togglePicker: 'onPickerToggle'
	} );
	this.disconnect( this, {
		click: 'annotate'
	} );
	this.$element = this.colorPicker.$element;
};

OO.inheritClass( bs.vec.ui.ColorStyleTool, bs.vec.ui.ButtonStyleTool );

bs.vec.ui.ColorStyleTool.prototype.getName = function() {
	return "textStyle/color";
};

bs.vec.ui.ColorStyleTool.prototype.getIcon = function() {
	return 'textColor';
};

bs.vec.ui.ColorStyleTool.prototype.getLabel = function() {
	return OO.ui.deferMsg( "bs-visualeditorconnector-text-style-tool-color-label" );
};

bs.vec.ui.ColorStyleTool.prototype.onPickerColorSelected = function( data ) {
	data = data || {};
	this.data = data;
	this.clearAnnotation();
	this.annotate();
};

bs.vec.ui.ColorStyleTool.prototype.onPickerClear = function() {
	this.clearAnnotation();
};

bs.vec.ui.ColorStyleTool.prototype.togglePicker = function( visible ) {
	if ( !this.colorPicker ) {
		return;
	}
	this.colorPicker.togglePicker( visible );
};

bs.vec.ui.ColorStyleTool.prototype.getData = function() {
	return this.data;
};

bs.vec.ui.ColorStyleTool.prototype.changeActive = function() {
	if ( this.isActive() ) {
		let annotations = this.fragment.getAnnotations().getAnnotationsByName( this.getName() );
		let annotation = annotations.get()[0];
		if ( !annotation.element || !annotation.element.hasOwnProperty( 'attributes' ) ) {
			// Should be impossible, but just in case
			return;
		}
		let attr = annotation.element.attributes;
		this.value = attr;
		if ( this.colorPicker ) {
			this.colorPicker.setValue( attr );
		}
	} else {
		this.colorPicker.setValue( {} );
	}
};


bs.vec.ui.ColorStyleTool.prototype.getMethod = function() {
	return 'set';
};

bs.vec.ui.ColorStyleTool.prototype.onPickerToggle = function( visible ) {
	var $mainPopup = this.$element.parents( '.oo-ui-popupWidget-popup' );
	if ( visible ) {
		$mainPopup.css( 'overflow', 'visible' );
	} else {
		$mainPopup.css( 'overflow', 'hidden' );
	}
};
