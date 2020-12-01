/**
 * This file contains the editor for the database. It uses the functions defined in e.g. libLW.local.js
 * to modify the data.
 *
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 * @requires {LW}
 */

/**
 * Namespace for the data editor
 */
var LWE = LWE || {};
var i18n = i18n || { t: function() { return false; } };

LWE.status = {
    'ok' : 'ok',
    'pending' : 'pending',
    'error' : 'error'
};

LWE.camelCase = function(text) {
    var arr = text.replace('×', 'X').split(/\s+/);
    var s = arr[0].toLowerCase();
    for (var i = 1; i < arr.length; i++) {
        if (arr[i].length > 0) {
            s += arr[i][0].toUpperCase() + arr[i].substring(1);
        }
    }
    s = s + ".html";
    return s;
};

LWE.setStatus = function(element, status) {
    element.removeClass('lwePending').removeClass('lweError');
    if (status === LWE.status.pending) {
        element.addClass('lwePending');
    } else if (status == LWE.status.error) {
        element.addClass('lweError');
    }
};

LWE.UI = {
    dom : {
        status : $('<div id="status"></div>'),

        topic : $('<div id="topicEditor" class="lweEditor"></div>'),
        taxon : $('<div id="taxonEditor" class="lweEditor"></div>'),
        question : $('<div id="questionEditor" class="lweEditor"></div>'),
        component : $('<div id="component" class="lweEditor"></div>'),
        range : $('<div id="range" class="lweEditor"></div>')
    },

    ui : {
        progress : new LWUI.ProgressBar({})
    },

    /// The only component that may receive focus.
    activeComponent : null

};

LWE.UI.init = function(container) {
    this.dom.container = container;

    this.dom.container.
        append(this.dom.topic).
        append(this.dom.taxon).
        append(this.dom.question).
        append(this.dom.component).
        //append(this.dom.range).
        append(this.dom.status);

    this.dom.status.append(this.ui.progress.dom);

    LW.root.init( this.ui.progress ).done( function () {
        LWE.UI.continueInit();
    } );

};
LWE.UI.continueInit = function() {

    var topicId = localStorage.getItem( 'lweTopicId' );
    if ( LW.root.topicModel.topic.hasOwnProperty( topicId ) ) {
        LW.root.setTopic( LW.root.topicModel.topic[topicId] );
    } else {
        for ( var tid in LW.root.topicModel.topic ) {
            if ( LW.root.topicModel.topic.hasOwnProperty( tid ) ) {
                LW.root.setTopic( LW.root.topicModel.topic[tid] );
                break;
            }
        }
    }


    var step = 0;
    var steps = 5;

    this.ui.progress.text('Loading topic editor').value(100*(step++)/steps);
    this.ui.question = new LWE.TopicEditorView(this.dom.topic);

    this.ui.progress.text('Loading question editor').value(100*(step++)/steps);
    this.ui.question = new LWE.QuestionEditorView(this.dom.question);

    this.ui.progress.text('Loading component editor').value(100*(step++)/steps);
    this.ui.component = new LWE.ComponentEditorView(this.dom.component);

    this.ui.progress.text('Loading range editor').value(100*(step++)/steps);
    this.ui.range = new LWE.RangeEditorView(this.dom.range);

    this.ui.progress.text('Loading taxon editor').value(100*(step++)/steps);
    this.ui.taxon = new LWE.TaxonEditorView(this.dom.taxon);

    this.ui.progress.text('Editors loaded').value(100);

    this.addTaxonListScroller();
};
LWE.UI.addTaxonListScroller = function () {
    var self = this;
    var taxonListScroller = function () {

        // Padding area: No changes are applied here (avoids flickering and nervous UI)
        var padding = 150;

        var windowTop = $( window ).scrollTop();

        var $taxonList = self.ui.taxon.dom.listContainer;
        var nextElement = self.dom.taxon.next();

        var nextVisible = false;
        if ( nextElement ) {
            nextVisible = nextElement.offset().top - windowTop < 0;
        }

        // true if the end of the list is
        var taxonDistanceToScreenTop = $taxonList.offset().top + $taxonList.outerHeight( true ) - windowTop;
        var listInvisible = taxonDistanceToScreenTop < 0;
        var belowPaddingArea = taxonDistanceToScreenTop + padding < 0;
        var withinPaddingArea = !belowPaddingArea && listInvisible;

        //console.log('Padding area: ' + withinPaddingArea + ', list invisible: ' + listInvisible + ', next visible: ' + nextVisible);
        if ( withinPaddingArea ) {
        } else {
            if ( listInvisible && !nextVisible ) {
                self.ui.taxon.dom.data.addClass( 'side' );
                self.ui.taxon.allowFocus( false );
            } else {
                self.ui.taxon.dom.data.removeClass( 'side' );
                self.ui.taxon.allowFocus( true );
            }
        }

    };

    $( window ).scroll( taxonListScroller );
    $( window ).resize( taxonListScroller );
};


LWE.QuestionEditorView = function(container) {
    if (!(this instanceof LWE.QuestionEditorView)) { return new LWE.QuestionEditorView(container); }

    this.dom = {};
    this.dom.container = $(
            '<div class="lweQuestionEditor">' +
                '<h3>' + (i18n.t('title.questionEditor') || 'Question Editor') + '</h3>' +
                '<div class="questionList tile"></div>' +
                '<div class="questionEditor tile"></div>' +
                //'<div class="equipmentList tile"></div>' +
                '<div class="characterList tile"></div>' +
                '<div class="characterEditor tile"></div>' +
                '<div class="timing"></div>' +
            '</div>'
        );
    container.html(this.dom.container);

    this.dom.questionList = $('.questionList', this.dom.container);
    this.dom.questionEditor = $('.questionEditor', this.dom.container);
    this.dom.equipmentList = $('.equipmentList', this.dom.container);
    this.dom.characterList = $('.characterList', this.dom.container);
    this.dom.characterEditor = $('.characterEditor', this.dom.container);
    this.dom.timing = $('.timing', this.dom.container);
    this.selectedQuestion = undefined;
    /// @type {Object.<String>}
    /// Remembers the selected character for questions
    this.selectedCharacter = {};

    this.updateUI();

    var editor = this;
    LW.root.addEventListener( LW.QuestionModel.prototype.id, function() { editor.updateUI(); } );
    LW.root.addEventListener( LW.CharacterModel.prototype.id, function() { editor.updateUI(); } );
    LW.root.addEventListener( LW.TopicModel.prototype.idCurrentTopic, function() { editor.updateUI(); } );

    console.log('Question editor initialised');

    return this;
};
LWE.QuestionEditorView.focusableComponents = {
    question : 'question',
    character : 'character'
};
LWE.QuestionEditorView.prototype.updateUI = function() {

    var start,
        end;

    start = new Date().getTime();

    this.dom.questionList.html( // \todo global container
        '<h3>' + (i18n.t('title.questions') || 'Questions') + '</h3>' +
        '<div class="data list"></div>' +
        '<div class="buttons"></div>'
    );
    var dataDOM = $('.data', this.dom.questionList);
    var buttonDOM = $('.buttons', this.dom.questionList);
    var editor = this;
    var item;

    var questionList = LW.root.questionModel.sortedByName();
    var question;
    for (var q = 0, Q = questionList.length; q < Q; q++) {
        question = questionList[q];
        item = $(LWE.tplListEntry({'text' : question.name}));
        item.click( function(id) { return function() {
            LWE.UI.activeComponent = LWE.QuestionEditorView.focusableComponents.question;
            editor.editQuestion(id);
        }}(question.id));
        dataDOM.append(item);
    }

    var addButton = LWUI.Button({
        'text' : (i18n.t('button.+add') || '+ Add'),
        'callback' : function() {
            LWE.UI.activeComponent = LWE.QuestionEditorView.focusableComponents.question;
            editor.editQuestion(undefined);
        }
    });
    buttonDOM.append(addButton.dom);

    this.dom.questionEditor.html('');
    this.dom.characterList.html('');
    this.dom.characterEditor.html('');

    if (this.selectedQuestion) {
        this.editQuestion(this.selectedQuestion);
    }


    end = new Date().getTime();
    this.dom.timing.html('<div>Q: ' + (end-start) + ' ms</div>');
};
LWE.QuestionEditorView.prototype.setSelectedQuestion = function(id) {
    this.selectedQuestion = id;
};
LWE.QuestionEditorView.prototype.editQuestion = function(id) {
    this.setSelectedQuestion(id);

    this.dom.questionEditor.unbind();
    this.dom.questionEditor.html(
        '<h4>' + (i18n.t('title.question') || 'Question') + '</h4>' +
        '<div class="data"></div>' +
        '<div class="buttons"></div>'
    );
    var editor = this;
    var editorDOM = this.dom.questionEditor;
    var dataDOM = $('.data', this.dom.questionEditor);
    var buttonDOM = $('.buttons', this.dom.questionEditor);

    /// @type LW.Question
    var question = LW.root.questionModel.question[id];
    var data = LW.root.difficultyModel.sortedList();
    var d, D;
    var difficultyList = [];
    for (d = 0, D = data.length; d < D; d++) {
        difficultyList.push({
            'id' : data[d].id,
            'text' : data[d].name
        });
    }
    data = LW.root.componentModel.sortedList(false);
    var componentList = [  ];
    for (d = 0, D = data.length; d < D; d++) {
        componentList.push({
            'id' : data[d].id,
            'text' : data[d].name
        });
    }
    data = question ? question.validParentList() : [];
    var parentCharacterList = [ {id: null, text: '—'} ];
    for (d = 0, D = data.length; d < D; d++) {
        parentCharacterList.push({
            'id' : data[d].id,
            'text' : data[d].name
        });
    }

    var file = LWUI.Editable({
        'text' : 'File',
        'val'  : question ? question.file : 'question.html',
        'saveCallback' : question ? function(file) {
            var self = this;
            self.setPending();
            question.changeFile(file)
                .done( function() { self.setOk(); } )
                .fail( function() { self.setError(); } );
        } : undefined
    });
    var name = LWUI.Editable({
        'text' : 'Name',
        'val'  : question ? question.name : 'New Question',
        'clearOnFocus' : !question,
        'callback' : question ? null : function() {
            file.text(LWE.camelCase(this.text()));
        },
        'saveCallback' : question ? function(name) {
            question.changeName(name);
        } : undefined
    });
    var time = LWUI.Editable({
        'text' : 'Time [s]',
        'val'  : question ? question.timeSec : '20',
        'clearOnFocus' : false,
        'saveCallback' : question ? function(time) {
            question.changeTime(time);
        } : undefined
    });
    var difficulty = LWUI.Select({
        'text' : 'Difficulty',
        'val'  : question ? (question.difficulty ? question.difficulty.id : null ) : 1,
        'data' : difficultyList,
        'saveCallback' : question ? function(id) {
            question.changeDifficulty(id);
        } : undefined
    });
    var component = LWUI.Select({
        'text' : 'Component',
        'val'  : question && question.component && question.component.id || null,
        'data' : componentList,
        'saveCallback' : question ? function(id) {
            question.changeComponent(id);
        } : undefined
    });
    var parentCharacter = LWUI.Select({
        'text' : 'Refines',
        'val'  : question && question.parentCharacterID || null,
        'data' : parentCharacterList,
        'saveCallback' : question ? function(id) {
            question.changeParentCharacter(id);
        } : undefined
    });
    var deleteB = LWUI.Button({
        'text' : (i18n.t('button.delete') || 'Delete'),
        'callback' : function() {
            if (confirm('Really delete question?\n' + question.name)) {
                LWE.UI.activeComponent = LWE.QuestionEditorView.focusableComponents.question;
                LWE.setStatus(editorDOM, LWE.status.pending);
                question.del().
                    done( function() {
                        LWE.setStatus(editorDOM, LWE.status.ok);
                    } ).
                    fail( function() {
                        LWE.setStatus(editorDOM, LWE.status.error);
                    } );
            }
        }
    });

    dataDOM.append(name.dom).append(time.dom).append(difficulty.dom).append(component.dom).append(parentCharacter.dom);
    if (!LW.wikibase) {
        file.dom.insertAfter(name.dom);
    }
    if (question) {
        buttonDOM.append(deleteB.dom);
    } else {
        var save = LWUI.Button({
            'text' : (i18n.t('button.save') || 'Save'),
            'title': LWUI.saveShortcut,
            'callback' : function() {
                LWE.UI.activeComponent = LWE.QuestionEditorView.focusableComponents.question;
                LWE.setStatus(editorDOM, LWE.status.pending);

                console.log('Topic is now: ', LW.root.currentTopic);

                var question = new LW.Question({
                    'name' : name.text(),
                    'topic' : LW.root.currentTopic.id,
                    'file' : file.text(),
                    'timeSec' : time.text(),
                    'difficulty' : difficulty.val(),
                    'component' : component.val(),
                    'parentCharacter' : parentCharacter.val()
                });
                question.addNew().done( function(questionID) {
                    LWE.setStatus(editorDOM, LWE.status.ok);
                    editor.setSelectedQuestion(questionID);
                } ).fail( function(jqXHR) {
                        LWE.setStatus(editorDOM, LWE.status.error);
                        alert(jqXHR.responseJSON.message || 'Please check if the required fields are set.');
                    });
            }
        });
        LWUI.addSaveShortcut(this.dom.questionEditor, function() { save.trigger(); });
        buttonDOM.append(save.dom);
    }


    if (LWE.UI.activeComponent == LWE.QuestionEditorView.focusableComponents.question) {
        name.focus();
    } else {
        console.log('Question box: Must not take focus.');
    }
    this.listCharacters(id);
    this.listEquipment(id);
};
LWE.QuestionEditorView.prototype.listEquipment = function(questionID) {
    this.dom.equipmentList.html(
        '<h4>' + (i18n.t('title.required-equipment') || 'Required Equipment') + '</h4>' +
        '<div class="data"></div>'
    );
    var dataDOM = $('.data', this.dom.equipmentList);

    var question = LW.root.questionModel.question[questionID];
    if (!question) {
        dataDOM.append('<p>[not yet available]</p>');
        return;
    }

    var equipment;
    var data = LW.root.equipmentModel.sortedList();
    for (var d = 0, D = data.length; d < D; d++) {
        equipment = LWUI.Checkable({
            'text' : data[d].name,
            'val' : question.equipment.indexOf(data[d]) >= 0,
            'callback' : function(equipmentID) { return function(enable) {
                LWE.UI.activeComponent = null;

                var self = this;
                self.setPending();
                question.setEquipment(equipmentID, enable).
                    done( function() {
                        self.setOk();
                    } ).fail( function() {
                        self.setError();
                    } );

            }}(data[d].id)
        });
        dataDOM.append(equipment.dom);
    }

};
LWE.QuestionEditorView.prototype.listCharacters = function(questionID) {
    this.dom.characterList.html(
        '<h4>' + (i18n.t('title.characters') || 'Characters') + '</h4>' +
        '<div class="data list"></div>' +
        '<div class="buttons"></div>'
    );
    var dataDOM = $('.data', this.dom.characterList);
    var buttonDOM = $('.buttons', this.dom.characterList);
    var editor = this;
    var item;

    var question = LW.root.questionModel.question[questionID];
    if (!question) return;

    var characterList = question.characterList();
    for (var c = 0, C = characterList.length; c < C; c++) {
        var character = characterList[c];
        item = $('<div class="listEntry">'+character.name+'</div>');
        item.click( function(id) { return function() {
            LWE.UI.activeComponent = LWE.QuestionEditorView.focusableComponents.character;
            editor.editCharacter(id);
        }}(character.id));
        dataDOM.append(item);
    }

    var add = LWUI.Button({
        'text' : (i18n.t('button.+add') || '+ Add'),
        'callback' : function() {
            LWE.UI.activeComponent = LWE.QuestionEditorView.focusableComponents.character;
            editor.editCharacter(undefined, questionID);
        }
    });
    buttonDOM.append(add.dom);

    this.editCharacter(this.selectedCharacter[questionID], questionID);
};
LWE.QuestionEditorView.prototype.editCharacter = function(characterID, questionID) {
    this.selectedCharacter[questionID] = characterID;

    this.dom.characterEditor.unbind();
    this.dom.characterEditor.html(
        '<h4>' + (i18n.t('title.character-editor') || 'Character Editor') + '</h4>' +
        '<div class="data"></div>' +
        '<div class="buttons"></div>'
    );
    var editorDOM = this.dom.characterEditor;
    var dataDOM = $('.data', this.dom.characterEditor);
    var buttonDOM = $('.buttons', this.dom.characterEditor);
    /// @type {LW.Character}
    var character = LW.root.characterModel.character[characterID];
    var editor = this;

    var file = LWUI.Editable({
        'text' : 'File',
        'val'  : character ? character.file : 'character.html',
        'saveCallback' : character ? function(file) {
            var self = this;
            self.setPending();
            character.changeFile(file)
                .done( function() { self.setOk(); } )
                .fail( function() { self.setError(); } );
        } : undefined
    });
    var name = LWUI.Editable({
        'text' : 'Name',
        'val' : character ? character.name : 'New Character',
        'clearOnFocus' : !character,
        'callback' : character ? null : function() {
            file.text(LWE.camelCase(this.text()));
        },
        'saveCallback' : character ? function(name) {
            character.changeName(name)
        } : undefined
    });
    var deleteB = LWUI.Button({
        'text' : (i18n.t('button.delete') || 'Delete'),
        'callback' : function() {
            if (confirm('Really delete character?\n' + character.name)) {
                LWE.setStatus(editorDOM, LWE.status.pending);
                character.del().
                    done( function() {
                        LWE.setStatus(editorDOM, LWE.status.ok);
                    } ).
                    fail( function() {
                        LWE.setStatus(editorDOM, LWE.status.error);
                    } );
            }
        }
    });

    // Save/delete buttons
    if (!character) {
        var save = LWUI.Button({
            'text' : (i18n.t('button.save') || 'Save'),
            'title' : LWUI.saveShortcut,
            'callback' : function() {
                LWE.UI.activeComponent = LWE.QuestionEditorView.focusableComponents.character;
                LWE.setStatus(editorDOM, LWE.status.pending);

                var character = new LW.Character({
                    'name' : name.text(),
                    'file' : file.text(),
                    'parentQuestion' : questionID
                });
                character.addNew().done( function() {
                    LWE.setStatus(editorDOM, LWE.status.ok);
                }).fail( function() {
                        LWE.setStatus(editorDOM, LWE.status.error);
                    });
            }
        });
        LWUI.addSaveShortcut(this.dom.characterEditor, function() { save.trigger(); });
        buttonDOM.append(save.dom);
    } else {
        buttonDOM.append(deleteB.dom);
    }

    dataDOM.append(name.dom);
    if (!LW.wikibase) { dataDOM.append(file.dom); }

    if (LWE.UI.activeComponent == LWE.QuestionEditorView.focusableComponents.character) {
        name.focus();
    } else {
        console.log('Character box: Must not take focus');
    }
};



LWE.TaxonEditorView = function(container) {
    if (!(this instanceof LWE.TaxonEditorView)) { return new LWE.TaxonEditorView(container); }

    this.addTaxonShortcut = 'shift+alt+t';
    this.initialised = false;
    this.focusAllowed = true;

    this.container = $(
        '<div class="lweTaxonEditor">' +
            '<h3>' + (i18n.t('title.taxonEditor') || 'Taxon Editor') + '</h3>' +
            '<div class="taxonListContainer"><div class="taxonList tile"></div></div>' +
            '<div class="taxonEditor tile"></div>' +
            '<div class="taxonCharacterContainer">' +
                '<div class="tceContainer"><div class="characterEditor tile"></div></div>' +
                '<div class="tcsContainer"><div class="similarTaxon tile"></div></div>' +
            '</div>' +
            //'<div class="rangeEditor tile"></div>' +
            '<div class="timing"></div>' +
        '</div>');

    this.dom = {
        'data': $( '.taxonList', this.container ),
        'listContainer': $( '.taxonListContainer', this.container ),
        'editor': $( '.taxonEditor', this.container ),
        'characterEditor': $( '.characterEditor', this.container ),
        'rangeEditor': $( '.rangeEditor', this.container ),
        'similarTaxon': $( '.similarTaxon', this.container ),
        'timing': $( '.timing', this.container )
    };

    this.ui = {
        taxonCharacterEditor : new LWE.TaxonEditorView.TaxonCharacterEditor(this, this.dom.characterEditor, this.dom.similarTaxon)
    };

    this.lastEditedTaxon = undefined;

    var editor = this;
    LW.root.addEventListener(LW.TaxonModel.prototype.id, function() { editor.updateUI(); });
    LW.root.addEventListener(LW.QuestionModel.prototype.id, function() { editor.updateUI(); });
    LW.root.addEventListener(LW.CharacterModel.prototype.id, function() { editor.updateUI(); });
    LW.root.addEventListener(LW.RangeModel.prototype.id, function() { editor.updateUI(); });
    LW.root.addEventListener( LW.TopicModel.prototype.idCurrentTopic, function() { editor.updateUI(); });

    container.html(this.container);
    this.updateUI();
    console.log('Taxon editor initialised');

    return this;
};
LWE.TaxonEditorView.focusableComponents = {
    taxon : 'taxon'
};
LWE.tplListEntry = Handlebars.compile('<div class="listEntry">{{text}}</div>');
LWE.TaxonEditorView.prototype.updateUI = function() {
    console.log('Updating taxon UI');

    var start,
        end;

    start = new Date().getTime();

    var editor = this;
    this.dom.data.html(
        '<h4>Taxa</h4> ' +
        '<div class="chart"></div>' +
        '<div class="data list"></div>' +
        '<div class="buttons"></div>'
    );
    var listDOM = $('.data', this.dom.data);

    // Existing taxa
    var taxonList = LW.root.taxonModel.sortedByName(false);

    for (var t = 0, T = taxonList.length; t < T; t++) {
        var taxon = taxonList[t];
        var elem = $(LWE.tplListEntry({ 'text' : taxon.name }));
        elem.click( function(id) { return function() {
            LWE.UI.activeComponent = LWE.TaxonEditorView.focusableComponents.taxon;
            editor.edit(id);
        }}(taxonList[t].id) );
        listDOM.append(elem);
    }

    // Taxon chart
    var barData = {
        data: [],
        callback: function(id) {
            LWE.UI.activeComponent = LWE.TaxonEditorView.focusableComponents.taxon;
            editor.edit(id);
        }
    };
    for (t = 0, T = taxonList.length; t < T; t++) {
        taxon = taxonList[t];
        barData.data.push( {
            name: taxon.name,
            value: taxon.characters.length,
            id: taxon.id
        } );
    }
    var taxonChart = new LWUI.BarChart(barData ),
        chartContainer = $( '.chart', this.dom.data );
    chartContainer.append( taxonChart.dom );

    var y = taxonChart.getY(this.lastEditedTaxon);
    if (y !== undefined) {
        console.log('Scrolling to position ' + y);
        chartContainer.scrollTop(y);
        taxonChart.highlight(this.lastEditedTaxon);
    } else {
        console.log('No scrolling position available for ' + this.lastEditedTaxon);
    }
    this.taxonChart = taxonChart;

    // Add button
    var add = LWUI.Button({
        'text' : (i18n.t('button.+add') || '+ Add'),
        'title' : this.addTaxonShortcut,
        'callback' : function(editor) { return function() {
            LWE.UI.activeComponent = LWE.TaxonEditorView.focusableComponents.taxon;
            editor.edit(undefined);
        }}(this)
    });
    LWUI.addShortcut('t', $(document), function() { add.trigger(); });
    $('.buttons', this.dom.data).append(add.dom);

    end = new Date().getTime();
    this.dom.timing.html('<div>T: ' + (end-start) + ' ms</div>');

    if (this.initialised) {
        start = new Date().getTime();
        this.edit(this.lastEditedTaxon);
        end = new Date().getTime();
        this.dom.timing.append('<div>E: ' + (end-start) + ' ms</div>');
    }
    this.initialised = true;
};
LWE.TaxonEditorView.prototype.edit = function(id) {
    this.lastEditedTaxon = id;
    this.taxonChart.highlight(id);

    var start,
        end,
        editor = this;

    start = new Date().getTime();

    this.dom.editor.unbind();
    this.dom.editor.html($(
        '<h4>Taxon</h4>' +
            '<div class="itemID">id = '+id+'</div>' +
            '<div class="data"></div>' +
        '<div class="buttons"></div>'
    ));

    var element = this.dom.editor;
    var buttonsDOM = $('.buttons', this.dom.editor);
    var dataDOM = $('.data', this.dom.editor);


    var taxon = LW.root.taxonModel.taxon[id];


    var degreeList = LW.root.degreeModel.sortedByID();
    var degreeData = [ { 'id' : null, 'text' : '—' } ];
    for (var d = 0, D = degreeList.length; d < D; d++) {
        degreeData.push({
            'id' : degreeList[d].id,
            'text' : degreeList[d].name
        });
    }

    var data = taxon && taxon.validParentList() || LW.root.taxonModel.sortedByName(false);
    var parentData = [ { 'id' : null, 'text' : '—' } ];
    for (var p = 0, P = data.length; p < P; p++) {
        parentData.push({
            'id' : data[p].id,
            'text' : data[p].name + (data[p].degree ? ' <em>(' + data[p].degree.name + ')</em>' : '')
        });
    }

    var degree = LWUI.Select({
        'text' : 'Degree',
        'val'  : (taxon && taxon.degree && taxon.degree.id) || null,
        'data' : degreeData,
        'saveCallback' : taxon ? function(id) {
            var self = this;
            self.setPending();
            taxon.changeDegree(id).
                done( function() {
                    self.setOk();
                } ).
                fail( function() {
                    self.setError();
                } );
        } : undefined
    });
    var parent = LWUI.Select({
        'text' : 'Parent',
        'val'  : taxon && taxon.parentTaxonID || null,
        'data' : parentData,
        'saveCallback' : taxon ? function(id) {
            var self = this;
            self.setPending();
            taxon.changeParent(id).
                done( function() {
                    self.setOk();
                } ).
                fail( function() {
                    self.setError();
                } );
        } : undefined
    });


    var file = LWUI.Editable({
        'text' : 'File',
        'val' : taxon ? taxon.file : 'taxon.html'
    });
    var name = LWUI.Editable({
        'text' : 'Name',
        'val' : taxon ? taxon.name : 'New Taxon',
        'clearOnFocus' : !taxon,
        'callback' : taxon ? null : function() {
            file.text(LWE.camelCase(this.text()));
        },
        'saveCallback' : taxon ? function(name) {
            var self = this;
            self.setPending();
            taxon.changeName(name).
                done( function() { self.setOk(); } ).
                fail( function() { self.setError(); } );
        } : undefined
    });

    // Save button for new taxon
    if (!taxon) {
        var save = LWUI.Button({
            'text' : (i18n.t('button.save') || 'Save'),
            'title' : LWUI.saveShortcut,
            'callback' : function() {
                LWE.UI.activeComponent = LWE.TaxonEditorView.focusableComponents.taxon;
                LWE.setStatus(element, LWE.status.pending);

                var newTaxon = new LW.Taxon({
                    'name' : name.text(),
                    'file' : file.text(),
                    'degree' : degree.val(),
                    'topic' : LW.root.currentTopic.id,
                    'parentTaxon' : parent.val()
                });
                newTaxon.addNew().done( function(taxonID) {
                    console.log('Taxon added: ' + taxonID);
                    LWE.setStatus(element, LWE.status.ok);
                    editor.lastEditedTaxon = taxonID;
                }).fail( function(data) {
                        console.log('Failed adding taxon', data);
                        LWE.setStatus(element, LWE.status.error);
                    });
            }
        });
        LWUI.addSaveShortcut(this.dom.editor, function() { save.trigger(); });
        buttonsDOM.append(save.dom);
    }

    dataDOM.append(name.dom).append(degree.dom).append(parent.dom);
    if (!LW.wikibase) { file.dom.insertAfter(name.dom); }

    this.editRanges(id);
    this.ui.taxonCharacterEditor.updateUI(id);

    end = new Date().getTime();
    this.dom.timing.html('<div>E: ' + (end-start) + ' ms</div>');

    if (LWE.UI.activeComponent == LWE.TaxonEditorView.focusableComponents.taxon) {
        if (this.focusAllowed) {
            name.focus();
        }
    } else {
        console.log('Taxon box: Must not take focus');
    }
};
LWE.TaxonEditorView.prototype.allowFocus = function(allow) {
    this.focusAllowed = allow;
};
LWE.TaxonEditorView.prototype.editRanges = function(taxonID) {

    this.dom.rangeEditor.html('<h4>Ranges</h4>');

    var taxon = LW.root.taxonModel.taxon[taxonID];
    if (!taxon) {
        this.dom.rangeEditor.append('<p>[not available]</p>');
        return;
    }

    var tpl = Handlebars.compile(
        '<div class="rangeValue">' +
            '<div class="desc">{{name}}</div>' +
            '<div class="content">' +
            '<div class="min"></div> to <div class="max"></div> {{unit}}' +
            '</div>' +
            '<div class="buttons"></div>' +
        '</div>'
    );

    var rangeValue,
        $elem, $elemButtons,
        view = this;

    LW.root.rangeModel.each( function(rid, range) {

            range = LW.root.rangeModel.range[rid];
            rangeValue = LW.root.taxonModel.taxon[taxonID].rangeValues[range.id];

            $elem = $(tpl(range));
            $elemButtons = $('.buttons', $elem);

            var min = new LWUI.Editable({
                'text' : 'min',
                'val'  : rangeValue ? rangeValue.min : ''
            });
            var max = new LWUI.Editable({
                'text' : 'max',
                'val'  : rangeValue ? rangeValue.max : ''
            });
            var save = new LWUI.Button({
                'text' : (i18n.t('button.save') || 'Save'),
                'title' : LWUI.saveShortcut,
                'callback' : function(range, min, max, elem) { return function() {
                    LWE.setStatus(elem, LWE.status.pending);
                    $.ajax('modify.php', {
                        'type' : 'POST',
                        'dataType' : 'json',
                        'data' : {
                            'action' : 'setTaxonRange',
                            'taxon' : taxonID,
                            'range' : range.id,
                            'min' : min.text(),
                            'max' : max.text()
                        }
                    }).done( function(data) {
                            if (data.ok) {
                                LWE.setStatus(elem, LWE.status.ok);
                                LW.root.taxonModel.dirtyData = true;
                                LW.root.updateModels();
                            } else {
                                LWE.setStatus(elem, LWE.status.error);
                            }
                        });
                } }(range, min, max, $elem)
            });
            var reset = new LWUI.Button({
                'text' : 'Reset',
                'callback' : function(range, elem) { return function() {
                    LWE.setStatus(elem, LWE.status.pending);
                    $.ajax('modify.php', {
                        'type' : 'POST',
                        'dataType' : 'json',
                        'data' : {
                            'action' : 'dropTaxonRange',
                            'taxon' : taxonID,
                            'range' : range.id
                        }
                    }).done( function(data) {
                            if (data.ok) {
                                LWE.setStatus(elem, LWE.status.ok);
                                LW.root.taxonModel.dirtyData = true;
                                LW.root.updateModels();
                            } else {
                                LWE.setStatus(elem, LWE.status.error);
                            }
                        });
                }} (range, $elem)
            });

            $('.min', $elem).append(min.dom);
            $('.max', $elem).append(max.dom);
            $elemButtons.append(save.dom).append(reset.dom);

            LWUI.addSaveShortcut($elem, function(button) { return function() {
                button.trigger();
            }} (save));

            view.dom.rangeEditor.append($elem);

    }, false);


};
LWE.TaxonEditorView.TaxonCharacterEditor = function(editor, container, similarContainer) {

    this.editor = editor;
    this.dom = container;
    this.similarDOM = similarContainer;

};
LWE.TaxonEditorView.TaxonCharacterEditor.prototype.updateUI = function(id) {
    console.log('Updating Taxon--Character UI');
    this.dom.empty();
    this.similarDOM.empty();



    var taxon = LW.root.taxonModel.taxon[id];
    if (!taxon) {
        return;
    }

    var c, C;

    this.dom.append('<h4>Characters for ' + taxon.name + '</h4>');


    // Build a list of available (visible) questions

    /// Helper model filled with the currently selected characters for this taxon
    /// to determine which question to show/hide
    var dKey = new LWF.DKey( {
        taxonFilter: 'wrongNumber',
        questionFilter: 'classification',
        questionCost: 'alphabetic'
    } );
    for (c = 0, C = taxon.characters.length; c < C; c++) {
        dKey.choices.character.set(taxon.characters[c].id, true, true);
    }
    dKey.recalculate();

    var dom = this.dom;
    var tplQuestionTile = Handlebars.compile(
        '<div class="question{{#if childSelected}} childSelected{{/if}}">' +
            '<div class="desc">{{name}} [id={{id}}]</div>' +
            '<div class="characters"></div>' +
        '</div>'
    );

    // @type {LWF.DKeyQCVisitor}
    var visitor = dKey.buildQCVisitor();
    visitor.options.dfs = true;
    visitor.preorderComponentVisitor = function(component, storage) {
        storage.visibleQuestions = 0;

        storage.componentContainer = $('<div class="component"><h4>'+component.name+'</h4><div class="questions"></div></div>');
        storage.questionDOM = $('.questions', storage.componentContainer);
        dom.append(storage.componentContainer);
    };
    /** @param {LW.Question} question
     *  @param questionStorage
     *  @param componentStorage */
    visitor.preorderQuestionVisitor = function ( question, questionStorage, componentStorage ) {
        var hidden = !this.acceptQuestion( question );
        var childSelected = this.ratingContainer.question[question.id].counter.selectedCharacters > 0;

        // Create the question DOM
        questionStorage.questionDOM = $( tplQuestionTile( {
            name: question.name,
            hidden: hidden,
            id: question.id,
            childSelected: childSelected
        } ) );
        questionStorage.characterDOM = $( '.characters', questionStorage.questionDOM );

        // Append only if not hidden
        if ( !hidden ) {
            componentStorage.questionDOM.append( questionStorage.questionDOM );
        }

        // Count the number of visible questions for this component
        if ( !hidden ) {
            componentStorage.visibleQuestions++;
            if ( !childSelected ) {
                componentStorage.charactersTodo++;
            } else {
                componentStorage.charactersDone++;
            }
        }
    };
    visitor.preorderCharacterVisitor = function ( character, questionStorage ) {

        var checkable = LWUI.Checkable( {
            text: character.name,
            val: this.choicesContainer.character.isSet( character.id ),
            callback: function () {

                LWE.UI.activeComponent = null;

                var dom = this.dom;
                LWE.setStatus( dom, LWE.status.pending );
                taxon.setCharacter( character.id, this.val() ).done(function () {
                    LWE.setStatus( dom, LWE.status.ok );
                    LW.root.taxonModel.dirtyReferences = true;
                    LW.root.updateModels();
                } ).fail( function ( data ) {
                        LWE.setStatus( dom, LWE.status.error );
                        alert( data.message );
                    } );
            }
        } );

        questionStorage.characterDOM.append( checkable.dom );

    };
    LW.root.componentModel.visit(visitor);

    this.similarDOM.append('<h4>Similar taxa</h4>');
    this.similarDOM.append('<p>Total characters set for ' + taxon.name + ': ' + taxon.characters.length + '</p>');
    this.similarDOM.append('<p>The following taxa have little differences to <em>'+taxon.name+'</em>—either them or this taxon need more characters.</p>');

    var list = $('<div class="data list"></div>').appendTo(this.similarDOM);
    var tpl = Handlebars.compile('<div class="listEntry"><tt>({{ok}}+ {{wrong}}–)</tt> {{name}}</div>'),
        elem;

    var self = this;
    var taxonVisitor = dKey.buildTaxonVisitor();
    taxonVisitor.taxonVisitor = function ( taxon, accepted, data ) {
        var rating = data.ratingContainer.taxon.data[taxon.id];
        if ( rating.characters.wrong.length <= 2 ) {
            if ( taxon.id != id ) {

                elem = $( tpl( {
                    ok: rating.characters.ok.length,
                    wrong: rating.characters.wrong.length,
                    //total : rating.ok + rating.unknown + rating.wrong,
                    name: taxon.name
                } ) );
                elem.click( function ( id ) {
                    return function () {
                        self.edit( id );
                    }
                }( rating.taxon.id ) );

                list.append( elem );
            }
        }
    };
    LW.root.taxonModel.visit( taxonVisitor );

};




LWE.ComponentEditorView = function(container) {
    if (!(this instanceof LWE.ComponentEditorView)) { return new LWE.ComponentEditorView(container); }

    var html = '<div class="lweComponentEditor">' +
        '<h3>' + (i18n.t('title.component') || 'Component') + '</h3>' +
        '<div class="componentList tile">' +
            '<div class="data list"></div>' +
            '<div class="buttons"></div>' +
        '</div>' +
        '<div class="componentEditor tile">' +
            '<div class="editor"></div>' +
        '</div>' +
        '</div>';

    this.data = {};
    this.container = $(html);
    this.dom = {
        'data' : $('.data', this.container),
        'buttons' : $('.buttons', this.container),
        'editor' : $('.editor', this.container)
    };
    this.initialised = false;

    container.html(this.container);

    this.updateUI();

    var editor = this;
    // Update the editor when the component model changes
    LW.root.addEventListener(LW.ComponentModel.prototype.id, function() { editor.updateUI(); });
    LW.root.addEventListener( LW.TopicModel.prototype.idCurrentTopic, function() { editor.updateUI(); });

    console.log('Component editor initialised');

    return this;
};
// \todo Move into prototype?
LWE.ComponentEditorView.focusableComponents = {
    component : 'component'
};
LWE.ComponentEditorView.prototype.updateUI = function() {
    var elem;
    var c, C;
    var tpl = Handlebars.compile('<div class="listEntry">{{name}}</div>');

    this.dom.data.empty();
    var list = LW.root.componentModel.sortedList(false);
    for (c = 0, C = list.length; c < C; c++) {
        elem = $(tpl(list[c]));
        elem.click( function(editor, id) { return function() {
            editor.edit(id);
        }}(this, list[c].id) );
        this.dom.data.append(elem);
    }

    var addButton = LWUI.Button({
        'text' : (i18n.t('button.+add') || '+ Add'),
        'callback' : function(editor) { return function() {
            LWE.UI.activeComponent = LWE.ComponentEditorView.focusableComponents.component;
            editor.edit(undefined);
        }}(this)
    });
    this.dom.buttons.html(addButton.dom);

    if (this.initialised) {
        this.edit(undefined);
    }

    this.initialised = true;

};
LWE.ComponentEditorView.prototype.edit = function(id) {
    var element = $(
        '<div>' +
            '<div class="itemData"></div>' +
            '<div class="itemButtons"></div>' +
        '</div>'
    );

    var component = LW.root.componentModel.component[id];
    var $data = $('.itemData', element);
    var $buttons = $('.itemButtons', element);

    var file = LWUI.Editable({
        'text' : 'File',
        'val' : component ? component.file : 'component.html'
    });
    var name = LWUI.Editable({
        'text' : 'Name',
        'val' : component ? component.name : 'New Component',
        'clearOnFocus' : !component, // Clear for new components
        'callback' : component ? null : function() {
            file.text(LWE.camelCase(this.text()));
        },
        'saveCallback' : component ? function(name) {
            component.changeName(name);
        } : undefined
    });

    if (!component) {
        var save = LWUI.Button( {
            'text' : (i18n.t('button.save') || 'Save'),
            'title' : LWUI.saveShortcut,
            'callback' : function() {
                LWE.UI.activeComponent = LWE.ComponentEditorView.focusableComponents.component;
                LWE.setStatus(element, LWE.status.pending);

                var component = new LW.Component({
                    'id' : component ? component.id : 'unknown',
                    'topic' : LW.root.currentTopic.id,
                    'name' : name.text(),
                    'file' : file.text()
                });
                component.addNew().done( function() {
                    LWE.setStatus(element, LWE.status.ok);
                } ).fail( function() {
                        LWE.setStatus(element, LWE.status.error);
                    } );
            }
        } );
        $buttons.append(save.dom);
        LWUI.addSaveShortcut(element, function() { save.trigger(); });
    }

    $data.append(name.dom);
    if (!LW.wikibase) { $data.append(file.dom); }


    this.dom.editor.html(element);

    if (LWE.UI.activeComponent == LWE.ComponentEditorView.focusableComponents.component) {
        name.focus();
    }
};




LWE.TopicEditorView = function(container) {
    if (!(this instanceof LWE.TopicEditorView)) { return new LWE.TopicEditorView(container); }

    var html = '<div class="lweTopicEditor">' +
        '<h3>' + (i18n.t('title.topic') || 'Topic') + '</h3>' +
        '<div class="topicList tile">' +
            '<div class="data list"></div>' +
            '<div class="buttons"></div>' +
        '</div>' +
        '<div class="topicEditor tile">' +
            '<div class="editor"></div>' +
        '</div>' +
        '</div>';

    this.data = {};
    this.container = $(html);
    this.dom = {
        'data' : $('.data', this.container),
        'buttons' : $('.buttons', this.container),
        'editor' : $('.editor', this.container)
    };
    this.initialised = false;

    container.html(this.container);

    this.updateUI();

    // Update the editor when the topic model changes
    var editor = this;
    LW.root.addEventListener(LW.TopicModel.prototype.id, function() { editor.updateUI(); } );

    console.log('Topic editor initialised');

    return this;
};
LWE.TopicEditorView.prototype.focusableComponents = {
    topic : 'topic'
};
LWE.TopicEditorView.prototype.updateUI = function() {
    var elem;
    var c, C;
    var tpl = Handlebars.compile('<div class="listEntry">{{name}}</div>');

    this.dom.data.empty();

    var list = [],
        topic;
    for (var tid in LW.root.topicModel.topic) {
        if (LW.root.topicModel.topic.hasOwnProperty(tid)) {
            topic = LW.root.topicModel.topic[tid];
            list.push(topic);
        }
    }
    list.sort( function(a,b) { return a.name.localeCompare(b.name); } );

    for (c = 0, C = list.length; c < C; c++) {
        elem = $(tpl(list[c]));
        elem.click( function(editor, id) { return function() {

            localStorage.setItem('lweTopicId', id);
            LW.root.setTopic(LW.root.topicModel.topic[id]);
            editor.edit(id);

        }}(this, list[c].id) );
        this.dom.data.append(elem);
    }

    var addButton = LWUI.Button({
        'text' : (i18n.t('button.+add') || '+ Add'),
        'callback' : function(editor) { return function() {
            LWE.UI.activeComponent = editor.focusableComponents.topic;
            editor.edit(undefined);
        }}(this)
    });
    this.dom.buttons.html(addButton.dom);

    this.edit(LW.root.currentTopic ? LW.root.currentTopic.id : undefined);

    this.initialised = true;

};
LWE.TopicEditorView.prototype.edit = function(id) {
    var topic = LW.root.topicModel.topic[id];

    var element = $( this.tplEditor( {
        'id' : id,
        'name' : topic ? topic.name : undefined
    } ) );

    var $data = $('.itemData', element);
    var $buttons = $('.itemButtons', element);
    var editor = this;

    var file = LWUI.Editable( {
        'text': 'File',
        'val': topic ? topic.file : 'topic.html',
        'saveCallback': topic ? function ( file ) {
            var self = this;
            self.setPending();
            topic.changeFile( file ).
                done( function() { self.setOk(); } ).
                fail( function() { self.setError(); } );
        } : undefined
    } );
    var name = LWUI.Editable( {
        'text': 'Name',
        'val': topic ? topic.name : 'New Topic',
        'clearOnFocus': !topic, // Clear for new topics
        'callback': topic ? null : function () {
            file.text( LWE.camelCase( this.text() ) );
        },
        'saveCallback': topic ? function ( name ) {
            var self = this;
            self.setPending();
            topic.changeName( name ).
                done( function() { self.setOk(); } ).
                fail( function() { self.setError(); } );
        } : undefined
    } );
    var save = LWUI.Button( {
        'text': (i18n.t( 'button.save' ) || 'Save'),
        'title': LWUI.saveShortcut,
        'callback': function () {
            LWE.UI.activeComponent = editor.focusableComponents.topic;
            LWE.setStatus( element, LWE.status.pending );

            var topic = new LW.Topic( {
                'id': topic ? topic.id : 'unknown',
                'name': name.text(),
                'file': file.text()
            } );
            topic.addNew().done(function () {
                LWE.setStatus( element, LWE.status.ok );
                LW.root.topicModel.dirty = true;
                LW.root.updateModels();

            } ).fail( function () {
                    LWE.setStatus( element, LWE.status.error );
                } );
        }
    } );

    $data.append( name.dom );
    if ( !LW.wikibase ) { $data.append( file.dom ); }
    if ( !topic ) { $buttons.append( save.dom ); }

    LWUI.addSaveShortcut( element, function () { save.trigger(); } );

    this.dom.editor.html( element );

    if ( LWE.UI.activeComponent == this.focusableComponents.topic ) {
        name.focus();
    }
};
LWE.TopicEditorView.prototype.tplEditor = Handlebars.compile(
    '<div>' +
        '<div class="itemID">id = {{id}}</div>' +
        '<h4>{{#if name}}{{name}}{{else}}New Topic{{/if}}</h4>' +
        '<div class="itemData"></div>' +
        '<div class="itemButtons"></div>' +
    '</div>'
);


LWE.RangeEditorView = function(container) {

    this.container = $(
        '<div class="lweRangeEditor">' +
            '<h3>' + (i18n.t('title.ranges') || 'Ranges') + '</h3>' +
            '<div class="rangeList tile">' +
                '<div class="data list"></div>' +
                '<div class="buttons"></div>' +
            '</div>' +
            '<div class="rangeEditor tile">' +
                '<div class="editor"></div>' +
            '</div>' +
        '</div>');
    this.dom = {
        'data' : $('.data', this.container),
        'buttons' : $('.buttons', this.container),
        'editor' : $('.editor', this.container)
    };

    this.initialised = false;
    this.currentRangeID = undefined;


    container.append(this.container);
    this.updateUI();

    LW.root.addEventListener(LW.RangeModel.id, function(editor) { return function() { editor.updateUI(); }}(this));

    console.log('Range editor initialised.');
    this.initialised = true;
};
LWE.RangeEditorView.prototype.focusableComponents = {
    range : 'range'
};
LWE.RangeEditorView.prototype.updateUI = function() {

    var tpl = Handlebars.compile('<div class="listEntry">{{name}}</div>'),
        view = this,
        range,
        elem;

    this.dom.data.empty();
    for (var rid in LW.root.rangeModel.range) {
        if (LW.root.rangeModel.range.hasOwnProperty(rid)) {
            range = LW.root.rangeModel.range[rid];
            elem = $(tpl({ 'name' : range.name }));
            elem.click( function(id) { return function() {
                LWE.UI.activeComponent = LWE.RangeEditorView.prototype.focusableComponents.range;
                view.edit(id);
            } }(rid) );
            this.dom.data.append(elem);
        }
    }

    var addButton = LWUI.Button({
        'text' : (i18n.t('button.+add') || '+ Add'),
        'callback' : (function(editor) { return function() {
            LWE.UI.activeComponent = LWE.RangeEditorView.prototype.focusableComponents.range;
            editor.edit(undefined);
        }})(this)
    });
    this.dom.buttons.html(addButton.dom);

    if (this.initialised) { this.edit(this.currentRangeID); }

};
LWE.RangeEditorView.prototype.edit = function(id) {
    this.currentRangeID = id;

    var element = $(
        '<div>' +
            '<div class="itemData"></div>' +
            '<div class="itemButtons"></div>' +
        '</div>'
    );
    var data = $('.itemData', element),
        buttons = $('.itemButtons', element);

    var range = LW.root.rangeModel.range[id];

    var components = LW.root.componentModel.sortedList(false),
        d, D;
    var componentList = [  ];
    for (d = 0, D = components.length; d < D; d++) {
        componentList.push({
            'id' : components[d].id,
            'text' : components[d].name
        });
    }

    var name = LWUI.Editable({
        'text' : 'Name',
        'val' : range ? range.name : 'New Range',
        'clearOnFocus' : !range,
        'saveCallback' : range ? function(name) {
            var self = this;
            self.setPending();
            range.changeName(name).
                done( function() { self.setOk(); } ).
                fail( function() { self.setError(); } );
        } : undefined
    });
    var unit = LWUI.Editable({
        'text' : 'Unit',
        'val' : range ? range.unit : 'Unit',
        'clearOnFocus' : !range
    });
    var type = LWUI.Select({
        'text' : 'Data type',
        'val' : range ? range.datatype : 'int',
        'data' : [
            { 'id' : 'int', 'text' : 'Integer'},
            { 'id' : 'float', 'text' : 'Floating point'}
        ]
    });
    var component = LWUI.Select( {
        'text' : 'Component',
        'val' : range && range.component ? range.component.id : null,
        'data' : componentList,
        'saveCallback' : range ? function(componentID) {
            var self = this;
            self.setPending();
            range.changeComponent(componentID).
                done( function() { self.setOk(); }).
                fail( function() { self.setError(); });
        } : undefined
    });

    // Save button for new ranges
    if (!range) {
        var save = LWUI.Button({
            'text' : (i18n.t('button.save') || 'Save'),
            'title' : LWUI.saveShortcut,
            'callback' : function() {
                LWE.setStatus(element, LWE.status.pending);
                var range = new LW.Range( {
                    'name' : name.text(),
                    'unit' : unit.text(),
                    'datatype' : type.val()
                } );
                range.addNew().
                    done( function() {
                        LWE.setStatus(element, LWE.status.ok);
                    } ).
                    fail( function() {
                        LWE.setStatus(element, LWE.status.error);
                    } );
            }
        });
        LWUI.addSaveShortcut(element, function() { save.trigger(); });
        buttons.append(save.dom);
    }



    data.append(name.dom)
        .append(unit.dom)
        .append(type.dom)
        .append(component.dom);

    this.dom.editor.html(element);

    if (LWE.UI.activeComponent == this.focusableComponents.range) {
        name.focus();
    }

};


(function() {

})();