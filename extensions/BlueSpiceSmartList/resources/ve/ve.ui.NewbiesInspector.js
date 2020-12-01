ve.ui.NewbiesInspector = function VeUiNewbiesInspector( config ) {
	// Parent constructor
	ve.ui.NewbiesInspector.super.call( this, ve.extendObject( { padded: true }, config ) );
};

/* Inheritance */

OO.inheritClass( ve.ui.NewbiesInspector, ve.ui.MWLiveExtensionInspector );

/* Static properties */

ve.ui.NewbiesInspector.static.name = 'newbiesInspector';

ve.ui.NewbiesInspector.static.title = OO.ui.deferMsg( 'bs-smartlist-ve-newbies-title' );

ve.ui.NewbiesInspector.static.modelClasses = [ ve.dm.NewbiesNode ];

ve.ui.NewbiesInspector.static.dir = 'ltr';

//This tag does not have any content
ve.ui.NewbiesInspector.static.allowedEmpty = true;
ve.ui.NewbiesInspector.static.selfCloseEmptyBody = true;

/* Methods */

/**
 * @inheritdoc
 */
ve.ui.NewbiesInspector.prototype.initialize = function () {

	// Parent method
	ve.ui.NewbiesInspector.super.prototype.initialize.call( this );

	this.input.$element.remove();
	// Index layout
	this.indexLayout = new OO.ui.PanelLayout( {
		scrollable: false,
		expanded: false,
		padded: true
	} );

	this.countInput = new OO.ui.NumberInputWidget( { min: 1, max: 250, isInteger: true } );

	this.countLayout = new OO.ui.FieldLayout( this.countInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-newbiesinspector-count' )
	} );

	// Initialization
	this.$content.addClass( 've-ui-newbies-inspector-content' );

	this.indexLayout.$element.append(
		this.countLayout.$element,
		this.generatedContentsError.$element
	);
	this.form.$element.append(
		this.indexLayout.$element
	);
};

/**
 * @inheritdoc
 */
ve.ui.NewbiesInspector.prototype.getSetupProcess = function ( data ) {
	return ve.ui.NewbiesInspector.super.prototype.getSetupProcess.call( this, data )
		.next( function () {
			var attributes = this.selectedNode.getAttribute( 'mw' ).attrs;

			if( attributes.count ) {
				this.countInput.setValue( attributes.count );
			}
			this.countInput.on( 'change', this.onChangeHandler );

			//Get this out of here
			this.actions.setAbilities( { done: true } );
		}, this );
};

ve.ui.NewbiesInspector.prototype.updateMwData = function ( mwData ) {
	// Parent method
	ve.ui.NewbiesInspector.super.prototype.updateMwData.call( this, mwData );

	if( this.countInput.getValue() ) {
		mwData.attrs.count = this.countInput.getValue();
	} else {
		delete( mwData.attrs.count );
	}
};

/**
 * @inheritdoc
 */
ve.ui.NewbiesInspector.prototype.formatGeneratedContentsError = function ( $element ) {
	return $element.text().trim();
};

/**
 * Append the error to the current tab panel.
 */
ve.ui.NewbiesInspector.prototype.onTabPanelSet = function () {
	this.indexLayout.getCurrentTabPanel().$element.append( this.generatedContentsError.$element );
};

/* Registration */

ve.ui.windowFactory.register( ve.ui.NewbiesInspector );
