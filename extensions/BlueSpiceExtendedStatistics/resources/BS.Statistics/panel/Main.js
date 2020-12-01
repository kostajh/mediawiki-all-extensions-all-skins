/**
 * Statistics panel
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Patric Wirth
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

Ext.define( 'BS.Statistics.panel.Main', {
	extend: 'Ext.tab.Panel',
	requires: [ 'BS.Statistics.panel.Filter', 'BS.Statistics.panel.Output' ],
	layout: 'border',
	border: true,

	initComponent: function() {
		this.pnlFilters = new BS.Statistics.panel.Filter( {
			id: 'bs-statistics-filterpanel'
		} );
		this.pnlMain = new BS.Statistics.panel.Output();

		this.items = [
			this.pnlFilters,
			this.pnlMain
		];

		this.on( 'beforetabchange', this.onBeforeTagChange, this );
		this.callParent();
	},

	onBeforeTagChange: function( tabPanel, newTab, oldTab, eOpts ) {
		if( this.pnlFilters.getForm().isValid() === false ) {
			return false;
		}

		if( newTab === this.pnlFilters ) {
			return true;
		}

		var filterSettings = this.pnlFilters.getValues();
		this.pnlMain.applyFilterSettings( filterSettings );

		return true;
	}
});
