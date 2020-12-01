/**
 * This file contains the filter (key) user interface.
 * This is only a sample implementation! To write your own user interface, this one may be used as template.
 * See the LWF library for the filter logic which the UI makes use of.
 *
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 * @requires {LWF}
 */

/**
 * Filter namespace.
 */
var LWF = LWF || {};
LWF.UI = LWF.UI || {};
var i18n = i18n || { t: function() { return false; } };

/**
 *
 * @param $container
 * @param {{dKey}} options
 * @returns {LWF.UI}
 * @constructor
 */
LWF.UI = function ( $container, options ) {
    if ( !(this instanceof LWF.UI) ) { return new LWF.UI( $container, options ); }

    LWF.initTranslations();

    this.options = options;
    this.ui = {
        progressBar: new LWUI.ProgressBar( {} ),
        taxonTree: null
    };
    this.dom = {};
    this.dom.container = $container;
    this.dom.filter = $( '<div id="lwfFilter"></div>' );
    this.dom.taxon = $( '<div id="lwfTaxon"></div>' );
    this.dom.options = $( '<div id="lwfOptions"></div>' );
    this.dom.taxonDetails = $( '<div id="lwfTaxonDetails"></div>' );
    this.dom.taxonTree = $( '<div id="lwfTaxonTree"></div>' );
    this.dom.timing = $( '<div class="timing" style="position: fixed;"></div>' );

    this.updateCallbacks = [];

    var ui = this;

    /// @type {LWF.DKey}
    this.dKey = new LWF.DKey( options && options.dKey );
    this.dKey.addCallback( function () { ui.updateUI(); } );


    this.dom.container.append( this.ui.progressBar.dom );

    LW.root.init( this.ui.progressBar ).done( function () {
        ui.initUI( $container );
    } );


    return this;
};
LWF.UI.prototype.initUI = function ( $container ) {

    var ui = this;
    var speciesDegree = LW.root.degreeModel.get( 'species' );
    if ( speciesDegree ) {
        this.dKey.choices.degree.set( speciesDegree.id, true );
        console.log( '########### Only displaying species.' );
    }


    var topicID = $container.attr( 'topicID' ),
        topic;
    if ( LW.root.topicModel.topic[topicID] === undefined ) {
        console.log( 'Topic is not set.' );

        var topicOptions = [ ];
        for ( var tid in LW.root.topicModel.topic ) {
            if ( LW.root.topicModel.topic.hasOwnProperty( tid ) ) {
                topic = LW.root.topicModel.topic[tid];
                topicOptions.push( {
                    id: tid,
                    text: topic.name
                } );
            }
        }

        this.ui.topic = new LWUI.Select( {
            text: (i18n.t( 'ui.select-topic' ) || 'Select topic'),
            saveCallback: function ( id ) {
                ui.changeTopic( id );
            },
            data: topicOptions
        } );

        this.dom.container.append( this.ui.topic.dom );

    } else {
        console.log( 'Topic is ' + topicID );
        this.changeTopic( topicID );
    }

    this.dom.container.append( this.dom.taxonTree );
    this.dom.container.append( this.dom.filter );
    this.dom.container.append( this.dom.taxon );
    this.dom.container.append( this.dom.options );
    this.dom.container.append( this.dom.taxonDetails );
    this.dom.container.append( this.dom.timing );


    this.loadTaxonTree();
};
LWF.UI.prototype.bindUpdateUI = function(func) {
    if (typeof func !== 'function') {
        throw Error('Cannot bind, is not a function!');
    }
    this.updateCallbacks.push(func);
};
LWF.UI.prototype.triggerUpdateUI = function() {
    for (var c = 0, C = this.updateCallbacks.length; c < C; c++) {
        this.updateCallbacks[c]();
    }
};
LWF.UI.prototype.changeTopic = function(topicID) {
    console.log('Setting topic to:', topicID, LW.root.topicModel.topic[topicID]);

    LW.root.setTopic(LW.root.topicModel.topic[topicID]);

    this.loadTaxonTree();
    this.dKey.recalculate();
};
LWF.UI.prototype.equipmentButton = function(eid) {
    var equipment = LW.root.equipmentModel.equipment[eid];

    return new LWUI.ToolButton({
        'url': equipment.description.images.length > 0 ? equipment.description.images[0] : '',
        'text': equipment.name,
        'checked': this.dKey.choices.equipment.isSet(eid),
        'callback': function (id, equipmentChoices) {
            return function () {
                equipmentChoices.toggle(id);
            }
        }(eid, this.dKey.choices.equipment)
    });
};
/**
 * @this {LWF.UI}
 */
LWF.UI.prototype.updateUI = function() {

    console.log('Updating UI ...');
    var start, end;

    // Display the questions and characters //

    start = new Date().getTime();
    this.dom.filter.html('');


    var ui = this;

    var dom = this.dom.filter;

    // The question visitor is responsible for displaying the questions in the right order, or for hiding them.
    // @type {LWF.DKeyQCVisitor|LW.QCVisitor}
    var visitor = this.dKey.buildQCVisitor();
    visitor.options.dfs = true;
    if ( this.options.disableComponents ) { visitor.options.useComponents = false; }
    visitor.options.data.dom = dom;
    visitor.preorderComponentVisitor = function(component, storage) {
        storage.visibleQuestions = 0;
        storage.charactersTodo = 0;
        storage.charactersDone = 0;

        storage.componentContainer = new LWF.UIComponentContainer(component.id, this.choicesContainer.component);
        visitor.options.data.dom = storage.componentContainer.questionDOM;
        dom.append(storage.componentContainer.dom);
    };
    visitor.postorderComponentVisitor = function(component, storage) {
        storage.componentContainer.setVisibleDefault(storage.visibleQuestions > 0);
        storage.componentContainer.setStatus('[' + storage.charactersDone + '/' + (storage.charactersTodo+storage.charactersDone) + ' identified]');
    };
    /** @param {LW.Question} question
     *  @param questionStorage
     *  @param componentStorage */
    visitor.preorderQuestionVisitor = function(question, questionStorage, componentStorage) {
        var hidden = !this.acceptQuestion(question);

        // Create the question DOM
        questionStorage.questionDOM = $(LWF.UIFilterTemplates.question({
            'name' : question.name,
            'description' : question.description.text,
            'hidden' : hidden,
            'debug' : JSON.stringify(this.ratingContainer.question[question.id].cost)
        }));
        visitor.options.data.dom.append(questionStorage.questionDOM);

        // List required equipment
        var equipmentDOM = $('.equipment', questionStorage.questionDOM);
        for (var e = 0, E = question.equipment.length; e < E; e++) {
            equipmentDOM.append(ui.equipmentButton(question.equipment[e].id).dom);
        }
        questionStorage.characterDOM = $('.characters', questionStorage.questionDOM);

        questionStorage.selectedChildren = 0;

        // Count the number of visible questions for this component
        if (!hidden) {
            componentStorage.visibleQuestions++;
            if (this.ratingContainer.question[question.id].counter.usedCharacters > 0) {
                componentStorage.charactersTodo++;
            } else {
                componentStorage.charactersDone++;
            }
        }
    };
    visitor.preorderCharacterVisitor = function(character, questionStorage) {

        var selected = this.choicesContainer.character.isSet(character.id);

        var images = '';
        if (character.description.images) {
            for (var k = 0, K = character.description.images.length; k < K; k++) {
                images += LWF.UIFilterTemplates.image({ 'url' : character.description.images[k]});
            }
        }

        var visible = this.ratingContainer.character[character.id].counter.ok > 0;
        var characterDOM = $(LWF.UIFilterTemplates.character({
            'selected' : selected,
            'name' : character.name,
            'info' : this.ratingContainer.character[character.id].counter.wrong,
            'images' : images,
            'description' : character.description.text,
            'hidden' : !visible,
            'debug' : JSON.stringify(this.ratingContainer.character[character.id].cost)
        }));
        if ( visible || selected ) {
            characterDOM.click( function ( visitor ) {
                return function () {
                    visitor.choicesContainer.character.toggle( character.id );
                }
            }( this ) );
        }

        questionStorage.characterDOM.append(characterDOM);

    };
    LW.root.visit(visitor);

    end = new Date().getTime();
    this.dom.timing.html('<div>Q: ' + (end-start) + ' ms</div>');


    dom.append(this.buildRanges());



    // Display the taxa //
    start = new Date().getTime();
    this.dom.taxon.html('<h3 id="matchingTaxa">' + (i18n.t('ui.matching-taxa') || 'Matching taxa') + '</h3>');

    var domMatching = $('<div class="status"></div>');
    this.dom.taxon.append(domMatching);


    // Taxa that could still match, according to the selected taxon accepting method
    var stillValid = 0;
    // Total number of displayed taxa
    var total = 0;

    var taxonDOM = this.dom.taxon;
    var taxonVisitor = this.dKey.buildTaxonVisitor();
    taxonVisitor.taxonVisitor = function ( taxon, accepted, data ) {
        if ( accepted ) { stillValid++; }
        total++;

        var elem = new LWF.UITaxonContainer( taxon, data.ratingContainer, accepted, taxonDOM );
        taxonDOM.append( elem.dom );
    };
    LW.root.taxonModel.visit( taxonVisitor );

    domMatching.text( i18n.t('ui.still-matching', { valid: stillValid, total: total } ) || (stillValid + ' of ' + total + ' taxa could still match.'));
    end = new Date().getTime();
    this.dom.timing.append('<div>T: ' + (end-start) + ' ms</div>');


    this.updateOptions();

    this.triggerUpdateUI();
};
LWF.UI.prototype.buildRanges = function() {

    var dom = $('<div></div>'),
        ui = this,
        elem;

    var opts,
        value;
    LW.root.rangeModel.each( function( rid, range ) {
        range = LW.root.rangeModel.range[rid];
        value = ui.dKey.choices.range.value(rid);

        opts = range.getRange();
        if (opts.min === undefined) { return; }

        elem = new LWF.UIRangeInput( {
            text: range.name,
            unit: range.unit,
            min: opts.min,
            max: opts.max,
            minVal: value ? value.min : opts.min,
            id: rid,
            enabled: !!value,
            callback: function ( range, rangeChoices ) {
                return function ( args ) {

                    if ( args.enabled ) {
                        console.log( 'Value changed: ' + args.min );
                        rangeChoices.setValue( range.id, args.min, args.min );
                    } else {
                        console.log( '«Unknown» checked' );
                        rangeChoices.unset( range.id );
                    }
                }
            }( range, ui.dKey.choices.range )
        } );

        dom.append(elem.dom);
    }, false );
    return dom;
};
LWF.UI.prototype.loadTaxonTree = function () {

    var taxonChoices = this.dKey.choices.taxon;
    this.ui.taxonTree = new LWUI.TreeMatrix( {
        titleHeight: 40,
        rowHeight: 30,
        padding: 20,
        lineLength: 15,
        lineWidth: 2,
        data: LW.root.taxonModel.treeMatrix(),
        callback: function ( id, selectedIDs ) {
            console.log( 'Clicked taxon ' + id + ': ' + LW.root.taxonModel.taxon[id].name + ', selected:', selectedIDs );
            taxonChoices.setAll( selectedIDs );
        }
    } );

    this.dom.taxonTree.empty().append( this.ui.taxonTree.dom );


    var visible = true;
    var $visibility = $('<div class="bVisibility"></div>');
    var $dom = this.dom.taxonTree;

    var toggleVisibility = function() {
        visible = !visible;
            $visibility.text( visible ? '−' : '+' );
        if (visible) {
            $dom.removeClass('hidden');
        } else {
            $dom.addClass('hidden');
        }
    };

    $visibility.click( toggleVisibility );

    toggleVisibility();
    this.dom.taxonTree.append($visibility);

};
LWF.UI.prototype.updateOptions = function() {
    var dom,
        elem;

    this.dom.options.empty();

    dom = $('<ul></ul>');
    for (var eid in LW.root.equipmentModel.equipment) {
        if (LW.root.equipmentModel.equipment.hasOwnProperty(eid)) {
            elem = $('<li></li>');
            elem.text(LW.root.equipmentModel.equipment[eid].name);
            elem.append(this.equipmentButton(eid).dom);
            dom.append(elem);
        }
    }
    this.dom.options.append('<h4>' + (i18n.t('ui.equipment') || 'Equipment') + '</h4>');
    this.dom.options.append(dom);

    dom = $('<ul></ul>');
    elem = $('<li>Two columns</li>');
    elem.click( function(ui) { return function() {
        ui.twocol();
        $(this).detach();
    } }(this));
    dom.append(elem);
    elem = $('<li>2 images</li>');
    elem.click( function(ui) { return function() {
        ui.wn(2);
    }}(this) );
    dom.append(elem);
    elem = $('<li>3 images</li>');
    elem.click( function(ui) { return function() {
        ui.wn(3);
    }}(this) );
    dom.append(elem);
    elem = $('<li>Debug</li>');
    elem.click( function(ui) { return function() {
        ui.debugView();
        $(this).detach();
    }} (this));
    dom.append(elem);

    this.dom.options.append('<h4>Style</h4>');
    this.dom.options.append(dom);
};
LWF.UI.prototype.twocol = function() {
    this.dom.taxon.css('height', '100%');
    this.dom.filter.css('height', '100%');
    this.dom.taxon.css('overflow-y', 'scroll');
    this.dom.filter.css('overflow-y', 'scroll');
    this.dom.taxon.width($('#lwfTaxon').width()-$('#lwfOptions').width() - 20);
};
LWF.UI.prototype.wn = function(n) {
    var W = $('#lwfTaxon h3').width();
    var wt = $('.lwfTaxon').first().width();
    var wto = $('.lwfTaxon').first().outerWidth(true);
    var dw = wto-wt;
    var w = Math.floor((W-n*dw)/n);
    LWUI.addCSSRule('.lwfTaxon { width: ' + w + 'px; }');
};
LWF.UI.prototype.debugView = function() {
    LWUI.addCSSRule('.lwfQuestion:hover .info { display: block; }');
    LWUI.addCSSRule('.lwfQuestion.tile.hidden { display: block; }')
    LWUI.addCSSRule('.lwfTaxon.hidden { display: block; }')
};


/**
 * Container for a taxon, enriched with rating information.
 * @param {LW.Taxon} taxon
 * @param {LWF.RatingsContainer} rating
 * @param {boolean} visible
 * @param detailDOM
 */
LWF.UITaxonContainer = function(taxon, rating, visible, detailDOM) {

    var images = '';
    for (var i = 0, I = taxon.description.images.length; i < I; i++) {
        images += LWF.UITaxonContainer.tplImage({ 'url' : taxon.description.images[i]});
    }
    this.dom = $(LWF.UITaxonContainer.tpl({
        'taxon' : taxon,
        'images' : images,
        'hidden' : !visible
    }));

    this.dom.click( this.buildClickHandler (taxon, images, detailDOM));

    this.ratingDetailsDOM = $('.ratingDetails', this.dom);
    this.ratingDetailsDOM.append(new LWF.UIRatingDetails(rating.taxon.data[taxon.id].characters.ok, 'okCharacter').dom);
    this.ratingDetailsDOM.append(new LWF.UIRatingDetails(rating.taxon.data[taxon.id].characters.wrong, 'wrongCharacter').dom);
    this.ratingDetailsDOM.append(new LWF.UIRatingDetails(rating.taxon.data[taxon.id].characters.unknown, 'unknownCharacter').dom);
};
LWF.UITaxonContainer.prototype.buildClickHandler = function(taxon, images, dom) { return function() {

    var desc = '';
    for (var c = 0, C = taxon.characters.length; c < C; c++) {
        desc += '<li>' + taxon.characters[c].parentQuestion.name + ': ' + taxon.characters[c].name + '</li>';
    }
    desc = '<ul>' + desc + '</ul>';

    var elem = $(LWF.UITaxonContainer.tplDetails({
        taxon : taxon,
        images : images,
        description : desc
    }));
    elem.click( function() {
        dom.empty();
    });
    $('.lwfTaxonDetails', elem).click( function(event) {
        console.log('Not propagating event.');
        // Only close popup if clicked outside, not on the popup itself
        event.stopPropagation();
    });
    dom.html(elem);
}};
LWF.UITaxonContainer.tpl = Handlebars.compile(
    '<div class="lwfTaxon tile{{#if hidden}} hidden{{/if}}" itemID="{{id}}">' +
        '<div class="ratingDetails"></div>' +
        '<div class="latin"> <span class="name">{{#with taxon}}{{name}}{{/with}}</span> </div>' +
        '<div class="gallery">{{{images}}}</div>' +
        '<div class="description"> <span class="desc">{{#with taxon}}{{{description.text}}}{{/with}}</span> </div>' +
    '</div>');
LWF.UITaxonContainer.tplImage = Handlebars.compile(
    '<div class="lwfImage">' +
        '<img src="{{url}}"/>' +
        //'<a href="{{url}}"><img src="{{url}}"/></a>' +
    '</div>'
);
LWF.UITaxonContainer.tplDetails = Handlebars.compile(
    '<div class="lwfTaxonDetailsContainer"><div class="lwfTaxonDetails"><div class="content">' +
        '<h3>{{taxon.name}}</h3>' +
        '<div class="description">{{{description}}}</div>' +
        '<div class="gallery">{{{images}}}</div>' +
    '</div></div></div>'
);
/**
 * Displays details to ratings: The number and the names of the matching characters.
 * @param {[LW.Character]} characterList
 * @param cssClass
 */
LWF.UIRatingDetails = function(characterList, cssClass) {
    var chars = '';
    for (var c = 0, C = characterList.length; c < C; c++) {
        if (c > 0) {
            chars += ' – ';
        }
        chars += characterList[c].name;
    }
    this.dom = LWF.UIRatingDetails.tpl({
        cssClass : cssClass,
        number : characterList.length,
        text : chars
    });
};
LWF.UIRatingDetails.tpl = Handlebars.compile(
    '<div class="rating{{#if cssClass}} {{cssClass}}{{/if}}">' +
        '<div class="number">{{number}}</div>' +
        '<div class="items">{{text}}</div>' +
    '</div>'
);



/**
 * @param id
 * @param {LWF.ComponentChoices} componentChoices
 */
LWF.UIComponentContainer = function ( id, componentChoices ) {
    this.component = LW.root.componentModel.component[id];

    /// @type {LWF.ComponentChoices}
    this.componentChoices = componentChoices;
    this.visibleDefault = true;
    this.id = id;

    var description = '',
        path;
    if (this.component.description) {
        if (this.component.description.text) {
            description = '<div class="text">' + this.component.description.text + '</div>';
        }
        if (this.component.description.images) {
            description += '<div class="gallery">';
            for (var i = 0, I = this.component.description.images.length; i < I; i++) {
                path = this.component.description.images[i];
                if ( path.substr( path.length - 3 ).toLowerCase() == 'svg' ) {
                    // If it is an SVG image, use the <object> tag so CSS and scripts work.
                    description += '<object data="' + path + '" type="image/svg+xml"></object>';
                } else {
                    description += '<img src="' + path + '"/>';
                }
            }
            description += '</div>';
        }
    }

    this.dom = $( this.tpl( {
        name: this.component.name,
        description: description
    } ) );
    this.questionDOM = $( '.questions', this.dom );
    this.statusDOM = $( '.status', this.dom );
    this.titleDOM = $( '.title', this.dom );

    var cc = this;
    this.titleDOM.click( function toggle() {
        componentChoices.toggle( id, cc.visibleDefault );
    } );
};
LWF.UIComponentContainer.prototype.setStatus = function ( text ) {
    this.statusDOM.text( text );
};
LWF.UIComponentContainer.prototype.setVisibleDefault = function ( visible ) {
    this.visibleDefault = visible;
    this.updateUI();
};
LWF.UIComponentContainer.prototype.updateUI = function () {
    if ( this.componentChoices.isVisible( this.id, this.visibleDefault ) ) {
        this.dom.removeClass( 'hidden' );
    } else {
        this.dom.addClass( 'hidden' );
    }
};
LWF.UIComponentContainer.prototype.tpl = Handlebars.compile(
    '<div class="lwfComponent">' +
        '<div class="title">' +
            '<h3>{{name}}</h3>' +
            '<div class="status"></div>' +
        '</div>' +
        '{{#if description}}<div class="componentDescription">{{{description}}}</div>{{/if}}' +
        '<div class="questions"></div>' +
    '</div>'
);



LWF.UIRangeInput = function(options) {
    if (!(this instanceof LWF.UIRangeInput)) { return new LWF.UIRangeInput(options); }

    this.dom = $(this.tpl(options));
    this.domCValue = $('.valueContainer', this.dom);
    this.domCUnknown = $('.unknownContainer', this.dom);
    this.domMin = $('.minVal', this.dom);

    this.min = options.min;
    this.max = options.max;
    this.enabled = undefined;
    this.callback = options.callback || function(args) {};

    this.initialised = false;


    var input = this;
    this.domCValue.click( function() {
        input.enableRange(true);
    } );
    this.domCUnknown.click( function() {
        input.enableRange(false);
    } );
    this.domMin.change( function() {
        input.trigger();
    });

    this.enableRange(options.enabled);
    this.initialised = true;

    return this;
};
LWF.UIRangeInput.prototype.minVal = function() {
    return this.domMin.val();
};
LWF.UIRangeInput.prototype.enableRange = function(enabled) {

    if (enabled == this.enabled) { return; }

    this.enabled = !!enabled;
    if (enabled) {
        this.domCValue.addClass('selected');
        this.domCUnknown.removeClass('selected');
    } else {
        this.domCValue.removeClass('selected');
        this.domCUnknown.addClass('selected');
    }

    this.trigger();
};
LWF.UIRangeInput.prototype.trigger = function() {

    if (!this.initialised) { return; }

    if (document.activeElement == this.domMin[0]) {
        console.log('Input has focus, not triggering update yet');
        return;
    }

    this.callback({
        enabled : this.enabled,
        min : this.minVal()
    });
};
LWF.UIRangeInput.prototype.tpl = Handlebars.compile(
    '<div class="lwfRange tile">' +
        '<h4>{{text}}</h4>' +
        '<div class="content">' +
            '<div class="left"><div class="valueContainer selectable">' +
                '<input type="number" class="minVal" value="{{minVal}}"/> {{unit}} ({{min}}…{{max}}) <input type="button" value="✔" class="confirm"/>' +
            '</div></div>' +
            '<div class="right"><div class="unknownContainer selectable">' +
                '<span>Unknown</span>' +
            '</div></div>' +
        '</div>' +
    '</div>'
);



LWF.UIFilterTemplates = {};
LWF.UIFilterTemplates.question = Handlebars.compile(
    '<div class="lwfQuestion tile{{#if hidden}} hidden{{/if}}">' +
        '<h4{{#if debug}} title="{{debug}}"{{/if}}>{{name}}</h4>' +
        '<div class="info debug">{{sumOk}} match, {{sumWrong}} dont</div>' +
        '{{#if description}}<div class="questionDescription">{{description}}</div>{{/if}}' +
        '<div class="equipment"></div>' +
        '<div class="characters"></div>' +
    '</div>'
);
LWF.UIFilterTemplates.character = Handlebars.compile(
    '<div class="lwfCharacter{{#if selected}} selected{{/if}}{{#if hidden}} hidden{{/if}}"{{#if debug}} title="{{debug}}"{{/if}}>' +
        '<div class="characterName"> <tt>{{#if selected}}[×]{{else}}[ ]{{/if}}</tt>{{name}}</div>' +
        '<div class="gallery">' +
        '{{{images}}}' +
        '</div>' +
        '<div class="description">{{description}}</div>' +
        '<div class="info debug">{{info}}</div>' +
    '</div>');
LWF.UIFilterTemplates.image = Handlebars.compile(
    '<div><img src="{{url}}"/></div>'
);