
$( document ).bind( 'BSSocialEntityListInit', function( event, EntityList, $el ) {
	new bs.social.EntityList.CreateDiscussionPage(
		$el,
		EntityList
	);
});

$( document ).bind( 'BSSocialInit', function( bssocial ) {
	new bs.social.CreateDiscussion(
		$( this )
	);
});

