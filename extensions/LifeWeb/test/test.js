console.log( 'Loaded test.js' );
var token;

mediaWiki.loader.load( 'mediawiki.api' );
console.log( 'mediawiki.api', mediaWiki.loader.getState( 'mediawiki.api' ) );
console.log( 'mediawiki.api.parse', mediaWiki.loader.getState( 'mediawiki.api.parse' ) );

mediaWiki.loader.using( 'mediawiki.api', function () {
	var api = new mediaWiki.Api();

	// mediaWiki.parse for testing (fails with parseerror)
	mediaWiki.loader.using( 'mediawiki.api.parse', function () {
		console.log( 'mediawiki.api.parse', mediaWiki.loader.getState( 'mediawiki.api.parse' ) );
		api.parse( "'''Hello world'''" )
			.done( function ( data ) { console.log( data ); } )
			.fail( function ( data ) { console.log( 'Parsing failed.', data ); } );
	}, function () {
		console.log( 'api.parse not loaded.' );
	} );
} );

// mediaWiki.user to get token (fails)
mediaWiki.loader.load( 'mediawiki.user' );
console.log( 'mediawiki.user', mediaWiki.loader.getState( 'mediawiki.user' ) );
console.log( 'using user module' );
mediaWiki.loader.using( [ 'mediawiki.user' ], function () {
	console.log( 'User:', mediaWiki.user, mediaWiki.user.getName() );
	token = mediaWiki.user.tokens.get( 'csrfToken' );
	console.log( 'Edit token:', token );
}, function ( msg ) {
	console.log( 'Loading module failed' );
	console.log( msg );
} );

console.log( 'wikibase.RepoApi', mediaWiki.loader.getState( 'wikibase.RepoApi' ) );
mediaWiki.loader.using( [ 'wikibase.RepoApi' ], function () {
	console.log( 'wikibase.RepoApi', mediaWiki.loader.getState( 'wikibase.RepoApi' ) );
} );

// Manual Ajax call to get token (works)
token = null;
jQuery.ajax( 'http://mediawiki/mediawiki-1.21.1/api.php', {
	async: false,
	type: 'GET',
	dataType: 'json',
	data: {
		action: 'tokens',
		type: 'edit',
		format: 'json'
	}
} ).done( function ( data ) {
	console.log( data );
	token = data.tokens.edittoken;

} );
console.log( 'Token (API): ', token );

mediaWiki.loader.using( 'wikibase.RepoApi', function () {
	var repoApi = new wikibase.RepoApi();

	repoApi.getEntities( 'p96' ).always( function ( data ) {
		console.log( 'Entity p96:', data );
	} );

	repoApi.get( {
		action: 'wbsearchentities',
		search: 'instanz',
		language: 'de',
		type: 'property'
	} ).done( function ( data ) {
		console.log( data );
	} ).fail( function ( data ) { console.log( 'Failed.', data ); } );

	jQuery( '#test' ).on( 'click', function () {
		var label = prompt( 'Label?' );

		repoApi.createEntity( 'item', {
			labels: {
				de: {
					language: 'de',
					value: label
				}
			}
		} ).done( function ( data ) {
			console.log( 'Create Item: done: ', data.entity.id, data );
		} ).fail( function ( data ) {
			console.log( 'Create Item: fail', data );
		} );

		repoApi.createEntity( 'property', {
			datatype: 'wikibase-item',
			labels: {
				de: {
					language: 'de',
					value: 'prop' + label
				}
			}
		} ).done( function ( data ) {
			console.log( 'Create Property: done: ', data.entity.id, data );
			// repoApi.editEntity(data.entity.id, data.entity.lastrevid, { type : 'property' }).always( function(data) { console.log(data); });
		} ).fail( function ( data ) {
			console.log( 'Create Property: fail', data );
		} );
	} );
} );

/*
 $('#test').click( function() {
 $.ajax('http://mediawiki/mediawiki-1.21.1/api.php?action=wbeditentity&data={}&format=json', {
 'type' : 'POST',
 'dataType' : 'json',
 'data' : {
 'action' : 'wbeditentity',
 'data' : {},
 'format' : 'json',
 'token' : token
 }
 }).done( function(data) {
 $('#return').text(JSON.stringify(data, null, '  '));
 });

 //api.getEditToken( function(token) { console.log('Token received!', token); } );
 });
 */
