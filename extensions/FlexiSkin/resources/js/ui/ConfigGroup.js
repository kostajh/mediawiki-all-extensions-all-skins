flexiskin.ui.ConfigGroup = function ( name, cfg ) {
	flexiskin.ui.ConfigGroup.parent.call( this );

	this.name = name;
	this.label = cfg.label;
	this.expanded = cfg.expanded || false;

	this.makeHeader();
	this.$itemContainer = $( '<div>' ).addClass( 'fs-group-item-container' );

	this.items = cfg.items || [];
	for ( var i = 0; i < this.items.length; i++ ) {
		this.$itemContainer.append( this.items[ i ].$element );
	}
	this.$element.append( this.$itemContainer );

	if ( !this.expanded ) {
		this.$itemContainer.hide();
	}

	this.$element.addClass( 'fs-group' );
};

OO.inheritClass( flexiskin.ui.ConfigGroup, OO.ui.Widget );

flexiskin.ui.ConfigGroup.prototype.makeHeader = function () {
	this.$headerContainer = $( '<div>' ).addClass( 'fs-group-header' );
	this.toggleButton = new OO.ui.ButtonWidget( {
		icon: 'expand',
		framed: false,
		classes: [ 'fs-group-toggle' ]
	} );

	this.toggleButton.connect( this, {
		click: 'onToggle'
	} );

	this.headerLabel = new OO.ui.LabelWidget( {
		label: this.label,
		classes: [ 'fs-group-header-label' ]
	} );

	this.$headerContainer.append( this.headerLabel.$element, this.toggleButton.$element );

	this.$element.append( this.$headerContainer );
};

flexiskin.ui.ConfigGroup.prototype.getName = function () {
	return this.name;
};

flexiskin.ui.ConfigGroup.prototype.getItems = function () {
	return this.items;
};

flexiskin.ui.ConfigGroup.prototype.onToggle = function () {
	if ( this.expanded ) {
		this.$itemContainer.slideUp( 300, function () {
			this.toggleButton.setIcon( 'expand' );
			this.expanded = false;
		}.bind( this ) );
	} else {
		this.$itemContainer.slideDown( 300, function () {
			this.toggleButton.setIcon( 'collapse' );
			this.expanded = true;
		}.bind( this ) );
	}
};
