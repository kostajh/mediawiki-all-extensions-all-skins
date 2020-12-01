( function( mw, $, bs ) {

	function _getInvalidFileNamespacesForReading() {
		var unreadableNS = [];
		var allNS = mw.config.get('wgFormattedNamespaces');
		var bsgNSBasePermissions = mw.config.get('bsgNSBasePermissions');
		for ( var nsIdx in allNS ) {
			if ( $.inArray( +nsIdx, bsgNSBasePermissions.read ) === -1 ) {
				unreadableNS.push( parseInt( nsIdx ) );
			}
		}

		var invalidIds = bs.ns.filter.NO_TALK
			.concat( bs.ns.filter.ONLY_CONTENT_NS )
			.concat( unreadableNS )
			.concat([ bs.ns.NS_FILE ]);

		return invalidIds;
	}

	function _getInvalidFileNamespacesForEditing() {
		var invalidIds = _getInvalidFileNamespacesForReading();

		var bsgNSBasePermissions = mw.config.get( 'bsgNSBasePermissions' );
		for ( var nsIdx in bsgNSBasePermissions.edit ) {
			if ( $.inArray( +nsIdx, invalidIds ) === -1 ) {
				invalidIds.push( nsIdx );
			}
		}

		return invalidIds;
	}

	bs.util.registerNamespace( 'bs.nsfrc' );
	bs.nsfrc.getInvalidFileNamespacesForReading = _getInvalidFileNamespacesForReading;
	bs.nsfrc.getInvalidFileNamespacesForEditing = _getInvalidFileNamespacesForEditing;

}( mediaWiki, jQuery, blueSpice ));