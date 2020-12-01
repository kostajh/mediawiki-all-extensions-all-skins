var DebateTree = {

	/**
	 * Get all the arguments in the page
	 */
	getArguments: function () {
		return $( '.debatetree ul li' );
	},

	/**
	 * Get the objections to the given argument
	 */
	getObjections: function ( argument ) {
		return $( argument ).children( 'ul' ).children( 'li' );
	},

	/**
	 * Get the sustained objections to the given argument
	 */
	getSustainedObjections: function ( argument ) {
		return DebateTree.getObjections( argument ).filter( DebateTree.isSustained );	
	},

	/**
	 * Get the nested objections of the given argument
	 */
	getNestedObjections: function ( argument ) {
		return $( 'li', argument );
	},

	/**
	 * Return true if the given argument is sustained
	 */
	isSustained: function ( index, argument ) {
		return DebateTree.getSustainedObjections( argument ).length ? false : true;
	},

	/**
	 * Prepend the status to the argument
	 */
	addStatus: function ( index, argument ) {
		var status = DebateTree.isSustained( index, argument ) ? 'sustained' : 'refuted',
			text = mw.message( 'debatetree-' + status );
			span = $( '<span>' ).addClass( 'debatetree-status debatetree-' + status ).text( text );

		$( argument ).prepend( span );
	},

	/**
	 * Append the counts to the argument
	 */
	addCounts: function ( index, argument ) {
		var objectionCount = DebateTree.getObjections( argument ).length,
			sustainedObjectionCount = DebateTree.getSustainedObjections( argument ).length,
			nestedObjectionCount = DebateTree.getNestedObjections( argument ).length,
			text = mw.message( 'debatetree-counts', objectionCount, sustainedObjectionCount, nestedObjectionCount ),
			span = $( '<span>' ).addClass( 'debatetree-counts' ).text( text );

		if ( objectionCount ) {
			$( argument ).children( 'ul' ).first().before( span );
		} else {
			$( argument ).append( ' ', span );
		}
	},

	/**
	 * Toggle the objections of the clicked argument
	 */
	toggleObjections: function ( event ) {
		DebateTree.getObjections( this ).toggle();
		event.stopPropagation();
	},

	/**
	 * Initialization script
	 */
	init: function () {
		var args = DebateTree.getArguments();
		args.each( DebateTree.addStatus );
		args.each( DebateTree.addCounts );
		args.click( DebateTree.toggleObjections ).click();
	}
};

$( DebateTree.init );