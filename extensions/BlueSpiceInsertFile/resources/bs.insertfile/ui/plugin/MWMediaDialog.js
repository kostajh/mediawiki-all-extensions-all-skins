bs.util.registerNamespace( 'bs.insertfile.ui.plugin' );

bs.insertfile.ui.plugin.MWMediaDialog = function BsInsertFileUiPluginMWMediaDialog( component ) {
	bs.insertfile.ui.plugin.MWMediaDialog.super.apply( this, arguments );
};

OO.inheritClass( bs.insertfile.ui.plugin.MWMediaDialog, bs.vec.ui.plugin.MWMediaDialog );

bs.insertfile.ui.plugin.MWMediaDialog.prototype.initialize = function() {
	this.advancedSearchTab = new OO.ui.TabPanelLayout( 'bs-insertfile-advanced-search-panel', {
		label: ve.msg( 'bs-insertfile-advanced-search-panel-label' ),
		classes: [
			'bs-insertfile-advanced-search-panel-container'
		]
	} );
	this.fileRepoGrid = null;

	this.component.searchTabs.addTabPanels( [ this.advancedSearchTab ] );

	/**
	 * Unfortunately rendering an ExtJS component within the constructor of an `OO.ui.Widget`
	 * is not always possible, as `OO.ui.Widget::$element` may not be appended to the DOM yet.
	 * Therefore we render the ExtJS component when the user actually opens the tab.
	 */
	this.component.searchTabs.on( 'set', this.onSearchTabsSet, [], this );
};

bs.insertfile.ui.plugin.MWMediaDialog.prototype.onSearchTabsSet = function( selectedTab ) {
	if( selectedTab === this.advancedSearchTab ) {
		this.component.setSize( 'larger' );
		this.component.actions.setAbilities( { cancel: true } );
		this.component.searchTabs.toggleMenu( true );
		this.component.actions.setMode( 'select' );
		this.component.search.runLayoutQueue();

		if ( this.fileRepoGrid === null ) {
			var me = this;
			mw.loader.using( 'ext.bluespice.extjs' ).done( function() {
				Ext.onReady( me.initFileRepoGrid, me );
			} );
		}
	}
};

bs.insertfile.ui.plugin.MWMediaDialog.prototype.initFileRepoGrid = function() {
	this.fileRepoGrid = Ext.create( 'BS.grid.FileRepo', {
		renderTo: this.advancedSearchTab.$element[0],
		height: this.advancedSearchTab.$element.height(),
		uploaderCfg: {}
	} );

	this.fileRepoGrid.on( 'select', this.onFileRepoGridSelect, this );
};

/**
 * Required fields extracted from https://github.com/wikimedia/mediawiki-extensions-VisualEditor/blob/b8bcba8cbeb38f9be8232c77140b04e8da1040cc/modules/ve-mw/ui/dialogs/ve.ui.MWMediaDialog.js#L476-L718
 * @param BS.grid.FileRepo sender
 * @param Ext.model.Model record
 * @param object eOpts
 * @returns void
 */
bs.insertfile.ui.plugin.MWMediaDialog.prototype.onFileRepoGridSelect = function( sender, record, eOpts ) {
	var imageInfo = {
		title: record.get( 'page_prefixed_text' ),
		extmetadata: [], //Not available from 'bs-filerepo-store' API
		user: record.get( 'file_user_text' ),
		timestamp: record.get( 'file_timestamp' ),
		descriptionurl: record.get( 'page_prefixed_text' ),
		url: record.get( 'file_url' ),
		mediatype: record.get( 'file_mediatype' ),
		width: record.get( 'file_width' ),
		height: record.get( 'file_height' ),
		thumburl: record.get( 'file_thumbnail_url' ),
		thumbwidth: 80 //Hardcoded in 'bs-filerepo-store'
	};

	this.component.chooseImageInfo( imageInfo );
};
