bs.social.EntityEditor = function ( config, entity ) {
	var me = this;
	config.items = config.items || [];
	config.action = '#';
	config.method = '';
	config.shortMode = config.shortMode || false;

	me.entity = entity;
	me.fields = me.makeFields();
	// Example of a form layout that wraps a fieldset layout
	me.submit = me.makeSubmit();
	me.submit.on( 'click', me.onBtnOKClick.bind( this ) );
	me.cancel = me.makeCancel();
	me.cancel.on( 'click', me.onBtnCancelClick.bind( this ) );
	me.settings = me.makeSettings();
	me.settings.on( 'click', me.onBtnSettingsClick.bind( this ) );
	me.contentfieldset = me.makeContentFieldset();
	me.advancedfieldset = me.makeAdvancedFieldset();
	me.actionfieldset = me.makeActionFieldset();
	me.shortMode = config.shortMode;

	me.addContentFieldsetItems();
	me.addAdvancedFieldsetItems();
	me.addActionFieldsetItems();

	for ( var i in me.fields ) {
		me.passFieldChange( me.fields[i] );
	}

	config.items.push( me.contentfieldset );
	me.advancedfieldset.$element.hide();
	if( this.advancedfieldset.items.length > 0 ) {
		config.items.push( me.advancedfieldset );
		me.settings.setDisabled( false );
	}
	config.items.push( me.actionfieldset );

	OO.ui.FormLayout.call( this, config, entity );

	var shortField = me.getShortModeField();
	if( me.shortMode && shortField ) {
		me.makeShortEditor( shortField );
	}

};
OO.initClass( bs.social.EntityEditor );
OO.inheritClass( bs.social.EntityEditor, OO.ui.FormLayout );

bs.social.EntityEditor.prototype.getEntity = function() {
	return this.entity;
};

bs.social.EntityEditor.prototype.makeShortEditor = function( shortField, reverse, placeholder ) {
	var reverse = reverse || false;
	var placeholder = placeholder || '';
	var me = this;
	for( var i in me.fields ) {
		if( me.fields[i] === shortField ) {
			if( !reverse ) {
				placeholder = shortField.$input.attr( 'placeholder' );
				if( placeholder && placeholder.length > 0 ) {
					
				}
				shortField.$input.attr(
					'placeholder',
					me.getEntity().data.get( 'header' )
				);
				shortField.$input.on( 'focusin', function( e ) {
					me.makeShortEditor( shortField, true, placeholder );
					$(this).off( e );
				});
			} else if ( placeholder != '' ) {
				shortField.$input.attr( 'placeholder', placeholder );
			}
			continue;
		}
		if( !reverse && me.fields[i].hide ) {
			me.fields[i].hide();
		} else if( !reverse && me.fields[i].$element ) {
			me.fields[i].$element.hide();
		}
		if( reverse && me.fields[i].show ) {
			me.fields[i].show();
		} else if( reverse && me.fields[i].$element ) {
			me.fields[i].$element.show();
		}
	}
	for( var i = 0; i < me.contentfieldset.items.length; i++ ) {
		if( !me.contentfieldset.items[i].$label ) {
			continue;
		}
		if( !reverse ) {
			me.contentfieldset.items[i].$label.hide();
		} else {
			me.contentfieldset.items[i].$label.show();
		}
	}
	if( !reverse ) {
		me.actionfieldset.$element.hide();
		me.$element.addClass( 'short' );
	} else {
		me.actionfieldset.$element.show();
		me.$element.removeClass( 'short' );
	}
};

bs.social.EntityEditor.prototype.onBtnOKClick = function () {
	var me = this;
	this.getData().done( function( data ) {
		me.emit( 'submit', me, data );
	});
};
bs.social.EntityEditor.prototype.onBtnCancelClick = function () {
	var shortField = this.getShortModeField();
	if( this.shortMode && shortField ) {
		this.makeShortEditor( shortField );
	}
	this.emit( 'cancel', this, this.getData() );
};
bs.social.EntityEditor.prototype.onBtnSettingsClick = function () {
	this.advancedfieldset.$element.toggle();
	this.emit( 'settings', this, this.getData() );
};

bs.social.EntityEditor.prototype.makeContentFieldset = function() {
	return new OO.ui.FieldsetLayout({});
};

bs.social.EntityEditor.prototype.makeAdvancedFieldset = function() {
	return new OO.ui.FieldsetLayout({
		//label: mw.message( 'bs-social-editor-advanced' ).plain()
	});
};

bs.social.EntityEditor.prototype.makeActionFieldset = function() {
	return new OO.ui.FieldsetLayout({
		align: 'right'
	});
};

bs.social.EntityEditor.prototype.makeFields = function() {
	return {};
};

bs.social.EntityEditor.prototype.makeSubmit = function() {
	return new OO.ui.ButtonInputWidget( {
		label: mw.message( 'bs-social-editor-ok' ).plain(),
		flags: ['progressive', 'primary'],
		align: 'right'
	});
};
bs.social.EntityEditor.prototype.makeCancel = function() {
	return new OO.ui.ButtonInputWidget( {
		label: mw.message( 'bs-social-editor-cancel' ).plain(),
		align: 'right'
	});
};
bs.social.EntityEditor.prototype.makeSettings = function() {
	return new OO.ui.ButtonWidget( {
		icon: 'settings',
		iconOnly: true,
		disabled: true,
		framed: false
	});
};

bs.social.EntityEditor.prototype.addContentFieldsetItems = function() {
	this.contentfieldset.addItems( [
		this.submit
	]);
};

bs.social.EntityEditor.prototype.addAdvancedFieldsetItems = function() {
	$(document).trigger( 'BSSocialEntityEditorAdvancedFieldset', [
		this,
		this.advancedfieldset
	]);
};

bs.social.EntityEditor.prototype.addActionFieldsetItems = function() {
	this.actionfieldset.addItems( [
		this.settings,
		this.cancel,
		this.submit
	]);
	$(document).trigger( 'BSSocialEntityEditorActionFieldset', [
		this,
		this.actionfieldset
	]);
};

bs.social.EntityEditor.prototype.getShortModeField = function() {
	return null;
};

bs.social.EntityEditor.prototype.getData = function() {
	var dfd = $.Deferred();
	var dfds = [];

	var data = {};
	for( var i in this.fields ) {
		//own implementations
		if( this.fields[i].getSelectedValue ) {
			data[i] = this.fields[i].getSelectedValue();
			continue;
		}
		//oojs ui checkboxes
		if( this.fields[i].isSelected ) {
			data[i] = this.fields[i].isSelected();
			continue;
		}
		//oojs ui text fields
		if( this.fields[i].getValue ) {
			var maybeDfd = this.fields[i].getValue();
			// maybeDfd is undefined when field is not set up yet
			if ( typeof maybeDfd === "undefined" ) {
				continue;
			}
			if( maybeDfd.done ) {
				dfds.push( maybeDfd );
				var fieldIndex = i;
				maybeDfd.done( function( value ) {
					data[fieldIndex] = value;
				} );
			}
			else {
				data[i] = maybeDfd;
			}

			continue;
		}
		//select2 jquery lib
		if( this.fields[i].select2 ) {
			data[i] = this.fields[i].$element.find('select').select2( "val" );
		}
	}

	if( dfds.length > 0 ) {
		$.when.apply( $, dfds ).then( function() {
			dfd.resolve( data );
		});
	}
	else {
		dfd.resolve( data );
	}

	return dfd;
};

bs.social.EntityEditor.prototype.appendTo = function($content) {
	this.$element.insertAfter( $content );
	for( var i in this.fields ) {
		if( !this.fields[i].init ) {
			continue;
		}
		this.fields[i].init();
	}
};

bs.social.EntityEditor.prototype.getVarLabel = function( varType ) {
	var varKeys = mw.config.get( 'bsgSocialVarMessageKeys', {} );
	return varKeys[varType] ? mw.message( varKeys[varType] ).plain() : varType;
};

bs.social.EntityEditor.prototype.onEditorStartup = function () {
	this.getEntity().showLoadMask();
};

bs.social.EntityEditor.prototype.onEditorStartupComplete = function () {
	this.getEntity().hideLoadMask();
};

bs.social.EntityEditor.prototype.passFieldChange = function ( field ) {
	var me = this;
	if (  typeof field !== 'object' ) {
		return;
	}
	if ( typeof field.on === 'function' ) {
		field.on( 'change', function() {
			me.emit( 'change', me, field );
		} );
	}
};
