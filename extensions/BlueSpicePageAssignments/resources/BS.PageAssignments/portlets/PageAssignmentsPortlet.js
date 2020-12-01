/**
 * ExtendedSearch extension
 *
 * @author     Wirth Patric <Wirth@hallowelt.com>
 * @version    2.27.0
 * @package    Bluespice_Extensions
 * @subpackage PageAssignments
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

Ext.define( 'BS.PageAssignments.portlets.PageAssignmentsPortlet', {
	extend: 'BS.portal.GridPortlet',
	requires: [ 'BS.store.BSApi' ],
	portletConfigClass:
		'BS.PageAssignments.portlets.PageAssignmentsPortletConfig',

	beforeInitComponent: function() {
		this.store = new BS.store.BSApi({
			apiAction: 'bs-mypageassignment-store',
			fields: [
				'page_id',
				'page_prefixedtext',
				'page_link',
				'assigned_by',
				'assignment'
			]
		});
		this.gdMainConfig = {
			store: this.store,
			columns: [{
				text : mw.message('bs-pageassignments-column-title').plain(),
				dataIndex: 'page_link',
				width: '40%'
			},{
				text : mw.message('bs-pageassignments-column-assignedby').plain(),
				dataIndex: 'assigned_by',
				sortable: false,
				width: '60%',
				renderer: function( value, metaData, record, rowIndex, colIndex, store, view ) {
					var html = '';
					for( var i = 0; i < record.get( 'assignment' ).length; i++ ) {
						var item = record.get( 'assignment' )[i];
						html += "<span class=\'bs-icon-" + item.pa_assignee_type + " bs-typeicon\'></span>";
						html += item.anchor;
						if( i !== record.get( 'assignment' ).length -1 ) {
							html += ',<br />';
						}
					};

					return html;
				}
			}]
		};
	},
	initComponent: function() {
		this.callParent(arguments);
	},

	setPortletConfig: function( cfg ) {
		this.callParent(arguments);
		this.store.reload();
	}
} );