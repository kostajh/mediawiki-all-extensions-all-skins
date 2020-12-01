/**
 * This file contains the filtering logic used to identify a taxon.
 *
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 * @require {LW}
 */

/**
 * Namespace for filtering taxa.
 *
 * {LWF.State} contains the user state,
 * {LWF.TaxonRating} and co. are used for rating items (e.g. for counting matching characters),
 * {LWF.IdentificationModel} is the object used for identification and making use of the above structures.
 *
 */
var LWF = LWF || {};
LWF.UI = LWF.UI || {};
var i18n = i18n || { t: function() { return false; } };

LWF.initTranslations = function() {
    /*
    LWF.AcceptTaxon.exact.name = i18n.t('lw.exact') || LWF.AcceptTaxon.exact.name;
    LWF.AcceptTaxon.exact.description = i18n.t('lw.exact-desc') || LWF.AcceptTaxon.exact.description;

    LWF.AcceptTaxon.errorTolerant.name = i18n.t('lw.error-tolerant') || LWF.AcceptTaxon.errorTolerant.name;

    LWF.UITaxonOrder.leastWrong.name = i18n.t('lw.least-wrong') || LWF.UITaxonOrder.leastWrong.name;
    LWF.QCVisitor.simple.name = i18n.t('lw.simple') || LWF.QCVisitor.simple.name;
    LWF.QCVisitor.mostUsed.name = i18n.t('lw.most-used') || LWF.QCVisitor.mostUsed.name;
    LWF.QCVisitor.hierarchical.name = i18n.t('lw.hierarchical') || LWF.QCVisitor.hierarchical.name;
    LWF.QCVisitor.performance.name = i18n.t('lw.performance') || LWF.QCVisitor.performance.name;
    */
};

/**
 * @param {LW.Taxon} taxon
 */
LWF.TaxonRating = function ( taxon ) {
    if ( !(this instanceof LWF.TaxonRating) ) { return new LWF.TaxonRating( taxon ); }

    /// @type {LW.Taxon}
    this.taxon = taxon;

    /// Division of the observed characters into matching (ok), mismatching (wrong), and unknown matching (unknown)
    this.characters = {
        /// @type {[LW.Character]}
        ok: [],
        /// @type {[LW.Character]}
        wrong: [],
        /// @type {[LW.Character]}
        unknown: []
    };
    this.cost = {};

    return this;
};
LWF.TaxonRatingsContainer = function () {
    if ( !(this instanceof LWF.TaxonRatingsContainer) ) { return new LWF.TaxonRatingsContainer(); }

    this.prefilteredTaxa = [];
    this.filteredTaxa = [];
    /// @type {LWF.TaxonRating}
    this.data = {};

    return this;
};
/**
 * @param {LW.Question} question
 */
LWF.QuestionRating = function ( question ) {
    if ( !(this instanceof LWF.QuestionRating) ) { return new LWF.QuestionRating( question ); }

    /// @type {LW.Question}
    this.question = question;

    this.counter = {
        /// Number of characters of this question that occur at least once in the set of taxa examined
        usedCharacters: 0,
        // Sum of the number of matches of this question's characters, for the set of taxa examined
        sumOk: 0,
        // Number of selected (observed) characters of this question
        selectedCharacters: 0
    };
    this.cost = {};

    return this;
};
/**
 * @param {LW.Character} character
 */
LWF.CharacterRating = function ( character ) {
    if ( !(this instanceof LWF.CharacterRating) ) { return new LWF.CharacterRating( character ); }

    /// @type {LW.Character}
    this.character = character;

    this.counter = {
        // Counts how many times this character was used in the set of examined taxa
        ok: 0,
        // Counts how many times this character is wrong in the set of examined taxa
        wrong: 0
    };

    this.cost = {};

    return this;
};
LWF.RatingsContainer = function () {
    if ( !(this instanceof LWF.RatingsContainer) ) { return new LWF.RatingsContainer(); }

    /// @type {LWF.TaxonRatingsContainer}
    this.taxon = new LWF.TaxonRatingsContainer();
    /// @type {Object.<LWF.QuestionRating>}
    this.question = {};
    /// @type {Object.<LWF.CharacterRating>}
    this.character = {};

    return this;
};

/**
 * Container for observations and user choices.
 * @returns {LWF.ChoicesContainer}
 * @constructor
 */
LWF.ChoicesContainer = function () {
    if ( !(this instanceof LWF.ChoicesContainer) ) { return new LWF.ChoicesContainer(); }

    this.callbacks = [];

    var self = this;
    var callbackFunction = function ( source, id ) { self.trigger( source, id ); };

    /// @type {LWF.CharacterChoices}
    this.character = new LWF.CharacterChoices( callbackFunction );
    /// @type {LWF.RangeChoices}
    this.range = new LWF.RangeChoices( callbackFunction );
    /// @type {LWF.ComponentChoices}
    this.component = new LWF.ComponentChoices( callbackFunction );
    /// @type {LWF.EquipmentChoices}
    this.equipment = new LWF.EquipmentChoices( callbackFunction );
    /// @type {LWF.DegreeChoices}
    this.degree = new LWF.DegreeChoices( callbackFunction );
    /// @type {LWF.TaxonChoices}
    this.taxon = new LWF.TaxonChoices( callbackFunction );
    /// @type {String}
    this.nameSearchString = '';

    return this;
};
LWF.ChoicesContainer.prototype.trigger = function ( source, id ) {

    for ( var c = 0, C = this.callbacks.length; c < C; c++ ) {
        this.callbacks[c]( source, id );
    }

};
LWF.ChoicesContainer.prototype.reset = function() {
    this.character.reset();
    this.nameSearchString = '';
};
LWF.ChoicesContainer.prototype.setNameSearchString = function ( search ) {
    var lowerSearch = search.toLowerCase();
    if ( lowerSearch != this.nameSearchString ) {
        this.nameSearchString = lowerSearch;
        this.trigger( 'nameSearchString', undefined );
    }
};
/**
 * @returns {Object.<String, Number>}
 */
LWF.ChoicesContainer.prototype.questionPriorities = function() {
    var prios = {},
        characterPriorities = this.character.priorities(),
        priority,
        qid;

    for (var cid in characterPriorities) {
        if (characterPriorities.hasOwnProperty(cid)) {
            priority = characterPriorities[cid];
            qid = LW.root.characterModel.character[cid].parentQuestionID;
            if (prios[qid] !== undefined) {
                if (priority > prios[qid]) {
                    prios[qid] = priority;
                }
            } else {
                prios[qid] = priority;
            }
        }
    }

    return prios;
};
/**
 * Add a callback function that is fired when the choices change.
 * The callback function receives a source parameter identifying the origin of the change
 * and the ID of the changed element.
 * @param {function(String, String)} callback
 */
LWF.ChoicesContainer.prototype.addCallback = function ( callback ) {
    if ( typeof callback != 'function' ) { throw Error( 'Callback must be a function.' ); }

    this.callbacks.push( callback );
};
/**
 * Remembers the characters that were observed
 * @constructor
 */
LWF.CharacterChoices = function ( callback ) {
    if ( !(this instanceof LWF.CharacterChoices) ) { return new LWF.CharacterChoices( callback ); }

    /// @type {Object.<String, {selected: Boolean, counter: Number}>}
    this.data = {};
    this.callback = callback;

    this.counter = 0;

    return this;
};
LWF.CharacterChoices.prototype.each = function ( handle ) {
    for ( var cid in this.data ) {
        if ( this.data.hasOwnProperty( cid ) ) {
            if ( this.isSet( cid ) ) {
                handle( cid );
            }
        }
    }
};
LWF.CharacterChoices.prototype.toggle = function ( id ) {
    this.set( id, !this.isSet( id ), false );
};
LWF.CharacterChoices.prototype.isSet = function ( id ) {
    return this.data[id] ? this.data[id].selected : false;
};
/**
 *
 * @param {Number} id Character ID
 * @param {bool} active Selected or not
 * @param {bool} disableCallback Disables callback functions, including the recalculation function
 */
LWF.CharacterChoices.prototype.set = function ( id, active, disableCallback ) {

    // Create the entry if it does not exist yet
    if ( !this.data[id] ) {
        this.data[id] = { selected: false, counter: Infinity };
    }

    this.data[id].selected = active;
    this.data[id].counter = this.counter++;

    if ( disableCallback !== true ) {
        this.trigger( id )
    }
};
LWF.CharacterChoices.prototype.reset = function () {
    this.data = {};
    this.trigger( null )
};
LWF.CharacterChoices.prototype.trigger = function ( id ) {
    this.callback( 'character', id );
};
LWF.CharacterChoices.prototype.count = function () {
    var n = 0;
    for ( var cid in this.data ) {
        if ( this.data.hasOwnProperty( cid ) ) {
            if ( this.isSet( cid ) ) {
                n++;
            }
        }
    }
    return n;
};
LWF.CharacterChoices.prototype.priorities = function () {
    var prios = {};
    for ( var cid in this.data ) {
        if ( this.data.hasOwnProperty( cid ) ) {
            var data = this.data[cid];
            if ( data.selected ) {
                prios[cid] = data.counter;
            }
        }
    }
    return prios;
};
/**
 * Observed numerical values
 * @constructor
 */
LWF.RangeChoices = function ( callback ) {
    if ( !(this instanceof LWF.RangeChoices) ) { return new LWF.RangeChoices( callback ); }

    /// @type {Object.<String, {{min,max}}>}
    this.data = {};
    this.callback = callback;

    return this;
};
LWF.RangeChoices.prototype.isSet = function ( id ) {
    return !!this.data[id];
};
LWF.RangeChoices.prototype.setValue = function ( id, min, max ) {
    this.data[id] = {
        min: min,
        max: max
    };
    this.callback( 'range', id );
};
/**
 * @returns {{min, max}}
 */
LWF.RangeChoices.prototype.value = function ( id ) {
    return this.data[id];
};
LWF.RangeChoices.prototype.unset = function ( id ) {
    delete this.data[id];
    this.callback( 'range', id );
};
/**
 * Remembers the visibility of components: Default, visible, or hidden.
 * @param callback
 * @constructor
 */
LWF.ComponentChoices = function ( callback ) {
    if ( !(this instanceof LWF.ComponentChoices) ) { return new LWF.ComponentChoices( callback ); }

    /// @type {Object.<String, Boolean|undefined>}
    this.data = {};
    this.callback = callback;

    return this;
};
/**
 * If the component state is true or false, it overrides the default. When clicked again,
 * the state is set to undefined (deleted) so the default value takes effect.
 */
LWF.ComponentChoices.prototype.toggle = function ( id, currentlyVisible ) {
    if ( this.data[id] === true || this.data[id] === false ) {
        delete this.data[id];
    } else {
        this.data[id] = !currentlyVisible;
    }
    this.callback( 'component', id );
};
LWF.ComponentChoices.prototype.isVisible = function ( id, defaultVisible ) {
    if ( this.data[id] === undefined ) { return defaultVisible; }
    else { return this.data[id]; }
};
/**
 * Choices for required equipment
 * @returns {LWF.EquipmentChoices}
 * @constructor
 */
LWF.EquipmentChoices = function ( callback ) {
    if ( !(this instanceof LWF.EquipmentChoices) ) { return new LWF.EquipmentChoices( callback ); }

    /// @type {Object.<String, Boolean|undefined>}
    this.data = {};
    this.callback = callback;

    return this;
};
LWF.EquipmentChoices.prototype.toggle = function ( id ) {
    this.data[id] = !this.data[id];
    this.callback( 'equipment', id );
};
LWF.EquipmentChoices.prototype.isSet = function ( id ) {
    return !!this.data[id];
};
/**
 * Taxonomic degrees that should be displayed
 * @returns {LWF.DegreeChoices}
 * @constructor
 */
LWF.DegreeChoices = function ( callback ) {
    if ( !(this instanceof LWF.DegreeChoices) ) { return new LWF.DegreeChoices( callback ); }

    this.data = {};
    this.callback = callback;

    this.enabledCount = 0;

    return this;
};
LWF.DegreeChoices.prototype.updateEnabledCount = function () {
    this.enabledCount = 0;
    for ( var did in this.data ) {
        if ( this.data.hasOwnProperty( did ) ) {
            if ( this.data[did] === true ) {
                this.enabledCount++;
            }
        }
    }
};
LWF.DegreeChoices.prototype.set = function ( id, enable ) {
    this.data[id] = enable;
    this.updateEnabledCount();
    this.callback( 'degree', id );
};
LWF.DegreeChoices.prototype.isVisible = function ( id ) {
    if ( this.enabledCount > 0 ) {
        return !!this.data[id];
    }
    return true;
};
/**
 * Taxonomic units whose children should be displayed
 * @returns {LWF.TaxonChoices}
 * @constructor
 */
LWF.TaxonChoices = function ( callback ) {
    if ( !(this instanceof LWF.TaxonChoices) ) { return new LWF.TaxonChoices( callback ); }

    this.data = {};
    this.callback = callback;

    // Number of selected taxa
    this.selectedTaxa = 0;

    return this;
};
LWF.TaxonChoices.prototype.setAll = function ( idArray ) {
    this.data = {};
    for ( var i = 0, I = idArray.length; i < I; i++ ) {
        this.data[idArray[i]] = true;
    }
    this.updateSelectedCount();
    this.callback( 'taxon', idArray );
};
LWF.TaxonChoices.prototype.updateSelectedCount = function() {
    var count = 0;
    for (var tid in this.data) {
        if (this.data.hasOwnProperty(tid)) {
            if (this.data[tid]) {
                count++;
            }
        }
    }
    this.selectedTaxa = count;
};
LWF.TaxonChoices.prototype.isSet = function ( id ) {
    return !!this.data[id];
};


LWF.DKeyQCVisitor = function ( ratingContainer, choicesContainer ) {
    LW.QCVisitor.apply( this );

    /// @type {LWF.RatingsContainer}
    this.ratingContainer = ratingContainer;
    /// @type {LWF.ChoicesContainer}
    this.choicesContainer = choicesContainer;

};

/**
 * Functions to filter out taxa based on simple criteria that do not require knowledge about matching characters,
 * like e.g. their degree if only Species should be shown, etc.
 *
 * The functions must set the LWF.TaxonRating to false for taxa that should be excluded.
 *
 * @type {Object.<{filter: Function}>}
 */
LWF.TaxonPrefilters = {

    /**
     * Filter out taxa that have a degree that is not selected by the user
     */
    selectedDegrees: {
        filter: function ( taxon ) {
            if (taxon.degree) {
                return this.choices.degree.isVisible(taxon.degree.id);
            }
            return true;
        }
    },

    selectedTaxa: {
        filter: function ( taxon ) {
            if ( this.choices.taxon.selectedTaxa > 0 ) {
                return this.choices.taxon.isSet( taxon.id );
            }
            return true;
        }
    },

    nameFilter: {
        filter: function ( taxon ) {
            return this.choices.nameSearchString.length <= 0 ||
                taxon.name.toLowerCase().indexOf( this.choices.nameSearchString.toLowerCase() ) >= 0;
        }
    }

};
/**
 * Rating functions to count ok/wrong/unknown matches of the provided (observed) characters.
 *
 * The functions have to fill the LWF.TaxonRating.characters fields.
 *
 * @type {Object.<{calculateRatings: Function(this:LWF.DKey, LWF.RatingsContainer}>}
 */
LWF.TaxonRatings = {

    /**
     * Default ok/wrong/unknown rating.
     * @type {{calculateRatings: Function(this:LWF.DKey, LWF.RatingsContainer}}
     * @this {LWF.DKey}
     */
    defaultRating: {
        /**
         * @this {LWF.DKey}
         * @param {LWF.RatingsContainer} ratingContainer
         */
        calculateRatings: function ( ratingContainer ) {

            // Check all characters that are marked as applicable
            this.choices.character.each( function ( cid ) {

                // Applicable character found
                var character = LW.root.characterModel.character[cid];
                var question = character.parentQuestion;

                var taxonRating;

                for ( var tid in ratingContainer.taxon.data ) {
                    if ( ratingContainer.taxon.data.hasOwnProperty( tid ) ) {

                        taxonRating = ratingContainer.taxon.data[tid];


                        if ( taxonRating.taxon.characters.indexOf( character ) >= 0 ) {

                            // ... and applies for this taxon!
                            taxonRating.characters.ok.push( character );

                        } else {

                            // Check if the answer to the question is known for this taxon
                            var otherChildSelected = false;
                            for ( var c = 0, C = question.characters.length; c < C; c++ ) {
                                var qCharacter = question.characters[c];
                                if ( taxonRating.taxon.characters.indexOf( qCharacter ) >= 0 ) {
                                    otherChildSelected = true;
                                    break;
                                }
                            }

                            // If the taxon has a different answer for this question, count the observed character as wrong.
                            // Otherwise, it is unknown.
                            if ( otherChildSelected ) {
                                taxonRating.characters.wrong.push( character );
                            } else {
                                taxonRating.characters.unknown.push( character );
                            }
                        }
                    }
                }
            } );
        }

    }

};
/**
 * Functions for sorting taxa.
 *
 * The functions may use the LWF.TaxonRating.cost field to store data.
 *
 * @type {Object.<{compare: Function}>}
 */
LWF.TaxonCosts = {

    /**
     * Sort the taxa by the number of errors.
     */
    leastErrors: {
        /**
         * @param {LWF.RatingsContainer} ratingContainer
         * @param {LW.Taxon} a
         * @param {LW.Taxon} b
         */
        compare: function ( ratingContainer, a, b ) {
            var ratingA = ratingContainer.taxon.data[a.id],
                ratingB = ratingContainer.taxon.data[b.id];

            if (!ratingB) {
                console.log(b);
            }
            if ( ratingA.characters.wrong.length != ratingB.characters.wrong.length ) {
                return ratingA.characters.wrong.length - ratingB.characters.wrong.length;
            }
            if ( ratingA.characters.ok.length != ratingB.characters.ok.length ) {
                return ratingB.characters.ok.length - ratingA.characters.ok.length;
            }
            return ratingA.taxon.name.localeCompare( ratingB.taxon.name );
        }
    }

};
/**
 * Functions for filtering taxa based on their rating, like the number of mismatching characters.
 *
 * The functions must return true if the taxon should not be excluded.
 *
 * @type {Object.<{filter: Function(LWF.RatingsContainer, LW.Taxon}>}
 */
LWF.TaxonFilters = {

    /**
     * No character must be wrong.
     */
    strictMatch: {
        /**
         * @param {LWF.RatingsContainer} ratingContainer
         * @param {LW.Taxon} taxon
         * @returns {boolean}
         */
        filter: function ( ratingContainer, taxon ) {
            if ( ratingContainer.taxon.data[taxon.id] ) {
                return ratingContainer.taxon.data[taxon.id].characters.wrong.length <= 0;
            }
            return true;
        }
    },
    /**
     * A percentage of characters is allowed to be wrong.
     */
    wrongPercent: {
        /**
         * @param {LWF.RatingsContainer} ratingContainer
         * @param {LW.Taxon} taxon
         * @returns {boolean}
         */
        filter: function ( ratingContainer, taxon ) {
            var max = 0.2 * this.choices.character.count();
            if ( ratingContainer.taxon.data[taxon.id] ) {
                return ratingContainer.taxon.data[taxon.id].characters.wrong.length <= max;
            }
            return true;
        }
    },
    /**
     * A constant number of characters may be wrong.
     */
    wrongNumber: {
        filter: function ( ratingContainer, taxon ) {
            if ( ratingContainer.taxon.data[taxon.id] ) {
                return ratingContainer.taxon.data[taxon.id].characters.wrong.length <= 2;
            }
            return true;
        }
    }

};
/**
 * Functions to filter out questions based on simple criteria that do not require knowledge about character ratings,
 * like e.g. missing equipment, too high difficulty, etc.
 *
 * The functions must set the LWF.QuestionRating to false for questions that should be excluded.
 *
 * @type {Object.<{filter: Function}>}
 */
LWF.QuestionPrefilters = {

    /**
     * Filters out the questions that require equipment not available to the user
     */
    selectedEquipment: {
        filter: function ( question ) {
            for ( var e = 0, E = question.equipment.length; e < E; e++ ) {
                if ( !this.choices.equipment.isSet(question.equipment[e].id) ) {
                    return false;
                }
            }
            return true;
        }
    }

};
/**
 * Functions to count how many times characters are used in the remaining set of taxa. Characters that are
 * not used at all provide no additional value when answered.
 *
 * The functions have to fill the LWF.QuestionRating.counter and LWF.CharacterRating.counter fields.
 *
 * @type {Object.<{calculateRatings: Function(this:LWF.DKey, LWF.RatingsContainer}>}
 */
LWF.QuestionRatings = {

    defaultRating: {
        /**
         * @param {LWF.RatingsContainer} ratingContainer
         */
        calculateRatings: function ( ratingContainer ) {

            var taxonRating,
                character,
                c, C;
            for ( var tid in ratingContainer.taxon.data ) {
                if ( ratingContainer.taxon.data.hasOwnProperty( tid ) ) {
                    taxonRating = ratingContainer.taxon.data[tid];

                    // Do not count taxa that should not be shown.
                    if ( !taxonRating || !this.taxonFilter.filter.call( this, ratingContainer, taxonRating.taxon ) ) {
                        continue;
                    }

                    // Contains all characters that apply for this taxon.
                    var okSet = {};

                    // Contains all characters that do not apply, i.e. another character of the parent question applies.
                    var wrongSet = {};

                    for (c = 0, C = taxonRating.taxon.characters.length; c < C; c++) {
                        character = taxonRating.taxon.characters[c];

                        // Remember that this character was used ...
                        okSet[character.id] = true;

                        // ... and that all its siblings are unused, i.e. wrong. (Some may still be used, fix those later)
                        for (var oc = 0, OC = character.parentQuestion.characters.length; oc < OC; oc++) {
                            var otherCharacter = character.parentQuestion.characters[oc];
                            if (otherCharacter.id != character.id) {
                                wrongSet[otherCharacter.id] = true;
                            }
                        }
                    }

                    // Fix: {wrong} = {wrong} - {ok}
                    var cid;
                    for (cid in okSet) {
                        if (okSet.hasOwnProperty(cid)) {
                            if (wrongSet[cid]) {
                                delete wrongSet[cid];
                            }
                            ratingContainer.character[cid].counter.ok++;
                        }
                    }

                    for (cid in wrongSet) {
                        if (wrongSet.hasOwnProperty(cid)) {
                            ratingContainer.character[cid].counter.wrong++;
                        }
                    }
                }
            }

            var questionRating,
                characterRating;
            for ( var qid in ratingContainer.question ) {
                if ( ratingContainer.question.hasOwnProperty( qid ) ) {
                    questionRating = ratingContainer.question[qid];

                    if ( !questionRating ) { continue; }

                    var usedCharacters = 0,
                        selectedCharacters = 0,
                        sumOk = 0;
                    for ( c = 0, C = questionRating.question.characters.length; c < C; c++ ) {
                        character = questionRating.question.characters[c];
                        // Number of characters used by the remaining taxon set
                        characterRating = ratingContainer.character[character.id];

                        if ( characterRating && characterRating.counter.ok > 0 ) {
                            usedCharacters++;
                        }

                        // Number of characters selected by the user
                        if ( this.choices.character.isSet( character.id ) ) {
                            selectedCharacters++;
                        }

                        // Sum of character occurrences in the remaining taxon set
                        sumOk += ratingContainer.character[character.id].counter.ok;
                    }

                    questionRating.counter.usedCharacters = usedCharacters;
                    questionRating.counter.selectedCharacters = selectedCharacters;
                    questionRating.counter.sumOk = sumOk;
                }
            }

        }
    }

};
/**
 * Functions to calculate the cost of, or the work done by, a question. Additionally,
 * a comparing function is provided for sorting the questions according to the cost calculated.
 *
 * All work can be done in the compare function, for efficiency the precalculate function can be used to store and
 * re-use results.
 *
 * The functions may use LWF.QuestionRating.cost and LWF.CharacterRating.cost to store information.
 *
 * @type {Object.<{name: String, precalculate: Function(LWF.RatingsContainer), compare: Function(LWF.RatingsContainer, LW.Question, LW.Question}>}
 */
LWF.QuestionCosts = {

    /**
     * Uses shannon entropy for measuring the work done by answering a question.
     *
     * Based on the paper “Algorithms for identification key generation and optimization with application to
     * yeast identification” by Reynolds et al.
     */
    informationGain: {
        name: 'Information Gain',
        /**
         * @param {LWF.RatingsContainer} ratingContainer
         */
        precalculate: function ( ratingContainer ) {

            // Number of displayed taxa
            var T = ratingContainer.taxon.filteredTaxa.length;

            var p = 1 / T;

            // Shannon Entropy: − sum( pk * log2(pk), k:1..T )
            // With p = 1/T, this is exactly log2(pk).
            var initialEntropy = -Math.log( p ) / Math.log( 2 );

            // To calculate the work done by answering a question, calculate the entropy of the remaining taxon set
            // Sij after answering question i with character j, for all characters of the question.
            var questionRating;
            for (var qid in ratingContainer.question) {
                if (ratingContainer.question.hasOwnProperty(qid)) {
                    questionRating = ratingContainer.question[qid];

                    if (!questionRating) { continue; }

                    var entropy = initialEntropy,
                        characterRating;

                    // If no characters of this questions have been used, the entropy does not change from
                    // its initial value. Otherwise, calculate the new entropy.
                    if (questionRating && questionRating.counter.sumOk > 0) {

                        entropy = 0;

                        for (var c = 0, C = questionRating.question.characters.length; c < C; c++) {
                            characterRating = ratingContainer.character[questionRating.question.characters[c].id];

                            // Probability of observing this character
                            characterRating.cost.Pij = characterRating.counter.ok / questionRating.counter.sumOk;

                            // Remaining number of taxa when choosing this character
                            characterRating.cost.Sij = T - characterRating.counter.wrong;
                            characterRating.cost.T = T;

                            // Shannon entropy for the remaining taxa
                            characterRating.cost.H = -Math.log(1/characterRating.cost.Sij) / Math.log(2);
                            if (characterRating.cost.Sij == 0) {
                                // Handle the special case that no taxa remain after choosing this character.
                                // Important! (Reduced number of steps in the benchmark from 400 to 300.)
                                characterRating.cost.H = 0;
                            }

                            // Add the weighted entropy to the question's entropy
                            entropy += characterRating.cost.Pij * characterRating.cost.H;
                        }
                    }

                    // Expected entropy of the taxon set after answering this question
                    questionRating.cost.expectedH = entropy;

                    // Work done by this question is defined by the entropy reduction achieved by answering it
                    questionRating.cost.workDone = initialEntropy - entropy;

                }
            }
        },
        /**
         * @param {LWF.RatingsContainer} ratingContainer
         * @param {LW.Question} questionA
         * @param {LW.Question} questionB
         */
        compare: function ( ratingContainer, questionA, questionB ) {
            return ratingContainer.question[questionB.id].cost.workDone - ratingContainer.question[questionA.id].cost.workDone;
        }
    },

    /**
     * Simple rating mechanism preferring questions whose characters are used most by the current set of taxa.
     */
    mostUsed: {
        name: 'Most Used',
        /**
         * @param {LWF.RatingsContainer} ratingContainer
         */
        precalculate: function ( ratingContainer ) {
            var questionRating;
            for ( var qid in ratingContainer.question ) {
                if ( ratingContainer.question.hasOwnProperty( qid ) ) {
                    questionRating = ratingContainer.question[qid];

                    var sumUsed = 0;
                    for ( var c = 0, C = questionRating.question.characters.length; c < C; c++ ) {
                        sumUsed += ratingContainer.character[questionRating.question.characters[c].id].counter.ok;
                    }

                    questionRating.cost.sumUsed = sumUsed;

                }
            }
        },
        /**
         * @param {LWF.RatingsContainer} ratingContainer
         * @param {LW.Question} questionA
         * @param {LW.Question} questionB
         */
        compare: function ( ratingContainer, questionA, questionB ) {
            var usedA = ratingContainer.question[questionA.id].cost.sumUsed,
                usedB = ratingContainer.question[questionB.id].cost.sumUsed;
            if ( usedA == usedB ) {
                return questionA.name.localeCompare( questionB.name );
            } else {
                return usedB - usedA;
            }
        }
    },

    /**
     * Orders the question alphabetically, without any rating.
     */
    alphabetic: {
        name: 'Alphabetic',
        precalculate: function ( ratingContainer ) {},
        compare: function ( ratingContainer, questionA, questionB ) {
            return questionA.name.localeCompare( questionB.name );
        }
    }
};
/**
 * Functions to decide whether a question should be shown, e.g. depending on its usefulness for further identification.
 *
 * @type {Object.<{filter: Function}>}
 */
LWF.QuestionFilters = {

    /**
     * Shows all questions that can still change the result, i.e. exclude some taxa.
     */
    allDistinctive: {
        /**
         * @param {LW.Question} question
         * @param {LWF.RatingsContainer} ratingContainer
         * @returns {boolean}
         */
        filter: function ( ratingContainer, question ) {
            return ratingContainer.question[question.id].counter.usedCharacters > 1 ||
                ratingContainer.question[question.id].counter.selectedCharacters > 0;
        }
    },
    distinctiveWithoutChildren: {
        /**
         * @param {LW.Question} question
         * @param {LWF.RatingsContainer} ratingContainer
         * @returns {boolean}
         */
        filter: function ( ratingContainer, question, data ) {

            var isUsed = ratingContainer.question[question.id].counter.usedCharacters > 1;
            var isSelected = ratingContainer.question[question.id].counter.selectedCharacters > 0;

            var isVisible;

            var myParentSelected = false,
                anyParentVisible = false;

            if ( isSelected ) {
                // Selected: Always show to allow deselecting
                isVisible = true;

            } else if ( !(question.parentCharacter && question.parentCharacter.parentQuestion) ) {
                // Is root node, no parent question
                isVisible = isUsed;

            } else {

                var parent = question.parentCharacter.parentQuestion;
                myParentSelected = ratingContainer.question[parent.id].counter.selectedCharacters > 0;

                var myData = {};
                LWF.QuestionFilters.distinctiveWithoutChildren.filter.call( this, ratingContainer, parent, myData );
                anyParentVisible = myData.visibleParent;

                if ( !isUsed ) {
                    // Question not used, no point in showing it
                    isVisible = false;
//                    console.log( 'Unused: ' + question.name );

                } else {

                    // Question is used by taxa but not selected by user
                    if ( myParentSelected ) {
                        // Parent is selected: Expand its children
                        isVisible = true;
//                        console.log( 'Parent of ' + question.name + ' is selected.' );

                    } else {
                        // Parent not selected:
                        // If a grandparent is selected, wait for the parent to be selected until displaying this question.
                        // Otherwise, display the question; Parents are disabled because they are unused by taxa.
                        isVisible = !anyParentVisible;
//                        if ( isVisible ) {
//                            console.log( 'Displaying because parents are hidden:' + question.name, 'Parent: ' + question.parentCharacter.parentQuestion.name );
//                        } else {
//                            console.log( 'Hiding; parent is visible: ' + question.name, 'Parent: ' + question.parentCharacter.parentQuestion.name );
//                        }
                    }
                }
            }

            if (data) {
                data.visibleParent |= isVisible || anyParentVisible;
            }

            return isVisible;

        }
    },
    /**
     * Questions with a parent character are not shown if the character is not selected.
     * Only suitable for classification, not for identification.
     *
     * Note for combining this with allDistinctive: Example where it would fail for identification:
     *
     * Question B depends on A.1, all remaining taxa show A.1 and none shows A.2;
     * A is therefore not shown and B neither since A.1 cannot be selected -- even if B might separate the taxa.
     */
    classification: {
        filter: function ( ratingContainer, question ) {

            // Show if the question has no parent character
            if ( !question.parentCharacter ) {
                return true;
            }

            var questionRating = ratingContainer.question[question.id];

            // Always show if one of this question's characters is selected
            if ( questionRating.counter.selectedCharacters > 0 ) {
                return true;
            }

            // Show if the parent character is selected
            if ( this.choices.character.isSet( question.parentCharacterID ) ) {
                return true;
            }

            return false;

        }
    }

};


/**
 * Constructs a Diagnostic Key for identifying taxa.
 * @param {{
 *      taxonPrefilters: Array.<String>,
 *      taxonRating: String,
 *      taxonCost: String,
 *      taxonFilter: String,
 *      questionPrefilter: String,
 *      questionRating: String,
 *      questionCost: String,
 *      questionFilter: String
 *      }} options Default options: LWF.DKey.defaultOptions
 * @returns {LWF.DKey}
 * @constructor
 */
LWF.DKey = function ( options ) {
    if ( !( this instanceof LWF.DKey ) ) { return new LWF.DKey( options ); }

    // Merge the options into the default options
    this.options = LWF.DKey.defaultOptions;
    for (var oid in options) {
        this.options[oid] = options[oid];
    }

    // Read the options telling which functions to use (strategy pattern)
    /// @type {Array.<{filter: Function}>}
    this.taxonPrefilters = [];
    this.taxonRating = LWF.TaxonRatings[this.options.taxonRating];
    this.taxonCost = LWF.TaxonCosts[this.options.taxonCost];
    this.taxonFilter = LWF.TaxonFilters[this.options.taxonFilter];

    /// @type {{filter: Function}}
    this.questionPrefilter = LWF.QuestionPrefilters[this.options.questionPrefilter];
    this.questionRating = LWF.QuestionRatings[this.options.questionRating];
    this.questionCost = LWF.QuestionCosts[this.options.questionCost];
    this.questionFilter = LWF.QuestionFilters[this.options.questionFilter] || LWF.QuestionFilters.allDistinctive;

    var prefilter;
    for ( var p = 0, P = this.options.taxonPrefilters.length; p < P; p++ ) {
        prefilter = LWF.TaxonPrefilters[this.options.taxonPrefilters[p]];
        if ( prefilter ) {
            this.taxonPrefilters.push( prefilter );
        }
    }


    /// @type {Array.<Function>}
    this.callbacks = [];
    /// @type {LWF.RatingsContainer|null}
    this.lastRatings = null;
    /// @type {LWF.ChoicesContainer}
    this.choices = new LWF.ChoicesContainer();


    this.choices.addCallback( function ( dkey ) {
        return function () {
            dkey.recalculate();
        }
    }( this ) );


    return this;
};
LWF.DKey.defaultOptions = {
    taxonPrefilters: [],
    taxonRating: 'defaultRating',
    taxonCost: 'leastErrors',
    taxonFilter: 'strictMatch',
    questionPrefilter: '',
    questionRating: 'defaultRating',
    questionCost: 'informationGain',
    questionFilter: 'allDistinctive'
};
/** The callback will be calculated when the model is updated. e.g. if a new character is selected. */
LWF.DKey.prototype.addCallback = function ( callback ) {
    if ( !(typeof callback == 'function') ) {
        throw Error( 'Callback must be a function.' );
    }
    console.log('Adding callback.');
    this.callbacks.push( callback );
};
LWF.DKey.prototype.loadQuestionFilter = function ( name ) {
    if ( LWF.QuestionFilters[name] ) {
        this.options.questionFilter = name;
        this.questionFilter = LWF.QuestionFilters[name];
    } else {
        throw Error( 'Cannot find question filter: ' + name );
    }
};
LWF.DKey.prototype.recalculated = function () {
    //console.log('Calling callbacks ...');
    for ( var c = 0, C = this.callbacks.length; c < C; c++ ) {
        //console.log('Callback ' + c);
        this.callbacks[c]( this );
    }
};
/**
 * Recalculates taxon and question ratings. The workflow is as follows:
 *
 * 1. Run prefilters to filter out taxa and questions that are currently not of interest, for example
 *    because of their taxonomic degree (taxon) or their difficulty (question).
 *
 * 2. Count the number of matches, mismatches, and unknowns of each remaining taxon based on the observed characters.
 *    This data is used in order to decide if a taxon can be accepted or not (i.e. it is excluded).
 *
 * 3. Count how many times characters are used by the remaining set of taxa (i.e. the set of accepted taxa).
 *    This knowledge is then used to decide whether a question should be shown or not.
 *
 * 4. Precalculate costs for questions and taxa to sort them afterwards. Costs can also be calculated directly
 *    in the respective sorting functions; however, to avoid redundant calculations, it can be done beforehand.
 *    Question costs can for example be the cost of answering a question in seconds, or the amount of work done
 *    by answering it, to help the user answer the best question first.
 *
 * 5. Done; call the callbacks which now can make use of the accept() and compare() functions using the previously
 *    calculated data.
 */
LWF.DKey.prototype.recalculate = function () {

    var ratings = new LWF.RatingsContainer(),
        rating;


    /* Taxon part */

    // Initialise taxon ratings
    var taxonPrefilters = this.taxonPrefilters,
        self = this;
    LW.root.taxonModel.each( function ( tid, taxon ) {
        for ( var p = 0, P = taxonPrefilters.length; p < P; p++ ) {
            if ( !taxonPrefilters[p].filter.call( self, taxon ) ) {
                return;
            }
        }
        ratings.taxon.data[taxon.id] = new LWF.TaxonRating( taxon );
        ratings.taxon.prefilteredTaxa.push( taxon.id );

    }, false );

    // Calculate the taxon ok/wrong/unknown ratings
    this.taxonRating.calculateRatings.call( this, ratings );

    // Count the number of filtered taxa
    for ( var tid in ratings.taxon.data ) {
        if ( ratings.taxon.data.hasOwnProperty( tid ) ) {
            rating = ratings.taxon.data[tid];
            if ( this.taxonFilter.filter( ratings, rating.taxon ) ) {
                ratings.taxon.filteredTaxa.push( tid );
            }
        }
    }


    /* Question/character part */

    // Initialise question and character ratings
    var questionPrefilter = this.questionPrefilter;
    LW.root.questionModel.each( function ( qid, question ) {
        if ( questionPrefilter && !questionPrefilter.filter.call( self, question ) ) {
            return;
        }
        ratings.question[question.id] = new LWF.QuestionRating( question );

        var character;
        for ( var c = 0, C = question.characters.length; c < C; c++ ) {
            character = question.characters[c];
            ratings.character[character.id] = new LWF.CharacterRating( character );
        }
    }, false );


    // Count the ok/wrong/unknown ratings per question and character
    this.questionRating.calculateRatings.call( this, ratings );

    // Precalculate the question costs for sorting (if necessary; for efficiency)
    if ( this.questionCost ) {
        this.questionCost.precalculate.call( this, ratings );
    }

    this.lastRatings = ratings;
    this.recalculated();
};
/**
 * @returns {LWF.DKeyQCVisitor|LW.QCVisitor}
 */
LWF.DKey.prototype.buildQCVisitor = function () {

    if ( !this.lastRatings ) { this.recalculate(); }

    var visitor = new LWF.DKeyQCVisitor( this.lastRatings, this.choices );

    visitor.questionPriorities = this.choices.questionPriorities();

    visitor.questionCompare = (function ( dKey, ratingContainer ) {
        return function ( a, b ) {
            return dKey.questionCost.compare.call( dKey, ratingContainer, a, b );
        }
    })( this, this.lastRatings );

    visitor.acceptQuestion = (function ( dKey, ratingContainer ) {
        return function ( question ) {
            return dKey.questionFilter.filter.call( dKey, ratingContainer, question );
        }
    })( this, this.lastRatings );

    return visitor;
};
/**
 * The taxonVisitor function will be provided a {{ratingsContainer: LWF.RatingsContainer, choicesContainer: LWF.ChoicesContainer}}
 * object as data parameter.
 * @returns {LW.TaxonVisitor}
 */
LWF.DKey.prototype.buildTaxonVisitor = function () {

    if ( !this.lastRatings ) { this.recalculate(); }

    var visitor = new LW.TaxonVisitor( {
        /// @type {LWF.RatingsContainer}
        ratingContainer: this.lastRatings,
        /// @type {LWF.ChoicesContainer}
        choicesContainer: this.choices
    } );

    visitor.acceptTaxon = (function ( dKey, ratingContainer ) {
        return function ( taxon ) {
            return dKey.taxonFilter.filter.call( dKey, ratingContainer, taxon );
        }
    })( this, this.lastRatings );

    if ( this.taxonCost ) {
        visitor.taxonCompare = (function ( dKey, ratingContainer ) {
            return function ( a, b ) {
                return dKey.taxonCost.compare.call( dKey, ratingContainer, a, b );
            }
        })( this, this.lastRatings );
    }

    var taxonPrefilters = this.taxonPrefilters,
        self = this;
    if ( this.taxonPrefilters.length > 0 ) {
        visitor.filterTaxon = function ( taxon ) {
            for ( var p = 0, P = taxonPrefilters.length; p < P; p++ ) {
                if ( !taxonPrefilters[p].filter.call( self, taxon ) ) {
                    return false;
                }
            }
            return true;
        };
    }

    return visitor;
};
