Ext.define('BS.ReadConfirmation.action.ApiReadTaskRemind', {
	extend: 'BS.ReadConfirmation.action.ApiReadTaskBase',

	//Custom Settings
	taskKey: 'remind',

	getDescription: function() {
		return mw.message('bs-readconfirmation-action-apiread-remind-description', this.pageTitle).parse();
	}
});