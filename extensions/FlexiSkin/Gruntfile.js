module.exports = function ( grunt ) {
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-banana-checker' );

	var conf = grunt.file.readJSON( 'extension.json' );
	grunt.initConfig( {
		eslint: {
			options: {
				extensions: [ '.json' ],
				cache: true
			},
			all: [
				'**/*.json',
				'!node_modules/**',
				'!vendor/**'
			]
		},
		banana: conf.MessagesDirs
	} );

	grunt.registerTask( 'test', [ 'eslint', 'banana' ] );
	grunt.registerTask( 'default', 'test' );
};
