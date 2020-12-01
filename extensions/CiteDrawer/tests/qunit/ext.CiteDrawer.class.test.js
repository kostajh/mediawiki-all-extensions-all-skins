( function ( mw, $, QUnit ) {

	QUnit.module( 'ext.citedrawer.main', QUnit.newMwEnvironment( {
		config: {
			wgCiteDrawerEnableDesktop: true,
			wgCiteDrawerEnableMobile: true
		}
	} ) );

	QUnit.test( 'Class is available within mw context', function ( assert ) {
		assert.strictEqual( typeof mw.Citedrawer, 'function' );
	} );

	QUnit.test( 'escapeID escapes ids correctly', function ( assert ) {
		var id = 'some.thing-:123::[data-test]', escaped;
		escaped = mw.Citedrawer.prototype.escapeID.call( this, id );
		assert.strictEqual( escaped, 'some\\.thing-\\:123\\:\\:\\[data-test\\]' );
	} );

	// TODO: I bet this one needs to be reworked
	QUnit.test( 'Drawer runs correctly', function ( assert ) {
		var $cites,
			$container = $( '<div>' ),
			$references = $( '<div>' ).addClass( 'references' ),
			i;

		// Generates dummy references markup
		for ( i = 0; i < 3; i++ ) {
			$references.append(
				$( '<li>' ).attr( 'id', 'cite_note-' + i ).append(
					$( '<span>' ).addClass( 'reference-text' ).text( 'Text' + i )
				)
			);
		}

		$cites = $( '<sup>' )
			.attr( 'id', 'cite_ref-1' )
			.addClass( 'reference' )
			.append(
				$( '<a>' )
					.attr( 'href', '#cite_note-1' )
					.text( '[1]' )
			);

		$cites = $cites.add(
			$( '<sup>' )
				.attr( 'id', 'cite_ref-2' )
				.addClass( 'reference' )
				.append(
					$( '<a>' )
						.attr( 'href', '#cite_note-2' )
						.text( '[2]' )
				)
		);

		$cites = $cites.add(
			$( '<sup>' )
				.attr( 'id', 'cite_ref-3' )
				.addClass( 'reference' )
				.append(
					$( '<a>' )
						.attr( 'href', '#cite_note-3' )
						.text( '[3]' )
				)
		);

		// eslint-disable-next-line no-new
		new mw.Citedrawer( $cites, $references, $container );

		assert.strictEqual( $container.find( '.cite-drawer-wrapper' ).length, 1 );

		$( $cites.get( 1 ) ).find( 'a' ).trigger( 'click' );

		assert.strictEqual( $container.find( '.drawer-content' ).text(), '[2] Text2' );
	} );

}( mediaWiki, jQuery, window.QUnit ) );
