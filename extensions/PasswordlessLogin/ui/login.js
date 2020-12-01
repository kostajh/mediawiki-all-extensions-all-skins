( function () {
	var api = new mw.Api();
	var form = $("[name='userlogin']");

	function submitForm() {
		form.submit();
	}

	function pendingVerification() {
		api.get({
			action: 'passwordlesslogin-verify-login'
		}).done(function(data) {
			if (data['login-verification']['result'] === 'Success') {
				$('<div/>')
					.css('z-index', '1')
					.css('height', '100%')
					.css('width', '100%')
					.css('position', 'absolute')
					.insertBefore(form);
				form.css('opacity', '0.5');
				submitForm();
			} else {
				pendingVerification();
			}
		});
	}

	if (mw.config.get('PLEnableApiVerification')) {
		pendingVerification();
	} else {
		setTimeout(function () {
			submitForm();
		}, 3000)
	}
}() );
