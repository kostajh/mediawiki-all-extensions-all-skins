/**
 * @license GPL-2.0-or-later
 */
( function () {
	QUnit.module( 'wikibase.lexeme.services.ItemLookup' );
	var ItemLookup = require( '../../../resources/services/ItemLookup.js' );

	var getMockApiWithResponse = function ( response ) {
			return {
				getEntities: function () {
					var deferred = $.Deferred();
					deferred.resolve( response );
					return deferred;
				}
			};
		},
		getFailingMockApi = function () {
			return {
				getEntities: function () {
					var deferred = $.Deferred();
					deferred.reject();
					return deferred;
				}
			};
		},
		newLookupWithApi = function ( api ) {
			return new ItemLookup( api );
		};

	QUnit.test( 'requires RepoApi', function ( assert ) {
		assert.throws( function () {
			new ItemLookup();
		} );
	} );

	QUnit.test( 'returns the entity from the API response', function ( assert ) {
		var responseItem = { id: 'Q123' },
			lookup = newLookupWithApi( getMockApiWithResponse( {
				entities: {
					Q123: responseItem
				}
			} ) );

		lookup.fetchEntity( 'Q123' ).done( function ( item ) {
			assert.deepEqual( responseItem, item );
		} );
	} );

	QUnit.test( 'fails for failing API', function ( assert ) {
		var lookup = newLookupWithApi( getFailingMockApi() );

		lookup.fetchEntity( 'Q123' ).fail( function () {
			assert.ok( true );
		} );
	} );

	QUnit.test( 'fails for unexpected API response', function ( assert ) {
		var lookup = newLookupWithApi( getMockApiWithResponse( { foo: 'bar' } ) );

		lookup.fetchEntity( 'Q123' ).fail( function () {
			assert.ok( true );
		} );
	} );

}() );
