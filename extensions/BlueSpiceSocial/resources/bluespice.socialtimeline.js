bs.social = bs.social || {};
bs.social.EntityListMenuFilters = bs.social.EntityListMenuFilters || {};
bs.social.EntityListMenuOptions = bs.social.EntityListMenuOptions || {};
$( document ).bind( 'BSSocialInit', function( event ) {
	/*$( ".bs-social-entitylist-menu" ).each( function() {
		if( bs.social.getUiID( $(this) ) ) {
			//already exists
			return null;
		}
		new bs.social.EntityListMenu( $(this) );
	});*/
	$( ".bs-social-entitylist" ).each( function() {
		if( bs.social.getUiID( $(this) ) ) {
			//already exists
			return null;
		}
		new bs.social.EntityList( $(this) );
	});
	$( ".bs-social-entityspawner" ).each( function() {
		if( bs.social.getUiID( $(this) ) ) {
			//already exists
			return null;
		}
	});
});

$( document ).bind( 'BSSocialEntityListMenuInit', function( event, EntityListMenu ) {
	var entitySpawnerButton = new bs.social.EntityListMenu.Button.EntitySpawner( EntityListMenu );
	entitySpawnerButton.init();
});