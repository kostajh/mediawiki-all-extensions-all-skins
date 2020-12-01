Ext.define( 'BS.Statistics.panel.Chart', {
	extend: 'Ext.panel.Panel',
	header: false,

	bsPayload: {},

	initComponent: function() {
		this.dockedItems = [{
			xtype: 'toolbar',
			dock: 'top',
			items: [
				'->',
				this.makeExportButton()
			]
		}];

		this.crtMain = new Ext.chart.CartesianChart( {
			theme: 'blue',
			engine: 'Ext.draw.engine.Svg', //Default of 'Ext.draw.engine.Canvas' does not allow for SVG export anymore
			height: 500,
			store: {
				fields: [ 'name', 'hits' ],
				data: this.bsPayload.data
			},
			axes: [ {
				title: mw.message( 'bs-statistics-label-count' ).plain(),
				type: 'numeric',
				position: 'left',
				grid: true,
				minimum: this.getMinValueForYAxis(),
				maximum: this.getMaxValueForYAxis(),
			}, {
				title: this.bsPayload.label,
				type: 'category',
				position: 'bottom',
				grid: true,
				label: {
					rotate: {
						degrees: -45
					}
				}
			} ],
			series: [ {
				type: 'line',
				xField: 'name',
				yField: 'hits',
				style: {
					lineWidth: 2
				},
				marker: {
					radius: 4,
					lineWidth: 2
				},
				label: {
					field: 'hits'
				}
			} ]
		} );

		this.items = [
			this.crtMain
		];

		return this.callParent( arguments );
	},

	getMaxValueForYAxis: function() {
		var maxValue = 0;
		for( var i = 0; i < this.bsPayload.data.length; i++ ) {
			if( this.bsPayload.data[i].hits > maxValue ) {
				maxValue = this.bsPayload.data[i].hits;
			}
		}

		return maxValue + 2;
	},

	getMinValueForYAxis: function() {
		var minValue = this.bsPayload.data[1].hits;
		for( var i = 0; i < this.bsPayload.data.length; i++ ) {
			if( this.bsPayload.data[i].hits < minValue ) {
				minValue = this.bsPayload.data[i].hits;
			}
		}

		return minValue - 2;
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
		if(item.value == 'image/png') {
			url =  mw.util.getUrl( 'Special:ExtendedStatistics/export-png' );
		}
		else {
			url = mw.util.getUrl( 'Special:ExtendedStatistics/export-svg' );
		}

		this.crtMain.download( {
			url: url,
			format: item.value,
			width: this.crtMain.getWidth(),
			height: this.crtMain.getHeight()
		} );
	}
});