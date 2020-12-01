/**
 * UserManager UserDialog
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage UserManager
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

Ext.define( 'BS.InterWikiLinks.InterWikiLinksDialog', {
	extend: 'MWExt.Dialog',
	currentData: {},
	selectedData: {},
	makeItems: function() {
		this.tfIWLPrefix = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message( 'bs-interwikilinks-labelprefix' ).plain(),
			labelWidth: 85,
			labelAlign: 'right',
			name: 'iw_prefix',
			allowBlank: false
		});
		this.tfIWLUrl = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message( 'bs-interwikilinks-labelurl' ).plain(),
			labelWidth: 85,
			labelAlign: 'right',
			name: 'iw_url',
			allowBlank: false
		});

		return [
			this.tfIWLPrefix,
			this.tfIWLUrl
		];
	},
	resetData: function() {
		this.tfIWLPrefix.reset();
		this.tfIWLUrl.reset();

		this.callParent();
	},
	setData: function( obj ) {
		this.currentData = obj;
		this.tfIWLPrefix.setValue( this.currentData.iw_prefix );
		this.tfIWLUrl.setValue( this.currentData.iw_url );
	},
	getData: function() {
		this.selectedData.iw_prefix = this.tfIWLPrefix.getValue();
		this.selectedData.iw_url = this.tfIWLUrl.getValue();
		this.selectedData.iw_prefix_old = this.currentData.iw_prefix;

		return this.selectedData;
	}
} );