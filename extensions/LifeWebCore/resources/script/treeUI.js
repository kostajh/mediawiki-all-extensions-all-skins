/**
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 * @requires {LWF}
 */

var LWF = LWF || {};


LWF.KeyUi = function ( $container, options, t ) {
    if ( !(this instanceof LWF.KeyUi) ) { return new LWF.KeyUi( $container, options, t ); }

    this.options = {
        dfs: true,
        useComponents: true,
        jumpToSelected: true,
        dkeyOptions: options && options.dKey ? options.dKey : {},
        topicId: 1
    };
    this.loadSettings();

    /// i18n.t function
    this.t = t || function() { return false; };

    var keyUi = this;

    var $structure = $( this.tpl() );
    $( '.character', $structure ).click( function () {
        $( this ).toggleClass( 'checked' );
    } );
    $( '.component > h2', $structure ).click( function () {
        $( this ).parent().toggleClass( 'collapsed' );
    } );

    $( '#taxonTreeButton', $structure ).click(function () {
        keyUi.hideSpecialPage();
        keyUi.showSpecialPage( keyUi.getTaxonTreePage(), $( this ) );
    } ).children( '.text' ).text( this.t( 'button.taxa' ) || 'Taxa' );

    $( '#aboutButton', $structure ).click(function () {
        keyUi.hideSpecialPage();
        keyUi.showSpecialPage( keyUi.getAboutPage(), $( this ) );
    } ).children( '.text' ).text( this.t( 'button.about' ) || 'About' );

    $( '#settingsButton', $structure ).click(function () {
        keyUi.hideSpecialPage();
        keyUi.showSpecialPage( keyUi.getSettingsPage(), $( this ) );
    } ).children( '.text' ).text( this.t( 'button.settings' ) || 'Settings' );

    $( '#newIdentificationButton', $structure ).click(function () {
        keyUi.getTaxonTreePage();
        keyUi.uiObjects.taxonTree.unselectAll();
        keyUi.dKey.choices.reset();
    } ).children( '.text' ).text( this.t( 'button.newIdentification' ) || 'New Identification' );


    // Search field
    var $search = $( '#searchByName', $structure );
    $( 'label', $search ).text( this.t( 'button.search' || 'Search' ) );

    var $nameSearchInput = $( 'input', $search );
    $nameSearchInput.change( function () {
        keyUi.dKey.choices.setNameSearchString( $( this ).val() );
    } );
    var lastTimeoutId = null;
    $nameSearchInput.keyup( function () {
        var $this = $( this );
        var val = $this.val();
        clearTimeout( lastTimeoutId );
        lastTimeoutId = setTimeout( function () {
            keyUi.dKey.choices.setNameSearchString( val );
        }, 500 );

    } );
    $( '#clearNameSearch', $structure ).click( function () {
        $nameSearchInput.val( '' ).triggerHandler( 'change' );
    } );

    $container.empty().append( $structure );

    // Esc only works consistently with keydown or keyup, NOT with keypress.
    $( window ).keydown( function ( event ) {
        // Close special page with Esc
        if ( event.which == 27 ) {
            event.preventDefault();
            // If the fullscreen gallery is active, close it; otherwise, close the special page.
            if ( !keyUi.uiObjects.fullscreenGallery.hide() ) {
                keyUi.hideSpecialPage();
            }
        }
    } );
    $( window ).keypress( function ( event ) {
        if ( keyUi.uiObjects.fullscreenGallery.isVisible() ) {
            if ( event.which == 37 ) {
                // Left
                keyUi.uiObjects.fullscreenGallery.prev();
                event.preventDefault();

            } else if ( event.which == 39 ) {
                // Right
                keyUi.uiObjects.fullscreenGallery.next();
                event.preventDefault();
            }
        } else if ( event.which == 47 ) {
            // Slash: Focus search field
            if ( !$nameSearchInput.is( ':focus' ) ) {
                $nameSearchInput.focus();
            } else {
                console.log()
            }
        }
    } );

    this.$structure = $structure;
    this.ui = {
        $topicIntro: $( '.introduction', $structure ),
        $taxon: $( '.rightBody', $structure ),
        $key: $( '.mainKey', $structure ),
        $taxonTreePage: null
    };
    this.uiObjects = {
        /// @type {LWF.KeyUi.TaxonUi}
        taxon: {},
        /// @type {LWF.KeyUi.ComponentUi}
        component: {},
        /// @type {LWF.KeyUi.QuestionUi}
        question: {},
        /// @type {LWF.KeyUi.CharacterUi}
        character: {},
        /// @type {LWUI.TreeMatrix}
        taxonTree: null,
        /// @type {LWF.KeyUi.GalleryUi}
        fullscreenGallery: new LWF.KeyUi.GalleryUi()
    };


    $( '#fullscreenGallery', $structure ).empty().append( this.uiObjects.fullscreenGallery.$dom );

    $( window ).scroll( function () { keyUi.scrollStopper( false ); } );
    $( window ).resize( function () { keyUi.scrollStopper( true ); } );


    LW.root.init().done(function () {
        keyUi.loadTopic( keyUi.options.topicId );
    } ).fail( function () {
            keyUi.ui.$topicIntro.html(
                '<h1>Failed to load data.</h1>' +
                '<p>Please notify an administrator, if possible.</p>'
            )
        } );

    return this;
};
LWF.KeyUi.prototype.storeSettings = function() {
    if ( window.localStorage ) {

        localStorage.setItem( 'keyUi.questionFilter', this.options.dkeyOptions.questionFilter );
        localStorage.setItem( 'keyUi.useComponents', this.options.useComponents );
        localStorage.setItem( 'keyUi.jumpToSelection', this.options.jumpToSelected );
        localStorage.setItem( 'keyUi.dfs', this.options.dfs );
        localStorage.setItem( 'keyUi.topicId', LW.root.currentTopic.id );

    }
};
LWF.KeyUi.prototype.loadSettings = function () {

    if ( window.localStorage ) {
        var value;
        if ( (value = localStorage.getItem( 'keyUi.questionFilter' )) !== null ) {
            console.log( 'Question filter:', value );
            this.options.dkeyOptions['questionFilter'] = value;
        }
        if ( (value = localStorage.getItem( 'keyUi.useComponents' )) !== null ) {
            this.options.useComponents = value == 'true';
        }
        if ( (value = localStorage.getItem( 'keyUi.jumpToSelection' )) !== null ) {
            this.options.jumpToSelected = value == 'true';
        }
        if ( (value = localStorage.getItem( 'keyUi.dfs' )) !== null ) {
            this.options.dfs = value == 'true';
        }
        if ( (value = localStorage.getItem( 'keyUi.topicId' )) !== null ) {
            this.options.topicId = value;
        }
    }
};
LWF.KeyUi.prototype.loadTopic = function ( id ) {

    if ( !LW.root.topicModel.topic.hasOwnProperty( id ) ) {
        console.log( 'Topic ' + id + ' does not exist, trying to fall back ...' );
        for ( id in LW.root.topicModel.topic ) {
            if ( LW.root.topicModel.topic.hasOwnProperty( id ) ) {
                console.log( 'Falling back to topic: ' + id );
                break;
            }
        }
    }

    var keyUi = this,
        topic = LW.root.topicModel.topic[id];
    LW.root.setTopic( topic );
    this.ui.$taxonTreePage = null;
    this.ui.$topicIntro.html( topic.description.text );

    this.dKey = new LWF.DKey( this.options.dkeyOptions );
    this.dKey.addCallback( function () {
        keyUi.updateUI();
    } );

    var speciesDegree = LW.root.degreeModel.get( 'species' );
    if ( speciesDegree ) {
        this.dKey.choices.degree.set( speciesDegree.id, true );
        console.log( '########### Only displaying species.' );
    }

    this.dKey.recalculate();
};
LWF.KeyUi.prototype.updateUI = function () {

    var keyUi = this;

    var start = new Date().getTime();

    keyUi.ui.$taxon.empty();
    var taxonVisitor = this.dKey.buildTaxonVisitor();
    taxonVisitor.taxonVisitor = function ( taxon, accepted, data ) {
        if ( accepted ) {
            var $elem = keyUi.getTaxonUi(taxon);
            $elem.loadRating(
                data.ratingContainer.taxon.data[taxon.id].characters.ok,
                data.ratingContainer.taxon.data[taxon.id].characters.wrong,
                data.ratingContainer.taxon.data[taxon.id].characters.unknown
            );
            keyUi.ui.$taxon.append( $elem.$dom );
            $elem.redefineHandlers();
        }
    };
    LW.root.taxonModel.visit( taxonVisitor );

    keyUi.ui.$key.empty();
    var qcVisitor = this.dKey.buildQCVisitor();
    qcVisitor.options.dfs = this.options.dfs;
    qcVisitor.options.useComponents = this.options.useComponents;
    if ( qcVisitor.options.useComponents ) {
        qcVisitor.preorderComponentVisitor = function ( component, storage ) {
            var $elem = keyUi.getComponentUi( component );
            $elem.empty();
            keyUi.ui.$key.append( $elem.$dom );
            $elem.setClickHandler();

            storage.componentUi = $elem;
            storage.count = {
                visible: 0,
                answered: 0
            };
        };
        qcVisitor.postorderComponentVisitor = function ( component, storage ) {
            storage.componentUi.loadRating( storage.count.answered, storage.count.visible );
            storage.componentUi.setVisible( this.choicesContainer.component.isVisible( component.id, storage.count.visible > 0 ) );
        };
    } else {
        var $elem = keyUi.getComponentUi( new LW.Component(
            { id: -1, name: 'All questions', description: { text: '', images: [] } }
        ) );
        $elem.empty();
        keyUi.ui.$key.append( $elem.$dom );

        qcVisitor.componentStorageAlternative = {
            componentUi: $elem,
            count: {
                visible: 0,
                answered: 0
            }
        };
    }
    qcVisitor.preorderQuestionVisitor = function ( question, questionStorage, componentStorage ) {
        if ( this.acceptQuestion( question ) ) {
            var $elem = keyUi.getQuestionUi( question );
            componentStorage.componentUi.appendQuestion( $elem );
            componentStorage.count.visible++;
            componentStorage.count.answered += this.ratingContainer.question[question.id].counter.selectedCharacters > 0;
            $elem.redefineHandlers();
        } else {
            //console.log('Question stays hidden: ' + question.name);
        }
    };
    qcVisitor.preorderCharacterVisitor = function ( character, questionStorage, componentStorage ) {
        var characterUi = keyUi.getCharacterUi( character, true );
        if ( !characterUi ) {
            //console.log( 'Character visited but is not visible: ' + character.name, keyUi.uiObjects.character[character.id] );
        } else {
            characterUi.loadRating(
                this.choicesContainer.character.isSet( character.id ),
                this.ratingContainer.character[character.id].counter.ok > 0
            );
        }
    };
    LW.root.visit( qcVisitor );
    this.scrollStopper( true );

    // Scroll to the previously toggled character
    if ( this.lastClickedCharacterUi ) {
        if ( this.options.jumpToSelected ) {
            setTimeout( function ( $dom ) {
                return function () {
                    $( window ).scrollTop( $dom.position().top );
                }
            }( this.lastClickedCharacterUi.$dom ), 1000 );
        }
        this.lastClickedCharacterUi = null;
    }

    var end = new Date().getTime();
    console.log( 'UI updated in ' + (end - start) + ' ms.' );
};
/**
 * Prevents the taxon block from scrolling out of the viewport.
 * @param recalculate Must be true if the taxa displayed or the window size has changed. Should be false for scrolling
 *                    so it runs smooth.
 */
LWF.KeyUi.prototype.scrollStopper = function ( recalculate ) {

    var $taxon = this.ui.$taxon;

    this.cache = this.cache || {
        scrollStopper: {
            hTaxon: 0,
            timeoutId: 0
        }
    };

    var hTaxon = this.cache.scrollStopper.hTaxon;

    if ( recalculate || hTaxon <= 0 ) {

        // Set to static before calculating because otherwise the wrong height (window) is returned.
        $taxon.css( 'position', 'static' );
        hTaxon = $taxon.height();
        this.cache.scrollStopper.hTaxon = hTaxon;

    } else {

        // Recalculate later. When scrolling, the position should not be recalculated as changing the layout (static
        // position is a potentially expensive operation. Therefore, wait for the scroll action to finish.
        // The reason to recalculate then is that eventually we get the correct height, even when images are loaded
        // with a delay.
        // Exact measurements are still needed.
        clearTimeout( this.cache.scrollStopper.timeoutId );
        this.cache.scrollStopper.timeoutId = setTimeout( function ( ui ) {
            return function () {
                ui.scrollStopper( true );
            }
        }( this ), 250 );
    }

    var hKey = this.ui.$key.height();

    // scrollHeight returns the height also with position: fixed -- but only approximately.
    //var hTaxon = $taxon[0].scrollHeight;

    var uncoveredY = hTaxon - $( window ).height();

    //console.log('Taxon:', hTaxon, ' Window:', $(window).height(), ' Delta: ', uncoveredY, ' Top: ', $(window).scrollTop());
    if ( hKey > hTaxon && $( window ).scrollTop() > uncoveredY ) {
        $taxon.css( 'position', 'fixed' );
        if ( hTaxon < $( window ).height() ) {
            $taxon.css( 'top', '40px' );
            $taxon.css( 'bottom', '' );
        } else {
            $taxon.css( 'top', '' );
            $taxon.css( 'bottom', 0 );
        }

    } else {
        $taxon.css( 'position', 'static' );
    }
};
/**
 * Taxon details page
 * @param {LW.Taxon} taxon
 * @returns {LWF.KeyUi.TaxonUi}
 */
LWF.KeyUi.prototype.getTaxonUi = function(taxon) {
    var ui = this;
    var $elem = this.uiObjects.taxon[taxon.id];
    if ( !$elem ) {
        $elem = new LWF.KeyUi.TaxonUi( taxon, function ( taxon ) {
            ui.hideSpecialPage();
            ui.showSpecialPage( ui.getTaxonUi( taxon ).getSpecialPage(), null );
        }, function(title, images, index) {
            ui.uiObjects.fullscreenGallery.loadGallery(title, images);
            ui.uiObjects.fullscreenGallery.loadImage(index);
        } );
        this.uiObjects.taxon[taxon.id] = $elem;
    }
    return $elem;
};
/**
 * @param {LW.Component} component
 * @returns {LWF.KeyUi.ComponentUi}
 */
LWF.KeyUi.prototype.getComponentUi = function(component) {
    var ui = this;
    var $elem = this.uiObjects.component[component.id];
    if ( !$elem ) {
        $elem = new LWF.KeyUi.ComponentUi( component, function(component) {
            ui.dKey.choices.component.toggle(component.id, $elem.isVisible() );
        } );
        this.uiObjects.component[component.id] = $elem;
    }
    return $elem;
};
/**
 * @param {LW.Question} question
 * @returns {LWF.KeyUi.QuestionUi}
 */
LWF.KeyUi.prototype.getQuestionUi = function ( question ) {
    var $elem = this.uiObjects.question[question.id];
    if ( !$elem ) {
        $elem = new LWF.KeyUi.QuestionUi( question );
        this.uiObjects.question[question.id] = $elem;

        var character;
        for ( var c = 0, C = question.characters.length; c < C; c++ ) {
            character = this.getCharacterUi( question.characters[c], false );
            $elem.addCharacter(character);
        }
    }
    return $elem;
};
/**
 * @param {LW.Character} character
 * @param {bool} existingOnly If true, the UI will not be created if it does not exist, and undefined is returned.
 * @returns {LWF.KeyUi.CharacterUi}
 */
LWF.KeyUi.prototype.getCharacterUi = function ( character, existingOnly ) {
    var keyUi = this;
    var dKey = this.dKey;
    var $elem = this.uiObjects.character[character.id];
    if ( !$elem && !existingOnly ) {
        $elem = new LWF.KeyUi.CharacterUi( character, function ( character, selected ) {
            keyUi.lastClickedCharacterUi = this;
            dKey.choices.character.set( character.id, selected, false );
        } );
        this.uiObjects.character[character.id] = $elem;
    }
    return $elem;
};
LWF.KeyUi.prototype.getTaxonTreePage = function () {
    var keyUi = this;
    if ( !this.ui.$taxonTreePage ) {
        this.uiObjects.taxonTree = new LWUI.TreeMatrix( {
            titleHeight: 40,
            rowHeight: 30,
            padding: 20,
            lineLength: 15,
            lineWidth: 2,
            data: LW.root.taxonModel.treeMatrix(),
            callback: function ( id, selectedIDs ) {
                keyUi.dKey.choices.taxon.setAll( selectedIDs );
            }
        } );
        this.ui.$taxonTreePage = $( LWF.KeyUi.tplSpecialPage( {
            title: keyUi.t('title.taxon-tree') || 'Taxonomic Tree',
            resetButton: true
        } ) );


        var $body = $( '.specialBody', this.ui.$taxonTreePage );
        $body.append( '<p>Im taxonomischen Stammbaum können Taxa spezifisch zur Bestimmung ausgewählt werden. </p>' );
        $body.append( this.uiObjects.taxonTree.dom );

        var treeMatrix = this.uiObjects.taxonTree;
        var $reset = $( '.resetButton', this.ui.$taxonTreePage );
        this.ui.$taxonTreePage.redefineHandlers = function () {
            $reset.off( 'click' );
            $reset.on( 'click',function () {
                console.log( 'Unselecting all' );
                treeMatrix.unselectAll();
            } ).attr( 'title', keyUi.t('ui.reset-selection') || 'Reset Selection' );
        }
    }
    return this.ui.$taxonTreePage;
};
LWF.KeyUi.prototype.getAboutPage = function () {
    if ( !this.ui.$aboutPage ) {
        this.ui.$aboutPage = $( LWF.KeyUi.tplSpecialPage( {
            title: this.t( 'button.about' ) || 'About'
        } ) );
        $( '.specialBody', this.ui.$aboutPage ).append(
            '<div>' +
                '<h2>Download</h2>' +
                '<p>Dieses Projekt besteht aus 3 Einzelprojekten:</p>' +
                '<ul>' +
                '<li>dem Kern, der die Algorithmen für die Bestimmung enthält; Git-Repository:<br/><code>https://gerrit.wikimedia.org/r/mediawiki/extensions/LifeWebCore</code></li>' +
                '<li>dem Benutzerinterface mit MySQL-Datenbank und Daten; Git-Repository: <br/><code>git://granjow.net/maCode.git</code></li>' +
                '<li>und einer MediaWiki-Erweiterung als Alternative zum vorherigen Projekt; <a href="https://www.mediawiki.org/wiki/Extension:LifeWeb">Link</a> / ' +
                    'Git-Repository:<br/> <code>https://gerrit.wikimedia.org/r/mediawiki/extensions/LifeWeb</code> </li>' +
                '</ul>' +
                '<h2>Autor</h2>' +
                '<p>Ich bin <a href="http://granjow.net">Simon A. Eugster</a> und habe diesen Bestimmungsschlüssel als Masterarbeit an der ETH Zürich geschrieben.</p>' +
            '</div>'
        );
    }
    return this.ui.$aboutPage;
};
LWF.KeyUi.prototype.getSettingsPage = function() { // \todo filter radios: double events?

    var $components,
        $jump,
        $dfs,
        $topics,
        $questionFilter;
    var keyUi = this;

    if ( !this.ui.$settingsPage ) {

        this.ui.$settingsPage = $( LWF.KeyUi.tplSpecialPage( {
            title: keyUi.t( 'button.settings' ) || 'Einstellungen'
        } ) );

        $components = $(
            '<label><input type="checkbox" id="cbUseComponents"/>' +
                'Fragen gruppieren in Kategorien' +
                '</label>'
            );
        $dfs = $(
            '<label><input type="checkbox" id="cbDfs"/>' +
                'Verwandte Fragen untereinander anzeigen (nur mit Kategorien)' +
                '</label>'
        );
        $jump = $(
            '<label><input type="checkbox" id="cbJumpToSelected"/>' +
                'Zum ausgewählten Merkmal springen nach Beantworten einer Frage' +
            '</label>'
        );
        $topics = $(
            '<label><select id="selectTopic"></select>' +
                ' Thema ändern' +
            '</label>'
        );
        $questionFilter = $(
            '<label id="questionFilter">' +
                'Filter für Fragen:' +
            '</label>'
        );

        var $topicSelect = $( 'select', $topics );
        for ( var tid in LW.root.topicModel.topic ) {
            if ( LW.root.topicModel.topic.hasOwnProperty( tid ) ) {
                var topic = LW.root.topicModel.topic[tid];
                $topicSelect.append( $( '<option value="' + tid + '" >' + topic.name + '</option>' ) );
            }
        }

        $questionFilter.append(
            '<label><input type="radio" name="questionFilter" value="allDistinctive"/>' +
                'Alle verwendeten Fragen anzeigen' +
            '</label>'
        );
        $questionFilter.append(
            '<label><input type="radio" name="questionFilter" value="distinctiveWithoutChildren"/>' +
                'Genauere Fragen erst bei Bedarf anzeigen' +
            '</label>'
        );

        $( '.specialBody', this.ui.$settingsPage )
            .append( '<h2>Bestimmungsschlüssel</h2>' )
            .append( $components )
            .append( $dfs )
            .append( $jump )
            .append($questionFilter)
            .append( '<h2>Thema</h2>' )
            .append( $topics );

        this.ui.$settingsPage.redefineHandlers = function () {
            $components.off( 'change' );
            $components.on( 'change', function () {
                keyUi.options.useComponents = !keyUi.options.useComponents;
                keyUi.storeSettings();

                keyUi.dKey.recalculate();
            } );
            $dfs
                .off( 'change' )
                .on( 'change', function () {
                    keyUi.options.dfs = !keyUi.options.dfs;
                    keyUi.storeSettings();

                    keyUi.dKey.recalculate();
                } );
            $jump
                .off( 'change' )
                .on( 'change', function () {
                    keyUi.options.jumpToSelected = !keyUi.options.jumpToSelected;
                    keyUi.storeSettings();
                } );
            $topicSelect
                .off( 'change' )
                .on( 'change', function ( ) {
                    keyUi.loadTopic( $( this ).val() );
                    keyUi.storeSettings();
                } );
            $( 'input', $questionFilter )
                .off( 'change' )
                .on( 'change', function () {
                    if ( $( this ).prop( 'checked' ) ) {
                        var newQuestionFilter = $( this ).val();

                        console.log( 'Changing question filter to ' + newQuestionFilter );
                        keyUi.options.dkeyOptions['questionFilter'] = newQuestionFilter;
                        keyUi.dKey.loadQuestionFilter( newQuestionFilter );
                        keyUi.storeSettings();

                        keyUi.dKey.recalculate();
                    }
                })
        }
    }

    // Update view
    $components = $( '#cbUseComponents', this.ui.$settingsPage );
    $components.prop( 'checked', keyUi.options.useComponents );

    $dfs = $( '#cbDfs', this.ui.$settingsPage );
    $dfs.prop( 'checked', keyUi.options.dfs );

    $jump = $( '#cbJumpToSelected', this.ui.$settingsPage );
    $jump.prop( 'checked', keyUi.options.jumpToSelected );

    $( '#selectTopic', this.ui.$settingsPage ).val( LW.root.currentTopic && LW.root.currentTopic.id || undefined );
    $( '#questionFilter input', this.ui.$settingsPage ).each( function () {
        var $this = $( this );
        $this.prop( 'checked', $this.val() == keyUi.dKey.options.questionFilter );
    } );

    return this.ui.$settingsPage;
};
LWF.KeyUi.prototype.hideSpecialPage = function () {
    $( '.button', this.$structure ).removeClass( 'active' );
    $( '#treeUiSpecialContent', this.$structure ).addClass( 'hidden' );
};
/**
 * @param $dom Special page to show. The template LWF.KeyUi.tplSpecialPage should be used for it.
 * @param $button Optional; will be marked as active
 */
LWF.KeyUi.prototype.showSpecialPage = function ( $dom, $button ) {
    var keyUI = this;
    $( '#treeUiSpecialContent', this.$structure ).removeClass( 'hidden' ).empty().append( $dom );
    $( '.closeButton', $dom ).unbind( 'click' ).bind( 'click', function () {
        keyUI.hideSpecialPage();
    } );
    if ( $button ) {
        $button.addClass( 'active' );
    }
    // Re-adds event handlers (get lost after calling unbind())
    if ($dom.redefineHandlers) {
        $dom.redefineHandlers();
    }
};

/**
 * Taxon thumbnail
 * @param { LW.Taxon } taxon
 * @param { function( LW.Taxon ) } detailsPageCallback
 * @param { function( title: String, images: Array.<String>, index: Number ) } galleryCallback
 * @constructor
 */
LWF.KeyUi.TaxonUi = function ( taxon, detailsPageCallback, galleryCallback ) {

    this.taxon = taxon;
    this.$dom = $( this.tpl( {
        name: taxon.name,
        image: taxon.description.images[0]
    } ) );

    this.ui = {
        $match: $( '.match', this.$dom ),
        $mismatch: $( '.mismatch', this.$dom )
    };

    this.rating = {
        ok: [],
        wrong: [],
        unknown: [],
        unset: []
    };

    /// @type { function( $dom ) }
    this.detailsPageCallback = detailsPageCallback || function() {};
    /// @type { function( title: String, images: Array.<String>, index: Number ) }
    this.galleryCallback = galleryCallback || function() {};

    this.loadRating( [], [], [] );

};
LWF.KeyUi.TaxonUi.prototype.redefineHandlers = function () {
    var ui = this;
    this.$dom.off( 'click' );
    this.$dom.on( 'click', function () {
        ui.clicked();
    } );
};
LWF.KeyUi.TaxonUi.prototype.clicked = function () {
    this.detailsPageCallback( this.taxon );
};
LWF.KeyUi.TaxonUi.prototype.loadRating = function ( ok, wrong, unknown ) {
    this.rating.ok = ok;
    this.rating.wrong = wrong;
    this.rating.unknown = unknown;
    this.rating.unset = [];

    var N = ok.length + wrong.length + unknown.length;
    this.ui.$match.css( 'width', ( (100 * ok.length / N) || 0 ) + '%' );
    this.ui.$mismatch.css( 'width', ( (100 * wrong.length / N) || 0 ) + '%' );

    for ( var c = 0, C = this.taxon.characters.length; c < C; c++ ) {
        var character = this.taxon.characters[c];
        if ( ok.indexOf( character ) >= 0 ||
            wrong.indexOf( character ) >= 0 ||
            unknown.indexOf( character ) >= 0 ) {
            continue;
        }
        this.rating.unset.push( character );
    }
};
LWF.KeyUi.TaxonUi.prototype.getSpecialPage = function () {
    var $content = $( this.tplDetailsContent( {
        name: this.taxon.name,
        text: this.taxon.description.text,
        images: this.taxon.description.images,
        match: this.rating.ok,
        mismatch: this.rating.wrong,
        unknown: this.rating.unknown,
        unset: this.rating.unset
    } ) );
    var ui = this;
    var $page = $( LWF.KeyUi.tplSpecialPage( {
        title: this.taxon.name
    } ) );
    $( '.specialBody', $page ).append( $content );
    $page.redefineHandlers = function () {
        $( '.galleryContainer img', $page ).each( function ( index ) {
            var $this = $( this );
            $this.off( 'click' );
            $this.on( 'click', function () {
                ui.openGallery( index );
            } )
        } )
    };
    return $page;
};
LWF.KeyUi.TaxonUi.prototype.openGallery = function(index) {
    this.galleryCallback(this.taxon.name, this.taxon.description.images, index);
};
/**
 * Fullscreen gallery
 * @constructor
 */
LWF.KeyUi.GalleryUi = function () {

    /// @type {Array.<String>}
    this.images = [];
    this.$dom = $( this.tplGalleryPage( {
        name: 'Gallery'
    } ) );

    this.index = 0;
    this.ui = {
        $image: $( '.image', this.$dom ),
        $h2: $( 'h2', this.$dom )
    };

    this.hide();


};
LWF.KeyUi.GalleryUi.prototype.show = function () {
    this.$dom.removeClass( 'disabled' );

    var ui = this;
    $( '.nextImage', this.$dom ).off( 'click' ).on( 'click', function () {
        ui.next();
    } );
    $( '.prevImage', this.$dom ).off( 'click' ).on( 'click', function () {
        ui.prev();
    } );
    $( '.closeButton', this.$dom ).off( 'click' ).on( 'click', function () {
        ui.hide();
    } );
};
/**
 * @returns {boolean} true if the gallery has been hidden, false if it is hidden already.
 */
LWF.KeyUi.GalleryUi.prototype.hide = function () {
    if ( !this.isVisible() ) {
        return false;
    } else {
        this.$dom.addClass( 'disabled' );
        return true;
    }
};
LWF.KeyUi.GalleryUi.prototype.isVisible = function() {
    return !this.$dom.hasClass( 'disabled' );
};

LWF.KeyUi.GalleryUi.prototype.loadGallery = function ( title, images ) {
    this.ui.$h2.text( title );
    this.images = images;
    this.show();
    this.loadImage( 0 );
};
LWF.KeyUi.GalleryUi.prototype.loadImage = function ( index ) {
    this.index = index;
    if ( index < 0 ) { this.index = 0; }
    if ( index >= this.images.length ) { this.index = this.images.length - 1; }

    console.log('Loading index ' + this.index + ', image: ' + this.images[this.index], this.images);

    this.ui.$image.css( 'background-image', 'url(' + this.images[this.index] + ')' );
};
LWF.KeyUi.GalleryUi.prototype.next = function () {
    this.loadImage( this.index + 1 );
};
LWF.KeyUi.GalleryUi.prototype.prev = function () {
    this.loadImage( this.index - 1 );
};
LWF.KeyUi.GalleryUi.prototype.tplGalleryPage = Handlebars.compile(
    '<div class="fullscreenContent">' +
        '<h2>{{name}}</h2>' +
        '<div class="fullscreenImage">' +
        '<div class="image"></div>' +
        '<div class="nextImage"></div>' +
        '<div class="prevImage"></div>' +
        '<div class="closeButton"></div>' +
    '</div>'
);
// Update: { ok } { wrong } { unknown }
LWF.KeyUi.TaxonUi.prototype.tpl = Handlebars.compile(
    '<div class="taxonThumbnail">' +
        '<h4>{{name}}</h4>' +
        '<div class="taxonRating">' +
            '<div class="match"></div>' +
            '<div class="mismatch"></div>' +
        '</div>' +
        '{{#if image}}<div class="image"><img src="{{image}}"/></div>{{/if}}' +
    '</div>'
);
LWF.KeyUi.TaxonUi.prototype.tplDetailsContent = Handlebars.compile(
    '<div>' +
        '<div class="galleryContainer"><table class="galleryBody"><tr>' +
            '{{#each images}}' +
            '<td><img src="{{this}}"/></td>' +
            '{{/each}}' +
        '</tr></table></div>' +
        '<p>{{{text}}}</p>' +
        '<h2>Merkmale</h2>' +
        '{{#if match}}<p>Zutreffende Merkmale</p><ul class="characters match">' +
            '{{#each match}}' +
            '<li><dl><dt>{{name}}</dt>{{#if description.text}}<dd>{{description.text}}</dd>{{/if}}</dl></li>' +
            '{{/each}}' +
        '</ul>{{/if}}' +
        '{{#if mismatch}}<ul class="characters mismatch">' +
            '{{#each mismatch}}' +
            '<li><dl><dt>{{name}}</dt>{{#if description.text}}<dd>{{description.text}}</dd>{{/if}}</dl></li>' +
            '{{/each}}' +
        '</ul>{{/if}}' +
        '{{#if unknown}}<p>Unbekannte Merkmale</p><ul class="characters unknown">' +
            '{{#each unknown}}' +
            '<li><dl><dt>{{name}}</dt>{{#if description.text}}<dd>{{description.text}}</dd>{{/if}}</dl></li>' +
            '{{/each}}' +
        '</ul>{{/if}}' +
        '{{#if unset}}<p>Zur Bestimmung nicht verwendete Merkmale</p><ul class="characters unset">' +
            '{{#each unset}}' +
            '<li><dl><dt>{{name}}</dt>{{#if description.text}}<dd>{{description.text}}</dd>{{/if}}</dl></li>' +
            '{{/each}}' +
        '</ul>{{/if}}' +
    '</div>'
);

// Update: { selected, visible }
LWF.KeyUi.CharacterUi = function ( character, callback ) {
    if ( !(this instanceof LWF.KeyUi.CharacterUi) ) { return new LWF.KeyUi.CharacterUi( character ); }

    /// @type {LW.Character}
    this.character = character;
    /// @type {function(LW.Character, bool)}
    this.callback = callback;

    this.$dom = $( this.tpl( {
        name: character.name,
        text: character.description.text,
        image: character.description.images[0]
    } ) );

    return this;
};
LWF.KeyUi.CharacterUi.prototype.loadRating = function ( selected, visible ) {
    if ( selected ) {
        this.$dom.addClass( 'checked' );
    } else {
        this.$dom.removeClass( 'checked' );
    }
    if ( !visible ) {
        this.$dom.addClass( 'hidden' );
    } else {
        this.$dom.removeClass( 'hidden' );
    }
};
LWF.KeyUi.CharacterUi.prototype.redefineHandlers = function () {
    var ui = this;
    this.$dom.off( 'click' );
    this.$dom.on( 'click', function () {
        ui.clicked();
    } );
};
LWF.KeyUi.CharacterUi.prototype.clicked = function () {
    if ( this.$dom.hasClass( 'hidden' ) && !this.$dom.hasClass( 'checked' ) ) {
        console.log( 'Hidden; ignoring click event.' );
    } else {
        this.$dom.toggleClass( 'checked' );
        this.callback( this.character, this.$dom.hasClass( 'checked' ) );
    }
};
LWF.KeyUi.CharacterUi.prototype.tpl = Handlebars.compile(
    '<div class="character">' +
        '<h4>{{name}}</h4>' +
        '{{#if text}}<div class="characterDescription">{{text}}</div>{{/if}}' +
        '{{#if image}}<div class="characterImage"><img src="{{image}}"/></div>{{/if}}' +
    '</div>'
);

// Update: { visible }
LWF.KeyUi.QuestionUi = function ( question ) {
    if ( !(this instanceof LWF.KeyUi.QuestionUi) ) { return new LWF.KeyUi.QuestionUi( question ); }

    this.question = question;
    this.$dom = $( this.tpl( {
        name: question.name,
        text: question.description.text
    } ) );

    this.ui = {
        $characters: $( '.characters', this.$dom )
    };

    /// @type {[LWF.KeyUi.CharacterUi]}
    this.characters = [];

    return this;
};
/**
 * @param {LWF.KeyUi.CharacterUi} character
 */
LWF.KeyUi.QuestionUi.prototype.addCharacter = function ( character ) {
    this.characters.push( character );
    this.ui.$characters.append( character.$dom );
};
LWF.KeyUi.QuestionUi.prototype.redefineHandlers = function() {
    for (var c = 0, C = this.characters.length; c < C; c++) {
        this.characters[c].redefineHandlers();
    }
};
LWF.KeyUi.QuestionUi.prototype.tpl = Handlebars.compile(
    '<div class="question">' +
        '<h3>{{name}}</h3>' +
        '{{#if text}}<div class="questionDescription">{{text}}</div>{{/if}}' +
        '<div class="characters"></div>' +
    '</div>'
);

LWF.KeyUi.ComponentUi = function ( component, callback ) {

    this.component = component;
    this.callback = callback || function() {};
    this.$dom = $( this.tpl( {
        name: component.name,
        text: component.description.text
    } ) );

    this.ui = {
        $questions: $( '.questions', this.$dom ),
        $gallery: $( '.componentGallery', this.$dom ),
        $status: $( '.componentStatus', this.$dom )
    };

    for ( var i = 0, I = component.description.images.length; i < I; i++ ) {
        // \todo Handle other image formats
        this.ui.$gallery.append( '<object type="image/svg+xml" data="' + component.description.images[i] + '"></object>' );
    }


};
LWF.KeyUi.ComponentUi.prototype.loadRating = function ( answered, total ) {
    this.ui.$status.text( '(' + answered + '/' + total + ')' );
};
LWF.KeyUi.ComponentUi.prototype.isVisible = function() {
    return !this.$dom.hasClass('collapsed');
};
LWF.KeyUi.ComponentUi.prototype.setVisible = function ( visible ) {
    if ( visible ) {
        this.$dom.removeClass( 'collapsed' );
    } else {
        this.$dom.addClass( 'collapsed' );
    }
};
LWF.KeyUi.ComponentUi.prototype.empty = function () {
    this.ui.$questions.empty();
};
LWF.KeyUi.ComponentUi.prototype.append = function ( $elem ) {
    this.ui.$questions.append( $elem );
};
/**
 * @param {LWF.KeyUi.QuestionUi} question
 */
LWF.KeyUi.ComponentUi.prototype.appendQuestion = function ( question ) {
    this.ui.$questions.append( question.$dom );
};
LWF.KeyUi.ComponentUi.prototype.setClickHandler = function () {
    var ui = this;
    var $header = $( 'header', this.$dom );

    $header.off( 'click' );
    $header.on( 'click', function () {
        ui.callback( ui.component );
    } );
};
LWF.KeyUi.ComponentUi.prototype.tpl = Handlebars.compile(
    '<div class="component">' +
        '<header>' +
            '<h2>{{name}}</h2>' +
            '<div class="componentStatus"></div>' +
        '</header>' +
        '<div class="componentDescription">{{text}}</div>' +
        '<div class="componentGallery">{{gallery}}</div>' +
        '<div class="questions"></div>' +
    '</div>'
);
LWF.KeyUi.tplSpecialPage = Handlebars.compile(
        '<div>' +
            '<div class="specialHeader">' +
                '<h1>{{title}}</h1>' +
                '<div class="button closeButton"></div>' +
                '{{#if resetButton}}<div class="button resetButton"></div>{{/if}}' +
            '</div>' +
            '<div class="specialContainer">' +
                '<div class="specialBody"></div>' +
            '</div>' +
        '</div>'
);
LWF.KeyUi.prototype.tpl = Handlebars.compile(
    '<div id="treeUI" class="mainContainer">' +
        '<div class="header">' +
            '<div class="button" id="taxonTreeButton">' +
                '<div class="text">Taxa</div>' +
                '<div class="image"><img src="images/buttonTaxon.png"/></div>' +
            '</div>' +
            '<div class="button" id="settingsButton">' +
                '<div class="text">Settings</div>' +
                '<div class="image"><img src="images/buttonSettings.png"/></div>' +
            '</div>' +
            '<div class="button" id="aboutButton">' +
                '<div class="text">About</div>' +
                '<div class="image"><img src="images/buttonAbout.png"/></div>' +
            '</div>' +
            '<div class="button" id="newIdentificationButton">' +
                '<div class="text">New Identification</div>' +
                '<div class="image"><img src="images/buttonNew.png"/></div>' +
            '</div>' +
            '<div class="button" id="searchByName">' +
                '<label for="nameSearchString" class="text">Search</label>' +
                '<input type="search" id="nameSearchString"/>' +
                '<div class="image"><img src="images/buttonClearSearch.png" id="clearNameSearch"/></div>' +
            '</div>' +
        '</div>' +
        '<div class="body">' +
            '<div class="bodyContent">' +

                '<div class="leftContainer"><div class="leftBody">' +
                    '<div id="treeUiContent">' +
                        '<div class="introduction">' +
                            '<h1>Bestimmungsschlüssel für Bäume</h1>' +
                            '<p>What is that tree? This identification key will help you find the correct plant.</p>' +
                        '</div>' +
                        '<div class="mainKey">' +
                            // Components, questions, etc. go here
                        '</div>' +
                    '</div>' +
                    '<div id="treeUiSpecialContent" class="hidden">' +
                    '</div>' +
                '</div></div>' +

                '<div class="rightContainer"><div class="rightBody">' +
                    // Taxon thumbnails go here.
                '</div></div>' +

            '</div>' +
        '</div>' +
        '<div id="fullscreenGallery">' +
            '<div class="fullscreenContent">' +
                '<h2>Pinus cembra</h2>' +
                '<div class="fullscreenImage">' +
                    '<div class="image"></div>' +
                    '<div class="nextImage"></div>' +
                    '<div class="prevImage"></div>' +
                    '<div class="closeButton"></div>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>'
);