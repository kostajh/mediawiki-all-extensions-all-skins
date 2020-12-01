/**
 * Entities and properties that are required for LifeWeb
 *
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

var LW = LW || {};

LW.wb = LW.wb || {};

LW.wb.repoApi = LW.wb.repoApi || new wikibase.RepoApi();

LW.wb.BaseEntity = function ( data ) {
	if ( !( this instanceof LW.wb.BaseEntity ) ) { return new LW.wb.BaseEntity( data ); }

	this.data = data;
	this.pid = undefined;
	this.oid = undefined;

	return this;
};
LW.wb.BaseEntity.prototype.setID = function ( pid ) {
	this.pid = pid;
	this.oid = {
		'entity-type': 'item',
		'numeric-id': Number( pid.substr( 1 ) )
	};
};
LW.wb.BaseEntity.prototype.init = function () {

	var deferred = jQuery.Deferred(),
		entity = this,
		lang = undefined;

	for ( var lid in this.data.labels ) {
		if ( this.data.labels.hasOwnProperty( lid ) ) {
			lang = lid;
			break;
		}
	}

	if ( lang === undefined ) {
		deferred.reject( {
			error: 'No labels given'
		} );
		return deferred;
	}

	LW.wb.searchExactEntity( this.data.labels[ lang ], lang, this.data.type ).done( function ( data ) {
		if ( data.success && data.search.length > 0 ) {

			var pid = data.search[ 0 ].id;
			entity.setID( pid );

		} else {
			console.log( entity.data.labels[ lang ], ' not found', data );

			var entityData = {};

			if ( entity.data.type === 'property' ) {
				entityData.datatype = entity.data.datatype;
			}

			entityData.labels = {};
			for ( var lid in entity.data.labels ) {
				if ( entity.data.labels.hasOwnProperty( lid ) ) {
					entityData.labels[ lid ] = {
						language: lid,
						value: entity.data.labels[ lid ]
					};
				}
			}
			LW.wb.repoApi.createEntity( entity.data.type, entityData ).done( function ( data ) {

				entity.setID( data.entity.id );
				deferred.resolve();

			} ).fail( function () {
				deferred.reject( {
					error: 'Could not create entity'
				} );
			} );

		}
		deferred.resolve();

	} ).fail( function () { deferred.reject(); } );

	return deferred;

};
LW.wb.searchExactEntity = function ( search, language, type ) {
	var deferred = jQuery.Deferred();

	LW.wb.repoApi.get( {
		action: 'wbsearchentities',
		search: search,
		language: language,
		type: type,
		limit: 1,
		continue: 0
	} ).fail( function ( details ) {
		console.log( 'Search command failed.', details );
		deferred.fail();
	} ).done( function ( data ) {

		if ( data.search.length > 0 ) {
			if ( data.search[ 0 ].label !== search ) {
				console.log( 'Not exact match', data );
				data.search = [];
			}
		} else {
			console.log( 'API found nothing', data );
		}
		deferred.resolve( data );
	} );

	return deferred;
};

LW.wb.baseEntityModel = {

	jobList: new LW.JobList( 'Fetching base items' ),

	init: function ( parallel ) {

		var baseEntity,
			count = 0,
			desc;
		for ( var entity in this.entity ) {
			if ( this.entity.hasOwnProperty( entity ) ) {

				baseEntity = this.entity[ entity ];
				desc = 'Entity ' + ( ++count ) + ': ' + JSON.stringify( baseEntity.data.labels );

				this.jobList.add( new LW.Job( desc, ( function ( entity ) {
					return function () {
						return entity.init();
					};
				}( baseEntity ) ) ) );

			}
		}

		this.init = function () {};

		return this.jobList.start( Number( parallel ) || 1 );
	},

	entity: {
		pInstanceOf: new LW.wb.BaseEntity( {
			type: 'property',
			datatype: 'wikibase-item',
			labels: { 'en-gb': 'instance of', de: 'Instanz von' }
		} ),
		pTopic: new LW.wb.BaseEntity( {
			type: 'property',
			datatype: 'wikibase-item',
			labels: { 'en-gb': 'Topic', de: 'Thema' }
		} ),
		pImage: new LW.wb.BaseEntity( {
			type: 'property',
			datatype: 'string',
			labels: { 'en-gb': 'image', de: 'Bild' }
		} ),
		pLatin: new LW.wb.BaseEntity( {
			type: 'property',
			datatype: 'string',
			labels: { 'en-gb': 'latin name', de: 'Lateinischer Name' }
		} ),
		pComponent: new LW.wb.BaseEntity( {
			type: 'property',
			datatype: 'wikibase-item',
			labels: { 'en-gb': 'component', de: 'Komponente' }
		} ),
		pParent: new LW.wb.BaseEntity( {
			type: 'property',
			datatype: 'wikibase-item',
			labels: { 'en-gb': 'parent', de: 'übergeordnetes Element' }
		} ),
		pParentQuestion: new LW.wb.BaseEntity( {
			type: 'property',
			datatype: 'wikibase-item',
			labels: { 'en-gb': 'parent question', de: 'gehört zu Frage' }
		} ),
		pParentCharacter: new LW.wb.BaseEntity( {
			type: 'property',
			datatype: 'wikibase-item',
			labels: { 'en-gb': 'refines character', de: 'präzisiert Merkmal' }
		} ),
		pHasCharacter: new LW.wb.BaseEntity( {
			type: 'property',
			datatype: 'wikibase-item',
			labels: { 'en-gb': 'has character', de: 'hat Merkmal' }
		} ),
		pDifficulty: new LW.wb.BaseEntity( {
			type: 'property',
			datatype: 'wikibase-item',
			labels: { 'en-gb': 'difficulty', de: 'Schwierigkeit' }
		} ),
		pLevel: new LW.wb.BaseEntity( {
			type: 'property',
			datatype: 'string',
			labels: { 'en-gb': 'level', de: 'Level' }
		} ),
		pEquipment: new LW.wb.BaseEntity( {
			type: 'property',
			datatype: 'wikibase-item',
			labels: { 'en-gb': 'equipment', de: 'equipment' }
		} ),
		pDegree: new LW.wb.BaseEntity( {
			type: 'property',
			datatype: 'wikibase-item',
			labels: { 'en-gb': 'taxon rank', de: 'taxonomischer Rang' }
		} ),
		pTime: new LW.wb.BaseEntity( {
			type: 'property',
			datatype: 'string',
			labels: { 'en-gb': 'time required [s]', de: 'Zeitaufwand [s]' }
		} ),

		qTopic: new LW.wb.BaseEntity( {
			type: 'item',
			labels: { 'en-gb': 'Topic', de: 'Thema' }
		} ),
		qTaxon: new LW.wb.BaseEntity( {
			type: 'item',
			labels: { 'en-gb': 'Taxon', de: 'Taxon' }
		} ),
		qComponent: new LW.wb.BaseEntity( {
			type: 'item',
			labels: { 'en-gb': 'Component', de: 'Komponente' }
		} ),
		qQuestion: new LW.wb.BaseEntity( {
			type: 'item',
			labels: { 'en-gb': 'Question', de: 'Frage' }
		} ),
		qCharacter: new LW.wb.BaseEntity( {
			type: 'item',
			labels: { 'en-gb': 'Character', de: 'Merkmal' }
		} ),
		qDifficulty: new LW.wb.BaseEntity( {
			type: 'item',
			labels: { 'en-gb': 'Difficulty', de: 'Schwierigkeit' }
		} ),
		qEquipment: new LW.wb.BaseEntity( {
			type: 'item',
			labels: { 'en-gb': 'Equipment', de: 'Ausrüstung' }
		} ),
		qDegree: new LW.wb.BaseEntity( {
			type: 'item',
			labels: { 'en-gb': 'Taxonomic degree', de: 'Taxonomischer Grad' }
		} ),
		qCollection: new LW.wb.BaseEntity( {
			type: 'item',
			labels: { 'en-gb': 'Collection', de: 'Sammlung' }
		} )
	}
};
// / Shorthand
// / @type {LW.wb.baseEntityModel.entity}
LW.entity = LW.wb.baseEntityModel.entity;
