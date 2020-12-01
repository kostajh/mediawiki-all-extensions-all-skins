( function ( mw ) {

	var moduleCollector = function runModuleCollector( data ) {
		var modulesTemplate = mw.template.get( 'ext.PerformanceInspector.analyze', 'moduleslocalstorage.mustache' ),
		humanSize = module.exports.humanSize;

		return {
			summary: {},
			view: {
				name: 'performanceinspector-modules-localstorage-name',
				label: 'performanceinspector-modules-localstorage-label',
				template: modulesTemplate,
				data: {
					store: {
						enabled: data.inspect.store.enabled,
						hits: data.inspect.store.hits,
						misses: data.inspect.store.misses,
						expired: data.inspect.store.expired,
						totalSize: humanSize( data.inspect.store.totalSizeInBytes )
					}
				}
			}
		};
	};

	module.exports.collectors.push( moduleCollector );
}( mediaWiki ) );
