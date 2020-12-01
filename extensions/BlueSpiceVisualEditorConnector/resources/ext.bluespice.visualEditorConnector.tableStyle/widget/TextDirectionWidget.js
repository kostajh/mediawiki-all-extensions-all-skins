/* INACTIVE CODE!!!! - Disabled due to incompatibility with Chrome and PDF export */
bs.util.registerNamespace( 'bs.vec.ui.widget' );

bs.vec.ui.widget.TextDirectionwidget = function( contextItem ) {
	var styles;
	bs.vec.ui.widget.TextDirectionwidget.parent.call( this, contextItem );

	styles = contextItem.getStyles();
	this.textDirection = styles.hasOwnProperty('textDirection' ) ? styles.textDirection : null;

	this.directionToggle = new OO.ui.ToggleButtonWidget( {
		title: OO.ui.deferMsg( 'bs-vec-widget-text-direction-tooltip' ),
		framed: false,
		icon: 'textDirVert',
		value: !!this.textDirection
	} );

	this.directionToggle.connect( this, {
		click: 'executeAction'
	} );

	this.$element.append( this.directionToggle.$element );
	this.$element.addClass( 'bs-vec-text-direction-widget' );
};

OO.inheritClass( bs.vec.ui.widget.TextDirectionwidget, bs.vec.ui.widget.CommandWidget );

bs.vec.ui.widget.TextDirectionwidget.prototype.executeAction = function() {
	if ( !this.shouldExecute() ) {
		return;
	}
	this.textDirection = this.textDirection ? null : 'vertical-rl';
	this.contextItem.execCommand( 'textDirection', { textDirection: this.textDirection } );
};
/* INACTIVE CODE!!!! - Disabled due to incompatibility with Chrome and PDF export */
