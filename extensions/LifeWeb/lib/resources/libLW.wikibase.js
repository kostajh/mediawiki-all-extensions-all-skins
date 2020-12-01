/**
 * Extensions to edit LW items on Wikibase
 *
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

LW.mwApi = new mediaWiki.Api();
LW.mwAjaxQuery = function ( action ) {
	return LW.mwApi.get( {
		action: 'query',
		list: 'LifeWeb',
		format: 'json',
		what: action
	} );
};

LW.wb.baseEntityModel.init( 2 );

LW.Functions = {};
LW.Functions.oid = function ( id ) {
	return {
		'entity-type': 'item',
		'numeric-id': Number( id.substr( 1 ) )
	};
};
LW.Functions.arrayToObject = function ( arr ) {
	var obj = {};
	for ( var a = 0, A = arr.length; a < A; a++ ) {
		obj[ arr[ a ].id ] = arr[ a ];
	}
	return obj;
};

LW.Abstract = {};
LW.Abstract.oid = function () {
	return {
		'entity-type': 'item',
		'numeric-id': Number( this.id.substr( 1 ) )
	};
};
LW.Taxon.prototype.oid = LW.Abstract.oid;
LW.Topic.prototype.oid = LW.Abstract.oid;
LW.Degree.prototype.oid = LW.Abstract.oid;
LW.Question.prototype.oid = LW.Abstract.oid;
LW.Character.prototype.oid = LW.Abstract.oid;
LW.Difficulty.prototype.oid = LW.Abstract.oid;
LW.Component.prototype.oid = LW.Abstract.oid;

/*
LW.Abstract.setRevid = function() {
    this.revid = this.data.revid;
};
LW.Question.prototype.revid = undefined;
LW.Question.prototype.loadData = LW.Abstract.setRevid;
LW.Character.prototype.revid = undefined;
LW.Character.prototype.loadData = LW.Abstract.setRevid;
LW.Taxon.prototype.revid = undefined;
LW.Taxon.prototype.loadData = LW.Abstract.setRevid;
LW.Component.prototype.revid = undefined;
LW.Component.prototype.loadData = LW.Abstract.setRevid;
*/

/**
 * Sets or updates a claim with value type wikibase-item
 *
 * @param entityID
 * @param entityRevid
 * @param propertyPid
 * @param itemID
 * @return {jQuery.promise}
 */
LW.wb.changePropertyItemClaim = function ( entityID, entityRevid, propertyPid, itemID ) {
	var deferred = jQuery.Deferred();

	LW.wb.repoApi.getClaims( entityID, propertyPid ).done( function ( response ) {

		if ( response.claims[ propertyPid ] && response.claims[ propertyPid ].length > 0 ) {
			console.log( 'Found claims:', response );

			var claim = response.claims[ propertyPid ][ 0 ];
			console.log( 'Claim: ', claim );

			LW.wb.repoApi.setClaimValue( claim.id, entityRevid, 'value', propertyPid, LW.Functions.oid( itemID ) ).done( function () {

				deferred.resolve();

			} ).fail( function () { deferred.reject(); } );
		} else {
			console.log( 'No existing claims found:', response );
			LW.wb.repoApi.createClaim( entityID, entityRevid, 'value', propertyPid, LW.Functions.oid( itemID ) ).done( function () {

				deferred.resolve();

			} ).fail( function () { deferred.reject(); } );
		}
	} );

	return deferred;
};

/**
 * Sets or updates a claim with value type string
 *
 * @param entityID
 * @param entityRevid
 * @param propertyPid
 * @param {string} val
 * @return {jQuery.promise}
 */
LW.wb.changePropertyStringClaim = function ( entityID, entityRevid, propertyPid, val ) {

	var deferred = jQuery.Deferred();

	LW.wb.repoApi.getClaims( entityID, propertyPid ).done( function ( response ) {

		if ( response.claims[ propertyPid ] && response.claims[ propertyPid ].length > 0 ) {
			console.log( 'Found claims:', response );

			var claim = response.claims[ propertyPid ][ 0 ];
			console.log( 'Claim: ', claim );

			LW.wb.repoApi.setClaimValue( claim.id, entityRevid, 'value', propertyPid, val ).done( function () {

				deferred.resolve();

			} ).fail( function () { deferred.reject(); } );
		} else {
			console.log( 'No existing claims found:', response );
			LW.wb.repoApi.createClaim( entityID, entityRevid, 'value', propertyPid, val ).done( function () {

				deferred.resolve();

			} ).fail( function () { deferred.reject(); } );
		}
	} );

	return deferred;
};

/**
 * Changes the value of the entity in the currently selected language
 *
 * @param entityID
 * @param entityRevid
 * @param label
 * @return {jQuery.Promise}
 */
LW.wb.changeLabel = function ( entityID, entityRevid, label ) {
	return LW.wb.repoApi.setLabel( entityID, entityRevid, label, mediaWiki.config.get( 'wgUserLanguage' ) );
};

LW.Taxon.prototype.addNew = function () {
	var taxon = this,
		deferred = jQuery.Deferred(),
		entities = LW.wb.baseEntityModel.entity;

	// Ensure this.degree is set
	this.loadReferences();

	LW.wb.repoApi.createEntity( 'item', {
		labels: {
			de: {
				language: 'de',
				value: taxon.name
			},
			'en-gb': {
				language: 'en-gb',
				value: taxon.name
			}
		}
	} ).fail( function ( data ) {
		console.log( 'Create Item: fail', data );
		deferred.reject();

	} ).done( function ( data ) {
		console.log( 'Create Item: done: ', data.entity.id, data );

		var list = new LW.JobList( 'Configuring taxon' );
		list.verbose = true;

		list.add( new LW.Job( 'Setting instance of taxon', function () {
			console.log( 'Property:', entities.pInstanceOf.pid );
			return LW.wb.repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', entities.pInstanceOf.pid, entities.qTaxon.oid );
		} ) );

		list.add( new LW.Job( 'Setting topic', function () {
			return LW.wb.repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', entities.pTopic.pid, taxon.topic.oid() );
		} ) );

		list.add( new LW.Job( 'Setting latin name', function () {
			return LW.wb.repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', entities.pLatin.pid, taxon.name );
		} ) );

		if ( taxon.degree !== undefined ) {
			list.add( new LW.Job( 'Setting taxonomic degree', function () {
				return LW.wb.repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', entities.pDegree.pid, taxon.degree.oid() );
			} ) );
		}

		if ( taxon.parentTaxonID !== undefined ) {
			var parentTaxon = LW.root.taxonModel.taxon[ taxon.parentTaxonID ];
			if ( parentTaxon ) {
				list.add( new LW.Job( 'Setting parent taxon', function () {
					return LW.wb.repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', entities.pParent.pid, parentTaxon.oid() );
				} ) );
			}
		}

		list.start( 1 ).done( function () {

			LW.root.taxonModel.dirtyData = true;
			LW.root.updateModels();
			deferred.resolve( data.entity.id );

		} ).fail( function ( data ) { deferred.reject( data ); } );

	} );

	return deferred;
};

/**
 * @param id
 * @deprecated
 */
LW.Taxon.prototype.addCharacter = function ( id ) {

	console.log( 'Adding character ' + id + ' to taxon ' + this.id );
	var promise = LW.wb.repoApi.createClaim( this.id, this.revid, 'value', LW.wb.baseEntityModel.entity.pHasCharacter.pid, LW.root.characterModel.character[ id ].oid() );

	promise.done( function () {
		console.log( 'Character added.' );
		LW.root.taxonModel.dirtyData = true;
		LW.root.updateModels();
	} );

	return promise;
};
LW.Taxon.prototype.setCharacter = function ( id, enabled ) {

	var deferred = jQuery.Deferred(),
		taxon = this,
		character = LW.root.characterModel.character[ id ],
		prop = LW.wb.baseEntityModel.entity.pHasCharacter.pid;

	LW.wb.repoApi.getClaims( taxon.id, prop ).done( function ( response ) {

		var foundClaim = null,
			claim;

		if ( response.claims[ prop ] && response.claims[ prop ].length > 0 ) {

			for ( var c = 0, C = response.claims[ prop ].length; c < C; c++ ) {

				claim = response.claims[ prop ][ c ];
				if ( claim.mainsnak.datavalue.type === 'wikibase-entityid' &&
                    claim.mainsnak.datavalue.value[ 'numeric-id' ] === Number( id.substr( 1 ) ) ) {
					foundClaim = claim;
					break;
				} else {
					console.log( 'Miss: ', claim.mainsnak.datavalue.value[ 'numeric-id' ], ' is not ', Number( id.substr( 1 ) ) );
				}

			}
		}

		if ( enabled ) {

			if ( foundClaim ) {
				deferred.reject( {
					error: 'Claim already exists'
				} );
			} else {

				LW.wb.repoApi.createClaim( taxon.id, taxon.revid, 'value', prop, character.oid() ).done( function () {
					LW.root.taxonModel.dirtyData = true;
					LW.root.updateModels();
					deferred.resolve();
				} ).fail( function () { deferred.reject(); } );

			}

		} else {

			if ( foundClaim ) {
				LW.wb.repoApi.removeClaim( foundClaim.id ).done( function () {
					LW.root.taxonModel.dirtyData = true;
					LW.root.updateModels();
					deferred.resolve();
				} ).fail( function () { deferred.reject(); } );
			} else {
				console.log( 'Searching for ' + Number( id.substr( 1 ) ) + ' in', response.claims[ prop ] );
				deferred.reject( {
					ok: false,
					message: 'Claim does not exist, cannot remove'
				} );
			}
		}

	} );

	return deferred;
};
LW.Taxon.prototype.changeName = function ( name ) {
	return LW.wb.changePropertyStringClaim( this.id, this.revid, LW.entity.pLatin.pid, name ).done( function () {
		LW.root.taxonModel.dirtyData = true;
		LW.root.updateModels();
	} );
};
LW.Taxon.prototype.changeDegree = function ( id ) {
	return LW.wb.changePropertyItemClaim( this.id, this.revid, LW.entity.pDegree.pid, id ).done( function () {
		LW.root.taxonModel.dirtyData = true;
		LW.root.updateModels();
	} );
};
LW.Taxon.prototype.changeParent = function ( id ) {
	return LW.wb.changePropertyItemClaim( this.id, this.revid, LW.entity.pParent.pid, id ).done( function () {
		LW.root.taxonModel.dirtyData = true;
		LW.root.updateModels();
	} );
};

LW.Question.prototype.changeName = function ( name ) {
	return LW.wb.changeLabel( this.id, this.revid, name ).done( function () {
		LW.root.questionModel.dirty = true;
		LW.root.updateModels();
	} );
};
LW.Question.prototype.changeTime = function ( time ) {
	return LW.wb.changePropertyStringClaim( this.id, this.revid, LW.entity.pTime.pid, time ).done( function () {
		LW.root.questionModel.dirty = true;
		LW.root.updateModels();
	} );
};
LW.Question.prototype.changeDifficulty = function ( id ) {
	return LW.wb.changePropertyItemClaim( this.id, this.revid, LW.entity.pDifficulty.pid, id ).done( function () {
		LW.root.questionModel.dirty = true;
		LW.root.updateModels();
	} );
};
LW.Question.prototype.changeComponent = function ( id ) {
	return LW.wb.changePropertyItemClaim( this.id, this.revid, LW.entity.pComponent.pid, id ).done( function () {
		LW.root.questionModel.dirty = true;
		LW.root.updateModels();
	} );
};
LW.Question.prototype.changeParentCharacter = function ( id ) {
	return LW.wb.changePropertyItemClaim( this.id, this.revid, LW.entity.pParentCharacter.pid, id ).done( function () {
		LW.root.questionModel.dirty = true;
		LW.root.updateModels();
	} );
};
LW.Character.prototype.changeName = function ( name ) {
	return LW.wb.changeLabel( this.id, this.revid, name ).done( function () {
		LW.root.characterModel.dirty = true;
		LW.root.updateModels();
	} );
};
LW.Component.prototype.changeName = function ( name ) {
	return LW.wb.changeLabel( this.id, this.revid, name ).done( function () {
		LW.root.componentModel.dirty = true;
		LW.root.updateModels();
	} );
};
LW.Topic.prototype.changeName = function ( name ) {
	return LW.wb.changeLabel( this.id, this.revid, name ).done( function () {
		LW.root.topicModel.dirty = true;
		LW.root.updateModels();
	} );
};

LW.Question.prototype.addNew = function () {

	var deferred = jQuery.Deferred(),
		locale = mediaWiki.config.get( 'wgUserLanguage' ),

		data = { labels: {} };
	data.labels[ locale ] = {
		language: locale,
		value: this.name
	};

	var question = this;
	LW.wb.repoApi.createEntity( 'item', data ).done( function ( data ) {

		var list = new LW.JobList( 'Configuring question' );

		// Instance type
		list.add( new LW.Job( 'Setting instance', function () {
			return LW.wb.repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', LW.entity.pInstanceOf.pid, LW.entity.qQuestion.oid );
		} ) );

		// Topic
		list.add( new LW.Job( 'Setting topic', function () {
			return LW.wb.repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', LW.entity.pTopic.pid, LW.root.componentModel.component[ question.componentID ].topic.oid() );
		} ) );

		// Set component
		list.add( new LW.Job( 'Setting component', function () {
			return LW.wb.repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', LW.entity.pComponent.pid, LW.Functions.oid( question.componentID ) );
		} ) );

		// Set difficulty
		if ( question.difficulty ) {
			list.add( new LW.Job( 'Setting difficulty', function () {
				return LW.wb.repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', LW.entity.pDifficulty.pid, question.difficulty.oid() );
			} ) );
		}

		// Set time required
		if ( question.timeSec ) {
			list.add( new LW.Job( 'Setting required time', function () {
				return LW.wb.repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', LW.entity.pTime.pid, String( question.timeSec ) );
			} ) );
		}

		list.start( 1 ).done( function () {

			LW.root.questionModel.dirty = true;
			LW.root.updateModels();
			deferred.resolve( data.entity.id );

		} ).fail( function ( data ) { deferred.reject( data ); } );

	} ).fail( function () { deferred.reject(); } );

	return deferred;
};

LW.Character.prototype.addNew = function () {

	var deferred = jQuery.Deferred(),
		locale = mediaWiki.config.get( 'wgUserLanguage' ),

		data = { labels: {} };
	data.labels[ locale ] = {
		language: locale,
		value: this.name
	};

	var character = this,
		parentQuestion = LW.root.questionModel.question[ character.parentQuestionID ];

	if ( !parentQuestion ) {
		deferred.reject( 'Parent question missing.' );
		return deferred;
	}

	LW.wb.repoApi.createEntity( 'item', data ).done( function ( data ) {

		var list = new LW.JobList( 'Configuring character' );

		// Instance type
		list.add( new LW.Job( 'Setting instance', function () {
			return LW.wb.repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', LW.entity.pInstanceOf.pid, LW.entity.qCharacter.oid );
		} ) );

		// Set parent question
		list.add( new LW.Job( 'Setting parent question', function () {
			return LW.wb.repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', LW.entity.pParentQuestion.pid, parentQuestion.oid() );
		} ) );

		list.start( 1 ).done( function () {

			LW.root.characterModel.dirty = true;
			LW.root.updateModels();
			deferred.resolve( data.entity.id );

		} ).fail( function ( data ) { deferred.reject( data ); } );

	} ).fail( function () { deferred.reject(); } );

	return deferred;
};

LW.Component.prototype.addNew = function () {

	var deferred = jQuery.Deferred(),
		locale = mediaWiki.config.get( 'wgUserLanguage' ),

		data = { labels: {} };
	data.labels[ locale ] = {
		language: locale,
		value: this.name
	};

	// Ensure this.topic is set
	this.loadReferences();

	var component = this;
	LW.wb.repoApi.createEntity( 'item', data ).done( function ( data ) {

		var list = new LW.JobList( 'Configuring component' );

		// Instance type
		list.add( new LW.Job( 'Setting instance', function () {
			return LW.wb.repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', LW.entity.pInstanceOf.pid, LW.entity.qComponent.oid );
		} ) );

		// Topic
		list.add( new LW.Job( 'Setting topic', function () {
			return LW.wb.repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', LW.entity.pTopic.pid, component.topic.oid() );
		} ) );

		list.start( 1 ).done( function () {

			LW.root.componentModel.dirty = true;
			LW.root.updateModels();
			deferred.resolve( data.entity.id );

		} ).fail( function ( data ) { deferred.reject( data ); } );

	} ).fail( function () { deferred.reject(); } );

	return deferred;

};

LW.Topic.prototype.addNew = function () {

	var deferred = jQuery.Deferred(),
		locale = mediaWiki.config.get( 'wgUserLanguage' ),

		data = { labels: {} };
	data.labels[ locale ] = {
		language: locale,
		value: this.name
	};

	LW.wb.repoApi.createEntity( 'item', data ).done( function ( data ) {

		var list = new LW.JobList( 'Configuring topic' );

		// Instance type
		list.add( new LW.Job( 'Setting instance', function () {
			return LW.wb.repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', LW.entity.pInstanceOf.pid, LW.entity.qTopic.oid );
		} ) );

		list.start( 1 ).done( function () {

			LW.root.topicModel.dirty = true;
			LW.root.updateModels();
			deferred.resolve( data.entity.id );

		} ).fail( function ( data ) { deferred.reject( data ); } );

	} ).fail( function () { deferred.reject(); } );

	return deferred;

};

LW.TopicModel.prototype.fetchData = function () {
	var promise = jQuery.Deferred();
	LW.mwAjaxQuery( 'topicData' ).done( function ( data ) {
		promise.resolve( LW.Functions.arrayToObject( data.topicData.data ) );
	} ).fail( function () { promise.reject(); } );
	return promise;
};
LW.ComponentModel.prototype.fetchData = function () {
	var promise = jQuery.Deferred();
	LW.mwAjaxQuery( 'componentData' ).done( function ( data ) {
		promise.resolve( LW.Functions.arrayToObject( data.componentData.data ) );
	} ).fail( function () { promise.reject(); } );
	return promise;
};
LW.DifficultyModel.prototype.fetchData = function () {
	var promise = jQuery.Deferred();
	LW.mwAjaxQuery( 'difficultyData' ).done( function ( data ) {
		promise.resolve( LW.Functions.arrayToObject( data.difficultyData.data ) );
	} ).fail( function () { promise.reject(); } );
	return promise;
};
LW.EquipmentModel.prototype.fetchData = function () {
	var promise = jQuery.Deferred();
	LW.mwAjaxQuery( 'equipmentData' ).done( function ( data ) {
		promise.resolve( LW.Functions.arrayToObject( data.equipmentData.data ) );
	} ).fail( function () { promise.reject(); } );
	return promise;
};
LW.DegreeModel.prototype.fetchData = function () {
	var promise = jQuery.Deferred();
	LW.mwAjaxQuery( 'degreeData' ).done( function ( data ) {
		promise.resolve( LW.Functions.arrayToObject( data.degreeData.data ) );
	} ).fail( function () { promise.reject(); } );
	return promise;
};
LW.CharacterModel.prototype.fetchData = function () {
	var promise = jQuery.Deferred();
	LW.mwAjaxQuery( 'characterData' ).done( function ( data ) {
		promise.resolve( LW.Functions.arrayToObject( data.characterData.data ) );
	} ).fail( function () { promise.reject(); } );
	return promise;
};
LW.QuestionModel.prototype.fetchData = function () {
	var promise = jQuery.Deferred();
	LW.mwAjaxQuery( 'questionData' ).done( function ( data ) {
		promise.resolve( LW.Functions.arrayToObject( data.questionData.data ) );
	} ).fail( function () { promise.reject(); } );
	return promise;
};
LW.TaxonModel.prototype.fetchData = function () {
	var promise = jQuery.Deferred();
	LW.mwAjaxQuery( 'taxonData' ).done( function ( data ) {
		data = data.taxonData.data;
		var taxon;
		for ( var tid in data ) {
			if ( data.hasOwnProperty( tid ) ) {
				taxon = data[ tid ];
				taxon.description = {
					images: taxon.images,
					text: ''
				};
			}
		}
		promise.resolve( LW.Functions.arrayToObject( data ) );
	} ).fail( function () { promise.reject(); } );
	return promise;
};
LW.RangeModel.prototype.fetchData = function () {
	// Ranges: Not yet implemented in Wikibase.
	return jQuery.Deferred().resolve( {} );
};
