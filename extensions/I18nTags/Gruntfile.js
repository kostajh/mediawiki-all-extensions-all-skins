/*jshint node:true */
module.exports = function ( grunt ) {
	'use strict';

	grunt.loadNpmTasks( 'grunt-jsonlint' );
	grunt.loadNpmTasks( 'grunt-banana-checker' );

	grunt.initConfig( {
		jsonlint: {
			all: [
				'**/*.json',
				'!node_modules/**',
				'!vendor/**'
			]
		},
		banana: {
			all: 'i18n/'
		}
	} );

	grunt.registerTask( 'test', [ 'jsonlint', 'banana' ] );
	grunt.registerTask( 'default', 'test' );
};
