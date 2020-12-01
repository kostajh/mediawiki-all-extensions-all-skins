module.exports = function ( grunt ) {
	grunt.loadNpmTasks( 'grunt-banana-checker' );

	var conf = grunt.file.readJSON( 'extension.json' );
	grunt.initConfig( {
		banana: Object.assign(
			conf.MessagesDirs,
			{
				options: {
					requireLowerCase: 'initial'
				}
			}
		),
	} );

	grunt.registerTask( 'test', [ 'banana' ] );
	grunt.registerTask( 'default', 'test' );
};
