/**
 * SnapshotStatistics Apply Button Toolbar
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Oleksandr Pinchuk <intracomof@gmail.com>
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2019 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

Ext.define( 'BS.ExtendedStatistics.toolbar.Apply', {
	extend: 'Ext.Toolbar',
	header: false,
	requires: [
		'Ext.Button',
		'BS.store.BSApi',
		'Ext.Toolbar',
		'Ext.data.JsonStore',
		'Ext.util.Grouper'
	],
	border: 0,
	filters: {},
	charts: {},
	MAIN_NAMESPACE_VALUE: 'main',

	initComponent: function () {
		var me = this;

		me.loadedDataStoresCount = 0;
		me.series = [];

		me.applyFilterSettings = function() {
			var formFilters = me.filters.getValues();
			me.series = [];

			if ( Ext.isArray( formFilters.series ) ) {
				me.series = formFilters.series;
			}

			if ( Ext.isString( formFilters.series ) ) {
				me.series.push( formFilters.series );
			}

			if ( formFilters.series.length < 1 ) {
				return;
			}

			var generalFilters = [];
			me.sort = [];

			me.xFieldObj = { name: 'date', label: 'date' };

			me.yFieldArr = [];

			var aggregation = [];
			if( formFilters['aggregation[property]'] !== 'none' ) {
				if ( formFilters[ 'aggregation[property]' ] !== 'timestampcreated' ) {
					me.xFieldObj.name = formFilters[ 'aggregation[property]' ];
					me.xFieldObj.label = me.filters.dynamicFiltersConfig[ me.xFieldObj.name ][ 'label' ];
					me.sort = me.buildSortFull( me.filters.currentSourceConfig.get( 'series' ) );
				}
				me.sort.push( { property: 'timestampcreated', direction: 'ASC' } );
				aggregation = me.buildAggregation( me.filters.getValues() );
			}

			var startDate = Ext.Date.format( me.filters.stringToDate( formFilters.startDate ), 'Ymdhis' );
			var endDate = Ext.Date.format( me.filters.stringToDate( formFilters.endDate ), 'Ymdhis' );

			generalFilters.push( { property: 'type', type: 'string', value: formFilters.datasource, comparison: 'eq' } );
			generalFilters.push( { property: 'timestampcreated', type: 'date', value: startDate, comparison: "gt" } );
			generalFilters.push( { property: 'timestampcreated', type: 'date', value: endDate, comparison: "lt" } );

			var responseFields = Object.keys( me.filters.currentSourceConfig.get( 'attributes' ) );
			responseFields.push( {
				name: 'date',
				calculate: function( data ) {
					return Ext.Date.format( me.filters.stringToDate( data.timestampcreated, 'Ymdhis' ), "m/d/Y" );
				}
			} );

			me.seriesDataStores = [];
			me.loadedDataStoresCount = 0;

			me.series.forEach( function( seriesName ) {
				var seriesFilters = [];
				var filtersStrings = [];
				var seriesLabel = me.filters.currentSourceConfig.get( 'seriesLabels' )[ seriesName ];
				Object.keys( me.filters.dynamicFiltersConfig ).forEach( function( key ) {
					var value = formFilters[ 'filter_' + seriesName + '_' + key ];
					if ( value ) {
						filtersStrings.push( value + ' ' + me.filters.currentSourceConfig.get( 'filtersLabels' )[ key ] );
						// this workaround is needed
						// because main namespace has empty value
						if ( key === 'namespacename' && value.toLowerCase() === me.MAIN_NAMESPACE_VALUE ) {
							value = '';
						}
						seriesFilters.push( me.buildDynamicFilter( key, value ) );
					}
				});

				if ( filtersStrings.length > 0 ) {
					seriesLabel = seriesLabel + ' (' + filtersStrings.join( ',' ) + ')';
				}

				me.yFieldArr.push( { label: seriesLabel, name: seriesName } );

				var dataStore = new BS.store.BSApi( {
					apiAction: 'bs-extendedstatistics-collection-store',
					proxy: {
						extraParams: {
							aggregate: Ext.encode( aggregation ),
							filter: Ext.encode( Ext.Array.merge( generalFilters, seriesFilters) ),
							sort: Ext.encode( me.sort ),
							limit: -1
						}
					},
					fields: responseFields,
					autoLoad: true
				} );

				me.seriesDataStores[ seriesName ] = {
					label: seriesLabel,
					dataStore: dataStore
				};

				dataStore.on( 'load', function() {
					me.mergeDataStores();
				}, this );

			} );
		};

		me.mergeDataStores = function() {
			me.loadedDataStoresCount++;
			// waiting for all dataStores loaded
			if ( me.loadedDataStoresCount < me.series.length ) {
				return;
			}

			var aggregationProperty = me.filters.getValues()['aggregation[property]'];
			var storeFields = Object.keys( me.filters.currentSourceConfig.get( 'attributes' ) );

			var mergedStore = new Ext.data.JsonStore( {
				sorters: me.sort,
				autoSort: true,
				fields: storeFields
			} );

			var emptyObject = {};
			storeFields.forEach( function( property ) {
				if (property !== 'id') {
					emptyObject[ property ]	= 0;
				}
			} );

			me.series.forEach( function( seriesName ) {
				var data = me.seriesDataStores[ seriesName ][ 'dataStore' ].getRange();
				for( var i = 0; i < data.length; i++ ) {

					var aggregatedProperty = data[ i ][ 'data' ][ aggregationProperty ];
					if ( aggregatedProperty === '' ) {
						aggregatedProperty = 'Main';
					}
					var objectToUpdate = mergedStore.getById( aggregatedProperty);
					if ( objectToUpdate ) {
						objectToUpdate.set( seriesName, data[ i ][ 'data' ][ seriesName ] );
					} else {
						var newDataObj = Object.assign({}, emptyObject);
						newDataObj[ 'id' ] = aggregatedProperty;
						newDataObj[ 'date' ] = data[ i ][ 'data' ][ 'date' ];

						newDataObj[ seriesName ] = parseInt( data[ i ][ 'data' ][ seriesName ] );
						newDataObj[ aggregationProperty ] = aggregatedProperty;

						mergedStore.add( newDataObj );
					}
				}
			} );

			me.charts.loadCharts( mergedStore, me.xFieldObj, me.yFieldArr );
		};

		me.applyButton = new Ext.Button( {
			cls: 'x-btn-progressive',
			text: mw.message( 'bs-statistics-button-label-apply' ).plain(),
			listeners: {
				click: me.applyFilterSettings
			}
		} );

		me.items = [
			'->',
			me.applyButton
		];

		me.buildDynamicFilter = function( filterName, value) {
			var filter = {
				property: filterName,
				type: me.filters.dynamicFiltersConfig[ filterName ][ 'type' ],
				value: value
			};
			return filter;
		};

		me.buildAggregation = function( filters ) {
			var aggregate = [];
			aggregate.push( {
				property: filters[ 'aggregation[property]' ],
				type: filters[ 'aggregation[type]' ],
				targets: filters[ 'targets' ]
			} );

			return aggregate;
		};

		me.buildSortFull = function( series ) {
			var sort = [];
			series.forEach( function( property ) {
				sort.push( { property: property.name, direction: 'ASC' } );
			} );

			return sort;
		};

		this.callParent();
	}

} );
