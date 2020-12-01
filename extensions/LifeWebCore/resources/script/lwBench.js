LWF.Benchmark = function ( dkeyOptions ) {
    if ( !(this instanceof LWF.Benchmark) ) { return new LWF.Benchmark( dkeyOptions ); }

    this.$dom = $( '<div></div>' );
    this.dkeyOptions = dkeyOptions;
    // @type {LW.Degree}
    this.speciesDegree = null;


    this.$dom.css( 'font-family', 'monospace' );

    return this;
};
LWF.Benchmark.prototype.init = function () {
    var promise = $.Deferred();
    var bench = this;
    LW.root.init().done( function () {
        LW.root.setTopic( LW.root.topicModel.topic[1] );
        bench.dKey = new LWF.DKey( bench.dkeyOptions );
        bench.speciesDegree = LW.root.degreeModel.get( 'species' );
        if ( bench.speciesDegree ) {
            bench.dKey.choices.degree.set( bench.speciesDegree.id, true );
        } else {
            throw Error( 'Could not find species.' );
        }
        promise.resolve();
    } );
    return promise;
};
/**
 * @param {String} questionCostMethod
 * @returns {{results: {}, taxonNumber: number, totalSteps: number, methodName: String}}
 */
LWF.Benchmark.prototype.runBench = function ( questionCostMethod ) {

    var start = new Date().getTime();

    var dKey = new LWF.DKey( this.dkeyOptions );
    dKey.choices.degree.set( this.speciesDegree.id, true );


    this.$dom.append( '<h2>' + LWF.QuestionCosts[questionCostMethod].name + '</h2>' );
    console.log( 'Running benchmark ...' );

    var bench = this;
    var benchData = {
        results: {},
        taxonNumber: 0,
        totalSteps: 0,
        methodName: LWF.QuestionCosts[questionCostMethod].name
    };


    // Get the number of taxa
    var taxonVisitor = dKey.buildTaxonVisitor();
    taxonVisitor.taxonVisitor = function ( taxon, accepted ) {

        if ( accepted ) {
            benchData.taxonNumber++;
        }
    };
    LW.root.taxonModel.visit( taxonVisitor );
    this.$dom.append( '<p>' + benchData.taxonNumber + ' taxa in total.</p>' );


    // Calculate the number of steps until identification for each taxon
    var $listDom = $( '<ol class="bench"></ol>' ),
        $listEntry,
        $details;
    this.$dom.append( $listDom );
    taxonVisitor.taxonVisitor = function ( taxon ) {

        var data = bench.runTaxonBench( taxon, questionCostMethod );
        benchData.results[taxon.id] = data;
        benchData.totalSteps += data.stepsTaken;

        $listEntry = $( '<li>' + data.name + ': ' + data.remainingTaxa + ' remaining after ' + data.stepsTaken +
            ' steps: ' + JSON.stringify( data.steps ) + '</li>' );
        if ( data.remainingTaxa <= 1 ) {
            $listEntry.addClass( 'identified' );
        } else {
            $listEntry.addClass( 'unidentified' );
        }

        $details = $( '<ol class="details"></ol>' );
        for ( var c = 0, C = data.characters.length; c < C; c++ ) {
            $details.append( $( '<li>' + data.characters[c] + '</li>' ) );
        }
        $listEntry.append( $details );

        $listDom.append( $listEntry );

    };
    LW.root.taxonModel.visit( taxonVisitor );


    var end = new Date().getTime();
    this.$dom.append( $( '<p>Total steps: <strong>' + benchData.totalSteps + '</strong></p>' ) );
    this.$dom.append( $( '<p>dt: ' + (end - start) + ' ms</p>' ) );

    return benchData;
};
/**
 * @param {LW.Taxon} taxon
 * @param {String} questionCostMethod
 * @returns {{name: String, steps: Array.<Number>, characters: Array.<String>, stepsTaken: Number}}
 */
LWF.Benchmark.prototype.runTaxonBench = function ( taxon, questionCostMethod ) {

    var options = {};
    for ( var oid in this.dkeyOptions ) {
        if ( this.dkeyOptions.hasOwnProperty( oid ) ) {
            options[oid] = this.dkeyOptions[oid];
        }
    }
    options.questionCost = questionCostMethod;

    var dKey = new LWF.DKey( options );
    dKey.choices.degree.set( this.speciesDegree.id, true );
    console.log( 'Question cost: ' + dKey.questionCost.name );


    var benchData = {
        name: taxon.id + '|' + taxon.name,
        steps: [],
        characters: [],
        details: [],
        questionDetails: [],
        skipped: [],
        characterDebug: [],
        stepsTaken: 0,
        remainingTaxa: Infinity
    };

    console.log( 'Benchmarking taxon: ' + taxon.name );

    var qcVisitor;
    var characterChosen = true,
        logOnly;
    var characters = [];
    var characterDebug = [];
    // Chose characters as long as the taxon has some left
    while ( characterChosen ) {

        // Get a list of characters that are not used yet, sorted by priority
        characters = [];
        characterDebug = [];
        qcVisitor = dKey.buildQCVisitor();
        qcVisitor.options.useComponents = false;
        qcVisitor.options.dfs = false;
        qcVisitor.preorderCharacterVisitor = function ( character ) {
            if ( !dKey.choices.character.isSet( character.id ) ) {
                characters.push( character );
                characterDebug.push( {
                    eH: dKey.lastRatings.question[character.parentQuestionID].cost.expectedH,
                    work: dKey.lastRatings.question[character.parentQuestionID].cost.workDone
                } )
            }
        };
        LW.root.visit( qcVisitor );
        //console.log(characterDebug);

        // Chose the first (highest rated) character
        characterChosen = false;
        logOnly = false;
        var skipped = [];
        for ( var c = 0, C = characters.length; c < C; c++ ) {
            if ( taxon.characters.indexOf( characters[c] ) >= 0 && !logOnly ) {

                var minH = 100,
                    minQ = null,
                    work = 0;
                for ( var qid in dKey.lastRatings.question ) {
                    if ( dKey.lastRatings.question.hasOwnProperty( qid ) ) {
                        var h = dKey.lastRatings.question[qid].cost.expectedH;
                        if ( h < minH ) {
                            minH = h;
                            minQ = dKey.lastRatings.question[qid].question;
                            work = dKey.lastRatings.question[qid].cost.workDone;
                        }

                    }
                }

                benchData.details.push( JSON.stringify( dKey.lastRatings.character[characters[c].id].cost ) );
                benchData.questionDetails.push( JSON.stringify( dKey.lastRatings.question[characters[c].parentQuestionID].cost ) +
                    ', min H: ' + minH + ' for question: ' + (minQ ? minQ.name : '[none]') + '; work: ' + work );
                benchData.characters.push( characters[c].name );

                dKey.choices.character.set( characters[c].id, true, false );
                characterChosen = true;

                logOnly = true;
                skipped.push( ' ---REST--- ');

                //break;

            } else {
                skipped.push( '[' + JSON.stringify(dKey.lastRatings.question[characters[c].parentQuestionID].cost.workDone) + ']' + characters[c].parentQuestion.name );
            }
        }

        benchData.skipped.push(skipped);
        benchData.characterDebug.push(characterDebug);

        var taxonVisitor = dKey.buildTaxonVisitor();
        var acceptedCount = 0;
        taxonVisitor.taxonVisitor = function ( taxon, accepted ) {
            if ( accepted ) acceptedCount++;
        };
        LW.root.taxonModel.visit( taxonVisitor );
        benchData.steps.push( acceptedCount );
    }

    // Count the number of steps until no further narrowing is possible
    if ( benchData.steps.length > 0 ) {
        var lastCount = benchData.steps[benchData.steps.length - 1];
        var stepsTaken = 0;
        var s;
        for ( s = benchData.steps.length - 1; s >= 0; s-- ) {
            if ( benchData.steps[s] != lastCount ) {
                stepsTaken = s + 2;
                break;
            }
        }
        benchData.stepsTaken = stepsTaken;
        benchData.remainingTaxa = lastCount;
    }

    return benchData;
};
LWF.Benchmark.prototype.comparisonData = function ( resultsA, resultsB ) {
    var data = {
        names: [ resultsA.methodName, resultsB.methodName ],
        values: []
    };
    for ( var rid in resultsA.results ) {
        if ( resultsA.results.hasOwnProperty( rid ) &&
            resultsB.results.hasOwnProperty( rid ) ) {
            data.values.push( {
                name: resultsA.results[rid].name,
                values: [
                    resultsA.results[rid].stepsTaken,
                    resultsB.results[rid].stepsTaken
                ]
            } );
        }
    }
    return data;
};

LWUI.DoubleBarChart = function ( options ) {

    this.lineHeight = options.lineHeight || 16;
    this.barWidth = options.barWidth || 16;
    this.barSpacing = options.barSpacing || 16;

    this.labelWidth = options.labelWidth || 180;
    this.chartWidth = options.chartWidth || 600;

    this.lowerIsBetter = !!options.lowerIsBetter;
    this.data = options.data;

    this.dom = $( '<div class="lwuiDoubleBarChart"></div>' );

    this.paint();
};
LWUI.DoubleBarChart.prototype.paint = function () {

    var dText = .4 * this.lineHeight;

    var hHeader = 4 * this.lineHeight;
    var hEntry = 2 * this.barWidth + this.barSpacing;
    var H = hHeader + this.data.values.length * hEntry;

    var canvas = SVG( this.dom[0] )
        .size( this.chartWidth, H )
        .attr( 'class', 'lwuiDoubleBarChart' );

    var graphWidth = this.chartWidth - this.labelWidth;

    canvas.rect( 2 * this.lineHeight, this.lineHeight )
        .move( this.labelWidth, 0 )
        .attr( 'class', 'fillA' );
    canvas.rect( 2 * this.lineHeight, this.lineHeight )
        .move( this.labelWidth, 1.5 * this.lineHeight )
        .attr( 'class', 'fillB' );
    canvas.text( this.data.names[0] )
        .move( this.labelWidth + 3 * this.lineHeight, 0 - dText )
        .attr( 'class', 'legend' );
    canvas.text( this.data.names[1] )
        .move( this.labelWidth + 3 * this.lineHeight, 1.5 * this.lineHeight - dText )
        .attr( 'class', 'legend' );

    var i, I;
    var maxValue = 0;
    for ( i = 0, I = this.data.values.length; i < I; i++ ) {
        if ( this.data.values[i].values[0] > maxValue ) {
            maxValue = this.data.values[i].values[0];
        }
        if ( this.data.values[i].values[1] > maxValue ) {
            maxValue = this.data.values[i].values[1];
        }
    }
    var negate = this.lowerIsBetter ? +1 : -1;
    this.data.values.sort( function ( a, b ) {
        var dA = a.values[1] - a.values[0],
            dB = b.values[1] - b.values[0];
        return negate * (dB - dA);
    } );

    var scale = graphWidth / maxValue;
    var entry, delta;
    for ( i = 0, I = this.data.values.length; i < I; i++ ) {
        entry = this.data.values[i];
        delta = entry.values[1] - entry.values[0];

        canvas.text( this.data.values[i].name )
            .move( this.labelWidth - this.lineHeight, hHeader + i * hEntry - dText )
            .attr( 'text-anchor', 'end' );
        canvas.text( delta > 0 ? '+' + String( delta ) : String( delta ) )
            .move( this.labelWidth - this.lineHeight, hHeader + i * hEntry + this.lineHeight )
            .attr( 'text-anchor', 'end' )
            .attr( 'font-size', '.8em' );
        canvas.rect( scale * entry.values[0], this.barWidth )
            .move( this.labelWidth, hHeader + i * hEntry )
            .attr( 'class', 'fillA' );
        canvas.rect( scale * entry.values[1], this.barWidth )
            .move( this.labelWidth, hHeader + i * hEntry + this.barWidth )
            .attr( 'class', 'fillB' );
    }
};