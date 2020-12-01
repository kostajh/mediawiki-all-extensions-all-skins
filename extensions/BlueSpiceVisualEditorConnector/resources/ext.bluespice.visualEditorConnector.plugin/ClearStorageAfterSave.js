// This fixes ERM13364 by making sure that the local session store is always
// cleared after save.
mw.hook( 've.activationComplete' ).add( function () {
	ve.init.target.connect( ve.init.target, { save: function() {
		this.getSurface().getModel().removeDocStateAndChanges();
	}})
});