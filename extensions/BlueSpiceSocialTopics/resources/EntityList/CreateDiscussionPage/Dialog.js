
bs.social.EntityList.CreateDiscussionPage.Dialog = function( config ) {
	var me = this;
	me.data = null;
	bs.social.EntityList.CreateDiscussionPage.Dialog.super.call( this, config );
};

OO.inheritClass( bs.social.EntityList.CreateDiscussionPage.Dialog, OO.ui.ProcessDialog );
OO.initClass( bs.social.EntityList.CreateDiscussionPage.Dialog );

bs.social.EntityList.CreateDiscussionPage.Dialog.static.name = 'createDiscussion';
bs.social.EntityList.CreateDiscussionPage.Dialog.static.title = '';

bs.social.EntityList.CreateDiscussionPage.Dialog.static.actions = [ {
	action: 'save',
	label: mw.message( 'bs-socialtopics-dialog-creatediscussionpage-btn-label-save' ).plain(),
	flags: [ 'primary', 'constructive' ],
	disabled: false
}, {
	action: 'cancel',
	label: mw.message( 'bs-socialtopics-dialog-creatediscussionpage-btn-label-cancel' ).plain(),
	flags: 'safe'
} ];

bs.social.EntityList.CreateDiscussionPage.Dialog.static.data = {};

bs.social.EntityList.CreateDiscussionPage.Dialog.prototype.setup = function( cfg ) {
	this.data = cfg;
	this.textField.setValue( this.data['text'] );
	return bs.social.EntityList.CreateDiscussionPage.Dialog.super.prototype.setup.apply(
		this,
		arguments
	);
};

bs.social.EntityList.CreateDiscussionPage.Dialog.prototype.initialize = function() {
	bs.social.EntityList.CreateDiscussionPage.Dialog.super.prototype.initialize.call(
		this
	);

	this.panel = new OO.ui.PanelLayout( {
		padded: true,
		expanded: false,
		id: 'bs-socialtopics-creatediscussionpage-dialog'
	});
	this.content = new OO.ui.FieldsetLayout();

	this.textField = this.makeTextInput();

	this.content.addItems( [
		new OO.ui.FieldLayout( this.textField, {
			label: mw.message( 'bs-socialtopics-dialog-creatediscussionpage-label-text' ).plain(),
			align: 'top'
		} )
	] );

	this.panel.$element.append( this.content.$element );
	this.$body.append( this.panel.$element );
};

bs.social.EntityList.CreateDiscussionPage.Dialog.prototype.makeTextInput = function() {
	return new OO.ui.MultilineTextInputWidget( {
		value: '',
		required: true,
		disabled: false
	});
};

bs.social.EntityList.CreateDiscussionPage.Dialog.prototype.getActionProcess = function ( action ) {
	var me = this;
	if ( action === 'save' ) {
		 return new OO.ui.Process( function () {
			var dfd = $.Deferred();
			me.close( { action: action } );
			me.data.entityList.showLoadMask();
			me.data.save( this.data.title, this.textField.getValue() ).fail(
				function( code, error ){
				dfd.reject( me.showError( error ) );
			} ).done( function( data ) {
				me.loadDiscussionPage( data );
				dfd.resolve( me.close( { action: action } ) );		
			});
			//return dfd.promise();
		} );
	} else if ( action === 'cancel' ) {
		me.close( { action: action } );
	}

	return bs.social.EntityList.CreateDiscussionPage.Dialog.super.prototype.getActionProcess.call(
		this,
		action
	);
};

bs.social.EntityList.CreateDiscussionPage.Dialog.prototype.showError = function( data ) {
	var messageDialog = new OO.ui.MessageDialog();
	var windowManager = new OO.ui.WindowManager();
	$( 'body' ).append( windowManager.$element );

	windowManager.addWindows( [ messageDialog ] );
	windowManager.openWindow( messageDialog, {
		message: data.error.info,
		actions: [ {
			action: 'accept',
			label: 'OK',
			flags: 'primary'
		}]
	});
	return messageDialog;
};

bs.social.EntityList.CreateDiscussionPage.Dialog.prototype.reloadPage = function() {
	window.location = mw.util.getUrl(
		mw.config.get( 'wgPageName' )
	);
};

bs.social.EntityList.CreateDiscussionPage.Dialog.prototype.loadDiscussionPage = function( data ) {
	window.location = mw.util.getUrl( data.edit.title );
};