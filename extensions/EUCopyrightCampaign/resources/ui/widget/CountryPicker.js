( function( mw, $ ) {
	eucc.ui.CountryPickerWidget = function( cfg ) {
		this.availableCountries = cfg.availableCountries;

		var menuOptions = [];
		for( var countryCode in this.availableCountries ) {
			var countryIcon = 'flag-' + countryCode.toLowerCase();

			menuOptions.push(
				new OO.ui.MenuOptionWidget( {
					icon: countryIcon,
					data: countryCode,
					label: this.availableCountries[ countryCode ]
				} )
			);
		}

		eucc.ui.CountryPickerWidget.parent.call( this, $.extend( {
			label: mw.message( 'eucc-country-picker-placeholder' ).text(),
			menu: {
				verticalPosition: 'below',
				items: menuOptions
			}
		}, cfg ) );

		this.$element.addClass( 'eucc-country-picker-widget' );
	};

	OO.inheritClass( eucc.ui.CountryPickerWidget, OO.ui.DropdownWidget );

	eucc.ui.CountryPickerWidget.prototype.onMenuSelect = function( item ) {
		this.setIcon( item.getIcon() );
		eucc.ui.CountryPickerWidget.parent.prototype.onMenuSelect.call( this, item );

		if( this.availableCountries[ item.data ] ) {
			var selectedCountry = {
				code: item.data,
				name: this.availableCountries[ item.data ]
			};

			this.emit( 'countrySelected', selectedCountry );
		}
	};

	eucc.ui.CountryPickerWidget.prototype.tryAutoSelect = function() {
		if( !window.Geo || !window.Geo.country ) {
			return;
		}

		var autoCode = window.Geo.country;

		if( autoCode !== '' && this.availableCountries[ autoCode ] ) {
			this.menu.selectItemByData( autoCode );
		}
	};

} ) ( mediaWiki, jQuery );
