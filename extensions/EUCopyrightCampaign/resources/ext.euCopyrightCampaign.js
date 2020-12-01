( function( mw, $ ) {

	eucc.ContactMEP = function() {
		this.$element = $( '<div>' );
		this.$element.addClass( 'eeuc-process-layout' );

		this.representatives = {};
		this.selectedCountry = {};
		this.selectedRepresentative = {};

		this.trackingInserted = false;
		this.selectionLayout = new OO.ui.HorizontalLayout();
		this.selectionLayout.$element.addClass( 'eucc-selection-layout' );

		this.makeCountryPicker();

		this.$element.append(
			this.selectionLayout.$element
		);

	};

	OO.initClass( eucc.ContactMEP );

	eucc.ContactMEP.prototype.makeCountryPicker = function() {
		var availableCountries = mw.config.get( 'euccCountries' );

		this.countryPicker = new eucc.ui.CountryPickerWidget( {
			availableCountries: availableCountries
		} );
		this.countryPicker.on( 'countrySelected', this.onCountrySelected.bind( this ) );
		// Auto select country based on ULS data
		this.countryPicker.tryAutoSelect();

		this.countryPickerLayout = new OO.ui.FieldLayout( this.countryPicker, {
			align: 'top',
			label: mw.message( 'eucc-country-picker-layout-label' ).text()
		} );

		this.selectionLayout.addItems( [ this.countryPickerLayout ] );
	};

	eucc.ContactMEP.prototype.makeRepresentativePicker = function() {
		if( $.isEmptyObject( this.selectedCountry ) ) {
			// for sanity
			return;
		}

		this.representativePicker = new eucc.ui.RepresentativePickerWidget( {
			country: this.selectedCountry
		} );

		this.representativePicker.on( 'representativesLoaded', this.onRepresentativesLoaded.bind( this ) );
		this.representativePicker.on( 'representativeSelected', this.onRepresentativeSelected.bind( this ) );

		this.representativePickerLayout = new OO.ui.FieldLayout( this.representativePicker, {
			align: 'top',
			label: mw.message( 'eucc-representative-picker-layout-label' ).text()
		} );
	};

	eucc.ContactMEP.prototype.makeContactPanel = function() {
		if( $.isEmptyObject( this.selectedRepresentative ) ) {
			// for sanity
			return;
		}

		if( this.contactLayout ) {
			this.contactLayout.$element.remove();
		}

		this.contactWidget = new eucc.ui.ContactWidget( {
			representative: this.selectedRepresentative,
			country: this.selectedCountry
		} );

		this.contactWidget.on( 'actionDone', this.onContactActionDone.bind( this ) );
		this.contactWidget.on( 'dialogOpened', this.onDialogOpened.bind( this ) );

		this.contactLayout = new OO.ui.FieldLayout( this.contactWidget, {
			align: 'top',
			label: mw.message( 'eucc-contact-layout-label' ).text()
		} );
		this.contactLayout.$element.addClass( 'eucc-content-layout' );

		this.$element.append( this.contactLayout.$element );
	};

	eucc.ContactMEP.prototype.onRepresentativesLoaded = function( representatives ) {
		this.representatives = representatives;
		this.selectionLayout.addItems( [ this.representativePickerLayout ] );
	};

	eucc.ContactMEP.prototype.onCountrySelected = function( country ) {
		this.selectedCountry = country;

		if( this.representativePickerLayout ) {
			this.representativePickerLayout.$element.remove();
			this.representativePickerLayout = null;
			this.representativePicker = null;
		}
		if( this.contactLayout ) {
			this.contactLayout.$element.remove();
			this.contactLayout = null;
			this.contactWidget = null;
		}

		this.makeRepresentativePicker();
	};

	eucc.ContactMEP.prototype.onRepresentativeSelected = function( representative ) {
		this.selectedRepresentative = representative;

		this.makeContactPanel();
	};

	eucc.ContactMEP.prototype.onContactActionDone = function() {
		this.showThankYou();
	};

	eucc.ContactMEP.prototype.onDialogOpened = function( type ) {
		if( type === 'mail' ) {
			this.submitNewsletterSubscription();
		}
		this.insertTracking();
	};

	eucc.ContactMEP.prototype.showThankYou = function() {
		this.$element.html(
			new eucc.ui.ThankYouLayout().$element
		);
	};

	eucc.ContactMEP.prototype.submitNewsletterSubscription = function() {
		var userInfo = this.contactWidget.getUserInfo();
		if( !userInfo.newsletterSignup || userInfo.emailAddress === '' ) {
			return;
		}

		this.$newsletterIframe = $( '<iframe>' )
				.attr( 'name', 'newsletterframe' )
				.css( 'display', 'none' );
		this.$newletterForm = $( '<form>' )
				.attr( 'target', 'newsletterframe' )
				.attr( 'action', mw.config.get( 'euccNewsletterSubmitTarget' ) )
				.attr( 'method', 'POST' )
				.attr( 'pageId', "22243122" )
				.attr( 'siteId', "546018" )
				.attr( 'parentPageId', "22243120" )
				.append(
					// Email
					$( '<input>' )
						.attr( 'name', 'Email' )
						.attr( 'type', 'text' )
						.attr( 'id', 'control_EMAIL')
						.attr( 'label', 'Email' )
						.val( userInfo.emailAddress ),
					// Country
					$( '<input>' )
						.attr( 'name', 'country' )
						.attr( 'type', 'text' )
						.attr( 'id', 'control_COLUMN3')
						.attr( 'label', 'country' )
						.val( userInfo.country.code ),
					// First name
					$( '<input>' )
						.attr( 'name', 'firstname' )
						.attr( 'type', 'text' )
						.attr( 'id', 'control_COLUMN1')
						.attr( 'label', 'firstname' )
						.val( userInfo.firstName ),
					// Last name
					$( '<input>' )
						.attr( 'name', 'lastname' )
						.attr( 'type', 'text' )
						.attr( 'id', 'control_COLUMN2')
						.attr( 'label', 'lastname' )
						.val( userInfo.lastName ),
					$( '<input>' )
							.attr( 'name', 'formSourceName' )
							.attr( 'type', 'hidden' )
							.val( 'StandardForm' ),
					$( '<input>' )
							.attr( 'name', 'sp_exp' )
							.attr( 'type', 'hidden' )
							.val( 'yes' ),
					$( '<input>' ).attr( 'value', 'submit' ).attr( 'type', 'submit' )
				).css( 'display', 'none' );
		this.$element.append( this.$newsletterIframe, this.$newletterForm );

		this.$newletterForm.submit();
	};

	eucc.ContactMEP.prototype.insertTracking = function() {
		if( this.trackingInserted ) {
			return;
		}
		var userData = this.contactWidget.getUserInfo();
		var selectedIssueTitles = [];
		for( var idx in userData.selectedIssues ) {
			var selectedIssue = userData.selectedIssues[ idx ];
			selectedIssueTitles.push( selectedIssue.header );
		}

		var trackData = {
			//country: userData.country.code,
			selectedIssues: selectedIssueTitles,
			representativeName: userData.selectedRepresentative.fullName,
			newsletterSignup: userData.newsletterSignup,
			language: mw.config.get( 'wgUserLanguage' )
		};

		mw.track( 'event.EUCCStats', trackData );
		this.trackingInserted = true;
	};

	var form = new eucc.ContactMEP();
	$( '#eucc-form-container' ).append(
		form.$element
	);

	window.onbeforeunload = function() {
		if( this.contactWidget ) {
			if( this.contactWidget.isDirty() ) {
				return true;
			}
		}
	}.bind( form );

} ) ( mediaWiki, jQuery );
