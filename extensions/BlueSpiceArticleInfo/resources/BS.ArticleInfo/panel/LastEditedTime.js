Ext.define( 'BS.ArticleInfo.panel.LastEditedTime', {
	extend: 'Ext.Panel',
	cls: 'bs-articleinfo-flyout-lasteditedtime',
	timestampText: '',
	anchorURL: '',
	initComponent: function () {
		var html = '<div class="flyout-articleinfo-lasteditedtime">';

		html += "<span>" + mw.message( 'bs-articleinfo-flyout-lasteditedtime-text' ).text() + "</span>";

		html += "<a href='" + this.anchorURL + "'>" + this.timestampText + "</a>";

		html += '</div>';

		this.html = html;

		this.callParent( arguments );
	}
});
