/**
 * Readers path Panel
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage Readers
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
Ext.define( 'BS.Readers.panel.Path', {
	extend: 'BS.CRUDGridPanel',
	requires: [ 'BS.store.BSApi' ],
	plugin: 'gridfilters',
	id: 'bs-readers-pathpanel',
	initComponent: function () {
		this.strMain =  new BS.store.BSApi({
			apiAction: 'bs-readers-data-store',
			proxy: {
				extraParams: {
					query: mw.config.get("bsReadersUserID")
				}
			},
			fields: [ 'pv_page', 'pv_page_link', 'pv_page_title', 'pv_ts',
				'pv_date', 'pv_readers_link' ]
		} );

		this.columns = [ Ext.create( 'Ext.grid.column.Template',{
			id: 'pvpage',
			header: mw.message( 'bs-readers-header-page' ).plain(),
			sortable: true,
			dataIndex: 'pv_page_title',
			tpl: '{pv_readers_link} {pv_page_link}',
			filter: {
				type: 'string'
			},
			flex: 1
		}), Ext.create( 'Ext.grid.column.Template', {
			id: 'pvts',
			header: mw.message( 'bs-readers-header-ts' ).plain(),
			sortable: true,
			dataIndex: 'pv_ts',
			tpl: '{pv_date}',
			filter: {
				type: 'date'
			},
			flex: 1
		}) ];

		this.colMainConf.columns = this.columns;
		this.callParent( arguments );
	},

	makeActionColumn: function( cols ) {
		return false;
	},
	makeTbar : function() {
		return false;
	}
} );
