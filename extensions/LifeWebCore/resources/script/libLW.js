
/**
 * This file contains the data model: Taxa with Characters belonging to Questions.
 *
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 * @requires {LWUI}
 */

/**
 * Namespace for the data model
 */
var LW = {};
var i18n = i18n || { t: function() { return false; } };

/// Use offline json files
LW.offline = false;
/// Use data from Wikibase
LW.wikibase = false;
/// Maximum depth to detect recursion
LW.recurseLimit = 100;
/// URL to the query page
/// For wikibase this may be e.g. '/mediawiki-1.21.1/api.php'
LW.queryURL = 'query.php';

LW.root = {
    /// @type {LW.TopicModel}
    topicModel : null,
    /// @type {LW.ComponentModel}
    componentModel : null,
    /// @type {LW.CharacterModel}
    characterModel : null,
    /// @type {LW.DifficultyModel}
    difficultyModel : null,
    /// @type {LW.EquipmentModel}
    equipmentModel : null,
    /// @type {LW.QuestionModel}
    questionModel : null,
    /// @type {LW.DegreeModel}
    degreeModel : null,
    /// @type {LW.TaxonModel}
    taxonModel : null,
    /// @type {LW.RangeModel}
    rangeModel : null,

    /// @type {Object.<String, [function]>}
    listeners : {},

    /// @type {LW.Topic|undefined}
    currentTopic : undefined,

    progressBar : new LWUI.ProgressBar({})
};
/**
 * @param {String} type For example LW.TaxonModel.prototype.id
 * @param {Function} listener
 */
LW.root.addEventListener = function(type, listener) {
    if (!this.listeners[type]) {
        this.listeners[type] = [];
    }
    this.listeners[type].push(listener);
};
LW.root.trigger = function(type) {
    console.log('Triggered: ' + type);
    var list = this.listeners[type];
    if (list) {
        for (var l = 0, L = list.length; l < L; l++) {
            list[l]();
        }
    }
};
/**
 * @param {LW.Topic} topic
 */
LW.root.setTopic = function(topic) {
    if (!(topic instanceof LW.Topic)) {
        console.log('Not LW.Topic:', topic);
        throw new Error('Not a LW.Topic.');
    }
    this.currentTopic = topic;
    this.trigger(LW.TopicModel.prototype.idCurrentTopic);
};
/**
 * @returns {jQuery.Deferred}
 */
LW.root.init = function ( progressBar ) {
    if ( progressBar ) { this.progressBar = progressBar; }


    var tStart = new Date().getTime();

    this.topicModel = new LW.TopicModel();
    this.degreeModel = new LW.DegreeModel();
    this.equipmentModel = new LW.EquipmentModel();
    this.difficultyModel = new LW.DifficultyModel();
    this.componentModel = new LW.ComponentModel();
    this.characterModel = new LW.CharacterModel();
    this.questionModel = new LW.QuestionModel();
    this.rangeModel = new LW.RangeModel();
    this.taxonModel = new LW.TaxonModel();

    var promise = this.updateModels();

    promise.done( function () {
        var tEnd = new Date().getTime();
        console.log( 'LW data initialised. Time taken: ', (tEnd - tStart), ' ms' );
    } ).fail( function() {
            console.log( 'Failed to load data!' );
        } );


    // Re-define instead of remembering if initialised
    LW.root.init = function () { };

    return promise;
};
/**
 * Updates the models for which the member “dirty” is set to true
 */
LW.root.updateModels = function () {
    var mainPromise = $.Deferred();
    var models = [
        this.topicModel,
        this.difficultyModel,
        this.equipmentModel,
        this.degreeModel,
        this.componentModel,
        this.characterModel,
        this.questionModel,
        this.rangeModel,
        this.taxonModel
    ];

    var triggerList = [];
    var dirtyIdentifiers = [];
    var M = models.length,
        step,
        m;

    var promises = [],
        promise;

    var progressBar = this.progressBar;
    progressBar.value( 0 );
    for ( step = 0, m = 0; m < M; m++ ) {
        console.log( 'Updating model: ' + models[m].id );

        progressBar.text( 'Loading ' + models[m].id + ' data' );

        // if loadData returns true, the corresponding callbacks will be triggered.
        promise = models[m].loadData().done( function ( index ) {
            return function ( updated ) {
                if ( updated ) {
                    dirtyIdentifiers.push( models[index].id );
                }
            }
        }( m ) );
        promises.push( promise );

        progressBar.value( step / M * 100 );
        step++;
    }
    progressBar.text( 'Data loaded, referencing now' ).value( 100 );

    console.log('Waiting for promises to finish ...');
    $.when.apply( $, promises ).done( function () {
        console.log('Promises finished.');

        progressBar.value( 0 );
        promises = [];

        for ( step = 0, m = 0; m < M; m++ ) {
            progressBar.text( 'Referencing ' + models[m].id + ' data' );

            // loadReferences should return true if the model has changed.
            // The callbacks subscribed to the model will then be triggered.
            promise = models[m].loadReferences( dirtyIdentifiers ).done( function ( index ) {
                return function ( updated ) {
                    if ( updated ) {
                        triggerList.push( models[index].id );
                    }
                }
            }( m ) );

            progressBar.value( step / M * 100 );
            step++;
        }


        $.when.apply( $, promises ).done( function () {
            progressBar.text( 'Data referenced' ).value( 100 );

            console.log( 'Triggers: ', triggerList );
            // Trigger the registered callbacks
            for ( var t = 0, T = triggerList.length; t < T; t++ ) {
                LW.root.trigger( triggerList[t] );
            }

            mainPromise.resolve();

        } ).fail( function() {
                mainPromise.reject();
            });

    } ).fail( function() {
            mainPromise.reject();
        });

    return mainPromise;
};


/**
 * @param {LW.QCVisitor} visitor
 */
LW.root.visit = function ( visitor ) {
    if ( visitor.options.useComponents ) {
        this.componentModel.visit( visitor );
    } else {
        this.questionModel.visit( visitor );
    }
};



LW.ajaxQuery = function(action) {
    if (!LW.offline) {
        return $.ajax(LW.queryURL, {
            'async' : false,
            'type' : 'GET',
            'dataType' : 'json',
            'data' : {
                'action' : action
            }
        });
    } else {
        return $.ajax(action + ".json", {
            'async' : false,
            'type' : 'GET',
            'dataType' : 'json'
        });
    }
};




/**
 * DFS visitor data structure. Instances must be created with the new operator.
 * @constructor
 */
LW.QCVisitor = function() {
    // Do not check the instance because the rent-a-constructor pattern fails otherwise.
    //if (!(this instanceof LW.QCVisitor)) { return new LW.QCVisitor(); }

    /// @type {LW.QCVisitorOptions}
    this.options = new LW.QCVisitorOptions();

    /// Will be passed to question visitors in case components are not visited.
    this.componentStorageAlternative = {};

    /// @type {Object.<{Number}>|null}
    this.questionPriorities = null;

    /** @param {LW.Character} character
     *  @param {Object} questionStorage Storage object of the parent question
     *  @param {Object} componentStorage Component's storage object */
    this.preorderCharacterVisitor = function(character, questionStorage, componentStorage) {};
    /** @param {LW.Character} character
     *  @param {Object} questionStorage Storage object of the parent question
     *  @param {Object} componentStorage Component's storage object */
    this.postorderCharacterVisitor = function(character, questionStorage, componentStorage) {};
    /** @param {LW.Question} question
     *  @param {Object} componentStorage Component's storage object
     *  @param {Object} questionStorage Storage object available to question and direct children */
    this.preorderQuestionVisitor = function(question, questionStorage, componentStorage) {};
    /** @param {LW.Question} question
     *  @param {Object} componentStorage Component's storage object
     *  @param {Object} questionStorage Storage object available to question and direct children */
    this.postorderQuestionVisitor = function(question, questionStorage, componentStorage) {};
    /** @param {LW.Component} component
     *  @param {Object} storage Storage object available to component and questions */
     this.preorderComponentVisitor = function(component, storage) {};
    /** @param {LW.Component} component
     *  @param {Object} storage Storage object available to component and questions */
    this.postorderComponentVisitor = function(component, storage) {};
    /**
     * @param {LW.Question} a
     * @param {LW.Question} b
     */
    this.questionCompare = function(a,b) { return a.name && b.name ? a.name.localeCompare(b.name) : 0; };
    /**
     * @param {LW.Character} a
     * @param {LW.Character} b
     */
    this.characterCompare = function(a,b) { return a.name && b.name ? a.name.localeCompare(b.name) : 0; };
    /**
     * @param {LW.Component} a
     * @param {LW.Component} b
     * @returns {Number}
     */
    this.componentCompare = function(a,b) { return a.name && b.name ? a.name.localeCompare(b.name) : 0; };

    /**
     * @param {LW.Question} question
     * @returns {boolean}
     */
    this.acceptQuestion = function ( question ) { return true; };

    return this;
};
LW.QCVisitorOptions = function () {
    if ( !(this instanceof LW.QCVisitorOptions) ) { return new LW.QCVisitorOptions(); }

    /// @type {LW.Component}
    this.currentComponent = null;

    /// @type {boolean}
    /// DFS visitor: true, linear visitor: false
    this.dfs = true;

    /// @type {boolean}
    /// Set to true to use components for ordering
    this.useComponents = true;

    this.data = {};

    return this;
};

/**
 *
 * @param data Additional data that will be made available to the taxon visitor function. This is an alternative to
 *             the approach taken in the LW.QCVisitor which uses inheritance.
 * @returns {LW.TaxonVisitor}
 * @constructor
 */
LW.TaxonVisitor = function (data) {
    if ( !(this instanceof LW.TaxonVisitor) ) { return new LW.TaxonVisitor(data);}

    this.data = data;

    /**
     * @param {LW.Taxon} a
     * @param {LW.Taxon} b
     * @returns {Number}
     */
    this.taxonCompare = function ( a, b ) { return a.name.localeCompare( b.name ); };
    /**
     * @param {LW.Taxon} taxon
     * @returns {boolean} true if this taxon's rating is high enough.
     */
    this.acceptTaxon = function ( taxon ) { return true; };

    /**
     * @param taxon
     * @returns {boolean} true if this taxon has been rated and may be processed.
     */
    this.filterTaxon = function(taxon) { return true; };

    /**
     * @param {LW.Taxon} taxon
     * @param {Boolean} accepted True if the taxon is not excluded by the observed characters
     */
    this.taxonVisitor = function ( taxon, accepted, data ) {};

    return this;
};


LW.TopicModel = function() {
    if (!(this instanceof LW.TopicModel)) { return new LW.TopicModel(); }

    /// @type {Object.<LW.Topic>}
    this.topic = {};

    this.dirty = true;

    return this;
};
LW.TopicModel.prototype.id = "Topic";
LW.TopicModel.prototype.idCurrentTopic = "CurrentTopic";
LW.TopicModel.prototype.fetchData = function () {
    throw Error( 'Data fetching not implemented.' );
};
LW.TopicModel.prototype.loadData = function () {

    var promise = $.Deferred();
    if ( !this.dirty ) { return promise.resolve( false ); }

    this.topic = {};
    var self = this;

    this.fetchData().done(function ( topicData ) {
        var item;
        for ( var tid in topicData ) {
            if ( topicData.hasOwnProperty( tid ) ) {
                item = topicData[tid];
                self.topic[tid] = new LW.Topic( item );
            }
        }
        promise.resolve( true );
    } ).fail( function() { promise.reject(); } );


    return promise;
};
LW.TopicModel.prototype.loadReferences = function () {
    var promise = $.Deferred();
    promise.resolve( this.dirty );
    this.dirty = false;
    return promise;
};
LW.Topic = function ( data ) {
    if ( !(this instanceof LW.Topic) ) { return new LW.Topic( data ); }

    this.id = undefined;
    this.name = undefined;
    this.data = undefined;

    this.description = {
        text: undefined,
        /// @type {Array.<String>}
        images: []
    };

    this.loadData( data );

    return this;
};



LW.ComponentModel = function() {
    if (!(this instanceof LW.ComponentModel)) { return new LW.ComponentModel(); }

    /// @type {Object.<LW.Component>}
    this.component = {};
    this.dirty = true;

    return this;
};
LW.ComponentModel.prototype.id = "Component";
/** @param {LW.QCVisitor} qcVisitor */
LW.ComponentModel.prototype.visit = function ( qcVisitor ) {

    /// @type {[LW.Component]}
    var components = [];
    this.each( function ( index, component ) { components.push( component ); }, false );

    if ( qcVisitor.componentCompare ) {
        components.sort( qcVisitor.componentCompare );
    }

    for ( var k = 0, K = components.length; k < K; k++ ) {
        components[k].visit( qcVisitor );
    }
};
LW.ComponentModel.prototype.each = function(handle, allTopics) {
    var cid,
        component;
    if (allTopics === true) {
        for (cid in this.component) {
            if (this.component.hasOwnProperty(cid)) {
                component = this.component[cid];
                handle(cid, component);
            }
        }
    } else {
        // Only call the handle on taxa that match the current topic
        for (cid in this.component) {
            if (this.component.hasOwnProperty(cid)) {
                component = this.component[cid];
                if (component.topic == LW.root.currentTopic) {
                    handle(cid, component);
                }
            }
        }
    }
};
LW.ComponentModel.prototype.fetchData = function () {
    throw Error( 'Data fetching not implemented.' );
};
LW.ComponentModel.prototype.loadData = function () {

    var promise = $.Deferred();

    if ( !this.dirty ) { return promise.resolve( false ); }

    this.component = {};

    var self = this;
    this.fetchData().done(function ( componentData ) {
        for ( var cid in componentData ) {
            if ( componentData.hasOwnProperty( cid ) ) {
                self.component[componentData[cid].id] = new LW.Component( componentData[cid] );
            }
        }
        promise.resolve(true);

    } ).fail( function () { promise.reject(); } );

    return promise;
};
/**
 * @param {[string]} dirty
 */
LW.ComponentModel.prototype.loadReferences = function ( dirty ) {
    var updated = false;
    if ( dirty.indexOf( LW.ComponentModel.prototype.id ) >= 0 ||
        dirty.indexOf( LW.QuestionModel.prototype.id ) >= 0 ) {
        this.updateQuestionReferences();
        this.updateOtherReferences();
        updated = true;
    }
    this.dirty = false;
    return $.Deferred().resolve( updated );
};
/**
 * Requirements:
 * – Question data loaded
 */
LW.ComponentModel.prototype.updateQuestionReferences = function () {
    for ( var cid in this.component ) {
        if ( this.component.hasOwnProperty( cid ) ) {
            this.component[cid].questions = [];
        }
    }
    for ( var qid in LW.root.questionModel.question ) {
        if ( LW.root.questionModel.question.hasOwnProperty( qid ) ) {
            /// @type {LW.Question}
            var question = LW.root.questionModel.question[qid];
            if ( question.componentID ) {
                this.component[question.componentID].questions.push( question );
            } else {
                console.log( 'Question has no component: ', question.id, question.name );
            }
        }
    }
};
LW.ComponentModel.prototype.updateOtherReferences = function() {
    for (var cid in this.component) {
        if (this.component.hasOwnProperty(cid)) {
            this.component[cid].loadReferences();
        }
    }
};
/**
 * @returns {[LW.Component]}
 */
LW.ComponentModel.prototype.sortedList = function(visitAll) {
    var list = [];
    this.each( function(index, component) {
        list.push(component);
    }, visitAll );
    list.sort(function(a,b) { return a.name.localeCompare(b.name); });
    return list;
};
LW.Component = function ( data ) {
    if ( !(this instanceof LW.Component) ) { return new LW.Component( data ); }

    this.id = undefined;
    this.name = undefined;
    this.topicID = data.topic;

    this.file = data.file;

    /// @type {LW.Topic}
    this.topic = undefined;
    /// @type {[LW.Question]}
    this.questions = [];

    this.description = {
        text: undefined,
        images: []
    };

    this.loadData( data );

    return this;
};
/** Updates this.topic. */
LW.Component.prototype.loadReferences = function() {
    this.topic = LW.root.topicModel.topic[this.topicID];
};
/**
 * @param {LW.QCVisitor} qcVisitor
 */
LW.Component.prototype.visit = function ( qcVisitor ) {
    var componentStorage = {};
    qcVisitor.options.currentComponent = this;

    qcVisitor.preorderComponentVisitor( this, componentStorage );

    /// @type {[LW.Question]}
    var nodes = [],
        q, Q;
    if ( qcVisitor.options.dfs ) {
        // Add root nodes only; visit in hierarchical manner.
        for ( q = 0, Q = this.questions.length; q < Q; q++ ) {
            if ( nodes.indexOf( this.questions[q].root ) < 0 ) {
                nodes.push( this.questions[q].root );
            }
        }
    } else {
        // Add all questions in this component, visit linearly.
        for ( q = 0, Q = this.questions.length; q < Q; q++ ) {
            nodes.push( this.questions[q] );
        }
    }
    if ( qcVisitor.questionCompare ) {
        nodes.sort( qcVisitor.questionCompare );
    }
    if ( qcVisitor.questionPriorities ) {

        /// @type {[LW.Question]}
        var priorityNodes = nodes
            .filter( function ( node ) {
                return qcVisitor.questionPriorities[node.id] !== undefined;
            } )
            .sort( function ( a, b ) {
                return qcVisitor.questionPriorities[b.id] - qcVisitor.questionPriorities[a.id];
            } );

        /// @type {[LW.Question]}
        var otherNodes = nodes
            .filter( function ( node ) {
                return qcVisitor.questionPriorities[node.id] === undefined;
            } );

        for ( q = 0, Q = priorityNodes.length; q < Q; q++ ) {
            priorityNodes[q].visit( qcVisitor, componentStorage );
        }
        for ( q = 0, Q = otherNodes.length; q < Q; q++ ) {
            otherNodes[q].visit( qcVisitor, componentStorage );
        }

        if (this.id == 1 ) console.log(priorityNodes, otherNodes);


    } else {
        for ( q = 0, Q = nodes.length; q < Q; q++ ) {
            nodes[q].visit( qcVisitor, componentStorage );
        }
    }
    qcVisitor.postorderComponentVisitor( this, componentStorage );
};


LW.DifficultyModel = function() {
    if (!(this instanceof LW.DifficultyModel)) { return new LW.DifficultyModel(); }

    /// @type {Object.<LW.Difficulty>}
    this.difficulty = {};
    this.dirty = true;

    return this;
};
LW.DifficultyModel.prototype.id = "Difficulty";
LW.DifficultyModel.prototype.fetchData = function () {
    throw Error( 'Data fetching not implemented.' );
};
LW.DifficultyModel.prototype.loadData = function () {

    var promise = $.Deferred();

    if ( !this.dirty ) { return promise.resolve( false ); }

    var self = this;
    this.difficulty = {};
    this.fetchData().done(function ( data ) {
        for ( var did in data ) {
            if ( data.hasOwnProperty( did ) ) {
                self.difficulty[data[did].id] = new LW.Difficulty( data[did] );
            }
        }
        promise.resolve( true );

    } ).fail( function () { promise.reject(); } );

    return promise;
};
LW.DifficultyModel.prototype.loadReferences = function () {
    this.dirty = false;
    return $.Deferred().resolve(false);
};
LW.DifficultyModel.prototype.sortedList = function() {
    var list = [];
    for (var did in this.difficulty) {
        if (this.difficulty.hasOwnProperty(did)) {
            list.push(this.difficulty[did]);
        }
    }
    list.sort(function(a,b) { return b.level - a.level; });
    return list;
};
LW.Difficulty = function(data) {
    if (!(this instanceof LW.Difficulty)) { return new LW.Difficulty(data); }

    this.id = data.id;
    this.name = data.name;
    this.level = data.level;

    return this;
};


LW.QuestionModel = function() {
    if (!(this instanceof LW.QuestionModel)) { return new LW.QuestionModel(); }

    /// @type {Object.<LW.Question>}
    this.question = {};
    /// @type {[LW.Question]}
    /// Questions which have no parent character
    this.rootNodes = [];
    this.dirty = true;

    return this;
};
/**
 *
 * @param { function( qid: Number, question: LW.Question ) } handle
 * @param {bool} allTopics Iterate over all or just the current topic
 */
LW.QuestionModel.prototype.each = function ( handle, allTopics ) {
    var qid,
        question;
    if ( allTopics === true ) {
        // Call the handle on all taxa
        for ( qid in this.question ) {
            if ( this.question.hasOwnProperty( qid ) ) {
                question = this.question[qid];
                handle( qid, question );
            }
        }
    } else {
        // Only call the handle on taxa that match the current topic
        for ( qid in this.question ) {
            if ( this.question.hasOwnProperty( qid ) ) {
                question = this.question[qid];
                if ( question.component && ( question.component.topic == LW.root.currentTopic) ) {
                    handle( qid, question );
                }
            }
        }
    }
};
LW.QuestionModel.prototype.id = "Question";
/** @param {LW.QCVisitor} qcVisitor */
LW.QuestionModel.prototype.visit = function ( qcVisitor ) {

    /// @type {[LW.Question]}
    var nodes = [];
    this.each( function ( qid, question ) {
        nodes.push( question )
    }, false );

    nodes.sort( qcVisitor.questionCompare );

    // \todo Hierarchical (DFS) visitor not working yet. Remember visited questions.

    if ( qcVisitor.questionPriorities ) {

        // First display the nodes with priority (e.g. selected ones), then the other ones.

        /// @type {[LW.Question]}
        var priorityNodes = nodes
            .filter( function ( node ) {
                return qcVisitor.questionPriorities[node.id] !== undefined;
            } )
            .sort( function ( a, b ) {
                return qcVisitor.questionPriorities[b.id] - qcVisitor.questionPriorities[a.id];
            } );

        /// @type {[LW.Question]}
        var otherNodes = nodes
            .filter( function ( node ) {
                return qcVisitor.questionPriorities[node.id] === undefined;
            } );

        for ( q = 0, Q = priorityNodes.length; q < Q; q++ ) {
            priorityNodes[q].visit( qcVisitor, qcVisitor.componentStorageAlternative );
        }
        for ( q = 0, Q = otherNodes.length; q < Q; q++ ) {
            otherNodes[q].visit( qcVisitor, qcVisitor.componentStorageAlternative );
        }
    } else {
        for ( var q = 0, Q = nodes.length; q < Q; q++ ) {
            nodes[q].visit( qcVisitor, qcVisitor.componentStorageAlternative );
        }
    }
};
LW.QuestionModel.prototype.fetchData = function () {
    throw Error( 'Data fetching not implemented.' );
};
LW.QuestionModel.prototype.loadData = function () {

    var promise = $.Deferred();

    if ( !this.dirty ) { return promise.resolve( false ); }

    var self = this;
    this.question = {};
    this.fetchData().done(function ( questionData ) {
        for ( var qid in questionData ) {
            if ( questionData.hasOwnProperty( qid ) ) {
                self.question[qid] = new LW.Question( questionData[qid] );
            }
        }
        promise.resolve( true );
    } ).fail( function () { promise.reject(); } );

    return promise;
};
/**
 * Requirements:
 * – Character references are loaded
 */
LW.QuestionModel.prototype.loadReferences = function ( dirty ) {
    var updated = false;
    if ( dirty.indexOf( LW.QuestionModel.prototype.id ) >= 0 ||
        dirty.indexOf( LW.CharacterModel.prototype.id ) >= 0 ||
        dirty.indexOf( LW.ComponentModel.prototype.id ) >= 0 ) {
        this.updateCharacterReferences();
        this.updateEquipmentReferences();
        this.updateComponentReferences();
        this.updateTree();
        updated = true;
    }
    this.dirty = false;
    return $.Deferred().resolve( updated );
};
LW.QuestionModel.prototype.updateComponentReferences = function () {
    for ( var qid in this.question ) {
        if ( this.question.hasOwnProperty( qid ) ) {
            var question = this.question[qid];
            question.component = LW.root.componentModel.component[question.componentID];
            question.difficulty = LW.root.difficultyModel.difficulty[question.difficultyID] || null;
        }
    }
};
/**
 * Requirements:
 * – Character data is read
 */
LW.QuestionModel.prototype.updateCharacterReferences = function() {
    // Purge references and set parent character
    var question;
    for (var qid in this.question) {
        if (this.question.hasOwnProperty(qid)) {
            question = this.question[qid];
            question.characters = [];
            question.parentCharacter = LW.root.characterModel.character[question.parentCharacterID];
        }
    }
    // Set up references
    for ( var cid in LW.root.characterModel.character ) {
        if ( LW.root.characterModel.character.hasOwnProperty( cid ) ) {
            var character = LW.root.characterModel.character[cid];
            if ( character.parentQuestionID ) {
                if ( !this.question[character.parentQuestionID] ) {
                    console.log( 'Fail:', this.question.name, ', Parent', character.parentQuestionID, character, character.name );
                    continue;
                }
                this.question[character.parentQuestionID].characters.push( character );
            }
        }
    }
};
/**
 * Requirements:
 * – Equipment data is read
 */
LW.QuestionModel.prototype.updateEquipmentReferences = function() {

    var equipmentMapping = null;

    LW.ajaxQuery('questionEquipmentData').done( function(data) {
            if (data.ok) {
                equipmentMapping = data.data;
            } else {
                console.log(data.message);
            }
        });

    // Purge equipment
    for (var qid in this.question) {
        if (this.question.hasOwnProperty(qid)) {
            this.question[qid].equipment = [];
        }
    }
    // Set new references
    if (equipmentMapping) {
        for (var e = 0, E = equipmentMapping.length; e < E; e++) {
            var item = equipmentMapping[e];
            this.question[item.question].equipment.push(
                LW.root.equipmentModel.equipment[item.equipment]);
        }
    }
};
/**
 * Requirements:
 * – Component data is read
 */
LW.QuestionModel.prototype.sortedByName = function() {
    var list = [];
    this.each( function(index, question) {
        list.push(question);
    }, false );
    list.sort(function(a,b) { return a.name.localeCompare(b.name); });
    return list;
};
/**
 * Requirements:
 * – Question data loaded
 * – Requirements for LW.Question.updateRoot
 */
LW.QuestionModel.prototype.updateTree = function() {
    this.rootNodes = [];
    for (var qid in this.question) {
        if (this.question.hasOwnProperty(qid)) {
            var question = this.question[qid];
            question.updateRoot(0);
            if (question.isRoot()) {
                this.rootNodes.push(question);
            }
        }
    }
    for (var r = 0, R = this.rootNodes.length; r < R; r++) {
        this.rootNodes[r].updateDepth(1);
    }
};
LW.Question = function ( data ) {
    if ( !(this instanceof LW.Question) ) { return new LW.Question( data ); }

    /* Mandatory */

    this.id = undefined;
    this.name = undefined;
    this.componentID = undefined;

    /* Optional */

    this.file = undefined;
    this.timeSec = undefined;
    this.difficultyID = undefined;
    this.parentCharacterID = undefined;
    this.description = {
        text: undefined,
        /// @type {Array.<String>}
        images: []
    };

    /* Loaded here */

    /// @type {LW.Difficulty}
    this.difficulty = null;
    /// @type {LW.Component}
    this.component = null;
    /// @type {[LW.Character]}
    this.characters = [];
    /// @type {[LW.Equipment]}
    this.equipment = [];
    /// @type {LW.Character|undefined}
    this.parentCharacter = undefined;

    /// @type {LW.Character}
    /// Points to the root node, or to this if this is the root
    this.root = null;
    this.depth = -1;


    this.loadData( data );

    return this;
};
/** @param {LW.QCVisitor} qcVisitor
 *  @param {Object} componentStorage */
LW.Question.prototype.visit = function(qcVisitor, componentStorage) {
    var questionStorage = {};

    // Only visit this question if the component is correct (to avoid visiting the same question multiple times)
    var visitThis = this.component == qcVisitor.options.currentComponent || !qcVisitor.options.useComponents;
    if (visitThis) {
        qcVisitor.preorderQuestionVisitor(this, questionStorage, componentStorage);
    }

    var characters;
    if (qcVisitor.characterCompare) {
        characters = this.characters.filter( function() { return true; });
        characters.sort(qcVisitor.characterCompare);
    } else {
        characters = this.characters
    }
    for (var c = 0, C = characters.length; c < C; c++) {
        characters[c].visit(qcVisitor, questionStorage, componentStorage);
    }

    if (visitThis) {
        qcVisitor.postorderQuestionVisitor(this, questionStorage, componentStorage);
    }
};
LW.Question.prototype.characterList = function() {
    var list = this.characters;
    list.sort( function(a,b) {
        return a.name.localeCompare(b.name);
    });
    return list;
};
/**
 * @returns {Array} List of valid parent characters that do not create a circular dependency
 */
LW.Question.prototype.validParentList = function() {

    var list = [];
    var self = this;
    LW.root.characterModel.each( function(cid, character) {
        if (character.parentQuestion.root != self.root ||
            character.parentQuestion.depth <= self.depth && character.parentQuestion != self) {
            list.push(character);
        }
    }, false);

    list.sort( function(a,b) {
        if (a.parentQuestion == b.parentQuestion) {
            return a.name.localeCompare(b.name);
        }
        return a.parentQuestion.name.localeCompare(b.parentQuestion.name);
    });

    return list;
};
/**
 * Requirements:
 * – Question model: Character references loaded
 * – Character model: Question references loaded
 */
LW.Question.prototype.updateRoot = function(currentDepth) {
    if (isNaN(currentDepth)) { currentDepth = 0; }

    if (currentDepth > LW.recurseLimit) {
        console.log('Question: ', this.name, this);
        console.log('Parent character: ', this.parentCharacter.name, this.parentCharacter);
        if (this.parentCharacter.parentQuestion.parentCharacter) {
            var pc = this.parentCharacter.parentQuestion.parentCharacter;
            console.log('Parent character 2: ', pc.name, pc);
            if (this.parentCharacter == pc) {
                console.log(pc.name, ' recurses itself; Attempting to fix this');
                this.parentCharacter.parentQuestion.changeParentCharacter(null).done( function() {
                    console.log('Recursion fixed!');
                } );
            }
        }
        throw new Error('Recursion limit reached; cycle in tree?');
    }

    if (this.root) { return this.root; }

    this.root = this;
    if (this.parentCharacter) {
        if (this.parentCharacter.parentQuestion) {
            this.root = this.parentCharacter.parentQuestion.updateRoot(currentDepth+1);
        } else {
            console.warn('Error: Character has no parent question: ' + this.parentCharacter.name + ' (' + this.parentCharacterID + ')');
        }
    }

    return this.root;
};
LW.Question.prototype.isRoot = function() {
    this.updateRoot(0);
    return !this.parentCharacter;
};
LW.Question.prototype.updateDepth = function(depth) {
    if (isNaN(depth)) { depth = 1; }

    if (depth > LW.recurseLimit) { throw new Error('Recursion depth limit reached; cycle in tree?'); }

    this.depth = depth;
    for (var c = 0, C = this.characters.length; c < C; c++) {
        var character = this.characters[c];
        for (var q = 0, Q = character.childQuestions.length; q < Q; q++) {
            character.childQuestions[q].updateDepth(depth+1);
        }
    }
};


LW.CharacterModel = function() {
    if (!(this instanceof LW.CharacterModel)) { return new LW.CharacterModel(); }

    /// @type {Object.<LW.Character>}
    this.character = {};
    this.dirty = true;

    return this;
};
LW.CharacterModel.prototype.id = "Character";
LW.CharacterModel.prototype.each = function(handle, allTopics) {
    var cid,
        character;
    if (allTopics === true) {
        for (cid in this.character) {
            if (this.character.hasOwnProperty(cid)) {
                character = this.character[cid];
                handle(cid, character);
            }
        }
    } else {
        // Only call the handle on taxa that match the current topic
        for ( cid in this.character ) {
            if ( this.character.hasOwnProperty( cid ) ) {
                character = this.character[cid];
                if ( !character.parentQuestion ) {
                    console.log( 'Fail: Character has no parent question.', character );
                    continue;
                }
                if ( character.parentQuestion.component.topic == LW.root.currentTopic ) {
                    handle( cid, character );
                }
            }
        }
    }
};
LW.CharacterModel.prototype.fetchData = function () {
    throw Error( 'Data fetching not implemented.' );
};
LW.CharacterModel.prototype.loadData = function () {

    var promise = $.Deferred();

    if ( !this.dirty ) { return promise.resolve( false ); }

    var self = this;
    this.character = {};
    this.fetchData().done(function ( characterData ) {
        for ( var cid in characterData ) {
            if ( characterData.hasOwnProperty( cid ) ) {
                self.character[cid] = new LW.Character( characterData[cid] );
            }
        }
        promise.resolve( true );
    } ).fail( function () { promise.reject(); } );

    return promise;
};
/**
 * @param {[string]} dirty
 */
LW.CharacterModel.prototype.loadReferences = function ( dirty ) {
    var updated = false;
    if ( dirty.indexOf( LW.CharacterModel.prototype.id ) >= 0 ||
        dirty.indexOf( LW.QuestionModel.prototype.id ) >= 0 ) {
        this.updateQuestionReferences();
        updated = true;
    }
    this.dirty = false;
    return $.Deferred().resolve( updated );
};
/**
 * Requirements:
 * – Question model data is read
 */
LW.CharacterModel.prototype.updateQuestionReferences = function() {
    // Purge the question children and set the parent question
    var question, character;
    for ( var cid in this.character ) {
        if ( this.character.hasOwnProperty( cid ) ) {
            character = this.character[cid];
            question = LW.root.questionModel.question[character.parentQuestionID];
            character.childQuestions = [];
            if ( question ) {
                character.parentQuestion = question;
            } else {
                console.log( 'Fail: Character ' + cid + ' has no parent question: ' + character.parentQuestionID + ' missing' );
            }
        }
    }
    // Set the question children
    for (var qid in LW.root.questionModel.question) {
        if (LW.root.questionModel.question.hasOwnProperty(qid)) {
            question = LW.root.questionModel.question[qid];
            if (question.parentCharacterID) {
                this.character[question.parentCharacterID].childQuestions.push(question);
            }
        }
    }
};
LW.Character = function ( data ) {
    if ( !(this instanceof LW.Character) ) { return new LW.Character( data ); }

    /* Mandatory */

    this.id = undefined;
    this.name = undefined;
    this.parentQuestionID = undefined;

    /* Optional */

    this.file = undefined;
    this.description = {
        text: undefined,
        /// @type {Array.<String>}
        images: []
    };

    /* Filled in */

    /// @type {LW.Question}
    this.parentQuestion = null;
    /// @type {[LW.Question]}
    /// Questions having this character as parent
    this.childQuestions = [];

    this.loadData( data );

    return this;
};
/** @param {LW.QCVisitor} qcVisitor
 *  @param {Object} questionStorage
 *  @param {Object} componentStorage */
LW.Character.prototype.visit = function(qcVisitor, questionStorage, componentStorage) {

    var visitThis = this.parentQuestion.component == qcVisitor.options.currentComponent || !qcVisitor.options.useComponents;

    if (visitThis) {
        qcVisitor.preorderCharacterVisitor(this, questionStorage, componentStorage);
    }

    // DFS: Visit the question children of this character as well.
    if (qcVisitor.options.dfs) {

        /// @type {[LW.Question]}
        var children = this.childQuestions.filter( function() { return true; });

        children = children.sort(qcVisitor.questionCompare);

        var c, C;
        if ( qcVisitor.questionPriorities ) {
            /// @type {[LW.Question]}
            var priorityNodes = children
                .filter( function ( node ) {
                    return qcVisitor.questionPriorities[node.id] !== undefined;
                } )
                .sort( function ( a, b ) {
                    return qcVisitor.questionPriorities[b.id] - qcVisitor.questionPriorities[a.id];
                } );

            /// @type {[LW.Question]}
            var otherNodes = children
                .filter( function ( node ) {
                    return qcVisitor.questionPriorities[node.id] === undefined;
                } );

            for ( c = 0, C = priorityNodes.length; c < C; c++ ) {
                priorityNodes[c].visit( qcVisitor, componentStorage );
            }
            for ( c = 0, C = otherNodes.length; c < C; c++ ) {
                otherNodes[c].visit( qcVisitor, componentStorage );
            }
        } else {
            for ( c = 0, C = children.length; c < C; c++ ) {
                children[c].visit( qcVisitor, componentStorage );
            }
        }
    }

    if (visitThis) {
        qcVisitor.postorderCharacterVisitor(this, questionStorage, componentStorage);
    }
};


LW.EquipmentModel = function() {
    if (!(this instanceof LW.EquipmentModel)) { return new LW.EquipmentModel(); }

    /// @type {Object.<LW.Equipment>}
    this.equipment = {};
    this.dirty = true;

    return this;
};
LW.EquipmentModel.prototype.id = "Equipment";
LW.EquipmentModel.prototype.fetchData = function () {
    throw Error( 'Data fetching not implemented.' );
};
LW.EquipmentModel.prototype.loadData = function () {

    var promise = $.Deferred();

    if ( !this.dirty ) { return promise.resolve( false ); }

    var self = this;
    this.fetchData().done(function ( data ) {
        for ( var did in data ) {
            if ( data.hasOwnProperty( did ) ) {
                self.equipment[did] = new LW.Equipment( data[did] );
            }
        }
        promise.resolve( true );
    } ).fail( function () { promise.reject(); } );

    return promise;
};
LW.EquipmentModel.prototype.loadReferences = function () {
    this.dirty = false;
    return $.Deferred().resolve( false );
};
LW.EquipmentModel.prototype.sortedList = function() {
    var list = [];
    for (var eid in this.equipment) {
        if (this.equipment.hasOwnProperty(eid)) {
            list.push(this.equipment[eid]);
        }
    }
    list.sort(function(a,b) { return a.name.localeCompare(b.name); });
    return list;
};
LW.Equipment = function ( data ) {
    if ( !(this instanceof LW.Equipment) ) { return new LW.Equipment( data ); }

    this.id = undefined;
    this.name = undefined;

    this.file = undefined;
    this.description = {
        images: {}
    };

    this.loadData( data );

    return this;
};




LW.RangeModel = function() {
    if (!(this instanceof LW.RangeModel)) { return new LW.RangeModel(); }

    // @type Object.<LW.Range>
    this.range = {};
    this.dirty = true;

    return this;

};
LW.RangeModel.id = "Range";
LW.RangeModel.prototype.id = LW.RangeModel.id;
LW.RangeModel.prototype.each = function(handle, allTopics) {
    var rid,
        range;
    if (allTopics === true) {
        // Call the handle on all taxa
        for (rid in this.range) {
            if (this.range.hasOwnProperty(rid)) {
                range = this.range[rid];
                handle(rid, range);
            }
        }
    } else {
        // Only call the handle on taxa that match the current topic
        for (rid in this.range) {
            if (this.range.hasOwnProperty(rid)) {
                range = this.range[rid];
                if (range.component.topic == LW.root.currentTopic) {
                    handle(rid, range);
                    //console.log('Topic ok: ', range.name, range.component.topic.name);
                } else {
                    //console.log('Wrong topic: ', range.name, range.component.topic.name);
                }
            }
        }
    }
};
LW.RangeModel.prototype.fetchData = function () {
    throw Error( 'Data fetching not implemented.' );
};
LW.RangeModel.prototype.loadData = function() {

    var promise = $.Deferred();

    if ( !this.dirty ) { return promise.resolve( false ); }

    var self = this;
    this.range = {};
    this.fetchData().done( function ( data ) {
        for ( var did in data ) {
            if ( data.hasOwnProperty( did ) ) {
                self.range[data[did].id] = new LW.Range( data[did] );
            }
        }
        promise.resolve( true );
    } ).fail( function () { promise.reject(); } );

    return promise;
};
LW.RangeModel.prototype.loadReferences = function ( dirty ) {
    var updated = false;

    if ( dirty.indexOf( this.id ) >= 0 ||
        dirty.indexOf( LW.TaxonModel.id ) >= 0 ) {
        this.updateReferences();
        updated = true;
    }

    this.dirty = false;
    return $.Deferred().resolve( updated );
};
LW.RangeModel.prototype.updateReferences = function() {

    var range;
    for (var rid in this.range) {
        if (this.range.hasOwnProperty(rid)) {
            range = this.range[rid];
            range.usedRanges = [];
            range.component = LW.root.componentModel.component[range.componentID];
        }
    }
    var rangeValue;
    for (var r = 0, R = LW.root.taxonModel.rangeValues.length; r < R; r++) {
        rangeValue = LW.root.taxonModel.rangeValues[r];
        this.range[rangeValue.rangeID].usedRanges.push(rangeValue);
    }
};
LW.Range = function ( data ) {
    if ( !(this instanceof LW.Range) ) { return new LW.Range( data ); }

    this.id = undefined;
    this.name = undefined;
    this.unit = undefined;
    this.datatype = undefined;

    this.componentID = undefined;
    this.parentCharacterID = undefined;


    /// @type {LW.Component|undefined}
    this.component = undefined;

    /// @type {LW.Character|null}
    this.parentCharacter = null;

    /// @type {[LW.RangeValue]}
    this.usedRanges = [];

    this.loadData( data );

    return this;
};
LW.Range.prototype.getRange = function() {
    var min, max;

    if (this.usedRanges.length > 0) {
        min = this.usedRanges[0].min;
        max = this.usedRanges[0].max;

        for (var r = 1, R = this.usedRanges.length; r < R; r++) {
            min = Math.min(min, this.usedRanges[r].min);
            max = Math.max(max, this.usedRanges[r].max);
        }
    }

    return {
        min : min,
        max : max
    };
};
LW.RangeValue = function(data) {
    if (!(this instanceof LW.RangeValue)) { return new LW.RangeValue(data); }

    this.min = data.min;
    this.max = data.max;
    this.taxonID = data.taxon;
    this.rangeID = data.range;

    return this;
};
/**
 * @param {LW.RangeValue} other
 */
LW.RangeValue.prototype.intersect = function(other) {

    if (this.min <= other.min && this.max >= other.min) {
        return true;
    }
    if (this.min <= other.max && this.max >= other.max) {
        return true;
    }

    return false;

};



LW.DegreeModel = function() {
    if (!(this instanceof LW.DegreeModel)) { return new LW.DegreeModel(); }

    /// @type {Object.<LW.Degree>}
    this.degree = {};
    this.dirty = true;

    return this;
};
LW.DegreeModel.prototype.id = "Degree";
LW.DegreeModel.prototype.fetchData = function () {
    throw Error( 'Data fetching not implemented.' );
};
LW.DegreeModel.prototype.loadData = function () {

    var promise = $.Deferred();

    if ( !this.dirty ) { return promise.resolve( false ); }

    var self = this;
    this.degree = {};
    this.fetchData().done(function ( data ) {
        for ( var did in data ) {
            if ( data.hasOwnProperty( did ) ) {
                self.degree[data[did].id] = new LW.Degree( data[did] );
            }
        }
        promise.resolve( true );
    } ).fail( function () { promise.reject(); } );

    return promise;
};
LW.DegreeModel.prototype.loadReferences = function () {
    this.dirty = false;
    return $.Deferred().resolve( false );
};
LW.DegreeModel.prototype.get = function ( lowercaseName ) {
    for ( var did in this.degree ) {
        if ( this.degree.hasOwnProperty( did ) ) {
            if ( this.degree[did].name.toLowerCase() == lowercaseName ) {
                return this.degree[did];
            }

        }
    }
    return null;
};
LW.DegreeModel.prototype.sortedByID = function() {
    var list = [];
    for (var did in this.degree) {
        if (this.degree.hasOwnProperty(did)) {
            list.push(this.degree[did]);
        }
    }
    list.sort( function(a,b) {
        return b.id - a.id;
    });
    return list;
};
LW.Degree = function(data) {
    if (!(this instanceof LW.Degree)) { return new LW.Degree(data); }

    this.id = data.id;
    // Latin name of this degree
    this.name = data.name;
    this.abbr = data.abbr;

    return this;
};



/**
 * Taxon model
 */
LW.TaxonModel = function() {
    if (!(this instanceof LW.TaxonModel)) { return new LW.TaxonModel(); }

    /// @type {Object.<string, LW.Taxon>}
    this.taxon = {};
    /// Contains all range values used by the taxa.
    /// @type {[LW.RangeValue]}
    this.rangeValues = [];
    this.dirtyData = true;
    this.dirtyReferences = true;

    return this;
};
LW.TaxonModel.prototype.id = "Taxon";
LW.TaxonModel.prototype.each = function(handle, allTopics) {
    var tid,
        taxon;
    if (allTopics === true) {
        // Call the handle on all taxa
        for (tid in this.taxon) {
            if (this.taxon.hasOwnProperty(tid)) {
                taxon = this.taxon[tid];
                handle(tid, taxon);
            }
        }
    } else {
        // Only call the handle on taxa that match the current topic
        for (tid in this.taxon) {
            if (this.taxon.hasOwnProperty(tid)) {
                taxon = this.taxon[tid];
                if (taxon.topic == LW.root.currentTopic) {
                    handle(tid, taxon);
                }
            }
        }
    }
};
/**
 * @param {LW.TaxonVisitor} visitor
 */
LW.TaxonModel.prototype.visit = function ( visitor ) {
    var taxa = [];
    this.each( function ( id, taxon ) {
        if ( visitor.filterTaxon( taxon ) ) {
            taxa.push( taxon );
        }
    }, false );
    taxa.sort( visitor.taxonCompare );
    taxa.forEach( function ( taxon ) {
        visitor.taxonVisitor( taxon, visitor.acceptTaxon( taxon ), visitor.data );
    } );
};
LW.TaxonModel.prototype.fetchData = function() {
    throw Error('Taxon model: fetchData not implemented.');
};
LW.TaxonModel.prototype.loadData = function () {

    var promise = $.Deferred();

    if ( !this.dirtyData ) { return promise.resolve( false ); }

    var self = this;
    this.taxon = {};
    this.fetchData().done(function ( data ) {
        for ( var tid in data ) {
            if ( data.hasOwnProperty( tid ) ) {
                self.taxon[tid] = new LW.Taxon( data[tid] );
            }
        }
        self.dirtyData = false;
        self.dirtyReferences = true;
        promise.resolve( true );
    } ).fail( function () { promise.reject(); } );


    return promise;
};
/**
 * @param {[string]} dirty
 */
LW.TaxonModel.prototype.loadReferences = function ( dirty ) {
    var updated = false;
    if ( this.dirtyReferences ||
        dirty.indexOf( LW.TaxonModel.prototype.id ) >= 0 ||
        dirty.indexOf( LW.CharacterModel.prototype.id ) >= 0 ) {

        this.updateCharacterReferences();
        this.updateOtherReferences();

        updated = true;
    }
    this.dirtyReferences = false;

    return $.Deferred().resolve( updated );
};
/**
 * Requirements:
 * – Character data is read
 */
LW.TaxonModel.prototype.updateCharacterReferences = function () {

    // Clear all current references
    var taxon;
    for ( var tid in this.taxon ) {
        if ( this.taxon.hasOwnProperty( tid ) ) {
            taxon = this.taxon[tid];
            taxon.characters = [];

            var cid;
            for ( var c = 0, C = taxon.characterIds.length; c < C; c++ ) {
                cid = taxon.characterIds[c];
                if ( LW.root.characterModel.character[cid] ) {
                    taxon.characters.push( LW.root.characterModel.character[cid] );
                } else {
                    console.log( 'Fail: Character ' + cid + ' used by taxon ' + taxon.id
                        + ' (' + taxon.name + ') does not exist.' );
                }
            }
        }
    }

};
LW.TaxonModel.prototype.updateOtherReferences = function () {
    var tid;
    for ( tid in this.taxon ) {
        if ( this.taxon.hasOwnProperty( tid ) ) {
            this.taxon[tid].loadReferences();
        }
    }
    for ( tid in this.taxon ) {
        if ( this.taxon.hasOwnProperty( tid ) ) {
            this.taxon[tid].loadSelfReferences();
        }
    }
    for ( tid in this.taxon ) {
        if ( this.taxon.hasOwnProperty( tid ) ) {
            this.taxon[tid].loadTaxonTree();
        }
    }
};
/**
 * @returns {[LW.Taxon]}
 */
LW.TaxonModel.prototype.sortedByName = function(allTopics) {
    var list = [];
    this.each( function(tid, taxon) { list.push(taxon); }, allTopics);
    list.sort( function(a,b) {
        return a.name.localeCompare(b.name);
    });
    return list;
};
LW.TaxonModel.prototype.treeMatrix = function() {

    var species = [], s, S;
    this.each( function(tid, taxon) {
        if ( taxon.degree && taxon.degree.name.toLowerCase() == 'species' ) {
            species.push( taxon );
        }
    }, false);

    species.sort( function ( a, b ) {

        if ( a.taxonomy.genus === undefined && b.taxonomy.genus === undefined ) {
            return a.name.localeCompare( b.name );
        }
        if ( a.taxonomy.genus === undefined || b.taxonomy.genus === undefined ) {
            return (a.taxonomy.genus === undefined) - (b.taxonomy.genus === undefined);
        }

        if ( a.taxonomy.ordo != b.taxonomy.ordo ) {
            return a.taxonomy.ordo.name.localeCompare( b.taxonomy.ordo.name );
        }
        if ( a.taxonomy.familia != b.taxonomy.familia ) {
            return a.taxonomy.familia.name.localeCompare( b.taxonomy.familia.name );
        }
        if ( a.taxonomy.genus != b.taxonomy.genus ) {
            return a.taxonomy.genus.name.localeCompare( b.taxonomy.genus.name );
        }
        return a.name.localeCompare( b.name );
    } );

    var data = [ {
        width: 150,
        title: 'ORDO',
        row: []
    }, {
        width: 200,
        title: 'FAMILIA',
        row: []
    }, {
        width: 200,
        title: 'GENUS',
        row: []
    }, {
        width: 250,
        title: 'SPECIES',
        row: []
    } ];
    for ( s = 0, S = species.length; s < S; s++ ) {
        data[3].row.push( {
            text: species[s].name,
            id: species[s].id
        } );
        if ( species[s].taxonomy.genus ) {
            data[2].row.push( {
                text: species[s].taxonomy.genus.name,
                id: species[s].taxonomy.genus.id
            } );
        } else {
            data[2].row.push( { text: null, id: undefined } );
        }
        if ( species[s].taxonomy.familia ) {
            data[1].row.push( {
                text: species[s].taxonomy.familia.name,
                id: species[s].taxonomy.familia.id
            } );
        } else {
            data[1].row.push( { text: null, id: undefined } );
        }
        if ( species[s].taxonomy.ordo ) {
            data[0].row.push( {
                text: species[s].taxonomy.ordo.name,
                id: species[s].taxonomy.ordo.id
            } );
        } else {
            data[0].row.push( { text: null, id: undefined } );
        }
    }

    return data;
};
LW.Taxon = function ( data ) {
    if ( !(this instanceof LW.Taxon) ) { return new LW.Taxon( data ); }


    /* Must be filled in loadData */

    // Taxon ID
    this.id = undefined;
    // Taxon name
    this.name = undefined;
    // This taxon's topic
    this.topicID = undefined;


    /* May be filled in loadData */

    // File containing the taxon description
    this.file = undefined;
    // List of character IDs
    this.characterIds = [];
    this.parentTaxonID = undefined;
    this.degreeID = undefined;

    this.description = {
        text: undefined,
        /// @type {Array.<String>}
        images: []
    };


    /* Will be filled later */

    /// @type {LW.Topic|undefined}
    this.topic = undefined;
    /// @type {LW.Degree|undefined}
    this.degree = undefined;
    /// @type {LW.Taxon|undefined}
    this.parentTaxon = undefined;
    /// @type {[LW.Character]}
    this.characters = [];
    /// @type {Object.<Number, LW.RangeValue>}
    this.rangeValues = {};


    this.taxonomy = {
        /// @type {LW.Taxon|undefined}
        genus: undefined,
        /// @type {LW.Taxon|undefined}
        familia: undefined,
        /// @type {LW.Taxon|undefined}
        ordo: undefined
    };

    this.loadData( data );

    return this;
};
LW.Taxon.prototype.loadReferences = function () {
    this.degree = LW.root.degreeModel.degree[this.degreeID];
    this.topic = LW.root.topicModel.topic[this.topicID];
    this.parentTaxon = LW.root.taxonModel.taxon[this.parentTaxonID];
};
LW.Taxon.prototype.loadSelfReferences = function () {
    // Set the next-higher parent taxon field
    if ( this.degree && this.parentTaxon ) {
        if ( !this.parentTaxon.degree ) {
            console.log( 'Parent taxon has no degree: ' + this.parentTaxon.name );
        } else {
            if ( this.degree.name.toLowerCase() == 'species' ) {
                if ( this.parentTaxon.degree.name.toLowerCase() == 'genus' ) {
                    this.taxonomy.genus = this.parentTaxon;
                } else {
                    console.log( this.name + ' has incorrect parent degree ' + this.parentTaxon.degree.name + ' (expected: genus)' );
                }
            } else if ( this.degree.name.toLowerCase() == 'genus' ) {
                if ( this.parentTaxon.degree.name.toLowerCase() == 'familia' ) {
                    this.taxonomy.familia = this.parentTaxon;
                } else {
                    if ( this.parentTaxon.degree.name.toLowerCase() == 'subfamilia' && this.parentTaxon.parentTaxon
                        && this.parentTaxon.parentTaxon.degree && this.parentTaxon.parentTaxon.degree.name.toLowerCase() == 'familia' ) {
                        this.taxonomy.familia = this.parentTaxon.parentTaxon;
                    } else {
                        console.log( this.name + ' has incorrect parent degree ' + this.parentTaxon.degree.name + ' (expected: familia)' );
                    }
                }
            } else if ( this.degree.name.toLowerCase() == 'familia' ) {
                if ( this.parentTaxon.degree.name.toLowerCase() == 'ordo' ) {
                    this.taxonomy.ordo = this.parentTaxon;
                } else {
                    console.log( this.name + ' has incorrect parent degree ' + this.parentTaxon.degree.name + ' (expected: ordo)' );
                }

            }
        }
    }
};
LW.Taxon.prototype.loadTaxonTree = function () {
    if ( this.taxonomy.genus ) {
        this.taxonomy.familia = this.taxonomy.genus.taxonomy.familia;
    }
    if ( this.taxonomy.familia ) {
        this.taxonomy.ordo = this.taxonomy.familia.taxonomy.ordo;
    }
};
LW.Taxon.prototype.validParentList = function() {
    var list = [],
        currentTaxon = this;
    LW.root.taxonModel.each( function(index, taxon) {

        var add = currentTaxon.degree === undefined || taxon.degree === undefined;

        if (currentTaxon.degree && taxon.degree) {
            var current = currentTaxon.degree.name.toLowerCase();
            var other = taxon.degree.name.toLowerCase();

            switch (current) {
                case 'species' :
                    add = other == 'genus';
                    break;
                case 'genus' :
                    add = other == 'subfamilia' || other == 'familia';
                    break;
                case 'subfamilia' :
                    add = other == 'familia';
                    break;
                case 'familia' :
                    add = other == 'ordo';
                    break;
            }
        }

        if (add) {
            if (this !== taxon) {
                list.push(taxon);
            }
        }
    }, false );
    list.sort(function(a,b) { return a.name.localeCompare(b.name); });
    return list;
};


/** Dummy function that can be overridden to load this.data correctly. */
(function () {
    var emptyLoader = function ( data ) {
        throw Error( 'No data loader defined!' );
    };
    LW.Taxon.prototype.loadData = emptyLoader;
    LW.Topic.prototype.loadData = emptyLoader;
    LW.Question.prototype.loadData = emptyLoader;
    LW.Character.prototype.loadData = emptyLoader;
    LW.Range.prototype.loadData = emptyLoader;
    LW.Equipment.prototype.loadData = emptyLoader;
    LW.Component.prototype.loadData = emptyLoader;
})();

LW.Taxon.prototype.loadData = function ( data ) {

    this.id = data.id;
    this.name = data.name;
    this.file = data.file;

    this.parentTaxonID = data.parentTaxon;
    this.degreeID = data.degree;
    this.topicID = data.topic;
    this.characterIds = data.characters;

    if ( data.description  ) {
        this.description = data.description;
    }

};
LW.Topic.prototype.loadData = function ( data ) {

    this.id = data.id;
    this.name = data.name;
    this.file = data.file;
    this.data = data;
    if ( data.description  ) {
        this.description = data.description;
    }

};
LW.Question.prototype.loadData = function ( data ) {

    this.id = data.id;
    this.name = data.name;
    this.file = data.file;
    this.timeSec = data.timeSec;
    this.parentCharacterID = data.parentCharacter;
    this.componentID = data.component;
    this.difficultyID = data.difficulty;
    if ( data.description ) {
        this.description = data.description;
    }
};
LW.Character.prototype.loadData = function ( data ) {

    this.id = data.id;
    this.name = data.name;
    this.file = data.file;
    this.data = data;
    this.parentQuestionID = data.parentQuestion;
    if ( data.description ) {
        this.description = data.description;
    }
};
LW.Range.prototype.loadData = function ( data ) {

    this.id = data.id;
    this.name = data.name;
    this.unit = data.unit;
    this.datatype = data.datatype;
    this.componentID = data.component;

    this.parentCharacterID = data.parentCharacter;
};
LW.Equipment.prototype.loadData = function ( data ) {

    this.id = data.id;
    this.name = data.name;
    this.file = data.file;
    this.description = {
        images: {}
    };

};
LW.Component.prototype.loadData = function ( data ) {

    this.id = data.id;
    this.name = data.name;
    this.file = data.file;

    this.topicID = data.topic;

    if ( data.description ) {
        this.description = data.description;
    }

};