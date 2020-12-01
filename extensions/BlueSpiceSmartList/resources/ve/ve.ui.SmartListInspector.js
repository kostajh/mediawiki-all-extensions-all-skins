ve.ui.SmartListInspector = function VeUiSmartListInspector( config ) {
	// Parent constructor
	ve.ui.SmartListInspector.super.call( this, ve.extendObject( { padded: true }, config ) );
};

/* Inheritance */

OO.inheritClass( ve.ui.SmartListInspector, ve.ui.MWLiveExtensionInspector );

/* Static properties */

ve.ui.SmartListInspector.static.name = 'smartListInspector';

ve.ui.SmartListInspector.static.title = OO.ui.deferMsg( 'bs-smartlist-ve-smartlist-title' );

ve.ui.SmartListInspector.static.modelClasses = [ ve.dm.SmartListNode ];

ve.ui.SmartListInspector.static.dir = 'ltr';

//This tag does not have any content
ve.ui.SmartListInspector.static.allowedEmpty = true;
ve.ui.SmartListInspector.static.selfCloseEmptyBody = true;

/* Methods */

/**
 * @inheritdoc
 */
ve.ui.SmartListInspector.prototype.initialize = function () {

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
		this.catmodeLayout.$element,
		this.minorLayout.$element,
		this.periodLayout.$element,
		this.newLayout.$element,
		this.headingLayout.$element,
		this.sortLayout.$element,
		this.orderLayout.$element,
		this.trimLayout.$element,
		this.showtextLayout.$element,
		this.trimtextLayout.$element,
		this.shownsLayout.$element,
		this.numwithtextLayout.$element,
		this.metaLayout.$element,
		this.targetLayout.$element,
		this.excludensLayout.$element,
		this.generatedContentsError.$element
	);
	this.form.$element.append(
		this.indexLayout.$element
	);
};

ve.ui.SmartListInspector.prototype.createFields = function() {
	this.countInput = new OO.ui.NumberInputWidget( { min: 1, max: 250, isInteger: true } );
	this.nsInput = new OO.ui.TextInputWidget();
	this.catInput = new OO.ui.TextInputWidget();
	this.minorInput = new OO.ui.ToggleSwitchWidget();
	this.catmodeInput = new OO.ui.DropdownInputWidget( {
		options: [
			{
				data: '',
				label: ''
			},
			{
				data: 'OR',
				label: 'OR'
			},
			{
				data: 'AND',
				label: 'AND'
			}
		]
	} );

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

	this.newInput = new OO.ui.ToggleSwitchWidget();
	this.headingInput = new OO.ui.TextInputWidget();
	this.trimInput = new OO.ui.NumberInputWidget( { min: 1, max: 250, isInteger: true } );
	this.showtextInput = new OO.ui.ToggleSwitchWidget();
	this.trimtextInput = new OO.ui.NumberInputWidget( { min: 1, max: 1000, isInteger: true } );
	this.sortInput = new OO.ui.DropdownInputWidget( {
		options: [
			{
				data: 'time',
				label: mw.message( 'bs-smartlist-ve-sort-time-label' ).plain()
			},
			{
				data: 'title',
				label: mw.message( 'bs-smartlist-ve-sort-title-label' ).plain()
			}
		]
	} );
	this.orderInput = new OO.ui.DropdownInputWidget( {
		options: [
			{
				data: '',
				label: ''
			},
			{
				data: 'DESC',
				label: 'DESC'
			},
			{
				data: 'ASC',
				label: 'ASC'
			}
		]
	} );
	this.shownsInput = new OO.ui.ToggleSwitchWidget();
	this.numwithtextInput = new OO.ui.NumberInputWidget( { min: 1, max: 1000, isInteger: true } );
	this.metaInput = new OO.ui.ToggleSwitchWidget();
	this.targetInput = new OO.ui.TextInputWidget();
	this.excludensInput = new OO.ui.TextInputWidget();
}

ve.ui.SmartListInspector.prototype.setLayouts = function() {
	this.countLayout = new OO.ui.FieldLayout( this.countInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-count' )
	} );

	this.nsLayout =  new OO.ui.FieldLayout( this.nsInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-ns' )
	} );
	this.catLayout = new OO.ui.FieldLayout( this.catInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-cat' )
	} );
	this.minorLayout = new OO.ui.FieldLayout( this.minorInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-minor' )
	} );
	this.catmodeLayout = new OO.ui.FieldLayout( this.catmodeInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-catmode' )
	} );
	this.periodLayout = new OO.ui.FieldLayout( this.periodInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-period' )
	} );
	this.newLayout = new OO.ui.FieldLayout( this.newInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-new' )
	} );
	this.headingLayout = new OO.ui.FieldLayout( this.headingInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-heading' )
	} );
	this.trimLayout = new OO.ui.FieldLayout( this.trimInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-trim' )
	} );
	this.showtextLayout = new OO.ui.FieldLayout( this.showtextInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-showtext' )
	} );
	this.trimtextLayout = new OO.ui.FieldLayout( this.trimtextInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-trimtext' )
	} );
	this.sortLayout = new OO.ui.FieldLayout( this.sortInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-sort' )
	} );
	this.orderLayout = new OO.ui.FieldLayout( this.orderInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-order' )
	} );
	this.shownsLayout = new OO.ui.FieldLayout( this.shownsInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-showns' )
	} );
	this.numwithtextLayout = new OO.ui.FieldLayout( this.numwithtextInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-numwithtext' )
	} );
	this.metaLayout = new OO.ui.FieldLayout( this.metaInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-meta' )
	} );
	this.targetLayout = new OO.ui.FieldLayout( this.targetInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-target' )
	} );
	this.excludensLayout = new OO.ui.FieldLayout( this.excludensInput, {
		align: 'right',
		label: ve.msg( 'bs-smartlist-ve-smartlistinspector-excludens' )
	} );
}

/**
 * @inheritdoc
 */
ve.ui.SmartListInspector.prototype.getSetupProcess = function ( data ) {
	return ve.ui.SmartListInspector.super.prototype.getSetupProcess.call( this, data )
		.next( function () {
			var attributes = this.selectedNode.getAttribute( 'mw' ).attrs;

			this.nsInput.setValue( attributes.ns || '' );
			this.catInput.setValue( attributes.cat || '' );
			if( attributes.count ) {
				this.countInput.setValue( attributes.count );
			}
			if( attributes.minor === 1 || attributes.minor === '1' ) {
				this.minorInput.setValue( true );
			}
			if( attributes.catmode ) {
				this.catmodeInput.setValue( attributes.catmode );
			}
			if( attributes.period ) {
				this.periodInput.setValue( attributes.period );
			}
			if( attributes.new === 1 || attributes.new === '1' ) {
				this.newInput.setValue( true );
			}
			this.headingInput.setValue( attributes.heading || '' );
			if( attributes.trim ) {
				this.trimInput.setValue( attributes.trim );
			}
			if( attributes.showtext === 1 || attributes.showtext === '1' ) {
				this.showtextInput.setValue( true );
			}
			if( attributes.trimtext ) {
				this.trimtextInput.setValue( attributes.trimtext );
			}
			if( attributes.sort ) {
				this.sortInput.setValue( attributes.sort );
			}
			if( attributes.order ) {
				this.orderInput.setValue( attributes.order );
			}
			if( attributes.showns === 1 || attributes.showns === '1' ) {
				this.shownsInput.setValue( true );
			}
			if( attributes.numwithtext ) {
				this.numwithtextInput.setValue( attributes.numwithtext );
			}
			this.metaInput.setValue( attributes.meta || '' );
			this.targetInput.setValue( attributes.target || '' );
			this.excludensInput.setValue( attributes.excludens || '' );

			this.wireEvents();

			//Get this out of here
			this.actions.setAbilities( { done: true } );
		}, this );
};

ve.ui.SmartListInspector.prototype.wireEvents = function() {

	this.nsInput.on( 'change', this.onChangeHandler );
	this.catInput.on( 'change', this.onChangeHandler );
	this.countInput.on( 'change', this.onChangeHandler );
	this.minorInput.on( 'change', this.onChangeHandler );
	this.catmodeInput.on( 'change', this.onChangeHandler );
	this.periodInput.on( 'change', this.onChangeHandler );
	this.newInput.on( 'change', this.onChangeHandler );
	this.trimInput.on( 'change', this.onChangeHandler );
	this.showtextInput.on( 'change', this.onChangeHandler );
	this.trimtextInput.on( 'change', this.onChangeHandler );
	this.sortInput.on( 'change', this.onChangeHandler );
	this.orderInput.on( 'change', this.onChangeHandler );
	this.shownsInput.on( 'change', this.onChangeHandler );
	this.numwithtextInput.on( 'change', this.onChangeHandler );
	this.metaInput.on( 'change', this.onChangeHandler );
	this.targetInput.on( 'change', this.onChangeHandler );
	this.excludensInput.on( 'change', this.onChangeHandler );
	this.headingInput.on( 'change', this.onChangeHandler );
}

ve.ui.SmartListInspector.prototype.updateMwData = function ( mwData ) {
	// Parent method
	ve.ui.SmartListInspector.super.prototype.updateMwData.call( this, mwData );

	// Get data from inspector
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
	if( this.minorInput.getValue() === true ) {
		mwData.attrs.minor = "1";
	} else {
		delete( mwData.attrs.minor );
	}
	if( this.catmodeInput.getValue() ) {
		mwData.attrs.catmode = this.catmodeInput.getValue();
	} else {
		delete( mwData.attrs.catmode );
	}
	if( this.periodInput.getValue() ) {
		mwData.attrs.period = this.periodInput.getValue();
	} else {
		delete( mwData.attrs.period );
	}
	if( this.newInput.getValue() === true ) {
		mwData.attrs.new = 1;
	} else {
		delete( mwData.attrs.new );
	}
	if( this.headingInput.getValue() ) {
		mwData.attrs.heading = this.headingInput.getValue();
	} else {
		delete( mwData.attrs.heading );
	}
	if( this.trimInput.getValue() ) {
		mwData.attrs.trim = this.trimInput.getValue();
	} else {
		delete( mwData.attrs.trim );
	}
	if( this.showtextInput.getValue() === true ) {
		mwData.attrs.showtext = "1";
	} else {
		delete( mwData.attrs.showtext );
	}
	if( this.trimtextInput.getValue() ) {
		mwData.attrs.trimtext = this.trimtextInput.getValue();
	} else {
		delete( mwData.attrs.trimtext );
	}
	if( this.sortInput.getValue() ) {
		mwData.attrs.sort = this.sortInput.getValue();
	} else {
		delete( mwData.attrs.sort );
	}
	if( this.orderInput.getValue() ) {
		mwData.attrs.order = this.orderInput.getValue();
	} else {
		delete( mwData.attrs.order );
	}
	if( this.numwithtextInput.getValue() ) {
		mwData.attrs.numwithtext = this.numwithtextInput.getValue();
	} else {
		delete( mwData.attrs.numwithtext );
	}
	if( this.shownsInput.getValue() === true ) {
		mwData.attrs.showns = "1";
	} else {
		delete( mwData.attrs.showns );
	}
	if( this.metaInput.getValue() ) {
		mwData.attrs.meta = this.metaInput.getValue();
	} else {
		delete( mwData.attrs.meta );
	}
	if( this.targetInput.getValue() ) {
		mwData.attrs.target = this.targetInput.getValue();
	} else {
		delete( mwData.attrs.target );
	}
	if( this.excludensInput.getValue() ) {
		mwData.attrs.excludens = this.excludensInput.getValue();
	} else {
		delete( mwData.attrs.excludens );
	}

};

/**
 * @inheritdoc
 */
ve.ui.SmartListInspector.prototype.formatGeneratedContentsError = function ( $element ) {
	return $element.text().trim();
};

/**
 * Append the error to the current tab panel.
 */
ve.ui.SmartListInspector.prototype.onTabPanelSet = function () {
	this.indexLayout.getCurrentTabPanel().$element.append( this.generatedContentsError.$element );
};

/* Registration */

ve.ui.windowFactory.register( ve.ui.SmartListInspector );
