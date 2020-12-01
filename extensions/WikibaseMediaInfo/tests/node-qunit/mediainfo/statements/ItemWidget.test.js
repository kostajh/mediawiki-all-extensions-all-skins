'use strict';

const sinon = require( 'sinon' ),
	pathToWidget = '../../../../resources/statements/ItemWidget.js',
	hooks = require( '../../support/hooks.js' );

QUnit.module( 'ItemWidget', hooks.mediainfo, function () {
	QUnit.test( 'Valid data roundtrip', function ( assert ) {
		const done = assert.async(),
			ItemWidget = require( pathToWidget ),
			widget = new ItemWidget( { propertyId: 'P1' } ),
			datamodel = require( 'wikibase.datamodel' ),
			data = new datamodel.Statement(
				new datamodel.Claim(
					new datamodel.PropertyValueSnak(
						'P1',
						new datamodel.EntityId( 'Q1' )
					)
				)
			);

		widget.setData( data ).then( function () {
			assert.ok( widget.getData() );
			assert.strictEqual( data.equals( widget.getData() ), true );
			done();
		} );
	} );

	QUnit.test( 'Setting other data triggers a change event', function ( assert ) {
		const done = assert.async(),
			ItemWidget = require( pathToWidget ),
			widget = new ItemWidget( { propertyId: 'P1' } ),
			datamodel = require( 'wikibase.datamodel' ),
			data = new datamodel.Statement(
				new datamodel.Claim(
					new datamodel.PropertyValueSnak(
						'P1',
						new datamodel.EntityId( 'Q1' )
					),
					new datamodel.SnakList( [
						new datamodel.PropertyValueSnak(
							'P2',
							new dataValues.StringValue( 'This is a string value' )
						)
					] )
				)
			),
			newData = new datamodel.Statement(
				new datamodel.Claim(
					new datamodel.PropertyValueSnak(
						'P1',
						new datamodel.EntityId( 'Q1' )
					),
					new datamodel.SnakList( [
						new datamodel.PropertyValueSnak(
							'P2',
							new dataValues.StringValue( 'This is a different string value' )
						)
					] )
				)
			),
			onChange = sinon.stub();

		widget.setData( data )
			.then( widget.on.bind( widget, 'change', onChange, [] ) )
			.then( widget.setData.bind( widget, newData ) )
			.then( function () {
				assert.strictEqual( onChange.called, true );
				done();
			} );
	} );

	QUnit.test( 'Setting same data does not trigger a change event', function ( assert ) {
		const done = assert.async(),
			ItemWidget = require( pathToWidget ),
			widget = new ItemWidget( { propertyId: 'P1' } ),
			datamodel = require( 'wikibase.datamodel' ),
			data = new datamodel.Statement(
				new datamodel.Claim(
					new datamodel.PropertyValueSnak(
						'P1',
						new datamodel.EntityId( 'Q1' )
					),
					new datamodel.SnakList( [
						new datamodel.PropertyValueSnak(
							'P2',
							new dataValues.StringValue( 'This is a string value' )
						)
					] )
				)
			),
			sameData = new datamodel.Statement(
				new datamodel.Claim(
					new datamodel.PropertyValueSnak(
						'P1',
						new datamodel.EntityId( 'Q1' )
					),
					new datamodel.SnakList( [
						new datamodel.PropertyValueSnak(
							'P2',
							new dataValues.StringValue( 'This is a string value' )
						)
					] )
				)
			),
			onChange = sinon.stub();

		widget.setData( data )
			.then( widget.on.bind( widget, 'change', onChange, [] ) )
			.then( widget.setData.bind( widget, sameData ) )
			.then( function () {
				assert.strictEqual( onChange.called, false );
				done();
			} );
	} );

	QUnit.test( 'Widget updates qualifier widgets with new data', function ( assert ) {
		const done = assert.async(),
			ItemWidget = require( pathToWidget ),
			widget = new ItemWidget( { propertyId: 'P1' } ),
			datamodel = require( 'wikibase.datamodel' ),
			noQualifiers = new datamodel.Statement(
				new datamodel.Claim(
					new datamodel.PropertyValueSnak(
						'P1',
						new datamodel.EntityId( 'Q1' )
					)
				)
			),
			oneQualifier = new datamodel.Statement(
				new datamodel.Claim(
					new datamodel.PropertyValueSnak(
						'P1',
						new datamodel.EntityId( 'Q1' )
					),
					new datamodel.SnakList( [
						new datamodel.PropertyValueSnak(
							'P2',
							new dataValues.StringValue( 'This is a string value' )
						)
					] )
				)
			),
			twoQualifiers = new datamodel.Statement(
				new datamodel.Claim(
					new datamodel.PropertyValueSnak(
						'P1',
						new datamodel.EntityId( 'Q1' )
					),
					new datamodel.SnakList( [
						new datamodel.PropertyValueSnak(
							'P2',
							new dataValues.StringValue( 'This is a string value' )
						),
						new datamodel.PropertyValueSnak(
							'P3',
							new datamodel.EntityId( 'Q4' )
						)
					] )
				)
			);

		widget.setData( oneQualifier )
			.then( function () {
				assert.strictEqual( widget.getItems().length, 1 );
				assert.strictEqual( oneQualifier.equals( widget.getData() ), true );
			} )
			.then( widget.setData.bind( widget, twoQualifiers ) )
			.then( function () {
				assert.strictEqual( widget.getItems().length, 2 );
				assert.strictEqual( twoQualifiers.equals( widget.getData() ), true );
			} )
			.then( widget.setData.bind( widget, oneQualifier ) )
			.then( function () {
				assert.strictEqual( widget.getItems().length, 1 );
				assert.strictEqual( oneQualifier.equals( widget.getData() ), true );
			} )
			.then( widget.setData.bind( widget, noQualifiers ) )
			.then( function () {
				assert.strictEqual( widget.getItems().length, 0 );
				assert.strictEqual( noQualifiers.equals( widget.getData() ), true );
				done();
			} );
	} );

	QUnit.test( 'createQualifier() returns a new QualifierWidget', function ( assert ) {
		const done = assert.async(),
			ItemWidget = require( pathToWidget ),
			QualifierWidget = require( '../../../../resources/statements/QualifierWidget.js' ),
			widget = new ItemWidget( { propertyId: 'P1' } );

		widget.createQualifier()
			.then( function ( qualifier ) {
				assert.strictEqual( qualifier instanceof QualifierWidget, true );
				done();
			} );
	} );

	QUnit.test( 'createQualifier sets QualifierWidget data when snak is provided', function ( assert ) {
		const done = assert.async(),
			ItemWidget = require( pathToWidget ),
			datamodel = require( 'wikibase.datamodel' ),
			widget = new ItemWidget( { propertyId: 'P1' } );

		const data = new datamodel.PropertyValueSnak(
			'P1',
			new datamodel.EntityId( 'Q1' )
		);

		widget.createQualifier( data )
			.then( function ( qualifier ) {
				assert.strictEqual( data.equals( qualifier.getData() ), true );
				done();
			} );
	} );

	QUnit.test( 'addQualifier creates a new QualifierWidget every time it is called', function ( assert ) {
		const done = assert.async(),
			ItemWidget = require( pathToWidget ),
			widget = new ItemWidget( { propertyId: 'P1' } ),
			spy = sinon.spy( widget, 'createQualifier' ),
			datamodel = require( 'wikibase.datamodel' ),
			data = new datamodel.Statement(
				new datamodel.Claim(
					new datamodel.PropertyValueSnak(
						'P1',
						new datamodel.EntityId( 'Q1' )
					)
				)
			);

		widget.setData( data );
		widget.render().then( function () {
			assert.strictEqual( spy.callCount, 0 );

			widget.addQualifier();
			assert.strictEqual( spy.callCount, 1 );

			widget.addQualifier();
			assert.strictEqual( spy.callCount, 2 );

			done();
		} );
	} );

	QUnit.test( 'Test enabling edit state', function ( assert ) {
		const done = assert.async(),
			ItemWidget = require( pathToWidget ),
			widget = new ItemWidget( { propertyId: 'P1' } ),
			datamodel = require( 'wikibase.datamodel' ),
			data = new datamodel.Statement(
				new datamodel.Claim(
					new datamodel.PropertyValueSnak(
						'P1',
						new datamodel.EntityId( 'Q1' )
					)
				)
			);

		widget.setData( data )
			.then( widget.setEditing.bind( widget, true ) )
			.then( function ( $element ) {
				assert.strictEqual( $element.find( '.wbmi-item-read' ).length, 0 );
				assert.strictEqual( $element.find( '.wbmi-item-edit' ).length, 1 );

				// buttons to add qualifier or remove item are available in edit mode
				assert.strictEqual( $element.find( '.wbmi-item-qualifier-add' ).length, 1 );
				assert.strictEqual( $element.find( '.wbmi-item-remove' ).length, 1 );
				done();
			} );
	} );

	QUnit.test( 'Test disabling edit state', function ( assert ) {
		const done = assert.async(),
			ItemWidget = require( pathToWidget ),
			widget = new ItemWidget( { propertyId: 'P1' } ),
			datamodel = require( 'wikibase.datamodel' ),
			data = new datamodel.Statement(
				new datamodel.Claim(
					new datamodel.PropertyValueSnak(
						'P1',
						new datamodel.EntityId( 'Q1' )
					)
				)
			);

		widget.setData( data )
			.then( widget.setEditing.bind( widget, false ) )
			.then( function ( $element ) {
				assert.strictEqual( $element.find( '.wbmi-item-read' ).length, 1 );
				assert.strictEqual( $element.find( '.wbmi-item-edit' ).length, 0 );

				// buttons to add qualifier or remove item are not available in read mode
				assert.strictEqual( $element.find( '.wbmi-item-qualifier-add' ).length, 0 );
				assert.strictEqual( $element.find( '.wbmi-item-remove' ).length, 0 );
				done();
			} );
	} );

	QUnit.test( 'Toggling item prominence changes item rank', function ( assert ) {
		const done = assert.async(),
			ItemWidget = require( pathToWidget ),
			widget = new ItemWidget( { propertyId: 'P1' } ),
			datamodel = require( 'wikibase.datamodel' ),
			data = new datamodel.Statement(
				new datamodel.Claim(
					new datamodel.PropertyValueSnak(
						'P1',
						new datamodel.EntityId( 'Q1' )
					)
				)
			);

		widget.setData( data )
			.then( function () {
				// default rank: normal
				assert.strictEqual( widget.getData().getRank(), datamodel.Statement.RANK.NORMAL );
			} )
			.then( widget.toggleItemProminence.bind( widget, { preventDefault: sinon.stub() } ) )
			.then( function () {
				assert.strictEqual( widget.getData().getRank(), datamodel.Statement.RANK.PREFERRED );
			} )
			.then( widget.toggleItemProminence.bind( widget, { preventDefault: sinon.stub() } ) )
			.then( function () {
				assert.strictEqual( widget.getData().getRank(), datamodel.Statement.RANK.NORMAL );
				done();
			} );
	} );

	QUnit.test( 'Valid data roundtrip with somevalue snak', function ( assert ) {
		const done = assert.async(),
			ItemWidget = require( pathToWidget ),
			widget = new ItemWidget( { propertyId: 'P1' } ),
			datamodel = require( 'wikibase.datamodel' ),
			data = new datamodel.Statement(
				new datamodel.Claim(
					new datamodel.PropertySomeValueSnak( 'P1' )
				)
			);

		widget.setData( data )
			.then( function () {
				assert.strictEqual( data.equals( widget.getData() ), true );
				assert.strictEqual( widget.state.snakType, data.getClaim().getMainSnak().getType() );
				done();
			} );
	} );

	QUnit.test( 'Valid data roundtrip with novalue snak', function ( assert ) {
		const done = assert.async(),
			ItemWidget = require( pathToWidget ),
			widget = new ItemWidget( { propertyId: 'P1' } ),
			datamodel = require( 'wikibase.datamodel' ),
			data = new datamodel.Statement(
				new datamodel.Claim(
					new datamodel.PropertyNoValueSnak( 'P1' )
				)
			);

		widget.setData( data )
			.then( function () {
				assert.strictEqual( data.equals( widget.getData() ), true );
				assert.strictEqual( widget.state.snakType, data.getClaim().getMainSnak().getType() );
				done();
			} );
	} );
} );
