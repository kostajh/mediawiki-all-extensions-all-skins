( function( mw, $ ){
	$( document ).ready( function() {
		$( 'Canvas[id^="bs-tagcloud-canvas3d-"]').each( function() {
			var $tagConvas = $( this ).tagcanvas( {
				textColour: '#3e5389',
				outlineColour: '#3e5389',
				reverse: true,
				depth: 0.8,
				maxSpeed: 0.05,
				weight: true,
				weightMode: 'size',
				wheelZoom: false,
				weightFrom: "data-weight",
				shadowBlur: 5,
				shadowOffset: [1,1],
				txtScale: 10,
				zoom: 1
			});
			if( !$tagConvas ) {
				$( this ).hide();
			}
			$( this ).width = "100%";
			$( this ).width( "100%" );
		});
	});
}( mediaWiki, jQuery ));