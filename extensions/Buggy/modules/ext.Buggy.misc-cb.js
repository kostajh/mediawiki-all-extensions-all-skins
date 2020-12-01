( function () {
	var i, xhr, ids = [
		'MSXML2.XMLHTTP.3.0',
		'MSXML2.XMLHTTP',
		'Microsoft.XMLHTTP'
	];

	if ( typeof window.XMLHttpRequest === 'function' ) {
		xhr = new XMLHttpRequest();
	} else {
		for ( i = 0; i < ids.length; i++ ) {
			try {
				xhr = new ActiveXObject( ids[ i ] );
				break;
			} catch ( e ) {}
		}
	}

	xhr.onreadystatechange = function () {
		if ( xhr.readyState > 3 ) { // make sure we only raise one error
			callANonExistentFunctionOnNativeAjaxSuccess();
		}
	};
	xhr.open( 'GET', '/wiki/Main_Page', true );
	xhr.send( '' );
}() );
