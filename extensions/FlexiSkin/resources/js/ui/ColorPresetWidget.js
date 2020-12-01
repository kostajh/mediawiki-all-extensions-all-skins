flexiskin.ui.ColorPresetWidget = function ( cfg ) {
	flexiskin.ui.ColorPresetWidget.parent.apply( this );

	/* Actual layouts of fields */
	this.customColorLayouts = [];
	/* List of itemId => widget instance pairs */
	this.colorItems = {};
	/* Available color presets */
	this.colorPresets = mw.config.get( 'wgFlexiSkinColorPresets' );

	this.$input.remove();
	this.selectedPreset = null;
	this.initialized = false;
	this.listenToControlChanges = false;
	this.customInitial = null;
};

OO.inheritClass( flexiskin.ui.ColorPresetWidget, OO.ui.InputWidget );

flexiskin.ui.ColorPresetWidget.static.tagName = 'div';

flexiskin.ui.ColorPresetWidget.prototype.init = function () {
	this.initPresets();
	this.createCustomSection();

	// Do initial set
	if ( this.selectedPreset ) {
		this.presetSelector.selectItemByData( this.selectedPreset );
	} else if ( this.customInitial === true ) {
		this.showCustomColorsPanel( this.getColorsFromControls(), true );
	}

	this.initialized = true;
};

flexiskin.ui.ColorPresetWidget.prototype.initPresets = function () {
	var options = [];
	for ( var key in this.colorPresets ) {
		if ( !this.colorPresets.hasOwnProperty( key ) ) {
			continue;
		}

		var optionItem = new flexiskin.ui.ColorPresetOptionWidget( {
			data: key,
			label: key,
			colors: this.colorPresets[ key ],
			editable: true
		} );
		optionItem.connect( this, {
			customize: 'showCustomColorsPanel'
		} );

		options.push( optionItem );
	}

	this.presetSelector = new OO.ui.RadioSelectWidget( {
		items: options
	} );

	this.presetSelector.connect( this, {
		select: this.onPresetSelected
	} );

	this.$element.append( this.presetSelector.$element );
};

flexiskin.ui.ColorPresetWidget.prototype.createCustomSection = function () {
	/* Wrap all color controls into a div that we can control from here */
	this.$wrapper = $( '<div>' ).addClass( 'flexiskin-ui-color-presets-wrapper' );
	this.customColorPalette = new flexiskin.ui.ColorPalleteWidget( {
		colors: {}
	} );
	this.$wrapper.append( new OO.ui.HorizontalLayout( {
		items: [
			new OO.ui.LabelWidget( {
				label: mw.message( 'flexiskin-ui-plugin-color-presets-custom-header' ).text(),
				classes: [ 'flexiskin-ui-color-presets-custom-header' ]
			} ),
			this.customColorPalette
		]
	} ).$element );

	this.$wrapper.append( this.customColorLayouts );
	this.$element.append( this.$wrapper );
	/* Color controls are hidden by default, shown only on "custom" preset"  */
	this.$wrapper.hide();
};

flexiskin.ui.ColorPresetWidget.prototype.getValue = function() {
	return this.selectedPreset;
};

flexiskin.ui.ColorPresetWidget.prototype.setValue = function ( value ) {
	if ( !value ) {
		return;
	}
	if ( value === 'custom' ) {
		// Mark initialization to "custom"
		this.customInitial = true;
		return;
	}
	this.selectedPreset = value;
	if ( value && this.presetSelector ) {
		this.presetSelector.selectItemByData( value );
	}
};

flexiskin.ui.ColorPresetWidget.prototype.addCustomColorLayout = function ( layout ) {
	this.customColorLayouts.push( layout.$element );
};

flexiskin.ui.ColorPresetWidget.prototype.setColorItems = function ( items ) {
	if ( items.hasOwnProperty( 'colors/color_presets/preset' ) ) {
		// Remove this control (self-remove)
		delete ( items[ 'colors/color_presets/preset' ] );
	}
	this.colorItems = items;
	for ( var key in this.colorItems ) {
		if ( !this.colorItems.hasOwnProperty( key ) ) {
			continue;
		}
		this.colorItems[ key ].connect( this, {
			change: 'onColorItemChange'
		} );
	}
};

flexiskin.ui.ColorPresetWidget.prototype.updateControls = function ( presetValues ) {
	if ( !presetValues && !this.selectedPreset ) {
		return null;
	}
	if ( !presetValues ) {
		presetValues = this.colorPresets[ this.selectedPreset ];
	}
	for ( var key in presetValues ) {
		if ( !presetValues.hasOwnProperty( key ) ) {
			continue;
		}
		if ( this.colorItems.hasOwnProperty( key ) ) {
			this.colorItems[ key ].setValue( presetValues[ key ] );
		}
	}
};

flexiskin.ui.ColorPresetWidget.prototype.getColorsFromControls = function () {
	var values = {};
	for ( var key in this.colorItems ) {
		if ( !this.colorItems.hasOwnProperty( key ) ) {
			continue;
		}
		values[ key ] = this.colorItems[ key ].getValue();
	}

	return values;
};

flexiskin.ui.ColorPresetWidget.prototype.onPresetSelected = function ( selected ) {
	if ( !selected ) {
		return;
	}

	if ( this.customInitial && this.isCustomDirty( this.customInitial ) ) {
		OO.ui.confirm( mw.message( 'flexiskin-ui-plugin-color-presets-custom-dirty-warning' ).text() )
			.done( function ( confirmed ) {
				if ( confirmed ) {
					return this.doSelectPreset( selected );
				}
				this.presetSelector.unselectItem( this.presetSelector.findSelectedItem() );
			}.bind( this ) );
	} else {
		this.doSelectPreset( selected );
	}
};

flexiskin.ui.ColorPresetWidget.prototype.doSelectPreset = function ( selected ) {
	var presetName = selected.getData();

	this.listenToControlChanges = false;
	this.customInitial = null;
	// Hide custom controls
	this.$wrapper.hide();
	this.selectedPreset = presetName;
	this.updateControls();
};

flexiskin.ui.ColorPresetWidget.prototype.showCustomColorsPanel = function( colors, isFromInit ) {
	this.selectedPreset = 'custom';

	this.presetSelector.unselectItem( this.presetSelector.findSelectedItem() );
	this.listenToControlChanges = true;
	if ( !isFromInit ) {
		// If this is called for initialization, we dont want to check for dirty, every change
		// to a preset should be considered a "dirty" change
		this.customInitial = colors;
	}
	this.customColorPalette.updateColors( colors );
	this.$wrapper.show();
};

flexiskin.ui.ColorPresetWidget.prototype.onColorItemChange = function () {
	if ( this.listenToControlChanges ) {
		this.customColorPalette.updateColors( this.getColorsFromControls() );
	}
};

flexiskin.ui.ColorPresetWidget.prototype.isCustomDirty = function ( testSet ) {
	if ( testSet === true ) {
		return testSet;
	}
	var controlColors = this.getColorsFromControls();
	for ( var key in testSet ) {
		if ( !testSet.hasOwnProperty( key ) ) {
			continue;
		}
		if ( controlColors.hasOwnProperty( key ) && controlColors[ key ] !== testSet[ key ] ) {
			return true;
		}
	}

	return false;
};
