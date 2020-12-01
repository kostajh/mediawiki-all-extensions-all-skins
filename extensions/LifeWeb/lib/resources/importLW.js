/**
 * Importer importing from a local database into Wikibase
 *
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

jQuery( function ( $, mw ) {

	var testrun = false,
		JOBS = 2,
		stop = false,

		repoApi = undefined,

		progressBar = new LWUI.ProgressBar( {} ),
		dom = $( '#lwImport' ),
		domStatus = $( '<div></div>' ),
		domDone = $( '<ol></ol>' );

	dom.append( progressBar.dom );
	dom.append( '<h2>Completed tasks</h2>' );
	dom.append( domDone );
	dom.append( '<h2>Current progress</h2>' );
	$( '<div style="cursor: pointer;">Stop</div>' ).on( 'click', function () { stop = true; } ).appendTo( dom );
	dom.append( domStatus );

	LW.queryURL = '/lifeWeb/query.php';
	LW.wikibase = false;
	LW.wb.baseEntityModel.init();
	LW.root.init( progressBar );

	var baseItems = LW.entity;

	function itemObject( pid ) {
		if ( pid === undefined ) {
			return undefined;
		}
		return {
			'entity-type': 'item',
			'numeric-id': Number( pid.substr( 1 ) )
		};
	}

	function searchEntities( search, language, type, limit, offset ) {
		return repoApi.get( {
			action: 'wbsearchentities',
			search: search,
			language: language,
			type: type,
			limit: limit,
			continue: offset
		} );
	}

	function searchExactEntity( search, language, type ) {
		var deferred = $.Deferred();

		repoApi.get( {
			action: 'wbsearchentities',
			search: search,
			language: language,
			type: type,
			limit: 1,
			continue: 0
		} ).fail( function () { deferred.fail(); } ).done( function ( data ) {
			var ret = {
				search: []
			};
			if ( data.search.length > 0 ) {
				if ( data.search[ 0 ].label === search ) {
					ret.search.push( data.search[ 0 ] );
				}
			}
			deferred.resolve( ret );
		} );

		return deferred;
	}

	function findDifficulties() {
		var list = new LW.JobList( 'Find difficulties' ),
			job,
			difficulty;

		domStatus.append( list.dom.$dom );
		for ( var did in LW.root.difficultyModel.difficulty ) {
			if ( LW.root.difficultyModel.difficulty.hasOwnProperty( did ) ) {
				difficulty = LW.root.difficultyModel.difficulty[ did ];
				job = new LW.Job();
				job.desc = 'Searching for difficulty: ' + difficulty.name;
				job.run = ( function ( difficulty ) {
					return function () {
						var deferred = $.Deferred();

						searchExactEntity( difficulty.name, 'de', 'item' ).done( function ( data ) {
							if ( data.search.length > 0 ) {
								difficulty.pid = data.search[ 0 ].id;
								difficulty.oid = itemObject( difficulty.pid );
							}
							deferred.resolve();
						} ).fail( function () { deferred.reject(); } );

						return deferred;
					};
				}( difficulty ) );
				list.jobs.push( job );
			}
		}
		return list.start( 4 );
	}
	function createDifficulties() {
		var list = new LW.JobList( 'Create difficulties' ),
			job,
			difficulty;

		domStatus.append( list.dom.$dom );
		for ( var did in LW.root.difficultyModel.difficulty ) {
			if ( LW.root.difficultyModel.difficulty.hasOwnProperty( did ) ) {
				difficulty = LW.root.difficultyModel.difficulty[ did ];

				if ( difficulty.pid === undefined ) {
					job = new LW.Job();
					job.desc = 'Creating difficulty: ' + difficulty.name;
					job.run = ( function ( difficulty ) {
						return function () {
							var deferred = $.Deferred();

							repoApi.createEntity( 'item', {
								labels: { de: {
									language: 'de',
									value: difficulty.name
								} }
							} ).fail( function () {
								deferred.reject();
							} ).done( function ( data ) {

								difficulty.pid = data.entity.id;
								difficulty.oid = itemObject( difficulty.pid );

								var list = new LW.JobList( 'Configuring difficulty' ),
									job;

								domStatus.append( list.dom.$dom );
								list.detachOnDone( true );

								job = new LW.Job();
								job.desc = 'Setting instance';
								job.run = function () {
									return repoApi.createClaim( difficulty.pid, data.entity.lastrevid, 'value', baseItems.pInstanceOf.pid, baseItems.qDifficulty.oid );
								};
								list.jobs.push( job );

								job = new LW.Job();
								job.desc = 'Setting level';
								job.run = function () {
									return repoApi.createClaim( difficulty.pid, data.entity.lastrevid, 'value', baseItems.pLevel.pid, difficulty.level );
								};
								list.jobs.push( job );

								list.start( 1 ).fail( function () { deferred.reject(); } ).done( function () { deferred.resolve(); } );

							} );

							return deferred;
						};
					}( difficulty ) );
					list.jobs.push( job );

				}
			}
		}
		return list.start( JOBS );
	}

	function findEquipment() {
		var list = new LW.JobList( 'Find equipment' ),
			job,
			equipment;

		domStatus.append( list.dom.$dom );
		for ( var eid in LW.root.equipmentModel.equipment ) {
			if ( LW.root.equipmentModel.equipment.hasOwnProperty( eid ) ) {
				equipment = LW.root.equipmentModel.equipment[ eid ];
				job = new LW.Job();
				job.desc = 'Searching for equipment: ' + equipment.name;
				job.run = ( function ( equipment ) {
					return function () {
						var deferred = $.Deferred();

						searchExactEntity( equipment.name, 'de', 'item' ).done( function ( data ) {
							if ( data.search.length > 0 ) {
								equipment.pid = data.search[ 0 ].id;
								equipment.oid = itemObject( equipment.pid );
							}
							deferred.resolve();
						} ).fail( function () { deferred.reject(); } );

						return deferred;
					};
				}( equipment ) );
				list.jobs.push( job );
			}
		}
		return list.start( 4 );
	}
	function createEquipment() {
		var list = new LW.JobList( 'Create equipment' ),
			job,
			equipment;

		domStatus.append( list.dom.$dom );
		for ( var eid in LW.root.equipmentModel.equipment ) {
			if ( LW.root.equipmentModel.equipment.hasOwnProperty( eid ) ) {
				equipment = LW.root.equipmentModel.equipment[ eid ];

				if ( equipment.pid === undefined ) {
					job = new LW.Job();
					job.desc = 'Creating equipment: ' + equipment.name;
					job.run = ( function ( equipment ) {
						return function () {
							var deferred = $.Deferred();

							repoApi.createEntity( 'item', {
								labels: { de: {
									language: 'de',
									value: equipment.name
								} }
							} ).fail( function () {
								deferred.reject();
							} ).done( function ( data ) {

								equipment.pid = data.entity.id;
								equipment.oid = itemObject( equipment.pid );

								var list = new LW.JobList( 'Configuring equipment' ),
									job;
								domStatus.append( list.dom.$dom );
								list.detachOnDone( true );

								job = new LW.Job();
								job.desc = 'Setting instance';
								job.run = function () {
									return repoApi.createClaim( equipment.pid, data.entity.lastrevid, 'value', baseItems.pInstanceOf.pid, baseItems.qEquipment.oid );
								};
								list.jobs.push( job );

								list.start( 1 ).fail( function () { deferred.reject(); } ).done( function () { deferred.resolve(); } );

							} );

							return deferred;
						};
					}( equipment ) );
					list.jobs.push( job );

				}
			}
		}
		return list.start( JOBS );
	}

	function findTopics() {
		var list = new LW.JobList( 'Find topics' ),
			job,
			topic;

		domStatus.append( list.dom.$dom );
		for ( var eid in LW.root.topicModel.topic ) {
			if ( LW.root.topicModel.topic.hasOwnProperty( eid ) ) {
				topic = LW.root.topicModel.topic[ eid ];
				job = new LW.Job();
				job.desc = 'Searching for topic: ' + topic.name;
				job.run = ( function ( topic ) {
					return function () {
						var deferred = $.Deferred();

						searchExactEntity( topic.name, 'de', 'item' ).done( function ( data ) {
							if ( data.search.length > 0 ) {
								topic.pid = data.search[ 0 ].id;
								topic.oid = itemObject( topic.pid );
							}
							deferred.resolve();
						} ).fail( function () { deferred.reject(); } );

						return deferred;
					};
				}( topic ) );
				list.jobs.push( job );
			}
		}
		return list.start( 4 );
	}
	function createTopics() {
		var list = new LW.JobList( 'Create equipment' ),
			job,
			topic;

		domStatus.append( list.dom.$dom );
		for ( var eid in LW.root.topicModel.topic ) {
			if ( LW.root.topicModel.topic.hasOwnProperty( eid ) ) {
				topic = LW.root.topicModel.topic[ eid ];

				if ( topic.pid === undefined ) {
					job = new LW.Job();
					job.desc = 'Creating topic: ' + topic.name;
					job.run = ( function ( topic ) {
						return function () {
							var deferred = $.Deferred();

							repoApi.createEntity( 'item', {
								labels: { de: {
									language: 'de',
									value: topic.name
								} }
							} ).fail( function () {
								deferred.reject();
							} ).done( function ( data ) {

								topic.pid = data.entity.id;
								topic.oid = itemObject( topic.pid );

								var list = new LW.JobList( 'Configuring topic' ),
									job;
								domStatus.append( list.dom.$dom );
								list.detachOnDone( true );

								job = new LW.Job();
								job.desc = 'Setting instance';
								job.run = function () {
									return repoApi.createClaim( topic.pid, data.entity.lastrevid, 'value', baseItems.pInstanceOf.pid, baseItems.qTopic.oid );
								};
								list.jobs.push( job );

								list.start( 1 ).fail( function () { deferred.reject(); } ).done( function () { deferred.resolve(); } );

							} );

							return deferred;
						};
					}( topic ) );
					list.jobs.push( job );

				}
			}
		}
		return list.start( JOBS );
	}

	function findDegrees() {
		var list = new LW.JobList( 'Find degrees' ),
			job,
			degree;

		for ( var did in LW.root.degreeModel.degree ) {
			if ( LW.root.degreeModel.degree.hasOwnProperty( did ) ) {
				degree = LW.root.degreeModel.degree[ did ];
				job = new LW.Job();
				job.desc = 'Searching for degree: ' + degree.name;
				job.run = ( function ( degree ) {
					return function () {
						var deferred = $.Deferred();

						searchExactEntity( degree.name, 'de', 'item' ).done( function ( data ) {
							if ( data.search.length > 0 ) {
								degree.pid = data.search[ 0 ].id;
								degree.oid = itemObject( degree.pid );
							}
							deferred.resolve();
						} ).fail( function () { deferred.reject(); } );

						return deferred;
					};
				}( degree ) );
				list.jobs.push( job );
			}
		}
		return list.start( 4 );
	}
	function createDegrees() {
		var list = new LW.JobList( 'Create degrees' ),
			job,
			degree;

		domStatus.append( list.dom.$dom );
		for ( var did in LW.root.degreeModel.degree ) {
			if ( LW.root.degreeModel.degree.hasOwnProperty( did ) ) {
				degree = LW.root.degreeModel.degree[ did ];

				if ( degree.pid === undefined ) {
					job = new LW.Job();
					job.desc = 'Creating degree: ' + degree.name;
					job.run = ( function ( degree ) {
						return function () {
							var deferred = $.Deferred();

							repoApi.createEntity( 'item', {
								labels: { de: {
									language: 'de',
									value: degree.name
								} }
							} ).fail( function () {
								deferred.reject();
							} ).done( function ( data ) {

								degree.pid = data.entity.id;
								degree.oid = itemObject( degree.pid );

								var list = new LW.JobList( 'Configuring degree' ),
									job;
								domStatus.append( list.dom.$dom );
								list.detachOnDone( true );

								job = new LW.Job();
								job.desc = 'Setting instance';
								job.run = function () {
									return repoApi.createClaim( degree.pid, data.entity.lastrevid, 'value', baseItems.pInstanceOf.pid, baseItems.qDegree.oid );
								};
								list.jobs.push( job );

								job = new LW.Job();
								job.desc = 'Setting level';
								job.run = function () {
									return repoApi.createClaim( degree.pid, data.entity.lastrevid, 'value', baseItems.pLevel.pid, String( degree.id * 10 ) );
								};
								list.jobs.push( job );

								list.start( 1 ).fail( function () { deferred.reject(); } ).done( function () { deferred.resolve(); } );

							} );

							return deferred;
						};
					}( degree ) );
					list.jobs.push( job );

				}
			}
		}
		return list.start( JOBS );
	}

	function findComponents() {

		var list = new LW.JobList( 'Find components' ),
			job,
			component;
		domStatus.append( list.dom.$dom );
		for ( var cid in LW.root.componentModel.component ) {
			if ( LW.root.componentModel.component.hasOwnProperty( cid ) ) {
				component = LW.root.componentModel.component[ cid ];
				job = new LW.Job();
				job.desc = 'Searching for component: ' + component.name;
				job.run = ( function ( component ) {
					return function () {
						var deferred = $.Deferred();
						// repoApi.searchEntities(
						searchEntities(
							component.name, 'de', 'item', 2, 0 ).done( function ( data ) {
							if ( data.success ) {
								if ( data.search.length > 0 ) {
									component.pid = data.search[ 0 ].id;
									component.oid = itemObject( component.pid );
								}
								if ( data.search.length > 1 ) {
									console.log( 'More than one component found: ', data );
								}
								deferred.resolve();
							}
						} ).fail( function () { deferred.reject(); } );
						return deferred;
					};
				}( component ) );
				list.jobs.push( job );
			}
		}
		return list.start( 4 );
	}
	function createComponentJob( component ) {

		var job = new LW.Job();

		job.desc = 'Adding component: ' + component.name;
		job.run = function () {
			var deferred = $.Deferred();

			repoApi.createEntity( 'item', {
				labels: {
					de: {
						language: 'de',
						value: component.name
					}
				}
			} ).fail( function ( data ) {
				console.log( 'Failed to create component.', data );
				deferred.reject();

			} ).done( function ( data ) {
				console.log( 'Component created.', data.entity.id, data );

				component.pid = data.entity.id;
				component.oid = itemObject( component.pid );

				var list = new LW.JobList( 'Configuring component' );
				list.detachOnDone( true );

				list.add( new LW.Job( 'Setting instance', function () {
					return repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', baseItems.pInstanceOf.pid, baseItems.qComponent.oid );
				} ) );
				list.add( new LW.Job( 'Setting topic', function () {
					return repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', baseItems.pTopic.pid, component.topic.oid );
				} ) );

				list.start( 1 ).done( function () { deferred.resolve(); } );

			} );

			return deferred;

		};

		return job;

	}
	function createComponents() {

		var list = new LW.JobList( 'Create components' ),
			component;

		domStatus.append( list.dom.$dom );
		var count = 0;
		for ( var cid in LW.root.componentModel.component ) {
			if ( LW.root.componentModel.component.hasOwnProperty( cid ) ) {

				component = LW.root.componentModel.component[ cid ];

				if ( component.pid === undefined ) {
					list.jobs.push( createComponentJob( component ) );
				} else {
					console.log( 'Skipping component', component.name, ', exists' );
				}

				count++;
				if ( testrun && count >= 3 ) {
					// break;
				}
			}
		}

		return list.start( JOBS );
	}

	function findQuestions() {
		var list = new LW.JobList( 'Find questions' ),
			job,
			question;
		domStatus.append( list.dom.$dom );
		for ( var qid in LW.root.questionModel.question ) {
			if ( LW.root.questionModel.question.hasOwnProperty( qid ) ) {
				question = LW.root.questionModel.question[ qid ];

				job = new LW.Job();
				job.desc = 'Searching for question ' + question.name;
				job.run = ( function ( question ) {
					return function () {
						var deferred = $.Deferred();

						searchEntities( question.name, 'de', 'item', 2, 0 ).fail(
							function () { deferred.reject(); }
						).done( function ( data ) {
							if ( data.search.length > 0 ) {
								question.pid = data.search[ 0 ].id;
								question.oid = itemObject( question.pid );
								console.log( 'Question found: ', question.name, question.pid );
							} else {
								console.log( 'Question not found: ', question.name, data );
							}
							deferred.resolve();
						} );

						return deferred;
					};
				}( question ) );
				list.jobs.push( job );
			}
		}
		return list.start( 4 );
	}
	/**
	 * @param {LW.Question} question
	 */
	function createQuestionJob( question ) {

		var job = new LW.Job();
		job.desc = 'Adding question: ' + question.name;
		job.run = function () {

			var deferred = $.Deferred();

			repoApi.createEntity( 'item', {
				labels: {
					de: {
						language: 'de',
						value: question.name
					}
				}
			} ).fail( function ( data ) {
				console.log( 'Failed to add question', data );
				deferred.reject();

			} ).done( function ( data ) {
				console.log( 'Question added.', data.entity.id, data );

				question.pid = data.entity.id;
				question.oid = itemObject( question.pid );

				var list = new LW.JobList( 'Configuring question' ),
					job;
				domStatus.append( list.dom.$dom );
				list.detachOnDone( true );

				// Instance type
				job = new LW.Job();
				job.desc = 'Setting instance';
				job.run = function () {
					return repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', baseItems.pInstanceOf.pid, baseItems.qQuestion.oid );
				};
				list.jobs.push( job );

				// Set component
				job = new LW.Job();
				job.desc = 'Setting component';
				job.run = function () {
					return repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', baseItems.pComponent.pid, question.component.oid );
				};
				list.jobs.push( job );

				// Set difficulty
				job = new LW.Job();
				job.desc = 'Setting difficulty';
				job.run = function () {
					return repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', baseItems.pDifficulty.pid, question.difficulty.oid );
				};
				list.jobs.push( job );

				// Set time required
				job = new LW.Job();
				job.desc = 'Setting required time';
				job.run = function () {
					return repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', baseItems.pTime.pid, String( question.timeSec ) );
				};
				list.jobs.push( job );

				// \todo import parent character

				list.start( 1 ).fail( function () { deferred.reject(); } ).done( function () { deferred.resolve(); } );

			} );

			return deferred;
		};

		return job;
	}
	function createQuestions() {

		var list = new LW.JobList( 'Create questions' ),
			question;

		domStatus.append( list.dom.$dom );
		var count = 0;
		for ( var qid in LW.root.questionModel.question ) {
			if ( LW.root.questionModel.question.hasOwnProperty( qid ) ) {

				question = LW.root.questionModel.question[ qid ];
				if ( question.pid === undefined ) {
					list.jobs.push( createQuestionJob( question ) );
				} else {
					console.log( 'Skipping question', question.name, ', exists' );
				}

				count++;
				if ( testrun && count >= 3 ) {
					break;
				}
			}
		}

		return list.start( JOBS );
	}

	function findCharacters() {

		var character,
			list = new LW.JobList( 'Find characters' ),
			job;
		domStatus.append( list.dom.$dom );
		for ( var cid in LW.root.characterModel.character ) {
			if ( LW.root.characterModel.character.hasOwnProperty( cid ) ) {
				character = LW.root.characterModel.character[ cid ];

				job = new LW.Job();
				job.desc = 'Searching for character ' + character.name;
				job.run = ( function ( character ) {
					return function () {
						var deferred = $.Deferred();

						searchEntities( character.name, 'de', 'item', 2, 0 ).fail( function () {
							deferred.reject();
						} ).done( function ( data ) {
							if ( data.search.length > 0 ) {
								character.pid = data.search[ 0 ].id;
								character.oid = itemObject( character.pid );
							}
							deferred.resolve();
						} );

						return deferred;
					};
				}( character ) );
				list.jobs.push( job );
			}
		}

		return list.start( 4 );
	}
	function createCharacterJob( character ) {

		var job = new LW.Job();
		job.desc = 'Adding character: ' + character.name;
		job.run = function () {

			var deferred = $.Deferred();

			repoApi.createEntity( 'item', {
				labels: {
					de: {
						language: 'de',
						value: character.name
					}
				}
			} ).fail( function () {
				deferred.reject();

			} ).done( function ( data ) {
				console.log( 'Character added: ', data.entity.id, data );

				character.pid = data.entity.id;
				character.oid = itemObject( character.pid );

				var list = new LW.JobList( 'Configuring character' ),
					job;
				domStatus.append( list.dom.$dom );
				list.detachOnDone( true );

				// Instance type
				job = new LW.Job();
				job.desc = 'Setting instance';
				job.run = function () {
					return repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', baseItems.pInstanceOf.pid, baseItems.qCharacter.oid );
				};
				list.jobs.push( job );

				// Parent question
				job = new LW.Job();
				job.desc = 'Setting parent question';
				job.run = function () {
					return repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', baseItems.pParentQuestion.pid, character.parentQuestion.oid );
				};
				list.jobs.push( job );

				// Description
				job = new LW.Job();
				job.desc = 'Setting description (de)';
				job.run = function () {
					return repoApi.setDescription( data.entity.id, data.entity.lastrevid, character.description.text, 'de' );
				};
				if ( character.description.text.length > 0 ) {
					list.jobs.push( job );
				}

				// Images
				var url;
				for ( var i = 0, I = character.description.images.length; i < I; i++ ) {
					url = character.description.images[ i ];
					job = new LW.Job();
					job.desc = 'Setting image ' + i;
					job.run = ( function ( url ) {
						return function () {
							return repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', baseItems.pImage.pid, url );
						};
					}( url ) );
					list.jobs.push( job );
				}

				// Child questions
				var question;
				for ( var q = 0, Q = character.childQuestions.length; q < Q; q++ ) {
					question = character.childQuestions[ q ];
					job = new LW.Job();
					job.desc = 'Updating child question: ' + question.name;
					job.run = ( function ( question ) {
						return function () {
							var deferred = $.Deferred();
							repoApi.getEntities( question.pid, 'info' ).fail( function () { deferred.reject(); } ).done( function ( data ) {
								repoApi.createClaim(
									question.pid, data.entities[ question.pid ].lastrevid, 'value',
									baseItems.pParentCharacter.pid, character.oid
								).fail( function () { deferred.reject(); } ).done( function () { deferred.resolve(); } );

							} );
							return deferred;
						};
					}( question ) );
					list.jobs.push( job );
				}

				list.start( 1 ).fail( function () { deferred.reject(); } ).done( function () { deferred.resolve(); } );

			} );

			return deferred;
		};

		return job;
	}
	function createCharacters() {

		var list = new LW.JobList( 'Create characters' ),
			character;

		domStatus.append( list.dom.$dom );
		var count = 0;
		for ( var cid in LW.root.characterModel.character ) {
			if ( LW.root.characterModel.character.hasOwnProperty( cid ) ) {

				character = LW.root.characterModel.character[ cid ];

				if ( character.pid === undefined ) {
					list.jobs.push( createCharacterJob( character ) );
				}

				count++;
				if ( testrun && count >= 3 ) {
					break;
				}

			}
		}

		return list.start( JOBS );
	}

	function findTaxa() {
		var list = new LW.JobList( 'Find taxa' ),
			job,
			taxon;

		domStatus.append( list.dom.$dom );
		for ( var tid in LW.root.taxonModel.taxon ) {
			if ( LW.root.taxonModel.taxon.hasOwnProperty( tid ) ) {
				taxon = LW.root.taxonModel.taxon[ tid ];

				job = new LW.Job();
				job.desc = 'Searching for ' + taxon.name;
				job.run = ( function ( taxon ) {
					return function () {

						var deferred = $.Deferred();
						searchEntities( taxon.name, 'de', 'item', 2, 0 ).fail( function () {
							deferred.reject();
						} ).done( function ( data ) {
							if ( data.search.length > 0 ) {
								taxon.pid = data.search[ 0 ].id;
								taxon.oid = itemObject( taxon.pid );
							}
							deferred.resolve();
						} );
						return deferred;

					};
				}( taxon ) );
				list.jobs.push( job );

			}
		}
		return list.start( 4 );
	}
	/**
	 * @param {LW.Taxon} taxon
	 */
	function createTaxonJob( taxon ) {

		var job = new LW.Job();
		job.desc = 'Adding taxon: ' + taxon.name;
		job.run = function () {

			var deferred = $.Deferred();

			repoApi.createEntity( 'item', {
				labels: {
					de: {
						language: 'de',
						value: taxon.name
					}
				}
			} ).fail( function ( data ) {
				console.log( 'Create Item: fail', data );
				deferred.reject();

			} ).done( function ( data ) {

				console.log( 'Create Item: done: ', data.entity.id, data );

				taxon.pid = data.entity.id;
				taxon.oid = itemObject( taxon.pid );

				var list = new LW.JobList( 'Add taxon data' ),
					job;

				domStatus.append( list.dom.$dom );
				list.detachOnDone( true );

				// Instance
				job = new LW.Job();
				job.desc = 'Setting instance for taxon ' + taxon.name;
				job.run = function () {
					return repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', baseItems.pInstanceOf.pid, baseItems.qTaxon.oid );
				};
				list.jobs.push( job );

				list.add( new LW.Job( 'Setting topic', function () {
					return repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', baseItems.pTopic.pid, taxon.topic.oid );
				} ) );

				// Latin name
				job = new LW.Job();
				job.desc = 'Setting latin name for taxon ' + taxon.name;
				job.run = function () {
					return repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', baseItems.pLatin.pid, taxon.name );
				};
				list.jobs.push( job );

				// Degree
				if ( taxon.degree ) {
					list.add( new LW.Job( 'Setting taxonomic degree for ' + taxon.name, function () {
						return repoApi.createClaim( data.entity.id, data.entity.lastrevid, 'value', baseItems.pDegree.pid, taxon.degree.oid );
					} ) );
				}

				// Images
				var img;
				for ( var i = 0, I = taxon.description.images.length; i < I; i++ ) {
					img = taxon.description.images[ i ];
					job = new LW.Job();
					job.desc = 'Adding image ' + i + ' to taxon ' + taxon.name;
					job.run = ( function ( url ) {
						return function () {
							return repoApi.createClaim( taxon.pid, data.entity.lastrevid, 'value', baseItems.pImage.pid, url );
						};
					}( img ) );
					list.jobs.push( job );
				}

				// Characters
				var character;
				for ( var c = 0, C = taxon.characters.length; c < C; c++ ) {
					character = taxon.characters[ c ];
					job = new LW.Job();
					job.desc = 'Adding character to ' + taxon.name + ': ' + character.name;
					job.run = ( function ( character ) {
						return function () {
							return repoApi.createClaim( taxon.pid, data.entity.lastrevid, 'value', baseItems.pHasCharacter.pid, character.oid );
						};
					}( character ) );
					list.jobs.push( job );
				}

				list.start( 1 ).done( function () {
					deferred.resolve();
				} ).fail( function () {
					deferred.reject();
				} );

			} );

			return deferred;
		};

		return job;
	}

	function createTaxa() {

		var list = new LW.JobList( 'Create taxa' ),
			taxon;

		domStatus.append( list.dom.$dom );
		var count = 0;
		for ( var tid in LW.root.taxonModel.taxon ) {
			if ( LW.root.taxonModel.taxon.hasOwnProperty( tid ) ) {
				taxon = LW.root.taxonModel.taxon[ tid ];

				if ( taxon.pid === undefined ) {
					list.jobs.push( createTaxonJob( taxon ) );

					count++;
					if ( testrun && count >= 3 ) {
						break;
					}
				}

			}
		}

		return list.start( JOBS );
	}

	function done( text ) {
		domDone.append( '<li>' + text + '</li>' );
	}

	function startImport() {
		if ( !stop ) {
			findDegrees().done( function ( data ) {
				done( 'Find existing degrees: ' + data.completed + ' checked' );
				if ( !stop ) {
					createDegrees().done( function ( data ) {
						done( 'Create missing degrees: ' + data.completed );
						if ( !stop ) {
							findDifficulties().done( function ( data ) {
								done( 'Find existing difficulties: ' + data.completed + ' checked' );
								if ( !stop ) {
									createDifficulties().done( function ( data ) {
										done( 'Create missing difficulties: ' + data.completed );
										if ( !stop ) {
											findEquipment().done( function ( data ) {
												done( 'Find existing equipment: ' + data.completed + ' checked' );
												if ( !stop ) {
													createEquipment().done( function ( data ) {
														done( 'Create missing equipment: ' + data.completed );
														if ( !stop ) {
															findTopics().done( function ( data ) {
																done( 'Find existing topics: ' + data.completed + ' checked' );
																if ( !stop ) {
																	createTopics().done( function ( data ) {
																		done( 'Create missing topics: ' + data.completed );
																		if ( !stop ) {
																			findComponents().done( function ( data ) {
																				done( 'Find existing components: ' + data.completed + ' checked' );
																				if ( !stop ) {
																					createComponents().done( function ( data ) {
																						done( 'Create missing components: ' + data.completed );
																						if ( !stop ) {
																							findQuestions().done( function ( data ) {
																								done( 'Find existing questions: ' + data.completed + ' checked' );
																								if ( !stop ) {
																									createQuestions().done( function ( data ) {
																										done( 'Create missing questions: ' + data.completed );
																										if ( !stop ) {
																											findCharacters().done( function ( data ) {
																												done( 'Find existing characters: ' + data.completed + ' checked' );
																												if ( !stop ) {
																													createCharacters().done( function ( data ) {
																														done( 'Create missing characters: ' + data.completed );
																														if ( !stop ) {
																															findTaxa().done( function ( data ) {
																																done( 'Find existing taxa: ' + data.completed + ' checked' );
																																if ( !stop ) {
																																	createTaxa().done( function ( data ) {
																																		done( 'Create missing taxa: ' + data.completed );
																																	} );
																																}
																															} );
																														}
																													} );
																												}
																											} );
																										}
																									} );
																								}
																							} );
																						}
																					} );
																				}
																			} );
																		}
																	} );
																}
															} );
														}
													} );
												}
											} );
										}
									} );
								}
							} );
						}
					} );
				}
			} );
		}
	}

	mw.loader.using( 'wikibase.RepoApi', function () {
		repoApi = new wikibase.RepoApi();
		$( '<strong style="cursor: pointer;">Start</strong>' ).on( 'click', function () {
			$( this ).detach();
			startImport();
		} ).appendTo( domStatus );
	} );

}( jQuery, mediaWiki ) );
