/* eslint-env node, es6 */
module.exports = function ( grunt ) {
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.initConfig( {
		eslint: {
			options: {
				extensions: [ '.js', '.json' ],
				cache: true
			},
			all: '.'
		},
		banana: {
			all: 'i18n/'
		}
	} );
	grunt.registerTask( 'lint', [ 'eslint', 'banana' ] );
	grunt.registerTask( 'test', [ 'lint' ] );
	grunt.registerTask( 'default', 'test' );
};
