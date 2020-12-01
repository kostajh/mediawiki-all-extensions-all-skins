bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.ColorAnnotationCommand = function() {
	// Parent constructor
	bs.vec.ui.ColorAnnotationCommand.super.call(
		this, 'color'
	);
	this.warning = null;
	this.supportedSelections = ['linear'];
	this.action = 'content';
	this.method = 'insert';
	this.args = [ 'textStyle/color' ];

	this.listenForChange = false;

};

OO.inheritClass( bs.vec.ui.ColorAnnotationCommand, ve.ui.Command );


/**
 * @inheritdoc
 */
bs.vec.ui.ColorAnnotationCommand.prototype.execute = function ( surface, args, source ) {
	if ( !this.isExecutable( surface.getModel().getFragment( surface.getModel().getSelection() ) ) ) {
		return;
	}

	var picker = this.showPicker();
	picker.connect( this, {
		colorSelected: function( data ) {
			data = data || {};
			var annotationAction = new ve.ui.AnnotationAction( surface );
			annotationAction.clear( 'textStyle/color' );

			var annotationData = {
				attributes: data,
				type: 'textStyle/color'
			};

			annotationAction.set( 'textStyle/color', annotationData );
		},
		clear: function() {
			var annotationAction = new ve.ui.AnnotationAction( surface );
			annotationAction.clear( 'textStyle/color' );
		}
	} );

	this.listenForChange = false;
	this.onSelectionChangedDebounced = ve.debounce( this.onSelectionChanged.bind( this ) );
	surface.getView().$document.on(
		'selectionchange',
		surface,
		this.onSelectionChangedDebounced
	);
};

bs.vec.ui.ColorAnnotationCommand.prototype.onSelectionChanged = function ( e ) {
	if ( !this.listenForChange ) {
		this.listenForChange = true;
		return;
	}

	$( document.body ).find( '.oojs-plus-color-picker-standalone' ).remove();
	e.data.getView().$document.off( 'selectionchange', this.onSelectionChangedDebounced );
};

bs.vec.ui.ColorAnnotationCommand.prototype.showPicker = function () {
	$( document.body ).find( '.oojs-plus-color-picker-standalone' ).remove();

	var offset = $( '.ve-ui-toolbar-group-style' ).offset(),
		picker = new OOJSPlus.ui.widget.ColorPickerStandaloneWidget( {
			colors: bs.vec.config.get( 'ColorPickerColors' )
		} );

	$( document.body ).append( picker.$element );
	offset.top += 45;
	offset.left -= 50;
	picker.$element.offset( offset );

	return picker;
};

bs.vec.ui.ColorAnnotationCommand.prototype.isExecutable = function ( fragment ) {
	// Parent method
	var ranges = fragment.getSelection().getRanges( ve.init.target.getSurface().getModel().getDocument() );
	var hasActualSelection = false;
	for( var i = 0; i < ranges.length; i++ ) {
		var range = ranges[i];
		if ( range.from !== range.to ) {
			hasActualSelection = true;
			break;
		}
	}

	if ( ve.init.target.getSurface() === null ) {
		return false;
	}
	return ve.init.target.getSurface().getMode() === 'visual' &&
		bs.vec.ui.ColorAnnotationCommand.super.prototype.isExecutable.apply( this, arguments ) &&
		hasActualSelection;
};


/* Registration */
ve.ui.commandRegistry.register( new bs.vec.ui.ColorAnnotationCommand() );
