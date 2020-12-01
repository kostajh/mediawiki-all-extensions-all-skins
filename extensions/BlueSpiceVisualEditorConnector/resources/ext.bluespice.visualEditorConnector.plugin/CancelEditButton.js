// I tried hard with toolFactory registration, but
// eventually gave up. This is a hacky but working
// solution.
mw.hook( 've.activationComplete' ).add( function () {
	if ( ve.init.target.$element.hasClass( 've-init-sa-target' ) ) {
		return;
	}
	var cancelButton = new OO.ui.ButtonWidget(
		{
			label: mw.msg( 'bs-visualeditorconnector-cancel-edit-no-unsaved-changes' ),
			invisibleLabel: true,
			title: mw.msg( 'bs-visualeditorconnector-cancel-edit-no-unsaved-changes' ),
			icon: 'cancel',
			classes: [ 'bs-visualeditorconnector-cancel-edit' ],
			framed: false,
			disabled: false,
			active: true
		}
	);

	cancelButton.on( 'click', function () {
		OO.ui.confirm( ve.msg( 'bs-visualeditorconnector-cancel-edit-confirm-message' ) ).done( function ( confirmed ) {
			if ( confirmed ) {
				// As this is an intentional act of the user, we assume the user
				// really wants to discard the changes. So we remove any locally stored
				// but not yet saved changes.
				ve.init.target.getSurface().getModel().removeDocStateAndChanges();
				// This flag triggers the browser's unsaved changes warning in
				// ve.init.mw.DesktopArticleTarget.prototype.onBeforeUnload
				ve.init.target.edited = false;
				window.location.href = mw.util.getUrl();
			}
		} );
	});

	// Needed for switching between Visual and Wikitext mode. Otherwise, we get this
	// error: this.items[i].destroy is not a function
	cancelButton.destroy = function() {};

	// Enable cancel button when the text is already dirty on load (happens when
	// there are edits in session storage in the browser).
	ve.init.target.isSaveable() ? cancelButton.setFlags( 'destructive' ) : null ;

	// Position 3 is just before the save button
	ve.init.target.getActions().addItems( [ cancelButton ], 3 );

	ve.init.target.cancelButton = cancelButton;
	// This override is dangerous, since it is hard to identify. It shouldn't be
	// necessary; see the comment (copied from the original) why we use it.
	// Putting the override in a better identifyable place outside of the mw.hook
	// context lead to an error, though
	// #Override
	ve.init.mw.ArticleTarget.prototype.updateToolbarSaveButtonState = function () {
		// This should really be an emit('updateState') but that would cause
		// every tool to be updated on every transaction.
		this.toolbarSaveButton.onUpdateState();
		// Added by BlueSpice
		if ( ve.init.target.isSaveable() ) {
			this.cancelButton.setFlags( ['destructive'] );
			this.cancelButton.setLabel( mw.msg( 'bs-visualeditorconnector-cancel-edit' ) );
			this.cancelButton.setTitle( mw.msg( 'bs-visualeditorconnector-cancel-edit' ) );
		} else {
			this.cancelButton.setFlags( { destructive : false } );
			this.cancelButton.setLabel( mw.msg( 'bs-visualeditorconnector-cancel-edit-no-unsaved-changes' ) );
			this.cancelButton.setTitle( mw.msg( 'bs-visualeditorconnector-cancel-edit-no-unsaved-changes' ) );
		}
	};
});
