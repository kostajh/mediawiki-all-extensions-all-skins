/**
 * This file contains UI elements.
 *
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

/**
 * Namespace for user interface elements
 */
var LWUI = {};
var i18n = i18n || { t: function() { return false; } };

/**
 * @type {CSSStyleSheet|null}
 */
LWUI.styleSheet = (function() {
    var elem = $('<style id="lwuiStyle"></style>');
    $('head').append(elem);

    var sheet;
    for (var sid in document.styleSheets) {
        if (document.styleSheets.hasOwnProperty(sid)) {
            sheet = document.styleSheets[sid];
            if (sheet.ownerNode == elem[0]) {
                return sheet;
            }
        }
    }
    return null;
})();


LWUI.saveShortcut = "shift+alt+s";
LWUI.addSaveShortcut = function(element, callback) {
    LWUI.addShortcut('s', element, callback);
};
/**
 * Binds Shift+Alt+Key on the given element
 */
LWUI.addShortcut = function(key, element, callback) {
    var lowerKey = key.toLowerCase();
    element.bind( 'keydown', function(event) {
        if (String.fromCharCode(event.which).toLowerCase() == lowerKey && event.shiftKey && event.altKey) {
            console.log('Shortcut triggered');
            callback();
            event.preventDefault();
        }
        //else { console.log('Shortcut not triggered', event.which, String.fromCharCode(event.which)); }
    });
};
LWUI.addEnterShortcut = function(element, callback) {
    element.bind( 'keydown', function( event ) {
        if (event.which == 13) {
            callback();
            event.preventDefault();
        }
    } );
};

LWUI.addCSSRule = function(rule) {
    if (this.styleSheet) {
        this.styleSheet.insertRule(rule, this.styleSheet.cssRules.length);
    } else {
        console.log('Cannot add rule, style sheet is null.');
    }
};


LWUI.status = {
    'ok' : 'ok',
    'pending' : 'pending',
    'error' : 'error'
};
LWUI.setStatus = function(element, status) {
    console.log('New status: ' + status);
    element.removeClass('lwePending').removeClass('lweError');
    if (status === LWE.status.pending) {
        element.addClass('lwePending');
    } else if (status == LWE.status.error) {
        element.addClass('lweError');
    }
};





/**
 * A Button with an optional callback.
 * @param options Configuration object with 'callback' (on click), 'text', 'class', 'title'
 * @see LWUI.tplButton
 * @constructor
 */
LWUI.Button = function(options) {
    if (!(this instanceof LWUI.Button)) { return new LWUI.Button(options); }

    this.callback = options.callback;
    this.dom = $(LWUI.Button.tpl(options));
    this.dom.click(function(button) { return function() { button.trigger(); }}(this));

    return this;
};
LWUI.Button.prototype.trigger = function() {
    this.dom.focus(); // blur() events on other fields are processed also when a shortcut was used
    if (typeof this.callback === "function") {
        this.callback();
    }
};
LWUI.Button.tpl = Handlebars.compile('<input type="button" class="lwuiButton {{class}}" value="{{text}}" {{#if title}}title="{{title}}"{{/if}}/>');

/**
 * Editable input field.
 * @param options Configuration object with 'class', 'text', 'val', 'callback' (change event), 'clearOnFocus' (first focus)
 * @returns {*}
 * @constructor
 */
LWUI.Editable = function(options) {
    if (!(this instanceof LWUI.Editable)) { return new LWUI.Editable(options); }

    this.focusCount = 0;
    this.clearOnFocus = !!options.clearOnFocus; // Ensure true/false
    this.callback = options.callback;
    this.saveCallback = options.saveCallback;
    this.options = options;

    this.dom = $(this.tpl(options));


    this.domVal = $('.val', this.dom);
    this.domButtons = $('.buttons', this.dom);

    var editable = this;
    this.domVal.focus( function() {
        editable.onfocus();
    } );
    this.domVal.change( function() {
        editable.trigger();
    } );


    if (typeof this.saveCallback === 'function') {
        var cancel = LWUI.Button({
            'text' : (i18n.t('button.cancel') || 'Cancel'),
            'callback' : function() {
                editable.reset();
            }
        });
        var save = LWUI.Button({
            'text' : (i18n.t('button.save') || 'Save'),
            'callback' : function() {
                editable.triggerSave();
            }
        });
        this.domButtons.append(cancel.dom);
        this.domButtons.append(save.dom);

        LWUI.addSaveShortcut(this.dom, function() { editable.triggerSave(); });
        LWUI.addEnterShortcut(this.dom, function() { editable.triggerSave(); });

    }


    return this;
};
LWUI.Editable.prototype.text = function(text) {
    if (text === undefined) {
        text = this.domVal.val();
    } else {
        this.domVal.val(text);
    }
    return text;
};
LWUI.Editable.prototype.onfocus = function() {
    if (this.clearOnFocus && this.focusCount == 0) {
        this.text('');
    }
    this.focusCount++;
};
LWUI.Editable.prototype.focus = function() {
    this.domVal.focus();
};
LWUI.Editable.prototype.trigger = function() {
    if (typeof this.callback === 'function') {
        this.callback();
    }
};
LWUI.Editable.prototype.triggerSave = function() {
    if (typeof this.saveCallback === 'function') {
        this.saveCallback(this.text());
    }
};
LWUI.Editable.prototype.reset = function() {
    this.domVal.val(this.options.val);
    this.clearOnFocus = !!this.options.clearOnFocus;
};
LWUI.Editable.prototype.setStatus = function(status) {
    LWUI.setStatus(this.dom, status);
};
LWUI.Editable.prototype.setPending = function() {
    this.setStatus(LWUI.status.pending);
};
LWUI.Editable.prototype.setError = function() {
    this.setStatus(LWUI.status.error);
};
LWUI.Editable.prototype.setOk = function() {
    this.setStatus(LWUI.status.ok);
}
LWUI.Editable.prototype.tpl = Handlebars.compile(
    '<div class="lwuiEditable {{class}}">' +
        '<span class="text">{{text}}</span>' +
        '<input type="text" class="val" value="{{val}}"/>' +
        '<div class="buttons"></div>' +
    '</div>');


/**
 * Progress bar
 * @param options Configuration object with 'class'
 * @constructor
 */
LWUI.ProgressBar = function(options) {
    if (!(this instanceof LWUI.ProgressBar)) { return new LWUI.ProgressBar(options); }

    this.dom = $(LWUI.ProgressBar.tpl(options));

    this.status = {
        text : 'Initialised.',
        value : 100
    };


    this.ui = {
        'done' : $('.done', this.dom),
        'remaining' : $('.remaining', this.dom),
        'text' : $('.text', this.dom)
    };

    return this;
};
LWUI.ProgressBar.prototype.text = function(text) {
    this.status.text = text;
    this.updateUI();
    return this;
};
LWUI.ProgressBar.prototype.value = function(val) {
    if (isNaN(parseInt(val))) {
        console.log('Invalid value, must be int: ' + val);
        return this;
    }
    if (val < 0) { val = 0; }
    if (val > 100) { val = 100; }
    this.status.value = val;
    this.updateUI();
    return this;
};
LWUI.ProgressBar.prototype.updateUI = function() {
    this.ui.done.css('width', this.status.value+'%');
    this.ui.remaining.css('width', (100-this.status.value)+'%');
    this.ui.text.text(this.status.text + ': ' + this.status.value + ' %');

    if (this.status.value >= 100) {
        this.dom.addClass('completed');
    } else {
        this.dom.removeClass('completed');
    }
};
LWUI.ProgressBar.tpl = Handlebars.compile(
    '<div class="lwuiProgressBar {{class}}">' +
        '<div class="done"></div><div class="remaining"></div>' +
        '<div class="text">Initialising …</div>' +
    '</div>'
);


/**
 * Select (dropdown) element
 * @param options Configuration object with 'callback', 'text', 'val', 'data' = [ {id, text} ]
 * @returns {LWUI.Select}
 * @constructor
 */
LWUI.Select = function(options) {
    if (!(this instanceof LWUI.Select)) { return new LWUI.Select(options); }

    this.options = options;
    this.callback = options.callback;
    this.saveCallback = options.saveCallback;
    this.dom = $(this.tpl(options));
    this.ui = {
        select : $('select', this.dom),
        buttons : $('.buttons', this.dom)
    };

    var elem;

    for (var d = 0, D = options.data.length; d < D; d++) {
        elem = $(this.tplOption(options.data[d]));
        this.ui.select.append(elem);
    }
    this.ui.select.val(options.val);

    var select = this;
    this.dom.click( function() { select.trigger(); } );
    this.dom.keypress( function() { select.trigger(); } );

    var cancel = new LWUI.Button({
        'text' : (i18n.t('button.cancel') || 'Cancel'),
        'callback' : function() { select.reset(); }
    });
    var save = new LWUI.Button({
        'text' : (i18n.t('button.save') || 'Save'),
        'callback' : function() { select.triggerSave(); }
    });

    if (typeof this.saveCallback === 'function') {
        this.ui.buttons.append(cancel.dom);
        this.ui.buttons.append(save.dom);
    }

    return this;
};
LWUI.Select.prototype.trigger = function() {
    if (this.val() != this.options.val) {
        this.dom.addClass('modified');
    }
    if (typeof this.callback === 'function') {
        this.callback(this.val());
    }
};
LWUI.Select.prototype.triggerSave = function() {
    if (typeof this.saveCallback === 'function') {
        this.saveCallback(this.val());
    }
};
LWUI.Select.prototype.reset = function() {
    this.ui.select.val(this.options.val);
};
LWUI.Select.prototype.val = function() {
    return this.ui.select.val();
};
LWUI.Select.prototype.setStatus = function(status) {
    LWUI.setStatus(this.dom, status);
};
LWUI.Select.prototype.setPending = function() {
    this.setStatus(LWUI.status.pending);
};
LWUI.Select.prototype.setError = function() {
    this.setStatus(LWUI.status.error);
};
LWUI.Select.prototype.setOk = function() {
    this.setStatus(LWUI.status.ok);
};
LWUI.Select.prototype.tplOption = Handlebars.compile('<option value="{{id}}">{{{text}}}</option>');
LWUI.Select.prototype.tpl = Handlebars.compile(
    '<div class="lwuiSelectable">' +
        '<label>{{text}}<select></select></label>' +
        '<div class="buttons"></div>' +
    '</div>'
);


/**
 * Select element with multiple options.
 * @param options Configuration object
 * @returns {LWUI.Multiselect}
 * @constructor
 */
LWUI.Multiselect = function(options) {
    if (!(this instanceof LWUI.Multiselect)) { return new LWUI.Multiselect(options); }

    this.callback = options.callback;
    this.dom = $('<div class="lwuiMultiselect"></div>');

    var multiselect = this;
    var tpl = Handlebars.compile('<label><input type="checkbox" value="{{id}}"/>{{text}}</label>');
    for (var d = 0, D = options.data.length; d < D; d++) {
        var elem = $(tpl(options.data[d]));
        if (options.val && options.val.indexOf(elem.id) >= 0) {
            $('input', elem).prop('checked', true);
        }
        $('input', elem).change( function() {
                multiselect.trigger();
            } );
        this.dom.append(elem);
    }

    return this;
};
LWUI.Multiselect.prototype.val = function() {
    var list = [ ];
    $('input:checked', this.dom).each( function() { list.push($(this).val()); } );
    return list;
};
LWUI.Multiselect.prototype.trigger = function() {
    if (typeof this.callback === 'function') {
        this.callback();
    }
};


/**
 * Checkbox
 * @param options Configuration object with 'text', 'val' (bool), 'callback'
 * @constructor
 */
LWUI.Checkable = function(options) {
    if (!(this instanceof LWUI.Checkable)) { return new LWUI.Checkable(options); }

    var checkable = this;

    this.dom = $(LWUI.Checkable.tpl(options));
    this.callback = options.callback;
    this.ui = {
        'checkbox' : $('input', this.dom)
    };


    $('input', this.dom).change( function() {
        checkable.trigger();
    });
    if (options.val === true) {
        this.ui.checkbox[0].checked = true;
    }

    return this;
};
LWUI.Checkable.prototype.val = function() {
    return this.ui.checkbox[0].checked;
};
LWUI.Checkable.prototype.trigger = function() {
    if (typeof this.callback === 'function') {
        this.callback(this.val());
    }
};
LWUI.Checkable.prototype.setStatus = function(status) {
    LWUI.setStatus(this.dom, status);
};
LWUI.Checkable.prototype.setPending = function() {
    this.setStatus(LWUI.status.pending);
};
LWUI.Checkable.prototype.setError = function() {
    this.setStatus(LWUI.status.error);
};
LWUI.Checkable.prototype.setOk = function() {
    this.setStatus(LWUI.status.ok);
};
LWUI.Checkable.tpl = Handlebars.compile('<div class="lwuiCheckable"><label><input type="checkbox"/>{{text}}</label></div>');



LWUI.ToolButton = function(options) {
    if (!(this instanceof LWUI.ToolButton)) { return new LWUI.ToolButton(options); }

    var toolButton = this;

    this.checked = !!options.checked;
    this.callback = options.callback;

    this.dom = $(this.tpl(options));

    this.dom.click( function() {
        toolButton.checked = !toolButton.checked;
        toolButton.trigger();
    });

    this.updateUI();

    return this;
};
LWUI.ToolButton.prototype.val = function(checked) {
    if (checked !== undefined) {
        this.checked = checked;
        this.trigger();
    }
    return this.checked;
};
LWUI.ToolButton.prototype.updateUI = function() {
    if (this.checked) {
        this.dom.addClass('checked');
    } else {
        this.dom.removeClass('checked');
    }
};
LWUI.ToolButton.prototype.trigger = function() {
    this.updateUI();
    if (typeof this.callback === 'function') {
        this.callback();
    }
};
LWUI.ToolButton.prototype.tpl = Handlebars.compile(
    '<div class="lwuiToolButton">' +
        '<img src="{{url}}" title="{{text}}"/>' +
    '</div>'
);


/**
 * @param {Object} options Options array with 'text'
 * @returns {LWUI.DynamicList}
 * @constructor
 */
LWUI.DynamicList = function(options) {
    if (!(this instanceof LWUI.DynamicList)) { return new LWUI.DynamicList(options); }

    this.$dom = $(this.tpl(options));
    this.$domList = $('ul', this.$dom);

    return this;
};
/**
 * @param {string} text
 * @returns {jQuery}
 */
LWUI.DynamicList.prototype.add = function(text) {

    var $elem = $( this.tplEntry({
        text: text
    }) );

    this.$domList.append($elem);

    return $elem;
};
LWUI.DynamicList.prototype.tpl = Handlebars.compile(
    '<div class="lwuiDynamicList">' +
        '<h4>{{text}}</h4>' +
        '<ul></ul>' +
    '</div>'
);
LWUI.DynamicList.prototype.tplEntry = Handlebars.compile('<li>{{text}}</li>');


/**
 * Draws a bar chart from the given data.
 * Data format: {{name: String, value: Number, id: Number}}
 * @param {{data: Array, callback: function}} options
 * @returns {LWUI.BarChart}
 * @constructor
 */
LWUI.BarChart = function ( options ) {
    if ( !(this instanceof LWUI.BarChart) ) { return new LWUI.BarChart( options ); }

    this.options = options;
    this.data = options.data;
    this.callback = options.callback;

    /// Holds the y positions of all entries
    /// @type {Object.<Number>}
    this.yPositions = {};

    this.dom = $('<div class="lwuiBarChart"></div>');


    var data = this.data,
        chart = this;
    var canvas = SVG( this.dom[0] ).size( 500, 30*data.length );

    this.highlightRect = canvas.rect( 500, 30 )
        .move( 0, -30 )
        .attr( 'class', 'highlightRect' );

    var text,
        rect,
        y;

    var d, D,
        max = 0;
    for ( d = 0, D = data.length; d < D; d++ ) {
        if ( data[d].value > max ) {
            max = data[d].value;
        }
    }

    for ( d = 0, D = data.length; d < D; d++ ) {

        y = 30 * d;
        this.yPositions[data[d].id] = y;

        text = canvas.text( data[d].name ).move( 0, y + 4 );
        text.on( 'click', function ( id ) {
            return function () {
                chart.trigger( id );
            }
        }( data[d].id ) );

        rect = canvas.rect( 300 * data[d].value / max, 30 - 2 )
            .move( 200, y + 1.5 )
            .attr( 'class', 'colRect' );

        text = canvas.text( String( data[d].value ) )
            .move( 210, y + 1.5 )
            .attr( 'class', 'dataValue' );

    }

    return this;
};
LWUI.BarChart.prototype.trigger = function( id ) {
    if ( typeof this.callback == 'function' ) {
        this.callback( id );
    }
};
/**
 * Returns the y position of the element with the given ID (e.g. for scrolling)
 * @param id
 * @returns {Number|undefined}
 */
LWUI.BarChart.prototype.getY = function ( id ) {
    return this.yPositions[id];
};
LWUI.BarChart.prototype.highlight = function ( id ) {
    var y = this.getY( id );
    if ( y !== undefined ) {
        this.highlightRect.move( 0, y );
    }
};

/**
 * Draws a tree with the data given as a matrix. Items can be selected.
 * The callback receives the ID of the clicked element, and a list of IDs that are currently selected. An item also
 * counts as selected if its parent is selected.
 * @param {{
 *      titleHeight: Number,
 *      rowHeight: Number,
 *      padding,
 *      lineLength,
 *      lineWidth,
 *      data: Array.<{width, row: Array.<{id, text}>}>,
 *      callback: function( id: Number|null, selectedIDs: Array.<Number> )
 *  }} options
 * @returns {LWUI.TreeMatrix}
 * @constructor
 */
LWUI.TreeMatrix = function ( options ) {
    if ( !(this instanceof LWUI.TreeMatrix) ) { return new LWUI.TreeMatrix( options ); }

    this.options = options;
    /// @type {Array.<{width, row: Array.<{id, text}>}>}
    this.data = options.data;
    this.callback = options.callback;

    /// @type {Object.<Number, {selected, svgElement}>}
    this.textElements = {};
    /// SVG element containing the cell text elements
    this.textGroup = undefined;

    this.dom = $( '<div class="lwuiTreeMatrix"></div>' );


    var c;
    this.cols = this.data.length;
    this.rows = this.cols > 0 ? this.data[0].row.length : 0;

    // Canvas height
    var H = options.rowHeight * this.rows + options.titleHeight;
    // Canvas width
    var W = 0;

    for ( c = 0; c < this.cols; c++ ) {

        if ( c > 0 ) {
            if ( this.data[c].row.length != this.data[c - 1].row.length ) {
                throw Error( 'Column ' + c + ' has a different number of entries.' );
            }
        }

        W += this.data[c].width;
    }


    var canvas = SVG( this.dom[0] )
        .size( W, H )
        .attr( 'class', 'lwuiTreeMatrix' );


    var colStatus = [],
        status;
    for ( c = 0; c < this.cols; c++ ) {

        status = {
            prevY: 0,
            prevID: undefined,
            width: this.data[c].width,
            x: c == 0 ? 0 : colStatus[c - 1].x + colStatus[c - 1].width
        };
        status.xp = status.x + this.options.padding;

        colStatus.push( status );

        canvas.rect( status.width, H )
            .move( status.x, 0 )
            .attr( 'class', c % 2 == 0 ? 'bg-even' : 'bg-odd' );

        canvas.text( this.data[c].title )
            .move( status.xp, 0 )
            .attr( 'class', 'title' );
    }

    var y, rowData, text,
        self = this;

    // For odd numbers, translate by 0.5 px to ensure integer pixel borders
    var dy = Math.floor( this.options.rowHeight / 2 ) + (this.options.lineWidth % 2) * .5;

    this.textGroup = canvas.group()
        .attr( 'class', 'items' );

    for ( var row = 0; row < this.rows; row++ ) {

        y = options.titleHeight + row * options.rowHeight;

        for ( c = this.cols - 1; c >= 0; c-- ) {

            /// @type {{id, text: String, duplicate: Boolean}}
            rowData = this.data[c].row[row];

            if ( colStatus[c].prevID == rowData.id ) {
                // This cell does not appear in the matrix.
                rowData.duplicate = true;

            } else {
                rowData.duplicate = false;

                // Item has changed: Write its text
                text = canvas.text( rowData.text || '(—)' )
                    .move( colStatus[c].xp, y )
                    .on( 'click', function ( id ) {
                        return function () {
                            self.trigger( id );
                        }
                    }( rowData.id ) );
                this.textGroup.add( text );
                this.textElements[rowData.id] = {
                    svgElement: text,
                    selected: false
                };

                // If it is not the leftmost item, draw the tree to the left of it.
                if ( c > 0 ) {

                    if ( colStatus[c - 1].prevID != this.data[c - 1].row[row].id ) {

                        // Left item changed: Single line to the current item is enough
                        canvas.line( colStatus[c].x - this.options.lineLength, y + dy,
                            colStatus[c].x + this.options.lineLength, y + dy );

                    } else {

                        // Left item was there already; draw a bottom--right polyline
                        canvas.polyline( [
                            [colStatus[c].x, colStatus[c - 1].prevY + dy],
                            [colStatus[c].x, y + dy],
                            [colStatus[c].x + this.options.lineLength, y + dy]
                        ] );

                    }
                }

                // Remember the y position since several items might lie in-between.
                colStatus[c].prevY = y;

            }

            colStatus[c].prevID = rowData.id;

        }

    }


    return this;
};
LWUI.TreeMatrix.prototype.unselectAll = function () {
    var rowData;
    for ( var row = 0; row < this.rows; row++ ) {

        for ( var col = 0; col < this.cols; col++ ) {
            rowData = this.data[col].row[row];

            if ( rowData.id ) {
                this.textElements[rowData.id].selected = false;
            }
        }
    }
    this.callback( null, this.updateUI() );
};
LWUI.TreeMatrix.prototype.updateUI = function () {
    var selected,
        selectedIDs = [],
        rowData;

    for ( var row = 0; row < this.rows; row++ ) {
        selected = false;

        for ( var col = 0; col < this.cols; col++ ) {
            rowData = this.data[col].row[row];

            if (rowData.id === undefined) {
                continue;
            }

            if ( this.textElements[rowData.id].selected ) {
                selected = true;
            }

            if ( !rowData.duplicate ) {

                if ( selected ) {
                    selectedIDs.push( rowData.id );
                    this.textElements[rowData.id].svgElement.attr( 'class', 'selected' );
                } else {
                    this.textElements[rowData.id].svgElement.attr( 'class', null );
                }

            }
        }
    }
    this.textGroup.attr( 'class', selectedIDs.length > 0 ? 'items childrenSelected' : 'items' );

    return selectedIDs;
};
LWUI.TreeMatrix.prototype.trigger = function ( id ) {

    this.textElements[id].selected = !this.textElements[id].selected;

    var selectedIDs = this.updateUI();
    this.callback( id, selectedIDs );
};

