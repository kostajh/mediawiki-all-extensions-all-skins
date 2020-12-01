bs.util.registerNamespace( 'bs.vec.ui.dialog' );

bs.vec.ui.dialog.TableAdditionalOptions = function( commands, contextItem ) {
	this.commands = commands;
	this.contextItem = contextItem;
	this.styleWidgets = {};

	bs.vec.ui.dialog.TableAdditionalOptions.parent.call( this, {
		size: 'medium'
	} );

	this.$element.on( 'mouseup mousedown', function( e ) {
		if ( $( e.target ).hasClass( 'oo-ui-inputWidget-input' ) ) {
			return;
		}
		e.preventDefault();
		e.stopPropagation();
	} );
};

OO.inheritClass( bs.vec.ui.dialog.TableAdditionalOptions, OO.ui.ProcessDialog );

bs.vec.ui.dialog.TableAdditionalOptions.static.name = 'tableAdditionalOptions';

bs.vec.ui.dialog.TableAdditionalOptions.static.actions = [
	{ action: 'save', label: OO.ui.deferMsg( 'bs-vec-dialog-action-done' ), flags: 'primary' },
	{ label: OO.ui.deferMsg( 'bs-vec-dialog-action-safe' ), flags: 'safe' }
];

bs.vec.ui.dialog.TableAdditionalOptions.static.title = '';

bs.vec.ui.dialog.TableAdditionalOptions.prototype.initialize = function() {
	var command, config, indLayout, mainLayout = new OO.ui.PanelLayout( {
		padded: true,
		expanded: false
	} );

	bs.vec.ui.dialog.TableAdditionalOptions.parent.prototype.initialize.call( this, arguments );

	for ( command in this.commands ) {
		if ( !this.commands.hasOwnProperty( command ) ) {
			continue;
		}
		config = this.commands[command];
		this.styleWidgets[command] = this.contextItem.getWidgetFromConfig( command, config );
		this.styleWidgets[command].setShouldExecute( false );
		indLayout = new OO.ui.FieldLayout( this.styleWidgets[command], {
			label: config.label || ''
		} );
		mainLayout.$element.append( indLayout.$element );
	}
	this.$body.append( mainLayout.$element );
};

bs.vec.ui.dialog.TableAdditionalOptions.prototype.getSetupProcess = function( data ) {
	data = data || {};
	data.title = this.contextItem.getAdditionalOptionsTitle();

	return bs.vec.ui.dialog.TableAdditionalOptions.parent.prototype.getSetupProcess.call( this, data );
};

bs.vec.ui.dialog.TableAdditionalOptions.prototype.getActionProcess = function ( action ) {
	var dialog = this;
	if ( action === 'save' ) {
		return new OO.ui.Process( function () {
			return dialog.close( { action: action, actionsToExecute: dialog.styleWidgets } );
		} );
	}
	return bs.vec.ui.dialog.TableAdditionalOptions.parent.prototype.getActionProcess.call( this, action );
};

bs.vec.ui.dialog.TableAdditionalOptions.prototype.getBodyHeight = function () {
	return this.$body.outerHeight() + 100;
};
