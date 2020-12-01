bs.util.registerNamespace( 'bs.vec.ui.widget' );

bs.vec.ui.widget.ButtonCommandWidget = function( contextItem, cfg ) {
	bs.vec.ui.widget.ButtonCommandWidget.parent.call( this, contextItem );

	cfg = cfg || {};
	this.command = cfg.command || '';
	this.icon = cfg.icon || '';
	this.title= cfg.title || '';
	this.framed = cfg.framed || '';
	// Specifies if running this command can take a long time
	// in which case accidental multi-execs will be prevented
	this.expensive = cfg.expensive || false;

	this.initWidget();
};

OO.inheritClass( bs.vec.ui.widget.ButtonCommandWidget, bs.vec.ui.widget.CommandWidget );

bs.vec.ui.widget.ButtonCommandWidget.prototype.executeAction = function() {
	if ( !this.shouldExecute() ) {
		return;
	}
	if ( !this.expensive ) {
		return this.contextItem.execCommand( this.command );
	}
	this.setWaiting( true ).done( function( commandWidget ) {
		commandWidget.contextItem.execCommand( commandWidget.command );
		commandWidget.setWaiting( false );
	} );

};

bs.vec.ui.widget.ButtonCommandWidget.prototype.setWaiting = function( isWaiting ) {
	var dfd = $.Deferred();
	if ( isWaiting ) {
		this.buttonWidget.setIcon( 'clock' );
	} else {
		this.buttonWidget.setIcon( this.icon );
	}
	this.buttonWidget.setDisabled( isWaiting );
	// Not particularly nice - we need to allow change to DOM
	// before starting potentially expensive command exec
	setTimeout( function() {
		dfd.resolve( this );
	}.bind( this ), 30 );
	return dfd.promise();
};

bs.vec.ui.widget.ButtonCommandWidget.prototype.initWidget = function() {
	this.buttonWidget = new OO.ui.ButtonWidget( {
		title: this.title,
		icon: this.icon,
		framed: this.framed
	} );

	this.buttonWidget.connect( this, {
		click: 'executeAction'
	} );

	this.$element.append( this.buttonWidget.$element );
	this.$element.addClass( 'bs-vec-button-widget' );
};
