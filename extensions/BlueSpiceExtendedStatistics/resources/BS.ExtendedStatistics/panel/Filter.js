/**
 * SnapshotStatistics Dynamic Filter panel
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Oleksandr Pinchuk <intracomof@gmail.com>
 * @author     Dejan Savuljesku <savuljesku@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2019 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

Ext.define( 'BS.ExtendedStatistics.panel.Filter', {
	extend: 'Ext.form.Panel',
	requires: [
		'Ext.picker.Date',
		'Ext.panel.Panel',
		'Ext.form.Panel',
		'Ext.form.field.Date',
		'Ext.form.field.Tag',
		'Ext.form.field.Text',
		'Ext.form.field.Number',
		'Ext.form.field.ComboBox',
		'Ext.form.field.Checkbox',
		'Ext.form.FieldContainer',
		'Ext.form.CheckboxGroup',
		'BS.form.SimpleSelectBox',
		'BS.store.ApiUser',
		'BS.store.LocalNamespaces',
		'BS.store.ApiCategory',
		'Ext.form.field.Hidden',
		'BS.ExtendedStatistics.toolbar.Apply',
		'Ext.toolbar.Toolbar'
	],
	header: false,
	border: true,
	clientValidation: true,
	collectionStore: {},
	applyButtonToolbar: {},
	dynamicFiltersConfig: {}, // Key => Object Storage of current set of Filters
	currentSourceConfig: {},

	initComponent: function() {
		this.dataSourceSelect = new Ext.form.field.ComboBox( {
			store: this.collectionStore,
			fieldLabel: mw.message( 'bs-statistics-datasource-label' ).plain(),
			name: 'datasource',
			displayField: 'displaytitle',
			valueField: 'key',
			typeAhead: true,
			mode: 'local',
			triggerAction: 'all',
			lastQuery: '',
			forceSelection: true,
			allowBlank: false,
			editable: true,
			labelAlign: 'top',
			cls: 'padding-5'
		} );

		this.dataSourceSelect.on( 'select', this.onDataSourceSelect, this );

		// <From-To DatePickers>
		var startDate = new Date();
		startDate.setDate( startDate.getDate() - 1 );
		this.filterTimespanEnd = new Ext.form.field.Date ( {
			labelAlign: 'top',
			fieldLabel: 'End Date',
			name: 'endDate',
			value: startDate,
			cls: 'padding-5',
			maxValue: new Date(),
		} );

		var endDate = new Date();
		endDate.setDate( endDate.getDate() - 365 );
		this.filterTimespanStart = new Ext.form.field.Date ( {
			labelAlign: 'top',
			name: 'startDate',
			fieldLabel: 'Start Date',
			value: endDate,
			maxValue: new Date(),
			cls: 'padding-5'
		} );
		// </From-To DatePickers>

		// Filters that don't generates dynamically (not depend on source)
		var mainFilters = {
			title: 'General filters',
			xtype: 'panel',
			layout: 'hbox',
			align: 'stretch',
			items: [
				this.dataSourceSelect,
				this.filterTimespanStart,
				this.filterTimespanEnd
			]
		};

		// Filters that depend on source
		this.dynamicFiltersConfig = {};
		this.dynamicFilters = new Ext.toolbar.Toolbar( {
			html: mw.message( 'bs-statistics-filters' ).plain(),
			cls: 'bold-label',
			items: []
		} );

		// <Aggregation>

		// Aggregation properties (depend on source)
		this.aggregationMode = new Ext.toolbar.Toolbar( {
			cls: 'bold-label reset-left',
			items: []
		} );

		// Aggregation Field (depends on aggregation mode)
		this.aggregationField = new Ext.toolbar.Toolbar( {
			cls: 'bold-label',
			items: []
		} );

		// Aggregation Panel
		this.aggregationPanel = new Ext.panel.Panel( {
			title: 'Aggregation',
			layout: 'hbox',
			items: [
				this.aggregationMode,
				this.aggregationField
			]
		} );

		// </Aggregation>

		this.seriesPanel = new Ext.panel.Panel( {
			title: 'Series',
			layout: 'vbox',
			border: '0 0 1 0',
			items: []
		} );

		// select first source from the list and activate needed filters
		this.dataSourceSelect.select( this.collectionStore.getAt( 0 ) );
		this.onDataSourceSelect(
			this.collectionStore.getAt( 0 ).get( 'key' ),
			this.collectionStore.getAt(0)
		);

		// pass Filter information to ApplyButton to make proper request
		this.applyButtonToolbar.filters = this;

		this.items = [
			mainFilters,
			this.aggregationPanel,
			this.seriesPanel,
			this.applyButtonToolbar
		];

		this.callParent();
	},

	/** Here we need to display needed Filters & Aggregation modes for selected source **/
	onDataSourceSelect: function( field, record ) {
		this.currentSourceConfig = record;
		var me = this;
		var filters = record.get( 'filters' ).slice();
		var series = record.get( 'series' ).slice();

		me.seriesPanel.removeAll();
		me.dynamicFiltersConfig = {};

		series.forEach( function( seriesItem ) {

			var seriesItemToolbar = new Ext.toolbar.Toolbar( {
				layout: 'hbox',
				padding: '0 0 0 0',
				items: [],
				border: '0 0 0 0',
				style: 'overflow: visible',
				width: '100%',
			} );

			var filterToolbar = new Ext.form.Panel( {
				title: mw.message( 'bs-statistics-filters' ).plain(),
				// cls: 'bold-label',
				border: true,
				layout: 'hbox',
				items: []
			} );

			var seriesCheckbox = new Ext.form.Panel( {
				border: false,
				title: 'Series name',
				height: '100%',
				layout: 'fit',
				style: 'overflow: visible',
				margin: '0 0 0 0',
				items: [
					{
						padding: '35px 0 0 20px',
						width: '180px',
						height: '100%',
						xtype: 'checkbox',
						boxLabel: seriesItem.label,
						checked: true,
						name: 'series',
						inputValue: seriesItem.name
					}
				]
			} );

			seriesItemToolbar.add( seriesCheckbox );
			filters.forEach( function( filterConf ) {
				// Key => Object Storage of current set of Filters
				me.dynamicFiltersConfig[ filterConf.name ] = filterConf;
				// possible filters for selected source
				filterToolbar.add( me.generateFilterByConf( seriesItem.name, filterConf ) );
			});

			if ( filterToolbar.items.length > 0 ) {
				seriesItemToolbar.add(filterToolbar);
			}

			me.seriesPanel.add( seriesItemToolbar );

		} );

		me.generateAggregationModeSelect( filters );
	},

	generateAggregationModeSelect: function( aggregationModeDataArr ) {
		this.aggregationMode.removeAll();
		aggregationModeDataArr.unshift( {
			name: 'timestampcreated',
			label: 'Date'
		} );

		this.aggregationModeSelect = new BS.form.SimpleSelectBox( {
			fieldLabel: mw.message( 'bs-statistics-aggregationmode-label' ).plain(),
			labelAlign: 'top',
			displayField: 'label',
			valueField: 'name',
			bsData: aggregationModeDataArr,
			value: aggregationModeDataArr[0].name,
			mode: 'local',
			name: 'aggregation[property]',
			editable: false,
			margin: '20 0 0 0',
		} );

		this.aggregationModeSelect.on( 'select', this.onAggregationModeSelect, this );

		this.onAggregationModeSelect(
			this.aggregationModeSelect.store.getAt( 0 ).get( 'key' ),
			this.aggregationModeSelect.store.getAt( 0 )
		);

		this.aggregationMode.add( this.aggregationModeSelect );
	},

	onAggregationModeSelect: function( field, record ) {
		this.aggregationField.removeAll();
		var aggregationFilter;

		var excludeTargets = [];
		excludeTargets.push( record.get( 'name' ) );

		switch ( record.get( 'name' ) ) {
			case 'none':
				return;
			case 'timestampcreated':
				aggregationFilter = this.generateIntervalFilterSelect();
				break;
			default:
				excludeTargets.push( 'timestampcreated' );
				this.aggregationField.add( new Ext.form.field.Hidden( {
					name: 'aggregation[type]',
					value: record.get('type')
				} ));
				break;
		}

		this.aggregationField.add( aggregationFilter );
		this.generateTargets( excludeTargets );
	},

	generateFilterByConf: function( seriesName, filterConf ) {
		var filterObj;
		switch( filterConf.type ) {
			case 'int':
				filterObj = new Ext.form.field.Number( {
					labelAlign: 'top',
					name: 'filter_' + seriesName + '_' + filterConf.name,
					fieldLabel: filterConf.label
				} );
				break;
			case 'string':
				filterObj = new Ext.form.field.Text( {
					labelAlign: 'top',
					name: 'filter_' + seriesName + '_' + filterConf.name,
					fieldLabel: filterConf.label,
					margin: '10'
				} );
				break;
			case 'boolean':
				filterObj = new Ext.form.field.Checkbox( {
					name: seriesName + '_' + filterConf.name,
					labelAlign: 'top',
					fieldLabel: filterConf.label,
					items: [
						{
							name: 'filter_' + seriesName + '_' + filterConf.name,
							boxLabel: filterConf.label,
							inputValue: true
						}
					]
				} );
				break;
			case 'date':
				filterObj = new Ext.form.field.Date( {
					labelAlign: 'top',
					name: 'filter_' + seriesName + '_' + filterConf.name,
					fieldLabel: filterConf.label
				} );
				break;
		}

		return filterObj;
	},

	generateIntervalFilterSelect: function() {
		return new BS.form.SimpleSelectBox( {
			bsData: [
				{ value: 'daily', name: mw.message( 'bs-statistics-day' ).plain() },
				{ value: 'weekly', name: mw.message( 'bs-statistics-week' ).plain() },
				{ value: 'monthly', name: mw.message( 'bs-statistics-month' ).plain() },
				{ value: 'yearly', name: mw.message( 'bs-statistics-year' ).plain() }
			],
			value: 'daily',
			fieldLabel: mw.message( 'bs-statistics-aggregationtype-label' ).plain(),
			labelAlign: 'top',
			name: 'aggregation[type]',
			editable: false,
			margin: '20 0 0 0'
		} );
	},

	generateTargets: function( excludeTargets ) {
		var me = this;
		this.currentSourceConfig.get('targets').forEach( function( target ) {
			if ( excludeTargets.indexOf( target ) === -1 ) {
				me.aggregationField.add( {
					name: 'targets',
					xtype: 'hiddenfield',
					value: target,
				} );
			}
		});
	},

	stringToDate: function( date, format ) {
		date = date.toString();
		format = format || 'd-m-Y';
		var datePattern = '';
		// Pretty pedestrian - but will do
		switch( format ) {
			case 'd-m-Y':
				datePattern = /(\d{2})\.(\d{2})\.(\d{4})/;
				date = date.replace( datePattern, '$3-$2-$1' );
				break;
			case 'Ymdhis':
				datePattern = /(\d{4})(\d{2})(\d{2})(.*)/;
				date = date.replace( datePattern, '$1-$2-$3' );
				break;
		}

		return new Date( date );
	}

} );
