/**
 * Initialization standalone BlueSpice target.
 *
 * @class
 * @extends ve.init.sa.DesktopTarget
 *
 * @constructor
 * @param {Object} [config] Configuration options
 * @cfg {Object} [toolbarConfig] Configuration options for the toolbar
 */
ve.init.sa.BlueSpiceTarget = function VeInitSaBlueSpiceTarget( config ) {
	// Parent constructor
	ve.init.sa.BlueSpiceTarget.super.call( this, config );

	// Experimental - do not remove
	/*mw.libs.ve.targetLoader.loadModules( 'visual' ).done( function() {
		mw.hook( 've.activationComplete' ).fire();
	} );*/
};

/* Inheritance */

OO.inheritClass( ve.init.sa.BlueSpiceTarget, ve.init.sa.DesktopTarget );

/* Static Properties */

ve.init.sa.BlueSpiceTarget.static.toolbarGroups = [
	{
		name: 'history',
		include: [ 'undo', 'redo' ]
	},
	{
		name: 'format',
		header: OO.ui.deferMsg( 'visualeditor-toolbar-paragraph-format' ),
		title: OO.ui.deferMsg( 'visualeditor-toolbar-format-tooltip' ),
		type: 'menu',
		include: [ { group: 'format' } ],
		promote: [ 'paragraph' ],
		demote: [ 'preformatted', 'blockquote' ]
	},
	{
		name: 'style',
		header: OO.ui.deferMsg( 'visualeditor-toolbar-text-style' ),
		title: OO.ui.deferMsg( 'visualeditor-toolbar-style-tooltip' ),
		include: [ 'bold', 'italic', 'moreTextStyle' ]
	},
	{
		name: 'link',
		include: [ 'link' ]
	},
	{
		name: 'structure',
		header: OO.ui.deferMsg( 'visualeditor-toolbar-structure' ),
		title: OO.ui.deferMsg( 'visualeditor-toolbar-structure' ),
		type: 'list',
		icon: 'listBullet',
		include: [ { group: 'structure' } ],
		demote: [ 'outdent', 'indent' ]
	},{
		name: 'insert',
		header: OO.ui.deferMsg( 'visualeditor-toolbar-insert' ),
		title: OO.ui.deferMsg( 'visualeditor-toolbar-insert' ),
		type: 'list',
		icon: 'add',
		label: '',
		include: ['media', 'gallery', 'comment']
	},{
		name: 'specialCharacter',
		include: [ 'specialCharacter' ]
	}
];

/* Methods */

/**
 * @inheritdoc
 */
ve.init.sa.BlueSpiceTarget.prototype.setupToolbar = function ( surface ) {
	// Parent method
	ve.init.sa.BlueSpiceTarget.super.prototype.setupToolbar.call( this, surface );

	this.getToolbar().$bar.append( surface.context.$element );
};

ve.init.sa.BlueSpiceTarget.prototype.getContentApi = function ( doc, options ) {
	return new mw.Api( options );
};

ve.init.sa.BlueSpiceTarget.prototype.parseWikitextFragment = function ( wikitext, pst ) {
	return new mw.Api().post( {
		action: 'visualeditor',
		paction: 'parsefragment',
		page: this.pageName,
		wikitext: wikitext,
		pst: pst
	} );
};
