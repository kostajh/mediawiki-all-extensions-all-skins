( function ( $ ) {
	// Create and append a window manager, which will open and close the window.
	var windowManager = new OO.ui.WindowManager();
	$( 'body' ).append( windowManager.$element );

	/**
	 * Class to setup, show and hide a new LoginOverlay.
	 *
	 * @class
	 */
	mw.OOJsUIAjaxLogin = {
		/**
		 * @member {null|OO.ui.MessageDialog} save the created overlay from setup
		 */
		overlay: null,
		/**
		 * @member {number} loginRetry How often the login was retried (after 3 times, the login will be aborted)
		 */
		loginRetry: 0,

		/**
		 * Setup the login overlay with all required fields, buttons and inputs.
		 * Resulting Ovleray will be saved in this.overlay.
		 *
		 * @private
		 */
		_setup: function () {
			var self = this;

			// initialize api to try to login later
			this.api = new mw.Api();

			/**
			 * Constructor for LoginOverlay MessageDialog
			 *
			 * @param {Object} config Configuration parameters
			 */
			function LoginOverlay( config ) {
				LoginOverlay.super.call( this, config );
			}
			// inherit MessageDialog to LoginOverlay
			OO.inheritClass( LoginOverlay, OO.ui.MessageDialog );

			// Default actions for this overlay
			LoginOverlay.static.actions = [
				{
					action: 'login',
					label: mw.msg( 'pt-login-button' ),
					flags: [ 'primary', 'progressive' ]
				},
				{
					action: 'register',
					label: mw.msg( 'userlogin-joinproject' ),
					flags: [ 'progressive' ]
				},
				{
					action: 'cancel',
					label: mw.msg( 'cancel' ),
					flags: [ 'safe', 'destructive' ]
				}
			];

			/**
			 * Handles a click on one of the defined actions for this overlay.
			 *
			 * @param {string} [action] Symbolic name of action
			 */
			LoginOverlay.prototype.getActionProcess = function ( action ) {
				var params, signupParams;

				switch ( action ) {
					// login action, try a login with the data provided in the input fields
					case 'login':
						// hide all fields and show the progress bar + resize the dialog
						this.fieldset.toggle( false );
						this.loginProgressBar.toggle( true );
						this.updateSize();
						return new OO.ui.Process( self.tryLogin(
							this.usernameInput.getValue(),
							this.passwordInput.getValue(),
							this
						), this );
					case 'register':
						// create the login link parameters
						params = {
							returnto: mw.config.get( 'wgPageName' )
						};
						signupParams = {
							type: 'signup'
						};

						// navigate to Special:UserLogin/signup
						window.location.href = mw.util.getUrl( 'Special:UserLogin', $.extend( params, signupParams ) );
						return;
					default:
						break;
				}
				// Fallback to parent handler
				return LoginOverlay.super.prototype.getActionProcess.call( this, action );
			};

			/**
			 * Key down handler to handle ENTER key and submit form.
			 *
			 * @param {jQuery.Event} ev Event object
			 */
			LoginOverlay.prototype.onDialogKeyDown = function ( ev ) {
				LoginOverlay.super.prototype.onDialogKeyDown.call( this, ev );

				// on key return, submit form
				if ( ev.which === OO.ui.Keys.ENTER ) {
					this.executeAction( 'login' );
				}
			};

			/**
			 * Initialize the overlay, create input fields and labels and so on.
			 */
			LoginOverlay.prototype.initialize = function () {
				var progressBarFieldset = new OO.ui.FieldsetLayout();

				// Call the parent method
				LoginOverlay.super.prototype.initialize.call( this );

				// create input fields
				this.usernameInput = new OO.ui.TextInputWidget( {
					placeholder: mw.msg( 'userlogin-yourname-ph' )
				} );

				this.passwordInput = new OO.ui.TextInputWidget( {
					placeholder: mw.msg( 'userlogin-yourpassword-ph' ),
					type: 'password'
				} );

				// create a field for error messages and hide it by default
				this.errorMessageContainer = new OO.ui.LabelWidget( {
					label: '',
					classes: [ 'errorbox', 'oojsui-ajaxlogin' ]
				} );
				this.errorMessageContainer.toggle( false );
				// same for a progress bar
				this.loginProgressBar = new OO.ui.ProgressBarWidget();
				this.loginProgressBar.toggle( false );

				progressBarFieldset.addItems( [
					new OO.ui.FieldLayout( this.loginProgressBar, {
						align: 'top'
					} )
				] );
				// Create a fieldset for the input fields.
				this.fieldset = new OO.ui.FieldsetLayout();

				// add input fields to the fieldset
				this.fieldset.addItems( [
					new OO.ui.FieldLayout( this.usernameInput, {
						label: mw.msg( 'userlogin-yourname' ),
						align: 'top'
					} ),
					new OO.ui.FieldLayout( this.passwordInput, {
						label: mw.msg( 'userlogin-yourpassword' ),
						align: 'top'
					} ),
					new OO.ui.FieldLayout( this.errorMessageContainer, {
						align: 'top'
					} )
				] );

				// set the content
				this.content = new OO.ui.PanelLayout( { $: this.$, padded: true, expanded: false } );
				this.content.$element.append(
					this.title.$element,
					this.fieldset.$element,
					progressBarFieldset.$element
				);
				this.$body.append( this.content.$element );
			};

			/**
			 * Get the ‘ready’ process.
			 *
			 * The ready process is used to ready a window for use in a particular
			 * context, based on the `data` argument. This method is called during the opening phase of
			 * the window’s lifecycle, after the window has been setup.
			 *
			 * @param {Object} [data] Window opening data
			 * @return {OO.ui.Process} Ready process
			 */
			LoginOverlay.prototype.getReadyProcess = function ( data ) {
				var self = this;
				// make sure, that username input field is focused
				return LoginOverlay.super.prototype.getReadyProcess.call( this, data )
					.next( function () {
						self.usernameInput.focus();
					} );
			};

			/**
			 * Override the getBodyHeight() method to specify a custom height (or don't to use the automatically generated height)
			 *
			 * @return {number} Height of the Dialog
			 */
			LoginOverlay.prototype.getBodyHeight = function () {
				// get the height of the body and let some place for error messages
				return this.content.$element.outerHeight( true );
			};

			// Create a new LoginOverlay and save it to this.overlay
			this.overlay = new LoginOverlay( {
				size: 'large'
			} );

			// Add the window to the window manager using the addWindows() method
			windowManager.addWindows( [ this.overlay ] );
		},

		/**
		 * Setup a new overlay (if needed) and show it with the windowManager
		 *
		 * @method
		 * @member mw.OOJsUIAjaxLogin
		 */
		show: function () {
			if ( !this.overlay ) {
				this._setup();
			}
			// Open the window!
			windowManager.openWindow( this.overlay, {
				title: mw.msg( 'login' )
			} );
		},

		/**
		 * Try to login with the given data through the Api.
		 *
		 * @param {string} username Plain User name
		 * @param {string} password Plain Password
		 * @param {LoginOverlay} ov The LoginOverlay object where this function is called from
		 * @param {string} [token] Optional logintoken provided by the login api
		 * @return {boolean}
		 * @method
		 * @member mw.OOJsUIAjaxLogin
		 */
		tryLogin: function ( username, password, ov, token ) {
			var self = this;

			// make the request
			return this.api.post( {
				action: 'login',
				lgname: username,
				lgpassword: password,
				lgtoken: token
			} ).done( function ( data ) {
				var params = {
						returnto: mw.config.get( 'wgPageName' )
					},
					welcomeWidget;

				// check, if this is the 6 try to the login api and if so, don't do anything
				if ( self.loginRetry < 6 && data.login.hasOwnProperty( 'result' ) ) {
					// check, if the login was successful or handle errors
					switch ( data.login.result ) {
						case 'NeedToken':
							// the first call to the api returns a token to call the Api again to
							// really try to login
							// increase the login try counter
							self.loginRetry++;
							// try again, now with the required token
							return self.tryLogin( username, password, ov, data.login.token );
						case 'Success':
							// The user was logged in successfully, show a welcome message and reload
							// this page
							ov.title.$element.fadeOut( 'slow', function () {
								ov.title.setLabel( mw.msg( 'welcomeuser', data.login.lgusername ) );
								ov.title.$element.fadeIn( 'slow' );
							} );
							ov.updateSize();

							window.location.reload();
							break;
						default:
							// the default is: false password, try again
							self.loginRetry = 0;
							return self.addErrorMessage( ov, mw.msg( 'wrongpassword' ) );
					}
				} else {
					// navigate to the full login page (Special:UserLogin)
					window.location.href = mw.util.getUrl( 'Special:UserLogin', params );
				}
			} ).fail( function () {
				// unknown error
				// FIXME: i18n & better error handling
				return self.addErrorMessage( ov, mw.msg( 'unknown-error' ) );
			} );
		},

		/**
		 * Adds an errorbox to the provided login form with the provided message and turns off any progressbar.
		 *
		 * @param {LoginOverlay} ov LoginOverlay object to show the errorbox on
		 * @param {string} msg Error text
		 * @return {boolean} always returns false
		 * @method
		 * @member mw.OOJsUIAjaxLogin
		 */
		addErrorMessage: function ( ov, msg ) {
			ov.loginProgressBar.toggle( false );
			ov.fieldset.toggle( true );
			ov.errorMessageContainer.$element.text( msg );
			ov.errorMessageContainer.toggle( true );
			if (
				( ov.usernameInput.getValue() && !ov.passwordInput.getValue() ) ||
				( ov.usernameInput.getValue() && ov.passwordInput.getValue() )
			) {
				ov.passwordInput.focus();
			} else {
				ov.usernameInput.focus();
			}
			ov.updateSize();
			return false;
		}
	};

}( jQuery ) );
