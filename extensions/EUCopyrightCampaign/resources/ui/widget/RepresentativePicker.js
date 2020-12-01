( function( mw, $ ) {
	eucc.ui.RepresentativePickerWidget = function( cfg ) {
		this.country = cfg.country;

		this.retrieveRepresentatives().done( function( response ) {
			if( !response.success ) {
				return;
			}
			this.representatives = response.representatives;
			this.setRepresentativeOptions();
			// Options set - safe to display the picker
			this.emit( 'representativesLoaded', this.representatives );

			this.menu.selectItemByData( this.representatives[ 0 ].email );
		}.bind( this ) );

		eucc.ui.RepresentativePickerWidget.parent.call( this, {} );

		this.$element.addClass( 'eucc-representative-picker-widget' );
	};

	OO.inheritClass( eucc.ui.RepresentativePickerWidget, OO.ui.DropdownWidget );

	eucc.ui.RepresentativePickerWidget.prototype.retrieveRepresentatives = function() {
		var apiData = {
			country: this.country.code,
			action: 'eucc-get-representatives'
		};

		return new mw.Api().get( apiData );
	};

	eucc.ui.RepresentativePickerWidget.prototype.onMenuSelect = function( item ) {
		eucc.ui.RepresentativePickerWidget.parent.prototype.onMenuSelect.call( this, item );

		for( var idx in this.representatives ) {
			if( this.representatives[ idx ].email === item.data ) {
				this.emit( 'representativeSelected', this.representatives[ idx ] );
			}
		}
	};

	eucc.ui.RepresentativePickerWidget.prototype.setRepresentativeOptions = function() {
		var representativesOptions = [];

		for( var idx in this.representatives ) {
			var representative = this.representatives[ idx ];

			representativesOptions.push(
				new OO.ui.MenuOptionWidget( {
					data: representative.email,
					label: representative.fullName
				} )
			);
		}
		this.menu.addItems( representativesOptions );
	};

} ) ( mediaWiki, jQuery );
