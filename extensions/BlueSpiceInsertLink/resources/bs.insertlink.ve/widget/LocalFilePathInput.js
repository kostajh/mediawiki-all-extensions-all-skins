bs.util.registerNamespace( 'bs.insertlink.widget' );

bs.insertlink.widget.LocalFilePathInputWidget = function( cfg ) {
	cfg = cfg || {};
	cfg.icon = 'link';
	bs.insertlink.widget.LocalFilePathInputWidget.super.call( this, cfg );
};

OO.inheritClass( bs.insertlink.widget.LocalFilePathInputWidget, OO.ui.TextInputWidget );

bs.insertlink.widget.LocalFilePathInputWidget.static.checkAndPrefix = function( value, doPrefix ) {
	doPrefix = doPrefix || false;
	var map = mw.config.get( 'bsInsertLinkFilesystemMap' ) || {},
		allowUnMapped = !!mw.config.get( 'bsInsertLinkAllowUnMapped' );

	if ( !value ) {
		return false;
	}

	if ( allowUnMapped ) {
		map['*'] = 'file:\/\/';
	}

	for ( var fsPrefix in map ) {
		var webPrefix = map[fsPrefix];
		if( webPrefix === value.substring( 0, webPrefix.length ) ) {
			return value;
		}
		if ( !doPrefix ) {
			continue;
		}
		if ( fsPrefix === '*' ) {
			value = webPrefix + value;
			return value;
		}
		if ( fsPrefix === value.substring( 0, fsPrefix.length ) ) {
			value = webPrefix + value.substring( fsPrefix.length );
			return value;
		}
	}
	return false;
};

bs.insertlink.widget.LocalFilePathInputWidget.prototype.setValue = function( value ) {
	var convertedValue;
	value = this.cleanUpValue( value );
	convertedValue = this.convertToWebPath( value );
	// Update the DOM if it has changed. Note that with cleanUpValue, it
	// is possible for the DOM value to change without this.value changing.
	if ( this.$input.val() !== value ) {
		this.$input.val( value );
	}
	if ( this.value !== convertedValue ) {
		this.value = convertedValue;
		this.emit( 'change', this.value );
	}
	// The first time that the value is set (probably while constructing the widget),
	// remember it in defaultValue. This property can be later used to check whether
	// the value of the input has been changed since it was created.
	if ( this.defaultValue === undefined ) {
		this.defaultValue = this.value;
		this.$input[ 0 ].defaultValue = this.defaultValue;
	}
	return this;
};

bs.insertlink.widget.LocalFilePathInputWidget.prototype.convertToWebPath = function( value ) {
	value = bs.insertlink.widget.LocalFilePathInputWidget.static.checkAndPrefix( value, true );
	if ( !value ) {
		return '';
	}

	value = value.replace( /\s/g, '+' );
	value = value.replace( /\\/g, '/' );
	return value.trim();
};

bs.insertlink.widget.LocalFilePathInputWidget.prototype.getValidity = function() {
	var dfd = $.Deferred(),
		value = this.getValue();

	if ( !value ) {
		dfd.reject();
	}
	if ( !ve.init.platform.getExternalLinkUrlProtocolsRegExp().exec( value ) ) {
		dfd.reject();
	}
	dfd.resolve();

	return dfd.promise();
};
