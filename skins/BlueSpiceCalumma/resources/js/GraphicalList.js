$(document).on( 'click', "*[data-graphicallist-callback]", function( e ) {
	var $elem = $(this);
	var key = $elem.data("graphicallist-callback");

	var direction= "east";
	if( $elem.has("data-graphicallist-direction") ) {
		direction = $elem.data("graphicallist-direction");
	}

	var flyoutRegistry = mw.config.get('bsGraphicalListRegistry');
	var callback = flyoutRegistry[key];

	//e.g. callback = "bs.extension.flyout.someKey"
	var parts = callback.split( '.' );
	var flyoutFactory = window[parts[0]];
	for( var i = 1; i < parts.length; i++ ) {
		flyoutFactory = flyoutFactory[parts[i]];
	}

	var data = {};
	$elem.dynamicGraphicalList({
		title: data.flyoutTitle,
		intro: data.flyoutIntro,
		body: function() {

		},
		direction: direction
	}).toggle();

	e.preventDefault();
	return false;
});
