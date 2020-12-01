Ext.define('BS.ReadConfirmation.action.ApiReadTaskCheck', {
	extend: 'BS.ReadConfirmation.action.ApiReadTaskBase',

	//Custom Settings
	taskKey: 'check',

	getDescription: function() {
		return mw.message('bs-readconfirmation-action-apiread-check-description', this.pageTitle).parse();
	}
});