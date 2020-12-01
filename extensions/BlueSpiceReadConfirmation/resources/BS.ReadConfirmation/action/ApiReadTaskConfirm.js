Ext.define('BS.ReadConfirmation.action.ApiReadTaskConfirm', {
	extend: 'BS.ReadConfirmation.action.ApiReadTaskBase',

	//Custom Settings
	taskKey: 'confirm',

	getDescription: function() {
		return mw.message('bs-readconfirmation-action-apiread-confirm-description', this.pageTitle).parse();
	}
});