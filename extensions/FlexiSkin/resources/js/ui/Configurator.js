flexiskin.ui.Configurator = function ( cfg ) {
	cfg = cfg || {};

	this.skin = cfg.skin;
	this.active = this.skin.active || false;
	this.skin.config = this.skin.config || {};

	flexiskin.ui.Configurator.parent.call( this );

	this.$element.addClass( 'fs-configure' );

	this.$messageCnt = $( '<div>' );
	this.$element.append( this.$messageCnt );

	if ( !this.active && this.skin.id !== null ) {
		this.makeDisabledWarning();
	}
	this.makeButtons();
	this.initPlugins();
	this.setValue( this.skin.config );
	this.makeForm();

	setTimeout( function() {
		// Run on another event loop go
		this.emit( 'renderComplete', this );
	}.bind( this ), 1 );
};

OO.inheritClass( flexiskin.ui.Configurator, OO.ui.Widget );

flexiskin.ui.Configurator.static.tagName = 'div';
flexiskin.ui.Configurator.static.VISIBILITY_BASIC = 'basic';
flexiskin.ui.Configurator.static.VISIBILITY_ADVANCED = 'advanced';

flexiskin.ui.Configurator.prototype.initPlugins = function () {
	this.plugins = {};
	this.controls = {};
	this.items = {};
	for ( var pluginId in flexiskin.registry.Plugin.registry ) {
		if ( !flexiskin.registry.Plugin.registry.hasOwnProperty( pluginId ) ) {
			continue;
		}

		this.plugins[ pluginId ] = new flexiskin.registry.Plugin.registry[ pluginId ]();
		var flatlist = this.plugins[ pluginId ].getFlatList();
		for ( var id in flatlist ) {
			if ( !flatlist.hasOwnProperty( id ) ) {
				continue;
			}
			var callback = this.getCallbackFor( 'init', flatlist[ id ] );
			if ( callback ) {
				callback.call( flatlist[ id ], this, id );
			}
		}

		this.items = $.extend( true, {}, this.items, flatlist );
		this.controls = $.extend( true, {}, this.controls, this.plugins[ pluginId ].getControls() );
	}
};

flexiskin.ui.Configurator.prototype.makeDisabledWarning = function () {
	this.$element.append( new OO.ui.MessageWidget( {
		type: 'warning',
		label: mw.message( "flexiskin-ui-configurator-warning-disabled-skin" ).text()
	} ).$element );
};

flexiskin.ui.Configurator.prototype.makeForm = function () {
	var grouped = this.groupItems(),
		items = [];
	this.formElements = {};

	for ( var groupId in grouped ) {
		if ( !grouped.hasOwnProperty( groupId ) ) {
			continue;
		}
		var layouts = this.getGroupLayouts( grouped[ groupId ] );
		this.formElements[ groupId ] = new flexiskin.ui.ConfigGroup( groupId, {
			label: this.getItemGroupLabel( groupId ),
			expanded: this.getItemGroupExpanded( groupId ),
			items: layouts
		} );
		items.push( this.formElements[ groupId ].$element );
	}

	this.emit( 'makeFormComplete', this.formElements );
	this.$element.append( items );
};

flexiskin.ui.Configurator.prototype.getGroupLayouts = function ( items ) {
	var fieldLayouts = {},
		roots = [];

	for ( var id in items ) {
		if ( !items.hasOwnProperty( id ) ) {
			continue;
		}

		var groups = this.getGroupsForItem( id ),
			rootGroup;
		rootGroup = groups[ 0 ];
		// Remove root group since we already group by it
		if ( groups.length > 1 ) {
			groups.shift();
		}
		var lastGroup;
		for ( var i = 0; i < groups.length; i++ ) {
			if ( i === 0 ) {
				roots.push( groups[ i ] );
			}
			var layout = fieldLayouts[ groups[ i ] ] || new OO.ui.FieldsetLayout( {
				label: rootGroup && rootGroup === groups[ i ] ? '' : this.getItemGroupLabel( groups[ i ] ),
				data: {
					group: groups[ i ]
				}
			} );
			if ( i === ( groups.length - 1 ) ) {
				var data = items[ id ].getData(), label = '';
				if ( data && data.hasOwnProperty( 'label' ) ) {
					label = data.label;
				}
				layout.addItems( new OO.ui.FieldLayout( items[ id ], {
					label: label,
					align: 'top'
				} ) );
			}
			if ( !fieldLayouts.hasOwnProperty( groups[ i ] ) ) {
				fieldLayouts[ groups[ i ] ] = layout;
				if ( lastGroup && fieldLayouts.hasOwnProperty( lastGroup ) ) {
					fieldLayouts[ lastGroup ].addItems( [ layout ] );
				}
			}
			lastGroup = groups[ i ];
		}
	}

	for ( var layoutGroupId in fieldLayouts ) {
		if ( roots.indexOf( layoutGroupId ) === -1 ) {
			delete ( fieldLayouts[ layoutGroupId ] );
		}
	}
	return Object.values( fieldLayouts );
};

flexiskin.ui.Configurator.prototype.groupItems = function () {
	var grouped = {}, itemId, groups, root;

	for ( itemId in this.items ) {
		if ( !this.items.hasOwnProperty( itemId ) ) {
			continue;
		}
		groups = this.getGroupsForItem( itemId );
		root = groups.shift();

		if ( !grouped.hasOwnProperty( root ) ) {
			grouped[ root ] = {};
		}
		grouped[ root ][ itemId ] = this.items[ itemId ];
	}

	return grouped;
};

flexiskin.ui.Configurator.prototype.getGroupsForItem = function ( id ) {
	var bits = id.split( '/' );
	bits.pop();
	if ( bits.length === 0 ) {
		return [];
	}

	return bits;
};

flexiskin.ui.Configurator.prototype.getVisibilityForItem = function ( id ) {
	var widget = this.items[ id ],
	 data = widget.getData();
	if ( data && data.hasOwnProperty( 'visibility' ) ) {
		return data.visibility;
	}
	return flexiskin.ui.Configurator.static.VISIBILITY_ADVANCED;
};

flexiskin.ui.Configurator.prototype.getItems = function ( group ) {
	if ( group ) {
		var items = {};
		for ( var id in this.items ) {
			if ( !this.items.hasOwnProperty( id ) ) {
				continue;
			}
			if ( !id.startsWith( group + '/' ) ) {
				continue;
			}
			items[ id ] = this.items[ id ];
		}

		return items;
	}

	return this.items;
};

flexiskin.ui.Configurator.prototype.getItemGroupLabel = function ( group ) {

	var item = this.findGroupByKey( this.controls, group );

	if ( item && item.hasOwnProperty( 'label' ) ) {
		return item.label;
	}

	return '';
};

flexiskin.ui.Configurator.prototype.getItemGroupExpanded = function ( group ) {
	var item = this.findGroupByKey( this.controls, group );

	if ( item && item.hasOwnProperty( 'expanded' ) ) {
		return !!item.expanded;
	}

	return false;
};

flexiskin.ui.Configurator.prototype.findGroupByKey = function ( obj, key ) {
	var keys = Object.keys( obj ),
		value;

	for ( var i = 0; i < keys.length; i++ ) {
		if ( keys[ i ] === key ) {
			return obj[ key ];
		} else if ( !( obj[ keys[ i ] ] instanceof OO.ui.Widget ) && $.type( obj[ keys[ i ] ] ) === 'object' ) {
			value = this.findGroupByKey( obj[ keys[ i ] ], key );
			if ( value ) {
				return value;
			}
		}
	}

	return value;
};

flexiskin.ui.Configurator.prototype.getValue = function ( forAction ) {
	var value = {},
		dfd = $.Deferred(),
		itemsToResolve = $.extend( {}, this.items );

	this.getValueForItems( dfd, value, itemsToResolve, forAction );

	return dfd.promise();
};

flexiskin.ui.Configurator.prototype.getValueForItems = function ( dfd, value, items, forAction ) {
	if ( $.isEmptyObject( items ) ) {
		return dfd.resolve( value );
	}
	var itemId = Object.keys( items ).shift(),
		item = items[ itemId ];
	delete ( items[ itemId ] );
	var callback = this.getCallbackFor( forAction, item );
	if ( callback ) {
		callback.call( item, {}, itemId )
			.done( function ( response ) {
				this.setToPath( value, itemId.split( '/' ), response );
				return this.getValueForItems( dfd, value, items, forAction );
			}.bind( this ) )
			.fail( function () {
				return this.getValueForItems( dfd, value, items, forAction );
			}.bind( this ) );
	} else {
		this.setToPath( value, itemId.split( '/' ), item.getValue() );
		return this.getValueForItems( dfd, value, items, forAction );
	}
};

flexiskin.ui.Configurator.prototype.setValue = function ( value ) {
	if ( !value ) {
		return;
	}

	this.value = value;

	for ( var itemId in this.items ) {
		var itemValue = this.getByPath( $.extend( true, {}, value ), itemId.split( '/' ) );
		if ( itemValue ) {
			var callback = this.getCallbackFor( 'setValue', this.items[ itemId ] );
			if ( callback ) {
				callback.call( this.items[ itemId ], itemValue );
			} else {
				this.items[ itemId ].setValue( itemValue );
			}
		}
	}
};

flexiskin.ui.Configurator.prototype.getByPath = function ( obj, path ) {
	var bit = path.shift();
	if ( $.type( obj ) !== 'object' || !obj.hasOwnProperty( bit ) ) {
		return null;
	}
	if ( path.length === 0 ) {
		return obj[ bit ];
	}
	obj = obj[ bit ];
	return this.getByPath( obj, path );
};

flexiskin.ui.Configurator.prototype.getCallbackFor = function ( action, item ) {
	var data = item.getData();
	if ( $.type( data ) !== 'object' ) {
		return null;
	}

	if ( !data.hasOwnProperty( 'actionCallback' ) ) {
		return null;
	}
	if ( !data.actionCallback.hasOwnProperty( action ) ) {
		return null;
	}

	return data.actionCallback[ action ];
};

flexiskin.ui.Configurator.prototype.setToPath = function ( obj, path, value, append ) {
	var bit = path.shift();
	if ( path.length === 0 ) {
		if ( append ) {
			obj[ bit ] = obj[ bit ] || [];
			obj[ bit ].push( value );
		} else {
			obj[ bit ] = value;
		}
		return obj;
	}
	obj[ bit ] = obj[ bit ] || {};
	this.setToPath( obj[ bit ], path, value, append );
};

flexiskin.ui.Configurator.prototype.makeButtons = function () {
	this.disableButton = new OO.ui.ButtonWidget( {
		label: mw.message( 'flexiskin-ui-configurator-button-disable-label' ).text(),
		flags: [
			'primary',
			'destructive'
		],
		disabled: !this.active
	} );
	this.previewButton = new OO.ui.ButtonWidget( {
		label: mw.message( 'flexiskin-ui-configurator-button-preview-label' ).text()
	} );

	this.saveButton = new OO.ui.ButtonWidget( {
		label: mw.message( 'flexiskin-ui-configurator-button-save-label' ).text(),
		flags: [
			'progressive',
			'primary'
		]
	} );

	this.disableButton.connect( this, { click: 'doDisable' } );
	this.previewButton.connect( this, { click: 'doPreview' } );
	this.saveButton.connect( this, { click: 'doSave' } );

	this.$element.append( new OO.ui.HorizontalLayout( {
		items: [ this.disableButton, this.previewButton, this.saveButton ],
		classes: [ 'fs-configurator-buttons' ]
	} ).$element );
};

flexiskin.ui.Configurator.prototype.doPreview = function () {
	this.clearPreview();

	var loadingDialog = new flexiskin.ui.dialog.PreviewLoading( { size: 'small' } ),
		windowManager = OO.ui.getWindowManager();
		windowManager.addWindows( [ loadingDialog ] );
		windowManager.openWindow( loadingDialog );
	this.getValue( 'preview' ).done( function ( value ) {
		new mw.Api().get( {
			action: 'flexiskin-preview',
			config: JSON.stringify( value ),
			_dc: new Date().getTime()
		} ).done( function ( response ) {
			if ( response.hasOwnProperty( 'loadData' ) ) {
				$.get( mw.util.wikiScript( 'load' ), {
					modules: response.loadData.modules.join( '|' ),
					vars: JSON.stringify( response.loadData.vars ),
					only: 'styles'
				} ).done( function ( data ) {
					// TODO: This will keep on adding new versions - not a big deal, but not nice
					$( 'style' ).append( data );
					windowManager.closeWindow( loadingDialog );
				} ).fail( function () {
					windowManager.closeWindow( loadingDialog );
					this.previewError();
				} );
			}
			windowManager.closeWindow( loadingDialog );
		} ).fail( function () {
			windowManager.closeWindow( loadingDialog );
			this.previewError();
		} );
	} );

};

flexiskin.ui.Configurator.prototype.doSave = function () {
	this.clearPreview();

	var confirmationMessage = mw.message( 'flexiskin-configurator-prompt-on-save' ).text();
	OO.ui.confirm( confirmationMessage, { size: 'large' } ).done( function ( confirmed ) {
		if ( confirmed ) {
			this.getValue( 'save' ).done( function ( value ) {
				new mw.Api().get( {
					action: 'flexiskin-save',
					config: JSON.stringify( value )
				} ).done( function ( response ) {
					if ( !response.hasOwnProperty( 'success' ) || !response.success ) {
						this.saveError();
						return;
					}
					this.debugReload();
				}.bind( this ) ).fail( function () {
					this.saveError();
				}.bind( this ) );
			}.bind( this ) );
		}
	}.bind( this ) );

};

flexiskin.ui.Configurator.prototype.doDisable = function() {
	var confirmationMessage = mw.message( 'flexiskin-configurator-prompt-on-disable' ).text();
	OO.ui.confirm( confirmationMessage, { size: 'large' } ).done( function ( confirmed ) {
		if ( confirmed ) {
			new mw.Api().get( {
				action: 'flexiskin-activation',
				active: 0
			} ).done( function ( response ) {
				if ( !response.hasOwnProperty( 'success' ) || !response.success ) {
					this.resetError();
					return;
				}
				this.debugReload();
			}.bind( this ) ).fail( function () {
				this.disableError();
			}.bind( this ) );
		}

	}.bind( this ) );
};

flexiskin.ui.Configurator.prototype.debugReload = function () {
	// Refresh to apply changes
	// Add debug=true param - works for cache, but breaks other things
	var url = new URL( window.location.href );
	url.searchParams.set( 'debug','true' );
	window.location.href = url.href;
};

flexiskin.ui.Configurator.prototype.clearPreview = function () {
	// TODO: Clear data from previous preview
};

flexiskin.ui.Configurator.prototype.previewError = function () {
	OO.ui.alert( mw.message( 'flexiskin-ui-error-preview-fail' ).text() );
};

flexiskin.ui.Configurator.prototype.saveError = function () {
	OO.ui.alert( mw.message( 'flexiskin-ui-error-save-fail' ).text() );
};

flexiskin.ui.Configurator.prototype.disableError = function () {
	OO.ui.alert( mw.message( 'flexiskin-ui-error-disable-fail' ).text() );
};
