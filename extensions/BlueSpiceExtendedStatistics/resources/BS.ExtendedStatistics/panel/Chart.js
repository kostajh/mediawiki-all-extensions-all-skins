/**
 * SnapshotStatistics Charts panel
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

Ext.define( 'BS.ExtendedStatistics.panel.Chart', {
	extend: 'Ext.panel.Panel',
	header: false,
	requires: [ 'Ext.chart.axis.Axis', 'Ext.TabPanel', 'Ext.Date' ],
	border: true,
	chartsStore: {},

	initComponent: function() {
		this.dockedItems = [{
			xtype: 'toolbar',
			dock: 'top',
			items: [
				'->',
				this.makeExportButton()
			]
		}];

		this.chartTabs = new Ext.TabPanel( {
			items: []
		} );

		this.items = [
			this.chartTabs
		];

		return this.callParent( arguments );
	},

	getMaxValueForYAxis: function( yFieldArr ) {
		var me = this;
		var maxValue = 0;
		yFieldArr.forEach( function( y ) {
			if( me.chartsStore.max( y.name ) > maxValue) {
				maxValue = me.chartsStore.max( y.name );
			}
		} );
		maxValue = Math.ceil( maxValue * 1.2 );
		return maxValue;
	},

	makeExportButton: function() {
		this.muExport = this.makeExportMenu();
		this.btnExport = new Ext.Button( {
			text: mw.message( 'bs-statistics-button-label-export' ).plain(),
			menu: this.muExport,
			id: 'bs-statistics-mainpanel-exportmenu'
		} );
		return this.btnExport;
	},

	makeExportMenu: function() {
		var menu = new Ext.menu.Menu();

		menu.add( {
			text: 'SVG',
			value: 'image/svg+xml'
		} );

		if( mw.config.get( 'BsExtendedStatisticsAllowPNGExport', false ) === true ) {
			menu.add( {
				text: 'PNG',
				value: 'image/png'
			} );
		}
		menu.on( 'click', this.onClickmuExport, this );
		return menu;
	},

	onClickmuExport: function( menu, item, e, eOpts ) {
		var url = '';
		if( item.value == 'image/png' ) {
			url =  mw.util.getUrl( 'Special:ExtendedStatistics/export-png' );
		} else {
			url = mw.util.getUrl( 'Special:ExtendedStatistics/export-svg' );
		}

		this.chartTabs.activeTab.download( {
			url: url,
			format: item.value,
			width: this.chartTabs.activeTab.getWidth(),
			height: this.chartTabs.activeTab.getHeight()
		} );

	},

	loadCharts: function( apiStore, xFieldObj, yFieldArr ) {
		this.chartTabs.removeAll();
		this.chartsStore = apiStore;
		this.chartTabs.add( this.generateBarChart( apiStore, xFieldObj, yFieldArr ) );
		this.chartTabs.add( this.generateLineChart( apiStore, xFieldObj, yFieldArr ) );
		this.chartTabs.setActiveTab(0);
	},

	generateLineChart: function( apiStore, xFieldObj, yFieldArr) {
		var seriesArr = [];
		var yTitles = [];
		yFieldArr.forEach( function (y) {
			yTitles.push( y.name );
			seriesArr.push( {
				type: 'line',
				xField: xFieldObj.name,
				yField: y.name,
				title: y.label,
				tooltip: {
					anchor: 'bottom',
					trackMouse: true,
					renderer: function( tooltip, record, item ) {
						var html = record.get( item.series.getXField() ) + " <br/> " + item.series.getTitle() + ': ' + record.get( item.series.getYField() );
						tooltip.setHtml( html );
					}
				},
				marker: {
					type: 'circle',
					animation: {
						duration: 200,
						easing: 'backOut'
					}
				},
				highlightCfg: {
					scaling: 2
				},
				style: {
					fill: '#3e5389',
					fillOpacity: 0.6,
					strokeOpacity: 0.6,
				}
			} );
		});

		return new Ext.chart.CartesianChart( {
			title: mw.message( 'bs-statistics-label-line-chart' ).plain(),
			theme: 'blue',
			engine: 'Ext.draw.engine.Svg',
			height: 500,
			store: apiStore,
			legend: {
				type: 'sprite',
				docked: 'bottom'
			},
			axes: [{
				type: 'numeric',
				position: 'left',
				fields: yTitles,
				title: {
					text: '',
					fontSize: 15
				},
				grid: true,
				minimum: 0,
				maximum: this.getMaxValueForYAxis( yFieldArr ),

			}, {
				type: 'category',
				position: 'bottom',
				fields: [ xFieldObj.name ],
				title: {
					text: xFieldObj.label,
					fontSize: 10
				},
				grid: true,
				label: {
					rotate: {
						degrees: 270
					}
				}
			}],
			series: seriesArr
		} );

	},

	generateBarChart: function( apiStore, xFieldObj, yFieldArr ) {
		var yTitles = [];
		var yFieldNames = [];
		yFieldArr.forEach( function ( y ) {
			yTitles.push( y.label );
			yFieldNames.push( y.name );
		} );

		var series = {
			type: 'bar3d',
			xField: xFieldObj.name,
			yField: yFieldNames,
			title: yTitles,
			highlight: true,
			label: {
				field: yFieldNames,
				display: 'insideEnd'
			},
			tooltip: {
				anchor: 'bottom',
				trackMouse: true,
				renderer: function( tooltip, record, item ) {
					var html = record.get( item.series.getXField() ) + '<br/>';

					for ( var i = 0; i < yTitles.length; i++ ) {
						html += yTitles[ i ] + ': ' + record.get( item.series.getYField()[i] ) + '<br/>';
					}
					tooltip.setHtml( html );
				}
			}
		};

		return new Ext.chart.CartesianChart( {
			title: mw.message( 'bs-statistics-label-bar-chart' ).plain(),
			engine: 'Ext.draw.engine.Svg',
			height: 500,
			store: apiStore,
			innerPadding: '0 10 0 10',
			legend: {
				type: 'sprite',
				docked: 'bottom'
			},
			axes: [{
				type: 'numeric3d',
				position: 'left',
				fields: yTitles,
				title: {
					text: 'items',
					fontSize: 15
				},
				grid: {
					odd: {
						fillStyle: 'rgba(255, 255, 255, 0.06)'
					},
					even: {
						fillStyle: 'rgba(0, 0, 0, 0.03)'
					}
				}

			}, {
				type: 'category3d',
				position: 'bottom',
				fields: xFieldObj.name,
				label: {
					fontSize: 12,
					rotation: {
						degrees: 270
					}
				}
			}],
			series: series
		} );
	},
});
