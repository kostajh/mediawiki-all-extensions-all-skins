bs.util.registerNamespace( 'bs.saferEdit' );

bs.saferEdit.Save = function( cfg ) {
	cfg = cfg || {};

	this.section = cfg.section || null;
	this.interval = cfg.interval || 0;

	this.startSaving();
};

OO.initClass( bs.saferEdit.Save );

bs.saferEdit.Save.prototype.startSaving = function() {
	BSPing.registerListener(
		'SaferEditSave',
		this.interval,
		[{
			section: this.section,
			bUnsavedChanges: this.hasUnsavedChanges( )
		}],
		this.startSaving.bind( this )
	);
};

bs.saferEdit.Save.prototype.hasUnsavedChanges = function() {
	// TODO: Make this actually work
	this.isUnsaved = this.editMode;
	return true;
};
