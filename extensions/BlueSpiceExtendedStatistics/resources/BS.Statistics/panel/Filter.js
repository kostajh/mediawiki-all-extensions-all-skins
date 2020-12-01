/**
 * Statistics filter panel
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

Ext.define( 'BS.Statistics.panel.Filter', {
	extend: 'Ext.form.Panel',
	requires: [
		'BS.store.BSApi', 'BS.store.ApiUser', 'BS.store.LocalNamespaces',
		'BS.store.ApiCategory', 'BS.form.SimpleSelectBox'
	],
	layout: 'form',
	fieldDefaults: {
		labelAlign: 'right'
	},
	clientValidation: true,
	title: mw.message('bs-statistics-filters').plain(),
	initComponent: function() {
		this.cbInputDiagrams = new Ext.form.field.ComboBox( {
			store: new BS.store.BSApi( {
				apiAction: 'bs-statistics-available-diagrams-store',
				fields: ['key', 'displaytitle', 'listable', 'filters']
			} ),
			fieldLabel: mw.message( 'bs-statistics-diagram' ).plain(),
			name: 'diagram',
			displayField: 'displaytitle',
			valueField: 'key',
			typeAhead: true,
			mode: 'local',
			triggerAction: 'all',
			lastQuery: '',
			forceSelection: true,
			allowBlank: false,
			editable: true
		} );

		this.cbInputDiagrams.on( 'select', this.cbInputDiagramsSelect, this );
		this.cbInputDiagrams.getStore().on( 'load', function( store, records ) {
			$.each( records, function( k, diagram ) {
				if( diagram.data.isDefault === true ) {
					this.select( diagram );
				}
			}.bind( this ) )
		}.bind( this.cbInputDiagrams ) );

		var lastMonth = new Date();
		with(lastMonth) { setMonth( getMonth() -1 ) }

		this.dfInputFrom = new Ext.form.field.Date( {
			fieldLabel: mw.message( 'bs-statistics-from' ).plain(),
			name: 'from',
			format: 'd.m.Y',
			maxValue: new Date(),
			value: lastMonth,
			editable: false
		} );

		this.dfInputTo = new Ext.form.field.Date( {
			fieldLabel: mw.message( 'bs-statistics-to' ).plain(),
			name: 'to',
			format: 'd.m.Y',
			maxValue: new Date(),
			value: new Date(),
			editable: false
		});

		this.msInputFilterUsers = new Ext.form.field.Tag( {
			store: new BS.store.ApiUser(),
			fieldLabel: mw.message( 'bs-statistics-filter-user' ).plain(),
			name: 'hwpFilterBsFilterUsers',
			displayField: 'user_name',
			valueField: 'user_name'
		} );

		this.msInputFilterNamespace = new Ext.form.field.Tag({
			store: new BS.store.LocalNamespaces( {} ),
			fieldLabel: mw.message( 'bs-ns' ).plain(),
			name: 'hwpFilterBsFilterNamespace',
			displayField: 'namespace',
			valueField: 'id'
		} );

		this.msInputFilterCategory = new Ext.form.field.Tag( {
			store: new BS.store.ApiCategory(),
			fieldLabel: mw.message( 'bs-statistics-filter-category' ).plain(),
			name: 'hwpFilterBsFilterCategory',
			displayField: 'cat_title',
			valueField: 'cat_title'
		} );

		this.cbInputDepictionMode = new BS.form.SimpleSelectBox( {
			bsData: [
				{ value: 'absolute', name: mw.message( 'bs-statistics-absolute' ).plain() },
				{ value: 'aggregated', name: mw.message( 'bs-statistics-aggregated' ).plain() }
			],
			value: 'aggregated',
			fieldLabel: mw.message( 'bs-statistics-mode' ).plain(),
			name: 'mode',
			editable: false
		} );

		this.cbInputDepictionGrain = new BS.form.SimpleSelectBox( {
			bsData: [
				{ value: 'Y', name: mw.message( 'bs-statistics-year' ).plain() },
				{ value: 'm', name: mw.message( 'bs-statistics-month' ).plain() },
				{ value: 'W', name: mw.message( 'bs-statistics-week' ).plain() },
				{ value: 'd', name: mw.message( 'bs-statistics-day' ).plain() }
			],
			value: 'W',
			fieldLabel: mw.message( 'bs-statistics-grain' ).plain(),
			name: 'grain',
			editable: false
		} );

		this.items = [
			this.cbInputDiagrams,
			this.dfInputFrom,
			this.dfInputTo,
			this.cbInputDepictionGrain,
			this.cbInputDepictionMode,
			this.msInputFilterUsers,
			this.msInputFilterNamespace,
			this.msInputFilterCategory
		];

		this.deactivateFilters();

		this.callParent();
	},

	cbInputDiagramsSelect: function( field, record ) {
		this.deactivateFilters();
		this.activateFilterByKeys( record.get( 'filters' ) );

		if( record.get( 'listable' ) ) {
			var dmStore = this.cbInputDepictionMode.getStore();
			dmStore.insert( 0, {
				value: 'list',
				name: mw.message( 'bs-statistics-list' ).plain()
			} );
		}
	},

	deactivateFilters: function() {
		this.removeAdditionalModes();

		this.msInputFilterUsers.disable();
		this.msInputFilterUsers.hide();

		this.msInputFilterNamespace.disable();
		this.msInputFilterNamespace.hide();

		this.msInputFilterCategory.disable();
		this.msInputFilterCategory.hide();
	},

	removeAdditionalModes: function () {
		var store = this.cbInputDepictionMode.getStore();
		store.removeAll();
		store.insert( 0, { value: 'aggregated', name: mw.message( 'bs-statistics-aggregated' ).plain() } );
		store.insert( 0, { value: 'absolute', name: mw.message( 'bs-statistics-absolute' ).plain() } );
		this.cbInputDepictionMode.setValue( 'aggregated' );
	},

	activateFilterByKeys: function( keys ) {
		for( var i = 0; i < keys.length; i++ ) {
			if( keys[i] == 'hwpFilterBsFilterUsers' ) {
				this.msInputFilterUsers.enable();
				this.msInputFilterUsers.show();
			} else if( keys[i] == 'hwpFilterBsFilterCategory' ) {
				this.msInputFilterCategory.enable();
				this.msInputFilterCategory.show();
			} else if( keys[i] == 'hwpFilterBsFilterNamespace' ) {
				this.msInputFilterNamespace.enable();
				this.msInputFilterNamespace.show();
			}
		}
	}
});