ve.ui.TopListInspector = function VeUiTopListInspector( config ) {
	// Parent constructor
	ve.ui.TopListInspector.super.call( this, ve.extendObject( { padded: true }, config ) );
};

/* Inheritance */

OO.inheritClass( ve.ui.TopListInspector, ve.ui.MWLiveExtensionInspector );

/* Static properties */

ve.ui.TopListInspector.static.name = 'topListInspector';

ve.ui.TopListInspector.static.title = OO.ui.deferMsg( 'bs-smartlist-ve-toplist-title' );

ve.ui.TopListInspector.static.modelClasses = [ ve.dm.TopListNode ];

ve.ui.TopListInspector.static.dir = 'ltr';

//This tag does not have any content
ve.ui.TopListInspector.static.allowedEmpty = true;
ve.ui.TopListInspector.static.selfCloseEmptyBody = true;

/* Methods */

/**
 * @inheritdoc
 */
ve.ui.TopListInspector.prototype.initialize = function () {

	// Parent method
	ve.ui.SmartListInspector.super.prototype.initialize.call( this );

	this.input.$element.remove();
	// Index layout
	this.indexLayout = new OO.ui.PanelLayout( {
		scrollable: false,
		expanded: false,
		padded: true
	} );

	this.createFields();

	this.setLayouts();

	// Initialization
	this.$content.addClass( 've-ui-smartlist-inspector-content' );

	this.indexLayout.$element.append(
		this.countLayout.$element,
		this.nsLayout.$element,
		this.catLayout.$element,
		this.periodLayout.$element,
		this.portletperiodLayout.$element
	);
	this.form.$element.append(
		this.indexLayout.$element
	);
};

ve.ui.TopListInspector.prototype.createFields = function() {
	this.countInput = new OO.ui.NumberInputWidget( { min: 1, max: 250, isInteger: true } );
	this.nsInput = new OO.ui.TextInputWidget();
	this.catInput = new OO.ui.TextInputWidget();

	this.periodInput = new OO.ui.DropdownInputWidget( {
		options: [
			{
				data: '-',
				label: '-'
			},
			{
				data: 'day',
				label: mw.message( 'bs-smartlist-ve-period-day-label' ).plain()
			},
			{
				data: 'week',
				label: mw.message( 'bs-smartlist-ve-period-week-label' ).plain()
			},
			{
				data: 'month',
				label: mw.message( 'bs-smartlist-ve-period-month-label' ).plain()
			}
		]
	} );

	this.portletperiodInput = new OO.ui.NumberInputWidget( { min: 1, max: 250, isInteger: true } );
}

ve.ui.TopListInspector.prototype.setLayouts = function() {
	this.countLayout = new OO.ui.FieldLayout( this.countInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-toplistinspector-count' )
	} );

	this.nsLayout =  new OO.ui.FieldLayout( this.nsInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-toplistinspector-ns' )
	} );
	this.catLayout = new OO.ui.FieldLayout( this.catInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-toplistinspector-cat' )
	} );
	this.periodLayout = new OO.ui.FieldLayout( this.periodInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-toplistinspector-period' )
	} );
	this.portletperiodLayout = new OO.ui.FieldLayout( this.portletperiodInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-toplistinspector-portletperiod' )
	} );
}

/**
 * @inheritdoc
 */
ve.ui.TopListInspector.prototype.getSetupProcess = function ( data ) {
	return ve.ui.TopListInspector.super.prototype.getSetupProcess.call( this, data )
		.next( function () {
			var attributes = this.selectedNode.getAttribute( 'mw' ).attrs;

			this.nsInput.setValue( attributes.ns || '' );
			this.catInput.setValue( attributes.cat || '' );
			if( attributes.count ) {
				this.countInput.setValue( attributes.count );
			}
			if( attributes.portletperiod ) {
				this.portletperiodLInput.setValue( attributes.portletperiod );
			}
			if( attributes.period ) {
				this.periodInput.setValue( attributes.period );
			}

			this.nsInput.on( 'change', this.onChangeHandler );
			this.catInput.on( 'change', this.onChangeHandler );
			this.countInput.on( 'change', this.onChangeHandler );
			this.portletperiodInput.on( 'change', this.onChangeHandler );
			this.periodInput.on( 'change', this.onChangeHandler );

			//Get this out of here
			this.actions.setAbilities( { done: true } );
		}, this );
};

ve.ui.TopListInspector.prototype.updateMwData = function ( mwData ) {
	// Parent method
	ve.ui.TopListInspector.super.prototype.updateMwData.call( this, mwData );

	if( this.nsInput.getValue() !== '' ) {
		mwData.attrs.ns = this.nsInput.getValue();
	} else {
		delete( mwData.attrs.ns );
	}
	if( this.catInput.getValue() !== '' ) {
		mwData.attrs.cat = this.catInput.getValue();
	} else {
		delete( mwData.attrs.cat );
	}
	if( this.countInput.getValue() ) {
		mwData.attrs.count = this.countInput.getValue();
	} else {
		delete( mwData.attrs.count );
	}
	if( this.periodInput.getValue() ) {
		mwData.attrs.period = this.periodInput.getValue();
	} else {
		delete( mwData.attrs.period );
	}
	if( this.portletperiodInput.getValue() ) {
		mwData.attrs.portletperiod = this.portletperiodInput.getValue();
	} else {
		delete( mwData.attrs.portletperiod );
	}
};

/**
 * @inheritdoc
 */
ve.ui.TopListInspector.prototype.formatGeneratedContentsError = function ( $element ) {
	return $element.text().trim();
};

/**
 * Append the error to the current tab panel.
 */
ve.ui.TopListInspector.prototype.onTabPanelSet = function () {
	this.indexLayout.getCurrentTabPanel().$element.append( this.generatedContentsError.$element );
};

/* Registration */

ve.ui.windowFactory.register( ve.ui.TopListInspector );
