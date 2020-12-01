/**
 * Js for Rating special page
 *
 * @author     Patric Wirth
 * @package    Bluespice_Extensions
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

Ext.define( 'BS.Rating.articlelike.grid.Panel', {
	extend: 'BS.CRUDGridPanel',
	requires: [ 'BS.store.BSApi' ],
	id: 'bs-specialratinglike-extgrid',
	features: [],

	initComponent: function() {
		this.strMain = new BS.store.BSApi( {
			apiAction: 'bs-ratingarticlelike-store',
			fields: [
				'page_namespace',
				'page_namespace_text',
				'page_title',
				'rat_reftype',
				'rat_ref',
				'totalcount',
				'content',
				'item'
			]
		});
		this.strMain.on( 'load', function( store, records, options ) {
			bs.rating.init();
		});  

		var filter = [];
		var namespaces = mw.config.get(
			'bsgRatingArticleLikeActiveNamespaces',
			{}
		);
		for( var idx in namespaces ) {
			if( !namespaces[idx] ) {
				continue;
			}
			filter.push( namespaces[idx] );
		}

		this.columns = [{
			id: 'page_namespace',
			header: mw.message ('bs-rating-specialrating-label-namespace' ).plain(),
			dataIndex: 'page_namespace',
			sortable: true,
			renderer: function( value, metaData, record, rowIndex, colIndex, store ) {
				return record.get( 'page_namespace_text' ) + " (" + value + ")"
			},
			filter: {
				type: 'list',
				options: filter
			}
		},{
			id: 'page_title',
			header: mw.message( 'bs-rating-specialrating-titleTitle' ).plain(),
			dataIndex: 'page_title',
			renderer: function(value, metaData, record, rowIndex, colIndex, store) {
				var ns = bs.util.getNamespaceText(
					record.get( 'page_namespace' )
				);
				if( ns && ns !== "" ) {
					value = ns + ':' + value;
				}
				return '<a href = "' + mw.util.getUrl( value ) + '">'
					+ value
					+ '</a>';
			},
			sortable: true,
			filter: {
				type: 'string'
			}
		},{
			id: 'votes',
			header:mw.message( 'bs-rating-special-recommendations-label-recommendation' ).plain(),
			dataIndex: 'totalcount',
			renderer: function(value, metaData, record, rowIndex, colIndex, store) {
				return record.get('content');
			},
			sortable: true,
			filter: {
				type: 'numeric'
			}
		}];

		this.colMainConf.columns = this.columns;
		this.callParent( arguments );
	},

	makeActionColumn: function( cols ) {
		return false;
	},
	makeTbar : function() {
		return false;
	}
});