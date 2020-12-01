Ext.override( Ext.grid.filters.filter.Base, {
	getFilterConfig: function() {
		var config = this.callParent( arguments );

		/**
		 * ExtJS 6 does not send the 'type' to the server in 'remoteFilter'
		 * scenarios anymore. Some implemenations (BlueSpiceFoundation) rely on
		 * this information in order to automatically filter store reponses.
		 * This is a shim.
		 */
		config.type = this.type;
		return config;
	}
} );