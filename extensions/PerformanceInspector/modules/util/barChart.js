// eslint-disable-next-line no-implicit-globals
function generate( html, name ) {

	var values = [],
	maxValue;

	$( html ).find( name ).each( function () {
		values.push( $( this ).data( 'value' ) );
	} );

	maxValue = Math.max.apply( Math, values );

	$( html ).find( name ).each( function () {
		var bar = $( this ),
			value = bar.data( 'value' ),
			percent = Math.ceil( ( value / maxValue ) * 100 );
		bar.width( percent + '%' );
	} );
}

module.exports.barChart = generate;
