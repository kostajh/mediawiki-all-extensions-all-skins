bs.social.EntityEditorProfile = function ( config, entity ) {
	this.visualEditor = false;
	bs.social.EntityEditor.call( this, config, entity );
};

OO.initClass( bs.social.EntityEditorProfile );
OO.inheritClass( bs.social.EntityEditorProfile, bs.social.EntityEditor );

bs.social.EntityEditorProfile.prototype.makeFields = function() {
	var fields = bs.social.EntityEditorProfile.super.prototype.makeFields.apply(
		this
	);
	for( var field in bs.social.config.profile.ProfileFieldsDefinitions ) {
		var userId = mw.config.get( 'wgUserId', 0 );
		if( userId < 1 || userId !== this.getEntity().data.get( 'ownerid' ) ) {
			continue;
		}
		var definition = bs.social.config.profile.ProfileFieldsDefinitions[field];
		if( definition.hidden ) {
			continue;
		}
		this[field] = new OO.ui.ButtonWidget( {
			label: mw.message( 'mypreferences' ).plain()
		});
		this[field].on( 'click', function() {
			var url = mw.util.getUrl(
				'Special:Preferences#mw-prefsection-personal'
			);
			var win = window.open( url, '_blank' );
			win.focus();
			return false;
		});
	}
	for( var field in bs.social.config.profile.ProfileCustomFieldsDefinitions ) {
		var definition = bs.social.config.profile.ProfileCustomFieldsDefinitions[field];
		var actions = this.getEntity().data.get( 'actions' );
		var hiddenedit = false;
		for( var i = 0; i < actions.length; i++ ) {
			if( actions[i] !== 'edithiddenfields' ) {
				continue;
			}
			hiddenedit = true;
		}
		if( definition.hidden && !hiddenedit ) {
			continue;
		}

		switch( definition.type || 'string' ) {
			case 'list':
				var items = [];
				var value = this.getEntity().data.get(
					field,
					definition.default || false
				);
				for( var i = 0; i < definition.options.length; i++ ) {
					items.push( {
						id: definition.options[i],
						text: definition.options[i],
						selected: value === definition.options[i]
					});
				}
				this[field] = {
					setElementGroup: function(){}
				};
				this[field].$element = $(
					'<label>'
					+ this.getVarLabel( field )
					+ '<select style="width:100%"></select>'
					+ '</label>'
				);
				this[field].select2 = this[field].$element.find( 'select' ).select2({
					multiple: true,
					data: items,
					placeholder: this.getVarLabel( field ),
					allowClear: !definition.required
				});

				//this[field].$element.find( 'select' ).select2( "val", value );
				break;
			case 'boolean':
				this[field] = new OO.ui.CheckboxInputWidget( {
					placeholder: this.getVarLabel( field ),
					value: this.getEntity().data.get(
						field,
						definition.default || false
					),
					reqired: definition.required
				});
				break;
			case 'integer':
				this[field] = new OO.ui.NumberInputWidget( {
					placeholder: this.getVarLabel( field ),
					value: this.getEntity().data.get(
						field,
						definition.default || 0
					),
					reqired: definition.required
				});
				break;
			case 'string':
			case 'select':
			default:
				if( definition.options ) {
					var items = [];
					var value = this.getEntity().data.get(
						field,
						definition.default || false
					);
					for( var i = 0; i < definition.options.length; i++ ) {
						items.push( {
							id: definition.options[i],
							text: definition.options[i],
							selected: value === definition.options[i]
						});
					}
					this[field] = {
						setElementGroup: function(){}
					};
					this[field].$element = $(
						'<label>'
						+ this.getVarLabel( field )
						+ '<select style="width:100%"></select>'
						+ '</label>'
					);
					this[field].select2 = this[field].$element.find( 'select' ).select2({
						multiple: false,
						data: items,
						placeholder: this.getVarLabel( field ),
						allowClear: !definition.required
					});
				} else {
					this[field] = new OO.ui.TextInputWidget( {
						placeholder: this.getVarLabel( field ),
						value: this.getEntity().data.get(
							field,
							definition.default || ''
						),
						reqired: definition.required
					});
				}
		}
		fields[field] = this[field];
	}
	return fields;
};
bs.social.EntityEditorProfile.prototype.addContentFieldsetItems = function() {
	for( var field in bs.social.config.profile.ProfileFieldsDefinitions ) {
		var definition = bs.social.config.profile.ProfileFieldsDefinitions[field];
		if( definition.hidden ) {
			continue;
		}
		var userId = mw.config.get( 'wgUserId', 0 );
		if( userId < 1 || userId !== this.getEntity().data.get( 'ownerid' ) ) {
			continue;
		}
		this.contentfieldset.addItems( [
			new OO.ui.FieldLayout( this[field], {
				label: this.getVarLabel( field ),
				align: 'top'
			} )
		]);
	}
	for( var field in bs.social.config.profile.ProfileCustomFieldsDefinitions ) {
		if( !this[field] ) {
			continue;
		}
		var definition = bs.social.config.profile.ProfileCustomFieldsDefinitions[field];
		if( definition.type === "list" || definition.options ) {
			this.contentfieldset.addItems([this[field]]);
			continue;
		}
		this.contentfieldset.addItems( [
			new OO.ui.FieldLayout( this[field], {
				label: this.getVarLabel( field ),
				align: 'top'
			} )
		]);
	}
	bs.social.EntityEditorProfile.super.prototype.addContentFieldsetItems.apply(
		this
	);
};

bs.social.EntityEditorProfile.prototype.getShortModeField = function() {
	return null;
};
