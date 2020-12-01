/**
 * Statistics portlet base
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

Ext.define('BS.Statistics.StatisticsPortlet', {
	extend: 'BS.portal.ChartPortlet',
	height: 350,
	axes: [],
	series:[],
	portletConfigClass : 'BS.Statistics.StatisticsPortletConfig',
	filters: [],
	diagram: '',
	beforeInitCompontent: function() {

		this.ctMainConfig = {
			axes: [],
			series: [],
			yTitle: mw.message( this.titleKey ).plain()
		};

		this.ctMainConfig.store = Ext.create('Ext.data.JsonStore', {
			fields: ['name', 'hits'],
			proxy: {
				type: 'ajax',
				url: this.makeStatisticsApiUrl(),
				actionMethods: {
					read   : 'POST'
				},
				reader: {
					type: 'json',
					rootProperty: 'payload.data'
				},
				extraParams: {
					token: mw.user.tokens.get( 'csrfToken' )
				}
			},
			autoLoad: true
		});

		Ext.Ajax.on('requestcomplete', this.onRequestComplete, this);

		this.ctMainConfig.axes.push({
			type: 'numeric',
			position: 'left',
			fields: ['hits'],
			title: this.ctMainConfig.yTitle,
			grid: {
				odd: {
					opacity: 1,
					fill: '#ddd',
					stroke: '#bbb',
					'stroke-width': 0.5
				}
			}
		});

		this.ctMainConfig.axes.push({
			type: 'category',
			position: 'bottom',
			fields: ['name']
		});

		this.ctMainConfig.series.push({
			type: 'line',
			highlight: {
				size: 7,
				radius: 7
			},
			axis: 'left',
			smooth: false,
			xField: 'name',
			yField: 'hits',
			marker: {
				radius: 4,
				lineWidth: 2
			},
		});

		this.on( 'configchange', this.onConfigChange, this);

	},

	afterInitComponent: function() {

	},

	onRequestComplete: function( conn, response, options) {
		if( typeof options.params.portletType === 'undefined' ) return;
		if( options.params.portletType !== 'ExtendedStatistics') return;

		var x = Ext.decode(response.responseText);
		this.ctMain.axes.getAt(1).title = x.label;

	},
	getPortletConfig: function() {
		var cfg = this.callParent(arguments);
		cfg.inputPeriod = this.inputPeriod;
		return cfg;
	},
	setPortletConfig: function( oConfig ) {
		this.inputPeriod = oConfig.inputPeriod;
		this.callParent(arguments);
	},

	getPeriod: function() {
		var oConfig = this.getPortletConfig();
		var oDate = new Date();

		switch(oConfig.inputPeriod) {
			case 'day':
				oDate.setDate(oDate.getDate() - 1);
				break;
			case 'week':
				oDate.setDate(oDate.getDate() - 7);
				break;
			case 'month':
				oDate.setDate(oDate.getDate() - 30);
				break;
		}

		return oDate;
	},

	getGrain: function() {
		var oConfig = this.getPortletConfig();
		var grain = 'd';
		switch(oConfig.inputPeriod) {
			case 'month':
				grain = 'W';
				break;
		}
		return grain;
	},

	onConfigChange: function( oConfig ) {
		this.ctMainConfig.store.getProxy().extraParams.inputFrom = Ext.Date.format( this.getPeriod(), 'd.m.Y' )
		this.ctMainConfig.store.getProxy().extraParams.InputDepictionGrain = this.getGrain();
		this.ctMainConfig.store.load();
	},

	makeStatisticsApiUrl: function() {
		return bs.api.makeUrl(
				'bs-statistics-tasks',
				{
					task: 'getData',
					taskData: JSON.stringify({
						portletType: 'ExtendedStatistics',
						diagram: this.diagram,
						mode: 'aggregated',
						to: Ext.Date.format(new Date(),'d.m.Y'),
						from: Ext.Date.format(this.getPeriod(), 'd.m.Y'),
						grain: this.getGrain()
					}),
				},
				true
		)
	}
});