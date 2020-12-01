/**
 * JS code for all the interactive parts of the special page
 *
 * @author Clark Verbrugge
 * @license CC BY-SA 3.0
**/


/**
 * *************************
 * Global variables section.
 *****************************
**/

// For indexing the displayed HTML nodes with unique numbers used in highlighting.
var nindex = 0;
// Array of parameter representations.  Each is an object with name and row fields.
var params = [];
// For indexexing AST nodes with unique numbers.
var xindex = 0;
// Mapping from nindex to xindex.
var nTox = [];
// Root of the AST.
var ast;
// Array of things done for undoing.  Each entry is a string or an array of strings.
var lastUndo = [];
// Stack of previous states (frames) from descending into templates.
var nestingStack = [];
// A flag used to help debounce.
var busy = false;

// Constant: maximum length of the lastUndo array.
var maxUndos = 1000;
// Constant: timeout interval in ms used in making sequences of API calls.
var apiCallInterval = 30;
// Constant: the checkmark symbol used for a set parameter.
var argy = '\u2714';
// Constant: the x symbol used for an unset parameter.
var argn = '\u2718';
// Constant: symbol used to represent the initial frame in the crumbs list.
var firstcrumb = '\u2a00';
// Constant: symbol used to represent the link from one crumb to another
var nextCrumb = '\u27ff';

// A reference to the undo button, to avoid having to look it up each time.
var undoButtonNode;
// A reference to the undo-all button, to avoid having to look it up each time.
var resetButtonNode;

/**
 * ******************
 * Utility functions.
 * ******************
**/

/**
 * Adds the given string to the error message area.
 *
 * @param {string} s
 *
**/
function debugNote( s ) {
    debugNoteHTML( document.createTextNode ( s ) );
}

/**
 * Adds the given dom structure to the error message area.
 *
 * Also ensures length does not exceed a bound, and installs a clear button on the first message.
 *
 * @param {HTMLElement} s
**/
function debugNoteHTML( s ) {
    var d = document.getElementById( 'dt-error' );
    while ( d.childNodes.length > 10 ) {
        d.removeChild( d.firstChild.nextSibling );
    }
    if ( !document.getElementById( 'dt-error-button' ) ) {
        var b = document.createElement( 'input' );
        b.type = 'button';
        b.id = 'dt-error-button';
        b.value = mw.message( 'debugtemplates-error-button' );
        b.className = 'dt-error-button';
        b.addEventListener( 'click', debugNoteClear, false );
        d.appendChild( b );
    }
    d.appendChild( s );
    d.appendChild( document.createElement( 'br' ) );
}

/**
 * Clear the debug message area.
**/
function debugNoteClear() {
    var d = document.getElementById( 'dt-error' );
    while ( d.childNodes.length > 0 ) {
        d.removeChild( d.firstChild );
    }
}

/**
 * Set the output pane to something, discarding all previous content.
 *
 * @param {HTMLElement|null} x
**/
function setOutput( x ) {
    debugNoteClear();
    var dout = document.getElementById( 'dt-output' );
    while ( dout.hasChildNodes() ) {
        dout.removeChild( dout.lastChild );
    }
    if ( x ) {
        dout.appendChild( x );
    }
}

/**
 * Perform a POST operation.
 *
 * @param {string} url
 * @param {string} params Assumed to be URI-encoded.
 * @param {function} callback Callback upong completion. It will receive 1 or 2 arguments; if everything
 *  was ok then it receives "OK" and the result, and if not then it receives an error message.
**/
function doPost( url, params, callback ) {
    var x = new XMLHttpRequest();
    x.open( "POST", url, true );
    x.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );
    x.setRequestHeader( "Content-length", params.length );
    x.setRequestHeader( "Connection", "close" );
    x.setRequestHeader( "Api-User-Agent", "DebugTemplatesExtension/1.0" );

    x.onreadystatechange = function() {
        if ( x.readyState == 4 ) {
            if ( x.status == 200 ) {
                callback( "OK", x.responseText );
            } else {
                callback( "An error has occurred making the request" );
            }
        }
    };
    //debugNote("sending "+url+" and " + params);
    x.send(params);
}

/**
 * Asks the wiki API to parse the given text into XML.
 *
 * @param {string} t The string to parse; it will be URI-encoded.
 * @param {function} callback Receives 1 or 2 args with the JSON-encoded result, as per doPost.
**/
function apiParse( t, callback ) {
    var args = "action=expandtemplates&format=json&prop=parsetree";
    var title = document.getElementById( 'dt-title' ).value;
    if ( title ) {
        args = args + "&title=" + encodeURIComponent( title );
    }
    args = args + "&text=" + encodeURIComponent( t );
    //debugNote("Action is "+action);
    var url = document.getElementById( 'dt-api' ).value;
    doPost( url, args, callback );
}

/**
 * Asks the wiki API to parse the given text into wikitext.
 *
 * @param {string} t The string to parse; it will be URI-encoded.
 * @param {function} callback Receives 1 or 2 args with the JSON-encoded result, as per doPost.
**/
function apiEval( t, callback ) {
    //var args = "action=expandtemplates&format=json&prop=wikitext&includecomments=";
    var args = "action=expandframe&format=json";
    var title = document.getElementById( 'dt-title' ).value;
    if ( title ) {
        args = args + "&title=" + encodeURIComponent( title );
    }
    args = args + "&text=" + encodeURIComponent( t );
    args = args + "&frame=" + encodeURIComponent( assembleParams() );
    // debugNote("Action is "+args);
    var url = document.getElementById( 'dt-api' ).value;
    doPost( url, args, callback );
}

/**
 * Determines whether a proper result was obtained from an apiEval call.
 *
 * Note that this assumes an "OK" result.
 *
 * @param {object} result The object returned from the apiEval call, JSON-decoded.
 * @return {boolean}
**/
function apiEvalHasResult( result ) {
    if ( result.expandtemplates && result.expandtemplates.wikitext !== undefined ) {
        return true;
    } else if ( result.expandframe && result.expandframe.result !== undefined ) {
        return true;
    }
    return false;
}

/**
 * Returns the wikitext from a valided result obtained from an apiEval call.
 *
 * Note that this assumes an "OK" result and that apiEvalHasResult was true.
 *
 * @param {object} result The object returned from the apiEval call, JSON-decoded.
 * @return {string}
**/
function apiEvalGetResult( result ) {
    if ( result.expandtemplates && result.expandtemplates.wikitext !== undefined ) {
        return result.expandtemplates.wikitext;
    }
    return result.expandframe.result;
}

/**
 * Asks the wiki API to return the raw content of the given page.
 *
 * @param {string} t The page title to parse; it will be URI-encoded.
 * @param {function} callback Receives 1 or 2 args with the JSON-encoded result, as per doPost.
**/
function apiGetPage( t, callback ) {
    var args = "action=query&format=json&prop=revisions&rvprop=content&titles=" +
            encodeURIComponent( t );
    var url = document.getElementById( 'dt-api' ).value;
    doPost( url, args, callback);
}

/**
 * Asks the wiki API to return the full name of the template being invoked.
 *
 * @param {string} t Template name.
 * @param {function} callback Receives 1 or 2 args with the JSON-encoded result, as per doPost.
**/
function apiGetTemplateName( t, callback ) {
    var args = "action=parse&format=json&prop=templates&contentmodel=wikitext&text="
            + encodeURIComponent( t );
    var url = document.getElementById( 'dt-api' ).value;
    doPost( url, args, callback );
}

/**
 * Retrieves an XML parser, or null if it cannot find one.
 *
 * @return {function|null}
**/
function getXMLParser() {
    if ( typeof window.DOMParser != "undefined" ) {
        return function( xmlStr ) {
            return ( new window.DOMParser() ).parseFromString( xmlStr, "text/xml" );
        };
    }
    return null;
}

/**
 * Returns an HTML element for the given text.
 *
 * This may be a single text node, or a span with <br>'s inserted to mimic linebreaks.
 *
 * @param {string} txt Input plain text, possibly with linebreaks.
 * @param {string|null} cname Optional class name to put on the multi-line structure.
 * @return {HTMLElement}
**/
function textWithLinebreaks( txt, cname ) {
    var s = txt.split( '\n' );
    if ( s.length <= 1 ) {
        return document.createTextNode( s[0] );
    }
    var h = document.createElement( 'span' );
    if ( cname ) {
        h.className = cname;
    }
    h.appendChild( document.createTextNode( s[0] ) );
    for ( var i = 1; i < s.length; i++ ) {
        h.appendChild( document.createElement( 'br' ) );
        h.appendChild( document.createTextNode( s[i] ) );
    }
    return h;
}

/**
 * Determines in which mode a mouse-click should be interpretted.
 *
 * @return {string} One of "nothing", "eval", or "descend".
**/
function getMouseClickMode() {
    if ( document.getElementById( 'dt-radio-eval' ).checked ) {
        return 'eval';
    }
    if ( document.getElementById( 'dt-radio-descend' ).checked ) {
        return 'descend';
    }
    return "nothing";
}

/**
 * Set or unset the busy flag
 *
 * A true flag indicates that a potentially long, asynchronous operation is in place and other ones should
 * not be allowed to proceed until it is done.
 *
 * @param {boolean} b
**/
function setBusy( b ) {
    if ( b && !busy ) {
        busy = true;
        document.getElementById( 'dt-output' ).classList.add( 'dt-busy' );
    } else if ( !b && busy ) {
        busy = false;
        document.getElementById( 'dt-output' ).classList.remove( 'dt-busy' );
    }
}

/**
 * A fancy fade-in effect to make where text is replaced real obvious.
 *
 * @param {HTMLElement} p The element to apply it to.
**/
function fader( p ) {
    // We will create a padding that shrinks over time. This is the initial padding.
    var bp = '12';
    p.style.padding = bp + 'px';
    p.classList.add( 'dt-fading' );
    var intervaltag = window.setInterval( function() {
        var c = parseInt( p.style.padding, 10 );
        c--;
        if ( c <= 0 ) {
            if ( intervaltag ) {
                window.clearInterval( intervaltag );
            }
            intervaltag = null;
            p.style.padding = '';
            p.classList.remove( 'dt-fading' );
        } else {
            p.style.padding = String(c) + 'px';
        }
    }, 50);
}

/**
 * Gathers current parameter definitions and assembles them into a JSON string.
 *
 * @return {string} A JSON-encoded object
**/
function assembleParams() {
    var argtable = document.getElementById( 'dt-argtable' );
    var tbody = argtable.getElementsByTagName( 'tbody' );
    if ( tbody ) {
        tbody = tbody[ 0 ];
    } else {
        // If there is no tbody then there are no args
        return '{}';
    }
    var pobj = {};

    for ( var i = 0; i < tbody.rows.length; i++ ) {
        var celln = tbody.rows[ i ].cells[ 1 ].firstChild;
        var cellv = tbody.rows[ i ].cells[ 2 ].firstChild;
        if ( cellv.classList.contains( 'dt-arg-set-yes' ) ) {
            pobj[ celln.nodeValue ] = cellv.value;
        }
    }
    return window.JSON.stringify( pobj );
}

/**
 * ***************************************
 * Creating the debug pane and param list.
 * ***************************************
**/

/**
 * Main update routine to process changed input text.
 *
 * @param {string} text The new input text to process.
 * @param {Object|null} newparams An optional set of parameters and their defined values which should be
 *  included in the list of input parameters constructed.
**/
function updateFromNewInput( text, newparams ) {
    setBusy ( false );
    if ( text === '' ) {
        updateFromXML( '' );
    } else {
        apiParse( text, function( k, t ) {
            if ( k == "OK" ) {
                var result = window.JSON.parse( t );
                if ( result.expandtemplates && result.expandtemplates.parsetree ) {
                    updateFromXML( result.expandtemplates.parsetree, newparams );
                } else {
                    updateFromXML( '' );
                    if ( !result.error || result.error.code!="notext" )
                        debugNote( mw.message( 'debugtemplates-error-parse' ) + ' ' + t );
                }
            } else {
                updateFromXML( '' );
                debugNote( mw.message( 'debugtemplates-error-parse' ) + ' ' + k );
            }
        });
    }
}

/**
 * Cleans up the given text for transcluding by parsing through the includeonly, onlyinclude, and
 * noinclude tags and extracting what would be included.
 *
 * @param {string} text
 * @return {string}
**/
function transcludeText( text ) {
    return transcludeOnlyInclude( text );
}

/**
 * Clean up the given text by extracting any <onlyinclude> blocks, and then processing
 * the remainder for includeonly and noinclude.
 *
 * @param {string} text
 * @param {boolean} imeanit If true this indicates to return nothing if no <onlyinclude> blocks are
 *  found.  Used in recursive calls.
 * @return {string}
**/
function transcludeOnlyInclude( text, imeanit ) {
    var re = new RegExp( '^((?:.|\\n)*?)(<onlyinclude\\s*/?>)((?:.|\\n)*)$', 'i' );
    var m = re.exec( text );
    if ( !m ) {
        // No onlyinclude tag found
        if ( imeanit ) {
            return '';
        }
        return transcludeNoAndOnly( text );
    }
    if ( m[2].indexOf( '\\/>' ) > 0) {
        // Singleton tag
        return transcludeOnlyInclude( m[3], true );
    }
    // Look for a closing tag
    // Note this is more restrictive, and not case-insensitive
    var reEnd = new RegExp( '^((?:.|\\n)*?)(</onlyinclude>)((?:.|\\n)*)$', '' );
    var mm = reEnd.exec( m[3] );
    if ( !mm ) {
        // No closing tag---opening tag doesn't count then
        if ( imeanit ) {
            return '';
        }
        return transcludeNoAndOnly( text );
    }
    // Ok, found some included text (contained in mm[1]), look for any more
    return transcludeNoAndOnly( mm[1] ) + transcludeOnlyInclude( mm[3], true );
}

/**
 * Clean up the given text by removing <noinclude> blocks and discarding <includeonly> tags.
 *
 * @param {string} text
 * @return {string}
**/
function transcludeNoAndOnly( text ) {
    var rc = '';
    var re = new RegExp( '^((?:.|\\n)*?)(<noinclude\\s*/?>)((?:.|\\n)*)$', 'i' );
    var reIO = new RegExp( '<includeonly\\s*/?>', 'ig' );
    var m = re.exec( text );
    if ( !m ) {
        // No noinclude tags
        return text.replace( reIO, '' ).replace( '</includeonly>', '' );
    }
    var singleTag = m[2].indexOf( '\\/>' ) > 0;

    // Certainly have the text prior to the tag
    rc = m[1].replace( reIO, '' ).replace( '</includeonly>', '' );

    // We have an outer noinclude.  Look for a closing tag, discard the enclosed text,
    //  and recurse on the remainder.
    // Note more restrictive, and not case-insensitive
    var reEnd = new RegExp( '^((?:.|\\n)*?)(</noinclude>)((?:.|\\n)*)$', '' );
    var mm = reEnd.exec( m[3] );
    if ( !mm ) {
        // No closing tag---assume it continues to the end
        return rc;
    }
    return rc + transcludeNoAndOnly( mm[3] );
}


/**
 * Callback function which updates the view given the XML derived from the raw input.
 *
 * @param {string} x Well-formed XML as a string
 * @param {Object|null} inheritparams An optional set of parameters and their defined values which should
 *  be included in the list of input parameters constructed.
**/
function updateFromXML( x, inheritparams ) {
    var i,pname;
    // First parse the xml and build an AST
    ast = ( x==='' ) ? null : getXMLParser()( x );

    // Wipe out the global var of previous params and define a new set
    params = [];

    // Now extract all the parameters in the AST so we can build our parameter list
    var newparams = {};
    var astparams = null;
    if (ast) {
        astparams = ast.getElementsByTagName( 'tplarg' );
        if ( astparams ) {
            for ( i = 0; i < astparams.length; i++ ) {
                pname = getParamName( astparams[i], i );
                if ( newparams[ pname ] === undefined ) {
                    newparams[ pname ] = true;
                    params.push( { name: pname, row: 0, used: true} );
                }
            }
        }
    }
    // Add in any in inheritparams
    if ( inheritparams ) {
        for ( var p in inheritparams ) {
            pname = p.trim();
            if ( newparams[ pname ] === undefined ) {
                newparams[ pname ] = true;
                params.push( { name: pname, row: 0 } );
            }
        }
    }
    // Now sort them alphabetically
    params.sort( function ( a, b ) {
        return a.name.localeCompare( b.name );
    } );
    // Create the mapping from AST nodes to their entry in the param array
    if ( astparams ) {
        for ( i=0; i < astparams.length; i++ ) {
            pname = getParamName( astparams[i], i );
            // Look for it in our list of params
            for ( var j = 0; j < params.length; j++ ) {
                if ( params[ j ].name == pname) {
                    // Set the 'pindex' property to the row number
                    astparams[ i ].setAttribute( 'pindex', j );
                    break;
                }
            }
        }
    }

    // Construct the params array
    updateParams( params, inheritparams );
    // Construct the output
    htmlFromAST( ast );
}

/**
 * Extracts a parameter name from a <tplarg> AST node.
 *
 * @param {XMLElement} node
 * @param {number|string} i Unique index used to help form a unique name when the parameter name is a
 *  constructed one.
 * @return {string}
**/
function getParamName( node, i ) {
    // This should not happen...
    if ( !node.firstChild.firstChild ) {
        return '';
    }
    // If the name itself is tree then we cannot determine the name until it has been fully parsed, so we
    //  make something up using the unique index number given.
    if ( node.firstChild.childNodes.length > 1 || node.firstChild.firstChild.nodeValue === null ) {
        return '<' + mw.message( 'debugtemplates-args-constructed' ) + i + '>';
    }
    return node.firstChild.firstChild.nodeValue.trim();
}

/**
 * Retrieves the manual value the user has associated with a parameter in the displayed list of
 * parameters.
 *
 * @param {number} pindex Row number of the corresponding parameter in the params array
 * @return {string|null} May return an empty string, so null is used to indicate a parameter that has not
 *  been set
**/
function getParamText( pindex ) {
    var rownum = params[ pindex ].row;
    var row = document.getElementById( 'dt-argtable-row-number-' + rownum );
    var ptext = row.cells[ 2 ].firstChild;
    if ( ptext.classList.contains( 'dt-arg-set-yes' ) ) {
        return ptext.value;
    }
    return null;
}

/**
 * Retrieves the DOM cell associated with the manual value of a parameter in the displayed list of
 * parameters.
 *
 * @param {string} name The name of the parameter
 * @param {HTMLElement|null} argtable The DOM node for the argtable <tbody>
 * @return {HTMLElement|null} Can return null if not found
**/
function getParamValue( name, argtable ) {
    if ( !argtable ) {
        return null;
    }
    for ( var i = 0; i < argtable.rows.length; i++ ) {
        var celln = argtable.rows[ i ].cells[ 1 ].firstChild;
        if ( celln.nodeValue == name ) {
            return argtable.rows[i].cells[2].firstChild;
        }
    }
    return null;
}

/**
 * Reconstructs the list of available parameters being displayed.
 *
 * @param {object} params The new set of parameters
 * @param {object|null} inheritparams The set of name -> value mappings that should initialize the displayed value
**/
function updateParams( params, inheritparams ) {
    var argtable = document.getElementById( 'dt-argtable' );
    var eall = mw.message( 'debugtemplates-args-eval-all' );
    // We are going to replace the entire table body and wipe out the existing one
    var new_tbody = document.createElement( 'tbody' );
    // There may not be a tbody, so it can be null
    var old_tbody = argtable.getElementsByTagName( 'tbody' );
    if ( old_tbody ) {
        old_tbody = old_tbody[ 0 ];
    }

    // Now construct each row from a param
    for ( var i = 0; i < params.length; i++ ) {
        var oldval = getParamValue( params[ i ].name, old_tbody );
        var row = document.createElement( 'tr' );

        // First cell is the set/unset status
        var c = document.createElement( 'td' );
        c.className = 'dt-arg-centered';
        // Create a toggle-able boolean value
        var span = document.createElement( 'span' );
        if ( ( inheritparams && inheritparams [ params[ i ].name ] !== undefined ) ||
             ( oldval !== null && oldval.classList.contains( 'dt-arg-set-yes' ) ) ) {
                 span.appendChild( document.createTextNode( argy ) );
        } else {
            span.appendChild( document.createTextNode( argn ) );
        }
        span.addEventListener( 'click', paramSetHandler, false );
        c.appendChild( span );
        row.appendChild( c );

        // Then create the parameter name
        c = document.createElement( 'td' );
        c.appendChild( document.createTextNode( params[ i ].name ) );
        row.appendChild( c );

        // The parameter value is a textarea since it can be large, multiline text
        c = document.createElement( 'td' );
        span = document.createElement( 'textarea' );
        if ( ( inheritparams && inheritparams[ params[ i ].name ] !== undefined ) ||
             ( oldval !== null && oldval.classList.contains( 'dt-arg-set-yes' ) ) ) {
                 span.setAttribute( 'class', 'dt-arg-set-yes' );
        } else {
            span.setAttribute( 'class', 'dt-arg-set-no' );
        }
        if ( inheritparams && inheritparams[ params[ i ].name ] !== undefined) {
            span.value = inheritparams[ params[ i ].name ];
        } else if ( oldval !== null ) {
            span.value = oldval.value;
        } else {
            span.value = '';
        }
        span.style.width = "95%";
        c.appendChild( span );
        row.appendChild( c );

        // Then create the eval-all-instances button
        c = document.createElement( 'td' );
        c.className = 'dt-arg-centered';
        span = document.createElement( 'input' );
        span.setAttribute( 'type', 'button' );
        span.setAttribute( 'value', eall );
        span.addEventListener( 'click', paramEval, false);
        c.appendChild( span );
        row.appendChild( c );

        // Ensure the params entry's row field is correct
        row.setAttribute( 'id', 'dt-argtable-row-number-' + i );
        if ( !params[ i ].used )
            row.classList.add( 'dt-arg-unused' );
        new_tbody.appendChild( row );
        params[ i ].row = i;
    }
    var prev = argtable.getElementsByTagName( 'tbody' )[ 0 ];
    if ( prev ) {
        argtable.replaceChild( new_tbody, prev );
    } else {
        argtable.appendChild( new_tbody );
    }
}

/**
 * Main entry point to construct the DOM tree from the AST and install it in the output pane.
 *
 * @param {XMLElement|null} ast
**/
function htmlFromAST( ast ) {
    // Reset our maximum index values and mappings between unique id numbers
    nindex = 0;
    nTox = {};
    xindex = 0;
    // No undo is possible after this
    resetButtonNode.setAttribute( 'disabled', 'disabled' );
    undoButtonNode.setAttribute( 'disabled', 'disabled' );
    if ( ast && ast.documentElement ) {
        var oh = htmlFromAST_r( ast.documentElement );
        setOutput( oh );
    }
}

/**
 * Recursive entry point to construct the DOM tree from the AST.
 *
 * @param {XMLElement|undefined} node
 * @return {HTMLElement}
**/
function htmlFromAST_r( node ) {
    var h, i, span, next;
    // This shouldn't happen but we'll be defensive
    if ( node === undefined ) {
        h = document.createElement( 'span' );
        h.className = 'dt-error';
        h.appendChild( document.createTextNode( 'Undefined' ) );
        return h;
    }
    switch( node.tagName ) {
    case 'root':
        // The <root> contains a list of elements
        h = document.createElement( 'span' );
        h.className = 'dt-node';
        // A special id for the root DOM node
        h.setAttribute( 'id', 'dt-id-root' );
        span = document.createElement( 'span' );
        span.className = 'dt-node';
        i = nindex++;
        node.id = xindex++;
        span.setAttribute( 'id', 'dt-id-' + i );
        h.appendChild(span);
        for ( i = 0; i < node.childNodes.length; i++ ) {
            span.appendChild( htmlFromAST_r( node.childNodes[ i ] ) );
        }
        break;
    case 'template':
        h = htmlFromAST_r_template( node );
        break;
    case 'part':
        // A <part> has a <name> and a <value>, possibly separated by an "="
        if ( node.childNodes.length != 2 && node.childNodes.length != 3) {
            // I don't think this is possible
            h = document.createElement( 'span' );
            h.className = 'dt-error';
            h.appendChild( document.createNode( 'improper argument structure: ' +
                                                node.childNodes.length ) );
        } else {
            // First child is the name
            h = document.createElement( 'span' );
            h.className = 'dt-node dt-node-arg';
            h.appendChild( htmlFromAST_r( node.firstChild ) );
            next = node.firstChild.nextSibling;
            if (next.tagName != 'value' ) {
                // The "=" sign
                h.appendChild( htmlFromAST_r( next ) );
                next = next.nextSibling;
            }
            h.appendChild( htmlFromAST_r( next ) );
        }
        break;
    case 'title':
        h = document.createElement( 'span' );
        h.className = 'dt-node dt-node-title';
        for ( i = 0; i < node.childNodes.length; i++ ) {
            h.appendChild( htmlFromAST_r( node.childNodes[ i ] ) );
        }
        break;
    case 'value':
        h = document.createElement( 'span' );
        h.className = 'dt-node dt-node-value';
        for ( i = 0; i < node.childNodes.length; i++ ) {
            h.appendChild( htmlFromAST_r( node.childNodes[ i ] ) );
        }
        break;
    case 'name':
        h = document.createElement( 'span' );
        h.className = 'dt-node dt-node-name';
        for ( i = 0; i < node.childNodes.length; i++ ) {
            h.appendChild( htmlFromAST_r( node.childNodes[ i ] ) );
        }
        break;
    case 'tplarg':
        h = htmlFromAST_r_tplarg( node );
        break;
    case 'comment':
        h = document.createElement( 'span' );
        h.className = 'dt-node dt-node-comment';
        h.appendChild( htmlFromAST_r( node.firstChild ) );
        break;
    case 'ignore':
        // These wrap <includeonly> and </onlyinclude> and their closers
        if ( node.childNodes.length > 0 ) {
            h = document.createElement( 'span' );
            h.className = 'dt-node dt-node-ignore';
            for ( i = 0; i < node.childNodes.length; i++ ) {
                h.appendChild( htmlFromAST_r( node.childNodes[ i ] ) );
            }
        }
        break;
    case 'ext':
        // The only nodes we recognize are nowiki and pre
        if ( node.firstChild &&
             node.firstChild.tagName == 'name' &&
             node.firstChild.firstChild &&
             node.firstChild.firstChild.nodeType == 3 &&
             (node.firstChild.firstChild.nodeValue == 'nowiki' ||
              node.firstChild.firstChild.nodeValue == 'pre' ) ) {
                  // Should have a <name>, an <attribute>, an <inner> and optionally a <close>
                  h = document.createElement( 'span' );
                  var extname = node.firstChild.firstChild.nodeValue;
                  h.className = 'dt-node dt-node-ext dt-node-ext-' + extname;
                  h.appendChild( document.createTextNode( '<' + extname ) );
                  next = node.firstChild.nextSibling;
                  if (next && next.tagName == 'attr' ) {
                      if ( next.firstChild ) {
                          h.appendChild( document.createTextNode( ' ' + next.firstChild.nodeValue ) );
                      }
                      next = next.nextSibling;
                  }
                  h.appendChild(document.createTextNode( '>' ) );
                  if ( next && next.tagName == 'inner' ) {
                      if ( next.firstChild ) {
                          h.appendChild( document.createTextNode( next.firstChild.nodeValue ) );
                      }
                  }
                  h.appendChild( document.createTextNode( '</' + extname + '>' ) );
                  break;
              }
        // For unrecognized cases of the ext tag, let it run into the default
    default:
        if ( node.nodeType != 3 ) {
            // Something we don't parse, so just represent it literally
            h = document.createElement( 'span' );
            h.className = 'dt-node';
            if ( node.childNodes.length == 0 ) {
                h.appendChild( document.createTextNode( '<' + node.tagName + '/>' ) );
            } else {
                h.appendChild( document.createTextNode( '<' + node.tagName + '>' ) );
                for ( i = 0; i < node.childNodes.length; i++ ) {
                    h.appendChild( htmlFromAST_r( node.childNodes[ i ] ) );
                }
                h.appendChild( document.createTextNode( '</' + node.tagName + '>' ) );
            }
        } else if ( node.nodeValue === null || node.nodeValue === undefined) {
            h = document.createTextNode( '' );
        } else {
            // Plain old text
            h = textWithLinebreaks( node.nodeValue, 'dt-node dt-node-multiline' );
        }
        break;
    }
    return h;
}


/**
 * Recursive entry point to construct the DOM tree from a <template> AST node.
 *
 * @param {XMLElement} node
 * @return {HTMLElement}
**/
function htmlFromAST_r_template( node ) {
    var i;
    // Give the input AST node a unique number
    node.id = xindex++;

    // A <template> has a <title> followed by a list of <part>s for the arguments
    var espans = [];
    var h = document.createElement( 'span' );
    h.className = 'dt-node dt-node-template';
    var tid = nindex++;
    h.setAttribute( 'id', 'dt-id-' + tid );

    // The opening braces
    var span = document.createElement( 'span' );
    span.appendChild( document.createTextNode( '{{' ) );
    var inid = nindex++;
    span.setAttribute( 'id', 'dt-id-' + inid );
    h.appendChild( span );
    espans.push( span );

    // Record the mapping in the global nTox array
    nTox[ 'dt-id-' + inid ] = node.id;
    span.addEventListener( 'click', evalText, false );

    // The template name
    span = document.createElement( 'span' );
    var nid = nindex++;
    span.setAttribute( 'id', 'dt-id-' + nid );
    span.appendChild( htmlFromAST_r( node.firstChild ) );
    h.appendChild( span );
    espans.push( span );

    // A span for all arguments
    span = document.createElement( 'span' );
    var aid = nindex++;
    span.setAttribute( 'id', 'dt-id-' + aid );

    var pipeIds = [];
    for ( i = 1; i < node.childNodes.length; i++ ) {
        var pspan = document.createElement( 'span' );
        var pid = nindex++;
        pspan.setAttribute( 'id', 'dt-id-' + pid );
        pipeIds.push( pid );
        pspan.appendChild( document.createTextNode( '|' ) );
        span.appendChild( pspan );
        span.appendChild( htmlFromAST_r( node.childNodes[ i ] ) );
    }
    h.appendChild( span );
    espans.push( span );

    // Closing braces
    span = document.createElement( 'span' );
    var outid = nindex++;
    span.setAttribute( 'id', 'dt-id-' + outid );
    span.appendChild( document.createTextNode( '}}' ) );
    h.appendChild( span );
    espans.push( span );

    // Record ids so the emphasize listener can find all the right pieces
    var pipeList = pipeIds.join( ' ' );
    for ( i = 0; i <espans.length; i++ ) {
        espans[ i ].setAttribute( 'dt-emph-template-out', outid );
        espans[ i ].setAttribute( 'dt-emph-template-in', inid );
        espans[ i ].setAttribute( 'dt-emph-template-name', nid );
        espans[ i ].setAttribute( 'dt-emph-template-args', aid );
        espans[ i ].setAttribute( 'dt-emph-template-pipes', pipeList );
        espans[ i ].addEventListener( 'mouseover', emphasizeTemplate );
        espans[ i ].addEventListener( 'mouseout', emphasizeTemplate );
    }
    return h;
}

/**
 * Emphasize handler for template structures.
**/
function emphasizeTemplate() {
    var outSpan = document.getElementById( 'dt-id-' + this.getAttribute( 'dt-emph-template-out' ) );
    var inSpan = document.getElementById( 'dt-id-' + this.getAttribute( 'dt-emph-template-in' ) );
    var nameSpan = document.getElementById( 'dt-id-' + this.getAttribute( 'dt-emph-template-name' ) );
    var argSpan = document.getElementById( 'dt-id-' + this.getAttribute( 'dt-emph-template-args' ) );
    var pipes = this.getAttribute( 'dt-emph-template-pipes' );

    outSpan.classList.toggle( 'dt-emphasize-template-out' );
    inSpan.classList.toggle( 'dt-emphasize-template-in' );
    nameSpan.classList.toggle( 'dt-emphasize-template-name' );
    argSpan.classList.toggle( 'dt-emphasize-template-args' );

    pipes = pipes.split( ' ' );
    for (var i = 0; i < pipes.length; i++ ) {
        if ( pipes[ i ] === '' ) {
            continue;
        }
        document.getElementById( 'dt-id-' + pipes[ i ] ).classList.toggle( 'dt-emphasize-template-pipe' );
    }
}

/**
 * Recursive entry point to construct the DOM tree from a <tplarg> AST node.
 *
 * @param {XMLElement} node
 * @return {HTMLElement}
**/
function htmlFromAST_r_tplarg( node ) {
    var i;
    // Give the input AST node a unique number
    node.id = xindex++;

    // Like templates, a <tplarg> has a <title> followed by a list of <part>s for the arguments
    var espans = [];
    var h = document.createElement( 'span' );
    h.className = 'dt-node dt-node-tplarg';
    var tid = nindex++;
    h.setAttribute( 'id', 'dt-id-' + tid );

    // The opening braces
    var span = document.createElement( 'span' );
    span.appendChild( document.createTextNode( '{{{' ) );
    var inid = nindex++;
    span.setAttribute( 'id', 'dt-id-' + inid );
    h.appendChild( span );
    espans.push( span );

    // Record the mapping
    nTox[ 'dt-id-' + inid ] = node.id;
    span.addEventListener( 'click', evalText, false );

    // The parameter name
    span = document.createElement( 'span' );
    var nid = nindex++;
    span.setAttribute( 'id', 'dt-id-' + nid );
    span.appendChild( htmlFromAST_r( node.firstChild ) );
    h.appendChild( span );
    espans.push( span );

    // Any parameter arguments (although at most 1 is meaningful I think)
    span = document.createElement( 'span' );
    var aid = nindex++;
    span.setAttribute( 'id', 'dt-id-' + aid );

    var pipeIds = [];
    for ( i = 1; i < node.childNodes.length; i++ ) {
        var pspan = document.createElement( 'span' );
        var pid = nindex++;
        pspan.setAttribute( 'id', 'dt-id-' + pid );
        pipeIds.push( pid );
        pspan.appendChild( document.createTextNode( '|' ) );
        span.appendChild( pspan );
        span.appendChild( htmlFromAST_r( node.childNodes[ i ] ) );
    }
    h.appendChild( span );
    espans.push( span );

    // Closing braces
    span = document.createElement( 'span' );
    var outid = nindex++;
    span.setAttribute( 'id', 'dt-id-' + outid );
    span.appendChild( document.createTextNode( '}}}' ) );
    h.appendChild( span );
    espans.push( span );

    // Record ids so the emphasize listener can find all the right pieces
    var pipeList = pipeIds.join( ' ' );

    for ( i = 0; i < espans.length; i++ ) {
        espans[ i ].setAttribute( 'dt-emph-tplarg-out', outid );
        espans[ i ].setAttribute( 'dt-emph-tplarg-in', inid );
        espans[ i ].setAttribute( 'dt-emph-tplarg-name', nid );
        espans[ i ].setAttribute( 'dt-emph-tplarg-args', aid );
        espans[ i ].setAttribute( 'dt-emph-tplarg-pipes', pipeList );
        espans[ i ].addEventListener( 'mouseover', emphasizeTplarg );
        espans[ i ].addEventListener( 'mouseout', emphasizeTplarg );
    }
    return h;
}

/**
 * Emphasize handler for tplarg structures.
**/
function emphasizeTplarg() {
    var outSpan = document.getElementById( 'dt-id-' + this.getAttribute( 'dt-emph-tplarg-out' ) );
    var inSpan = document.getElementById( 'dt-id-' + this.getAttribute( 'dt-emph-tplarg-in' ) );
    var nameSpan = document.getElementById( 'dt-id-' + this.getAttribute( 'dt-emph-tplarg-name' ) );
    var argSpan = document.getElementById( 'dt-id-' + this.getAttribute( 'dt-emph-tplarg-args' ) );
    var pipes = this.getAttribute( 'dt-emph-tplarg-pipes' );

    outSpan.classList.toggle( 'dt-emphasize-tplarg-out' );
    inSpan.classList.toggle( 'dt-emphasize-tplarg-in' );
    nameSpan.classList.toggle( 'dt-emphasize-tplarg-name' );
    argSpan.classList.toggle( 'dt-emphasize-tplarg-args' );

    pipes = pipes.split( ' ' );
    for ( var i = 0; i < pipes.length; i++ ) {
        if ( pipes[ i ] === '' ) {
            continue;
        }
        document.getElementById( 'dt-id-' + pipes[ i ] ).classList.toggle( 'dt-emphasize-tplarg-pipe' );
    }
}

/**
 * **************************
 * Extracting text functions.
 * **************************
**/

/**
 * Main and recursive entry point for converting an AST node into equivalent wikitext.
 *
 * @param {XMLElement} node
 * @return {string}
**/
function textFromAST( node ) {
    var i;
    var txt = '';

    switch( node.tagName ) {
    case 'part':
    case 'title':
    case 'value':
    case 'name':
    case 'comment':
    case 'ignore':
    case 'root':
        // These elements just wrap their children
        for ( i = 0; i <node.childNodes.length; i++ ) {
            txt += textFromAST( node.childNodes[ i ] );
        }
        break;
    case 'template':
        txt = '{{' + textFromAST( node.firstChild );
        for ( i = 1; i < node.childNodes.length; i++ ) {
            txt += '|';
            txt += textFromAST( node.childNodes[ i ] );
        }
        txt += '}}';
        break;
    case 'tplarg':
        // First check for manual settings of this parameter
        var pindex = node.getAttribute( 'pindex' );
        txt = getParamText( pindex );
        if (txt === null) {
            // It is unset so construct the usual text
            txt = '{{{' + textFromAST( node.firstChild );
            for ( i = 1; i < node.childNodes.length; i++ ) {
                txt += '|';
                txt += textFromAST( node.childNodes[ i ] );
            }
            txt += '}}}';
        }
        break;
    case 'ext':
        // Only recognize nowiki and pre
        if (node.firstChild &&
            node.firstChild.tagName == 'name' &&
            node.firstChild.firstChild &&
            node.firstChild.firstChild.nodeType == 3 &&
            (node.firstChild.firstChild.nodeValue == 'nowiki' ||
             node.firstChild.firstChild.nodeValue == 'pre' ) ) {
                 // Should have a <name>, an <attribute>, an <inner> and optionally a <close>
                 var extname = node.firstChild.firstChild.nodeValue;
                 txt += '<' + extname;
                 var next = node.firstChild.nextSibling;
                 if (next && next.tagName == 'attr' ) {
                     if (next.firstChild) {
                         txt += ' ' + next.firstChild.nodeValue;
                     }
                     next = next.nextSibling;
                 }
                 txt += '>';
                 if (next && next.tagName == 'inner' ) {
                     if (next.firstChild) {
                         txt += next.firstChild.nodeValue;
                     }
                 }
                 txt += '</' + extname + '>';
                 break;
             }
        // Unrecognized case of the ext tag which we let run into the default
    default:
        if ( node.nodeType != 3 ) {
            if ( node.childNodes.length == 0 ) {
                txt = '<' + node.tagName + '/>';
            } else {
                txt = '<' + node.tagName + '>';
                for ( i = 0; i < node.childNodes.length; i++ ) {
                    txt += textFromAST( node.childNodes[ i ] );
                }
                txt += '</' + node.tagName + '>';
            }
        } else if ( node.nodeValue === null || node.nodeValue === undefined) {
            txt = '';
        } else {
            txt = node.nodeValue;
        }
        break;
    }
    return txt;
}

/**
 * Handler attached to a template start.  It either does nothing, or replaces the template text with its
 * evaluated version, or descends into the template call, depending on the click mode.
**/
function evalText() {
    if ( busy ) {
        return;
    }
    setBusy( true );

    var mode = getMouseClickMode();
    if (mode == 'nothing' ) {
        setBusy( false );
        return;
    }
    // For templates we have 2 different modes to consider
    if ( this.parentNode.classList.contains( 'dt-node-template' ) ) {
        if (mode == 'descend' ) {
            descendInto( this );
            return;
        }
    }
    // First check if it is already evaluated and we're just redoing it
    if ( this.parentNode.lastChild.classList.contains( 'dt-node-evaluated' ) ) {
        for ( var j = 0; j < this.parentNode.childNodes.length; j++ ) {
            this.parentNode.childNodes[ j ].classList.toggle( 'dt-node-invisible' );
        }
        setBusy( false );
        return;
    }

    // Map this node to its AST equivalent
    var n = this.id;
    var x = nTox[ n ];
    var astNode = ast.getElementById( x );
    // Get the AST text
    var txt = textFromAST( astNode );
    if ( txt == '' ) {
        // Empty strings do not need an evaluation
        evalTextDisplay( txt, n );
    } else {
        // We call the API to parse it into wikitext
        apiEval( txt, function( k, t ) {
            if ( k == "OK" ) {
                var result = window.JSON.parse( t );
                if ( apiEvalHasResult( result ) ) {
                    evalTextDisplay( apiEvalGetResult( result ), n );
                } else {
                    debugNote( mw.message('debugtemplates-error-eval') + ' ' + t );
                    setBusy( false );
                }
            } else {
                debugNote( mw.message('debugtemplates-error-eval') + ' ' + k );
                setBusy( false );
            }
        });
    }
}

/**
 * Callback after parsing text to display it.
 *
 * @param {string} t The text to display
 * @param {string} node The id of the node that had the evalText handler
 * @param {boolean} more A flag to indicate whether the busy flag should be turned off after
**/
function evalTextDisplay( t, n, more ) {
    //debugNote('evalled text: "'+t+'"');
    var node = document.getElementById( n );
    if ( node ) {
        // It should be the first child of the node we want to replace
        node = node.parentNode;
        if ( node.lastChild.classList.contains( 'dt-node-evaluated' ) ) {
            // Already evaluated
            if ( !more )
                setBusy( false );
            return;
        }
        // Make all current children invisible
        for ( var i = 0; i < node.childNodes.length; i++ ) {
            node.childNodes[ i ].classList.toggle( 'dt-node-invisible' );
        }
        // And add a new visible span containing the evaluated text
        var span = document.createElement( 'span' );
        span.appendChild( textWithLinebreaks( t ) );
        span.className = 'dt-node-evaluated';
        span.addEventListener( 'click', unevalText, false );
        span.addEventListener( 'mouseover', emphasizeEvalText );
        span.addEventListener( 'mouseout', emphasizeEvalText );
        node.appendChild( span );
        // A new undo event is possible now
        resetButtonNode.removeAttribute( 'disabled' );
        undoButtonNode.removeAttribute( 'disabled' );
        if ( lastUndo.length >= maxUndos ) {
            lastUndo.shift();
        }
        if ( more ) {
            // If we're not done then just accumulate this undo with the previous
            var prevDid = lastUndo[ lastUndo.length - 1 ];
            prevDid.push( node.id );
        } else {
            lastUndo.push( node.id );
        }
        // And do a fancy fade-in effect
        fader( span );
    }
    if ( !more ) {
        setBusy( false );
    }
}

/**
 * Handler to un-show evaluated text and restore the original.
 *
**/
function unevalText() {
    if ( busy ) {
        return;
    }
    setBusy( true );
    var mode = getMouseClickMode();
    if ( mode == 'nothing' ) {
        setBusy( false );
        return;
    }
    var node = this.parentNode;
    for ( var i = 0; i < node.childNodes.length; i++ ) {
        node.childNodes[ i ].classList.toggle( 'dt-node-invisible' );
    }
    setBusy( false );
}

/**
 * Emphasize handler for evaluated text.
**/
function emphasizeEvalText() {
    this.classList.toggle( 'dt-node-emphasize-evaluated' );
}

/**
 * Handler for the parameter eval-all buttons.
**/
function paramEval() {
    if ( busy ) {
        return;
    }
    this.setAttribute( 'disabled', 'disabled' );
    var row = this.parentNode.parentNode;
    var pname = row.childNodes[ 1 ].firstChild;
    if ( !pname ) {
        return;
    }
    setBusy( true );
    // We need to know our row number
    var rown = row.id.replace( /[^0-9]*/g, '' );
    // Look through all the parameters displayed
    var instances = document.getElementsByClassName( 'dt-node-tplarg' );
    // Start off a chain of individual lookups
    if ( instances ) {
        paramEvalNext( 0, instances, rown );
    } else {
        setBusy( false );
    }
}

/**
 * Chaining function to evaluate the next instance of a specific parameter.
 *
 * @param {number} i The index into the instances array
 * @param {object} instances An array of HTMLElements
 * @param {number} rown The row-index of the specific parameter in the displayed list
**/
function paramEvalNext( i, instances, rown ) {
    var first = ( i == 0 ) ? true : false;
    var continuing = false;
    // Look for the next instance to evaluate from i onward
    while ( i < instances.length ) {
        // Verify this parameter is actually an instance of our specific parameter
        // This is a bit round about.  We first get the id of the parameter, then
        //  look it up in the node->AST map to get the matching AST node, and
        //  then find the param entry from that AST node's pindex, and then
        //  check if that's us.
        var n = instances[ i ].firstChild.id;
        var x = nTox[ n ];
        var astNode = ast.getElementById( x );
        var pindex = astNode.getAttribute( 'pindex' );
        if ( params[ pindex ] && params[ pindex ].row == rown ) {
            // Yes, found a matching param
            // If this is the first one, setup the undo list
            if ( first ) {
                resetButtonNode.removeAttribute( 'disabled' );
                undoButtonNode.removeAttribute( 'disabled' );
                if ( lastUndo.length >= maxUndos ) {
                    lastUndo.shift();
                }
                lastUndo.push( new Array() );
                first = false;
            }
            // Get the parameter text
            var txt = textFromAST( astNode );
            if ( txt == '' ) {
                // If empty string we can just show it
                evalTextDisplay( txt, n, true );
            } else {
                // If non-empty, we need to evaluate it through the API
                window.setTimeout( function( txt, n, i, instances, rown ) {
                    apiEval( txt, function( k, t ) {
                        var row;
                        if ( k == "OK" ) {
                            var result = window.JSON.parse( t );
                            if ( apiEvalHasResult( result ) ) {
                                evalTextDisplay( apiEvalGetResult( result ), n, true );
                                // Chain into an eval of the next one
                                paramEvalNext( i + 1, instances, rown );
                            } else {
                                debugNote( mw.message( 'debugtemplates-error-eval' ) + ' ' + t );
                                setBusy( false );
                                row = document.getElementById( 'dt-argtable-row-number-' + rown );
                                row.childNodes[ 3 ].firstChild.removeAttribute( 'disabled' );
                            }
                        } else {
                            debugNote( mw.message( 'debugtemplates-error-eval' ) + ' ' + k );
                            setBusy( false );
                            row = document.getElementById( 'dt-argtable-row-number-' + rown );
                            row.childNodes[ 3 ].firstChild.removeAttribute( 'disabled' );
                        }
                    } );
                }, apiCallInterval, txt, n, i, instances, rown );
                continuing = true;
                break;
            }
        }
        i++;
    }
    if ( !continuing ) {
        // Last one done
        // Just check that the undo wasn't empty
        var lastLastUndo = ( lastUndo.length > 0 ) ? lastUndo[ lastUndo.length - 1 ] : null;
        if ( lastLastUndo &&
             ( typeof lastLastUndo == 'object' ) &&
             lastLastUndo.length == 0 ) {
                 lastUndo.pop();
             }
        setBusy( false );
        var row = document.getElementById( 'dt-argtable-row-number-' + rown );
        row.childNodes[ 3 ].firstChild.removeAttribute( 'disabled' );
    }
}


/**
 * ***************************
 * Descending into a template.
 * ***************************
**/

/**
 * Delegated handler from exalText for entering into a called template.
 *
 * Assumes the busy flag is set.
 *
 * @param {HTMLElement} node The node to which the evalText handler was attached
 * @return {}
**/
function descendInto( node ) {
    var i;
    var n = node.id;
    var x = nTox[ n ];
    var astNode = ast.getElementById( x );
    // Cannot descend into a parameter
    if ( astNode.tagName != 'template' ) {
        setBusy( false );
        return;
    }

    // For each parameter, including the title, we need to evaluate them, so build up a list
    var args = [ ];
    for ( i = 0; i < astNode.childNodes.length; i++ ) {
        // Titles and unnamed arguments are simple and just get expanded.  Named ( arg=value ) args are
        //  trickier and give us two pieces that need to be evaluated separately.
        if ( astNode.childNodes[ i ].tagName == 'part' &&
             astNode.childNodes[ i ].childNodes.length == 3 ) {
                 // Also trim them, as whitespace around the arg name and value is discarded
                 args.push( textFromAST( astNode.childNodes[ i ].firstChild ).trim(  ) );
                 args.push( textFromAST( astNode.childNodes[ i ].childNodes[ 2 ] ).trim(  ) );
             } else {
                 args.push( textFromAST( astNode.childNodes[ i ] ) );
             }
    }

    // Define a counter in the closure so we have a global way of detecting when all the evals are done
    var count = 0;

    // And initialize the counter to the number of non-empty-string entries
    for ( i = 0; i < args.length; i++ ) {
        if ( args[ i ] == '' ) {
            count++;
        }
    }

    // Now evaluate each arg that is not the empty string
    for ( i = 0; i < args.length; i++ ) {
        if ( args[ i ] != '' ) {
            // Use apiEval, but wrap it in a function to preserve individual i values
            window.setTimeout( function( i ) {
                apiEval( args[ i ], function( k, t ) {
                    if ( k == "OK" ) {
                        var result = window.JSON.parse( t );
                        if ( apiEvalHasResult( result) ) {
                            args[ i ] = apiEvalGetResult ( result );
                            count++;
                            // Once count is at max, all evals are done and we can display them
                            if ( count == args.length ) {
                                descendDisplay( astNode, args );
                            }
                        } else {
                            debugNote( mw.message( 'debugtemplates-error-arg-eval' ) + ' ' + t );
                            setBusy( false );
                        }
                    } else {
                        debugNote( mw.message( 'debugtemplates-error-arg-eval' ) + ' ' + k );
                        setBusy( false );
                    }
                } );
            }, apiCallInterval * i, i );
        }
    }
}

/**
 * Callback upon descent, once all args have been evaluated.
 *
 * @param {XMLElement} node The AST node representing this template
 * @param {object} args The ordered array of textual arguments, including the title and with named
 *  arguments represented by 2 entries
**/
function descendDisplay( node, args ) {
    // First we assemble our list of arguments, using the original AST node as a guideline
    var newparams = {};
    // This counter tracks unnamed argument indices
    var pindex = 1;
    // Index into the args array
    var argi = 1;
    for ( var i = 1; i < node.childNodes.length; i++ ) {
        if ( node.childNodes[ i ].tagName == 'part' && node.childNodes[ i ].childNodes.length == 3 ) {
            // Named argument, consume two entries in the args array
            argi++;
            newparams[ args[ argi - 1 ] ] = args[ argi ];
        } else {
            // Indexed argument, assign to the next index
            newparams[ pindex ] = args[ argi ];
            pindex++;
        }
        argi++;
    }

    // var s = args[ 0 ]+': ';
    // for ( var p in newparams ) {
    //     s+= '[ '+p+' ]="'+newparams[ p ]+'",  ';
    // }
    //debugNote( 'Extracted: '+s );

    // Ok, now we need to find the real name of the template page.  Note that this can be different from
    // the name used in invoking it, as a namespace can be assumed, and there might be a ':' in front, or
    // it may not actually exist (such as for parserfunctions)
    window.setTimeout( function() {
        apiGetTemplateName( '{{' + args[ 0 ] + '}}', function( k, t ) {
            if ( k == "OK" ) {
                var result = window.JSON.parse( t );
                if ( result.parse && result.parse.templates && result.parse.templates[ 0 ] ) {
                    var tplate = result.parse.templates[ 0 ];
                    var tname = tplate[ '*' ];
                    if ( tplate[ "exists" ] !== undefined ) {
                        // Page actually exists and we have its full name
                        loadTemplate( args[ 0 ], tname, newparams );
                    } else {
                        debugNote( mw.message( 'debugtemplates-warning-template-not-a-template' ) + ' ' +
                                   tname );
                        setBusy( false );
                    }
                } else {
                    debugNote( mw.message( 'debugtemplates-warning-template-not-found' ) + ' ' + args[ 0 ] );
                    setBusy( false );
                }
            } else {
                debugNote( mw.message( 'debugtemplates-error-template-name' ) + ' ' + k );
                setBusy( false );
            }
        } );
    }, apiCallInterval);
}

/**
 * The penultimate step in descending into a template.  Here we assume we have resolved the template name
 * and arguments, and now we can load the actual page as our new input text.
 *
 * @param {string} tinv The template named used in the invocation
 * @param {string} tname The full page name of the template
 * @param {object} newparams The expanded arguments it will be invoked with
**/
function loadTemplate( tinv, tname, newparams ) {
    apiGetPage( tname, function( k, t ) {
        if ( k == "OK" ) {
            var result = window.JSON.parse( t );
            if ( result.query && result.query.pages ) {
                for ( var p in result.query.pages ) {
                    var pg = result.query.pages[ p ];
                    if ( pg.revisions && pg.revisions[ 0 ] ) {
                        finishDescent( tinv, newparams, pg.revisions[ 0 ][ '*' ] );
                    } else  {
                        debugNote( mw.message( 'debugtemplates-error-template-page' ) + ' ' + tname );
                        setBusy( false );
                    }
                }
            } else {
                debugNote( mw.message( 'debugtemplates-error-template-page' ) + ' ' + t );
                setBusy( false );
            }
        } else {
            debugNote( mw.message( 'debugtemplates-error-template-page' ) + ' ' + k );
            setBusy( false );
        }
    } );
}

/**
 * Callback forming the last step in descending into a template.  This pushes a new crumb, and installs
 * the new text.
 *
 * @param {string} t Template name used in the invoke
 * @param {object} newparams The expanded parameters mapping names to values
 * @param {string} text The template text itself
**/
function finishDescent( t, newparams, text ) {
    //debugNote( 'descending into '+text );
    pushCrumb( t );

    //debugNote( 'transcluding: '+text );
    text = transcludeText( text );
    //debugNote( 'transcluded: '+text );

    document.getElementById( 'dt-input' ).value = text;
    updateFromNewInput( text, newparams );
    setBusy( false );
}

/**
 * ***********************
 * Breadcrumbs management.
 * ***********************
**/

/**
 * Wipe out the list of breadcrumbs and reset it to the initial crumb.
**/
function clearCrumbs() {
    var bc = document.getElementById( 'dt-crumbs' );
    while ( bc.firstChild )
        bc.removeChild( bc.firstChild );
    nestingStack = [];
    // An initial crumb is required
    pushCrumb( firstcrumb );
}

/**
 * Pushes a new crumb onto the end of the stack, turning the previous one into a history link.
 *
 * @param {string} t The crumb name
**/
function pushCrumb( t ) {
    var bc = document.getElementById( 'dt-crumbs' );
    var span = document.createElement( 'span' );
    if ( bc.lastChild ) {
        bc.lastChild.classList.add( 'dt-crumb-visited' );
        bc.lastChild.addEventListener( 'click', crumbHandler, false );
        var h = makeStackFrame();
        nestingStack.push( h );
        bc.lastChild.setAttribute( 'hindex', nestingStack.length - 1 );
        bc.appendChild( document.createTextNode( nextCrumb ) );
    }

    span.appendChild( document.createTextNode( t ) );
    bc.appendChild( span );
}

/**
 * Pop the crumb stack to restore to the given crumb.
 *
 * @param {HTMLElement} c The crumb we want to go to
**/
function popToCrumb( c ) {
    if ( busy ) {
        return;
    }
    var bc = document.getElementById( 'dt-crumbs' );
    // Remove children until we've rewound the stack to c
    var ci = c.getAttribute( 'hindex' );
    var s = '';
    while ( bc.lastChild ) {
        if ( bc.lastChild.nodeType == 3 || !bc.lastChild.hasAttribute( 'hindex' ) ) {
            bc.removeChild( bc.lastChild );
        } else if ( bc.lastChild.getAttribute( 'hindex' ) != ci ) {
            bc.removeChild( bc.lastChild );
            nestingStack.pop();
        } else {
            // Found our crumb
            c.classList.remove( 'dt-crumb-visited' );
            c.removeAttribute( 'hindex' );
            c.removeEventListener( 'click', crumbHandler, false );
            // Get our history
            restoreStackFrame( nestingStack.pop() );
            break;
        }
    }
}

/**
 * Construct a history of the current state to store in a crumb.
 *
 * @return {object} An associative array storing the state
**/
function makeStackFrame() {
    // Preserve:
    //  - input pane ( simple string )
    //  - output pane  ( html: will be replaced )
    //  - abstract syntax tree ( xml: will be replaced )
    //  - mapping betwween html and ast nodes ( json: deep-copy )
    //  - max indices for both ast nodes and html nodes ( simple data types )
    //  - param list ( whole table body ) ( html: will be replaced )
    //  - undo history ( json: deep-copy )
    var h = { input: null,
              output: null,
              ast: ast,
              nTox: window.JSON.stringify( nTox ),
              nindex: nindex,
              xindex: xindex,
              params: null,
              undo: window.JSON.stringify( lastUndo ) };
    h.input = document.getElementById( 'dt-input' ).value;
    h.output = document.getElementById( 'dt-output' ).firstChild;
    var argtable = document.getElementById( 'dt-argtable' );
    if ( argtable.getElementsByTagName( 'tbody' ) ) {
        h.params = argtable.getElementsByTagName( 'tbody' )[ 0 ];
    }
    return h;
}

/**
 * Restore the state to the given stack frame.
 *
 * @param {object} h A history previously constructed by makeStackFrame
**/
function restoreStackFrame( h ) {
    // Restore:
    //  - input pane data
    //  - output pane html
    //  - abstract syntax tree
    //  - mapping betwween html and ast nodes
    //  - max indices for both ast nodes and html nodes
    //  - param list ( whole table body )
    //  - undo history
    ast = h.ast;
    var k = h.nTox;
    nTox = window.JSON.parse( k );
    nindex = h.nindex;
    xindex = h.xindex;
    lastUndo = window.JSON.parse( h.undo );
    document.getElementById( 'dt-input' ).value = h.input;
    var dout = document.getElementById( 'dt-output' );
    if ( !h.output ) {
        while ( dout.lastChild ) {
            dout.removeChild( dout.lastChild );
        }
    } else {
        if ( dout.firstChild ) {
            dout.replaceChild( h.output, dout.firstChild );
        } else {
            dout.appendChild( h.output );
        }
    }
    var argtable = document.getElementById( 'dt-argtable' );
    if ( !h.params ) {
        h.params = document.createElement( 'tbody' );
    }
    var prev = argtable.getElementsByTagName( 'tbody' )[ 0 ];
    if ( prev ) {
        argtable.replaceChild( h.params, prev );
    } else {
        argtable.appendChild( h.params );
    }
    // Finally,  fix any lingering emphasis tags
    var emps = [ 'dt-emphasize-template-out',
                 'dt-emphasize-template-in',
                 'dt-emphasize-template-name',
                 'dt-emphasize-template-args',
                 'dt-emphasize-template-pipe',
                 'dt-emphasize-tplarg-out',
                 'dt-emphasize-tplarg-in',
                 'dt-emphasize-tplarg-name',
                 'dt-emphasize-tplarg-args',
                 'dt-emphasize-tplarg-pipe',
                 'dt-node-emphasize-evaluated' ];
    for ( var i = 0; i <emps.length; i++ ) {
        var e = document.getElementsByClassName( emps[ i ] );
        if ( e ) {
            for ( var j = 0; j < e.length; j++ ) {
                e[ j ].classList.remove( emps[ i ] );
            }
        }
    }
}

/**
 * Handler for when a crumb is clicked on.
**/
function crumbHandler() {
    popToCrumb( this );
}

/**
 * ****************
 * Basic listeners.
 * ****************
**/

/**
 * Handler for toggling a parameter's set/unset state.
**/
function paramSetHandler() {
    paramSetToggle( this.parentNode.parentNode );
}

/**
 * Toggles or changes the set/unset state of a parameter.
 *
 * @param {HTMLElement} row Row DOM element of the parameter
 * @param {string|null} tostate Either 'on', 'off' to set it to a specific value, or null to toggle
**/
function paramSetToggle( row, tostate ) {
    var cset = row.cells[ 0 ].firstChild;
    var cval = row.cells[ 2 ].firstChild;
    if ( !tostate ) {
        // Detect the state to decide how to change it
        if ( cval.classList.contains( 'dt-arg-set-yes' ) ) {
            tostate = "off";
        } else {
            tostate = "on";
        }
    }
    if ( cval.classList.contains( 'dt-arg-set-yes' ) && tostate == 'off' ) {
        cset.replaceChild( document.createTextNode( argn ), cset.firstChild );
        cval.classList.remove( 'dt-arg-set-yes' );
        cval.classList.add( 'dt-arg-set-no' );
    } else if ( cval.classList.contains( 'dt-arg-set-no' ) && tostate == 'on' ) {
        cset.replaceChild( document.createTextNode( argy ), cset.firstChild );
        cval.classList.add( 'dt-arg-set-yes' );
        cval.classList.remove( 'dt-arg-set-no' );
    }
}

/**
 * Handler for if the API URL is changed.
 *
 * Technically this is a readonly field, so changing it should not be possible.  This is here for future
 * flexibility.
**/
function apiHandler() {
    updateFromNewInput( document.getElementById( 'dt-input' ).value );
}

/**
 * Handler for when the page context title is changed.
**/
function titleHandler() {
    updateFromNewInput( document.getElementById( 'dt-input' ).value );
}

/**
 * Handler for when the input text is changed.
**/
function debugTextHandler() {
    updateFromNewInput( this.value );
}

/**
 * Handler to keep the parameter table the same height as the input text box.
**/
function resizeArgTable() {
    document.getElementById( 'dt-argtable-wrapper' ).style.height =
        document.getElementById( 'dt-input' ).clientHeight + 'px';
}

/**
 * Handler for the undo-all button
**/
function resetButton() {
    var i;
    if ( busy ) {
        return;
    }
    this.setAttribute( 'disabled', 'disabled' );
    undoButtonNode.setAttribute( 'disabled', 'disabled' );
    var evaluated = document.getElementsByClassName( 'dt-node-evaluated' );
    if ( evaluated ) {
        for ( i = evaluated.length - 1; i >= 0; i-- ) {
            var n = evaluated[ i ].parentNode;
            n.removeChild( evaluated[ i ] );
        }
    }
    evaluated = document.getElementsByClassName( 'dt-node-invisible' );
    if ( evaluated ) {
        for ( i = evaluated.length - 1; i >= 0; i-- ) {
            evaluated[ i ].classList.toggle( 'dt-node-invisible' );
        }
    }
    lastUndo = [  ];
}

/**
 * Handler for the undo button.
**/
function undoButton() {
    // A sub-function to undo a single event
    function undoSomething( undo ) {
        var node = document.getElementById( undo );
        if ( node.lastChild.classList.contains( 'dt-node-evaluated' ) ) {
            node.removeChild( node.lastChild );
            for ( var  i = 0; i < node.childNodes.length; i++ ) {
                node.childNodes[ i ].classList.remove( 'dt-node-invisible' );
            }
        }
    }
    if ( busy ) {
        return;
    }
    if ( lastUndo.length == 1 ) {
        this.setAttribute( 'disabled', 'disabled' );
        resetButtonNode.setAttribute( 'disabled', 'disabled' );
    }
    var toUndo = lastUndo.pop();
    // Undo entries may be a single string,  or an array
    if ( typeof toUndo == 'string' || typeof toUndo == 'number' ) {
        undoSomething( toUndo );
    } else {
        for ( var j = 0; j < toUndo.length; j++ ) {
            undoSomething( toUndo[ j ] );
        }
    }
}

/**
 * Handler for the evaluate all button
**/
// handler for the evaluate all button
function evalAllButton() {
    if ( busy ) {
        return;
    }
    setBusy( true );
    if ( ast && ast.documentElement ) {
        var txt = textFromAST( ast.documentElement );
        // The root wrapper is always id 0
        var n = 'dt-id-0';
        apiEval( txt, function( k, t ) {
            if ( k == "OK" ) {
                var result = window.JSON.parse( t );
                if ( apiEvalHasResult( result ) ) {
                    evalTextDisplay( apiEvalGetResult( result ), n );
                    setBusy ( false );
                } else {
                    debugNote( mw.message( 'debugtemplates-error-eval' ) + ' ' + t );
                    setBusy ( false );
                }
            } else {
                debugNote( mw.message( 'debugtemplates-error-eval' ) + ' ' + k );
                setBusy ( false );
            }
        } );
    } else {
        setBusy ( false );
    }
}

/**
 * Handler for the set/unset all button.
**/
function toggleSetButton() {
    var i;
    var args = document.getElementById( 'dt-argtable' );
    var abody = args.getElementsByTagName( 'tbody' );
    if ( abody && abody[ 0 ] ) {
        abody = abody[ 0 ];
        // First figure out if we're toggling them all on or off.  We assume on, unless they're all on
        //  already, in which case it is off.
        var setgoal = "off";
        for ( i = 0; i < abody.rows.length; i++ ) {
            if ( abody.rows[ i ].cells[ 2 ].firstChild.classList.contains( 'dt-arg-set-no' ) ) {
                setgoal = "on";
                break;
            }
        }
        for ( i = 0; i < abody.rows.length; i++ ) {
            paramSetToggle( abody.rows[ i ], setgoal );
        }
    }
}

/**
 * Handler for the clear all button.
**/
function clearAllButton() {
    var args = document.getElementById( 'dt-argtable' );
    var abody = args.getElementsByTagName( 'tbody' );
    if ( abody && abody[ 0 ] ) {
        abody = abody[ 0 ];
        for ( var i = 0; i < abody.rows.length; i++ ) {
            abody.rows[ i ].cells[ 2 ].firstChild.value = '';
        }
    }
}

/**
 * Handler for the click mode radio buttons.
**/
function clickMode() {
    var dout = document.getElementById( 'dt-output' );
    if( getMouseClickMode() ==  'descend' ) {
        if ( !dout.classList.contains( 'dt-descend' ) ) {
            dout.classList.add( 'dt-descend' );
        }
    } else {
        if ( dout.classList.contains( 'dt-descend' ) ) {
            dout.classList.remove( 'dt-descend' );
        }
    }
}

/**
 * Initialization.
 *
 * Setup the main handlers, clear the stack, kick off the initial parse.
**/
function init() {
    var dtin = document.getElementById( 'dt-input' );
    dtin.addEventListener( 'change', debugTextHandler, false );
    document.getElementById( 'dt-api' ).addEventListener( 'change', apiHandler, false );
    document.getElementById( 'dt-title' ).addEventListener( 'change', titleHandler, false );

    undoButtonNode = document.getElementById( 'dt-undo' );
    resetButtonNode = document.getElementById( 'dt-reset' );
    resetButtonNode.addEventListener( 'click', resetButton, false );
    undoButtonNode.addEventListener( 'click', undoButton, false );

    document.getElementById( 'dt-eval' ).addEventListener( 'click', evalAllButton, false );
    document.getElementById( 'dt-args-set-toggle' ).addEventListener( 'click', toggleSetButton, false );
    document.getElementById( 'dt-args-value-clear' ).addEventListener( 'click', clearAllButton, false );

    document.getElementById( 'dt-radio-select' ).addEventListener( 'click', clickMode, false );
    document.getElementById( 'dt-radio-eval' ).addEventListener( 'click', clickMode, false );
    document.getElementById( 'dt-radio-descend' ).addEventListener( 'click', clickMode, false );

    clearCrumbs();
    setOutput();

    resizeArgTable();
    // Use mouseup rather than resize to capture the textarea resizing, as the user-resizable textarea
    //  elements do not send resize events
    document.getElementById( 'dt-input' ).addEventListener( 'mouseup', resizeArgTable, false );

    // Set a minimum output height so it does not jump around quite so much
    var mh = window.getComputedStyle( document.getElementById( 'dt-input' ) ).lineHeight;
    document.getElementById( 'dt-output' ).style.minHeight = mh;

    // Make the cursor correct for whatever mode we're in
    clickMode();
    // Finally, parse the input
    updateFromNewInput( dtin.value );
}

debugNoteClear();
init();
