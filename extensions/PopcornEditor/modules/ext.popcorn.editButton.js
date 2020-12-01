/**
 * PopcornEditor extension: add 'Edit video' button
 * @copyright 2010-2016 Brion Vibber <brion@pobox.com>
 */

(function($, mw) {

$(document).ready(function() {
	if (mw.config.get('wgCanonicalNamespace') === 'File' &&
		mw.config.get('wgAction') === 'view' &&
		mw.config.get('wgTitle').match(/\.popcorn$/i)) {

		var holder = $( '<div id="mw-popcorn-holder"></div>' ).appendTo( 'body' );
		var trigger = function() {
			mw.popcorn.open({
				filename: mw.config.get('wgTitle'),
				replace: '#mw-popcorn-holder',
				onclose: function(filename) {
					if (filename) {
						// Saved! Refresh parent window...
						window.location.reload(true);
					}
				},
				leaveopen: true // Our reload will get rid of the UI.
			});
		};

		var tab = mw.util.addPortletLink('p-cactions',
			document.location + '#!action=popcorn',
			mw.msg('popcorn-edit-tab'),
			'ca-ext-popcorn',
			mw.msg('popcorn-edit-tab-tooltip'),
			'',
			document.getElementById('ca-edit'));

		$(tab).find('a').click(function(event) {
			trigger();
			event.preventDefault();
			return false;
		});

		var button = $('<button></button>')
			.text(mw.msg('popcorn-editbutton-edit'))
			.click(function() {
				trigger();
			});
		$('.fullMedia').append(button);

		if (window.location.hash.indexOf('!action=popcorn') !== -1) {
			window.location.hash = '';
			trigger();
		}
	}
});

})(jQuery, mediaWiki);
