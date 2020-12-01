/**
 * InsertMagic command.
 *
 * @class
 * @extends ve.ui.Command
 *
 * @constructor
 */
ve.ui.BSInsertMagicCommand = function VeUiBSInsertMagicCommand() {
	// Parent constructor
	ve.ui.BSInsertMagicCommand.super.call(
		this, 'bsInsertMagic'
	);
	this.warning = null;
	this.supportedSelections = ['linear'];
	this.action = 'content';
	this.method = 'insert';
	this.insertMagicWindow = null;
};

/* Inheritance */

OO.inheritClass( ve.ui.BSInsertMagicCommand, ve.ui.Command );

/* Methods */

/**
 * @inheritdoc
 */
ve.ui.BSInsertMagicCommand.prototype.execute = function () {
	var me = this;

	mw.loader.using( 'ext.bluespice.extjs' ).done( function() {
		Ext.require( 'BS.InsertMagic.MWVEWindow', function() {
			if ( !me.insertMagicWindow ) {
				me.insertMagicWindow = new BS.InsertMagic.MWVEWindow();
			}
			me.insertMagicWindow.on( 'ok', BsInsertMagicMWVEConnector.applyData );
			var surfaceModel = ve.init.target.getSurface().getModel();
			BsInsertMagicMWVEConnector.fragment = surfaceModel.getFragment();
			me.insertMagicWindow.show( me );
		} );
	} );
	return true;
};

var BsInsertMagicMWVEConnector = {
	fragment: null,

	getData: function() {
		var data = {};
		return data;
	},

	applyData: function( sender, data ) {
		if ( data.mwvecommand ) {
			BsInsertMagicMWVEConnector.fragment.select();
			ve.init.target.getSurface().executeCommand( data.mwvecommand );
		} else if ( data.code ) {
			BsInsertMagicMWVEConnector.fragment.adjustLinearSelection( 1 )
				.insertContent( data.code, true );
		}
	}
};


/* Registration */

ve.ui.commandRegistry.register( new ve.ui.BSInsertMagicCommand() );
