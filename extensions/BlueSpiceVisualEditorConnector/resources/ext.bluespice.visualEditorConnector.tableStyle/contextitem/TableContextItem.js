
bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.TableContextItem = function ( context, model, config ) {
	// Parent constructor
	bs.vec.ui.TableContextItem.super.call( this, context, model, config );

	this.actions = this.actions || {};
	this.additionalActions = {};
	this.tableStyleRegistry =  bs.vec.registry.TableStyle.registry;
	this.overrideLayout();
	this.$element.addClass( 'bs-vec-ui-tableContextItem' );
};

/* Inheritance */

OO.inheritClass( bs.vec.ui.TableContextItem, ve.ui.LinearContextItem  );

/* Static Properties */
bs.vec.ui.TableContextItem.static.embeddable = false;

bs.vec.ui.TableContextItem.prototype.overrideLayout = function () {
	var cmd, commandConfig, widget;

	this.$actions.children().remove();

	this.actions = $.extend( this.actions, this.getActionsFromRegistry() );

	for( cmd in this.actions ) {
		if ( !this.actions.hasOwnProperty( cmd ) ) {
			continue;
		}
		commandConfig = this.actions[cmd];
		commandConfig.displaySection = commandConfig.displaySection || 'additional';
		if ( commandConfig.displaySection === 'quick' ) {
			widget = this.getWidgetFromConfig( cmd, commandConfig );
			this.$actions.append( widget.$element );
		} else {
			this.additionalActions[cmd] = commandConfig;
		}
	}
	if ( !$.isEmptyObject( this.additionalActions ) ) {
		var moreButton = new OO.ui.ButtonWidget( {
			title: OO.ui.deferMsg( 'bs-vec-contextitem-table-additional-options' ),
			framed: false,
			icon: 'menu'
		} );
		moreButton.on( 'click', this.displayAdditional.bind( this ) );
		this.$actions.append( moreButton.$element );
	}
};

bs.vec.ui.TableContextItem.prototype.displayAdditional = function () {
	var additionalOptionsDialog = new bs.vec.ui.dialog.TableAdditionalOptions(
		this.additionalActions,
		this
	);

	var windowManager = new OO.ui.WindowManager();
	$( document.body ).append( windowManager.$element );
	windowManager.addWindows( [ additionalOptionsDialog ] );
	var selection = this.context.getSurface().getModel().getSelection();
	windowManager.openWindow( additionalOptionsDialog ).closed.then( function ( data ) {
		// We need to execute actions after the dialog is closed and selection is restore,
		// because, as soon as we click on a dialog table selection is gone
		this.context.getSurface().getModel().setSelection( selection );
		if ( !data ) {
			return;
		}
		this.executeAdditionalActions( data.actionsToExecute );
	}.bind( this ) );
};

bs.vec.ui.TableContextItem.prototype.executeAdditionalActions = function( actions ) {
	var command;
	for( command in actions ) {
		if ( !actions.hasOwnProperty( command ) ) {
			continue;
		}
		actions[command].setShouldExecute( true );
		actions[command].executeAction();
	}
};

bs.vec.ui.TableContextItem.prototype.getAdditionalOptionsTitle = function () {
	// STUB
	return '';
};

bs.vec.ui.TableContextItem.prototype.getWidgetFromConfig = function ( cmd, commandConfig ) {
	var widgetClass, contextItem = this;

	if ( commandConfig.hasOwnProperty( 'widget' ) ) {
		widgetClass = commandConfig.widget;
		return new widgetClass( contextItem );
	} else {
		return new bs.vec.ui.widget.ButtonCommandWidget( contextItem, {
			icon: commandConfig.icon,
			title: commandConfig.label,
			framed: false,
			command: cmd,
			expensive: commandConfig.expensive || false
		} );
	}
};

bs.vec.ui.TableContextItem.static.isCompatibleWith = function ( model ) {
	return model instanceof ve.dm.Node && model.isCellable();
};

bs.vec.ui.TableContextItem.prototype.execCommand = function ( command, args ) {
	var cmd = this.getCommand( command );
	args = args || cmd.getArgs();

	if ( cmd ) {
		cmd.execute( this.context.getSurface(), args );
		this.emit( 'command' );
	}
};

bs.vec.ui.TableContextItem.prototype.getCommand = function ( command ) {
	return this.context.getSurface().commandRegistry.lookup( command );
};

bs.vec.ui.TableContextItem.prototype.getStyles = function () {
	// STUB
	return {};
};

bs.vec.ui.TableContextItem.prototype.getSection = function () {
	return '';
};

bs.vec.ui.TableContextItem.prototype.getActionsFromRegistry = function () {
	var tableStyleKey, tableStyle, tools = {};
	for( tableStyleKey in this.tableStyleRegistry ) {
		if ( !this.tableStyleRegistry.hasOwnProperty( tableStyleKey ) ) {
			continue;
		}
		tableStyle = this.tableStyleRegistry[tableStyleKey];
		if ( tableStyle.applies( this.getSection() ) ) {
			tools[tableStyleKey] = tableStyle.getTool();
		}
	}
	return tools;
};
