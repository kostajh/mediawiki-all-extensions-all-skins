bs.util.registerNamespace( 'bs.vec.mixin' );

bs.vec.mixin.TableBorderStylePopup = function ( cfg ) {
	cfg = cfg || {};

	this.content = new OO.ui.PanelLayout( {
		expanded: false,
		framed: false,
		padded: false
	} );

	this.makeTriggers();
	this.$itemContainer = $( '<div>' ).addClass( 'item-container' );
	this.content.$element.append( this.$itemContainer );

	this.items = {};
	this.makeBorderPositions();
	this.makeBorderWidth();
	this.makeBorderStyle();
	this.makeBorderColor();

	this.connect( this, {
		toggleItem: 'onToggleItem'
	} );

	this.triggers.selectItemByData( 'position' );

	var popupCfg = $.extend( {
		width: '200px',
		padded: cfg.padded || false,
		autoClose: false,
		$autoCloseIgnore: this.content.$element,
		position: 'above'
	}, cfg.popup || {} );

	bs.vec.mixin.TableBorderStylePopup.parent.call( this, {
		popup: popupCfg
	} );
	OO.EventEmitter.call( this );

	this.$element.addClass( 'bs-vec-border-style' );
	this.popup.$body.append( this.content.$element );
};

OO.inheritClass( bs.vec.mixin.TableBorderStylePopup, OO.ui.mixin.PopupElement );
OO.mixinClass( bs.vec.mixin.TableBorderStylePopup, OO.EventEmitter );

bs.vec.mixin.TableBorderStylePopup.prototype.setValue = function( value ) {
	// NOOP
};

bs.vec.mixin.TableBorderStylePopup.prototype.getValue = function() {
	// NOOP
	return null;
};

bs.vec.mixin.TableBorderStylePopup.prototype.onToggleItem = function( item ) {
	if ( !this.items.hasOwnProperty( item.data )  ) {
		return;
	}
	this.$itemContainer.html( this.items[item.data].$element );

};

bs.vec.mixin.TableBorderStylePopup.prototype.makeTriggers = function() {
	var clearTrigger = new OO.ui.ButtonOptionWidget( {
		data: 'clear',
		icon: 'cancel',
		title: OO.ui.deferMsg( "bs-vec-table-border-clear-all" ),
		framed: false
	} );
	clearTrigger.connect( this, {
		click: function() {
			this.emit( 'change', { mode: 'clear', prop: null } );
		}
	} );

	this.triggers = new OO.ui.ButtonSelectWidget( {
		classes: [ 'border-triggers' ],
		items: [
			new OO.ui.ButtonOptionWidget( {
				data: 'position',
				icon: 'cellBorderAll',
				title: OO.ui.deferMsg( "bs-vec-table-border-borders" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'width',
				icon: 'cellBorderWidthGroup',
				title: OO.ui.deferMsg( "bs-vec-table-border-width" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'style',
				icon: 'cellBorderStyleGroup',
				title: OO.ui.deferMsg( "bs-vec-table-border-styles" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'color',
				icon: 'cellBorderColor',
				title: OO.ui.deferMsg( "bs-vec-table-border-color" ),
				framed: false
			} ),
			clearTrigger
		]
	} );

	this.triggers.connect( this, {
		select:	function( item ) {
			if ( !item ) {
				return;
			}
			this.onToggleItem( item );
		}
	} );

	this.content.$element.append( this.triggers.$element );

};

bs.vec.mixin.TableBorderStylePopup.prototype.makeBorderPositions = function() {
	this.positionWidget = new OO.ui.ButtonSelectWidget( {
		classes: [ 'grid-picker' ],
		items: [
			new OO.ui.ButtonOptionWidget( {
				data: 'none',
				icon: 'cellBorderNone',
				title: OO.ui.deferMsg( "bs-vec-table-border-none" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'left',
				icon: 'cellBorderLeft',
				title: OO.ui.deferMsg( "bs-vec-table-border-left" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'top',
				icon: 'cellBorderTop',
				title: OO.ui.deferMsg( "bs-vec-table-border-top" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'right',
				icon: 'cellBorderRight',
				title: OO.ui.deferMsg( "bs-vec-table-border-right" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'bottom',
				icon: 'cellBorderBottom',
				title: OO.ui.deferMsg( "bs-vec-table-border-bottom" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'all',
				icon: 'cellBorderAll',
				title: OO.ui.deferMsg( "bs-vec-table-border-all" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'leftright',
				icon: 'cellBorderLeftRight',
				title: OO.ui.deferMsg( "bs-vec-table-border-leftright" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'topbottom',
				icon: 'cellBorderTopBottom',
				title: OO.ui.deferMsg( "bs-vec-table-border-topbottom" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'round',
				icon: 'cellBorderRound',
				title: OO.ui.deferMsg( "bs-vec-table-border-round" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'horizontal',
				icon: 'cellBorderHorizontal',
				title: OO.ui.deferMsg( "bs-vec-table-border-horizontal" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'roundhorizontal',
				icon: 'cellBorderRoundHorizontal',
				title: OO.ui.deferMsg( "bs-vec-table-border-roundhorizontal" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'roundvertical',
				icon: 'cellBorderRoundVertical',
				title: OO.ui.deferMsg( "bs-vec-table-border-roundvertical" ),
				framed: false
			} )
		]
	} );
	this.positionWidget.connect( this, {
		select:	function( item ) {
			if ( !item ) {
				return;
			}

			this.emit( 'change', { mode: item.data, prop: { style: 'init' } } );
		}
	} );
	this.items.position = this.positionWidget;
};

bs.vec.mixin.TableBorderStylePopup.prototype.makeBorderWidth = function() {
	this.widthWidget =  new OO.ui.ButtonSelectWidget( {
		classes: [ 'grid-picker' ],
		items: [
			new OO.ui.ButtonOptionWidget( {
				data: '1px',
				icon: 'cellBorderWidth1',
				title: OO.ui.deferMsg( "bs-vec-table-border-width-1" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: '2px',
				icon: 'cellBorderWidth2',
				title: OO.ui.deferMsg( "bs-vec-table-border-width-2" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: '3px',
				icon: 'cellBorderWidth3',
				title: OO.ui.deferMsg( "bs-vec-table-border-width-3" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: '4px',
				icon: 'cellBorderWidth4',
				title: OO.ui.deferMsg( "bs-vec-table-border-width-4" ),
				framed: false
			} )
		]
	} );
	this.widthWidget.connect( this, {
		select:	function( item ) {
			if ( !item ) {
				return;
			}

			this.emit( 'change', { mode: 'all', prop: { width: item.data } } );
		}
	} );
	this.items.width = this.widthWidget;
};

bs.vec.mixin.TableBorderStylePopup.prototype.makeBorderStyle = function() {
	this.styleWidget =  new OO.ui.ButtonSelectWidget( {
		classes: [ 'grid-picker' ],
		items: [
			new OO.ui.ButtonOptionWidget( {
				data: 'solid',
				icon: 'cellBorderWidth1',
				title: OO.ui.deferMsg( "bs-vec-table-border-solid" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'dashed',
				icon: 'cellBorderStyleDashed',
				title: OO.ui.deferMsg( "bs-vec-table-border-dashed" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'dotted',
				icon: 'cellBorderStyleDotted',
				title: OO.ui.deferMsg( "bs-vec-table-border-dotted" ),
				framed: false
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'double',
				icon: 'cellBorderStyleDouble',
				title: OO.ui.deferMsg( "bs-vec-table-border-double" ),
				framed: false
			} )
		]
	} );
	this.styleWidget.connect( this, {
		select:	function( item ) {
			if ( !item ) {
				return;
			}
			this.emit( 'change', { mode: 'all', prop: { style: item.data } } );
		}
	} );
	this.items.style = this.styleWidget;
};

bs.vec.mixin.TableBorderStylePopup.prototype.makeBorderColor = function() {
	this.colorWidget = new OOJSPlus.ui.widget.ColorPickerEmbeddable( {
		colors: bs.vec.config.get( 'CellBorderColors' ) || []
	} );

	this.colorWidget.connect( this, {
		colorSelected: function( data ) {
			if ( !data.hasOwnProperty( 'code' ) ) {
				return;
			}
			this.emit( 'change', { mode: 'all', prop: { color: data.code } } );
		},
		clear: function() {
			this.emit( 'change', { mode: 'all', prop: { color: null } } );
		}
	} );

	this.items.color = this.colorWidget;
};
