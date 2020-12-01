module.exports = function ( grunt ) {
	var conf = grunt.file.readJSON( 'extension.json' );
	grunt.loadNpmTasks( 'grunt-jsonlint' );
	grunt.loadNpmTasks( 'grunt-banana-checker' );

	grunt.initConfig( {
		banana: conf.MessagesDirs,
		jsonlint: {
			all: [
				'**/*.json',
				'!node_modules/**',
				'!vendor/**'
			]
		}
	} );

	grunt.registerTask( 'lint', ['jsonlint', 'banana'] );
	grunt.registerTask( 'test', ['lint'] );
	grunt.registerTask( 'default', ['test'] );
};
