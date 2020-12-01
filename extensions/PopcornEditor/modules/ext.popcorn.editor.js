/**
 * PopcornEditor extension: edit widget fun
 * @copyright 2010-2016 Brion Vibber <brion@pobox.com>
 */

(function($, mw) {

mw.popcorn = {
	isOpen: false,

	init: function() {

	},

	/**
	 * Open the Popcorn editor.
	 * Will have no effect if the editor is already open.
	 *
	 * @param options: object
	 *           filename: wiki name of existing file to initially load
	 *           replace: selector or DOM node to replace the editor with while it runs
	 *           onclose: function(filename) callback; if saved, new file's on-wiki name is passed otherwise null
	 *           leaveopen: pass true to leave the editor goodies up after successful save: it's caller's responsibility to tidy up the page UI state
	 */
	open: function(options) {
		if (mw.popcorn.isOpen) {
			return false;
		}
		mw.popcorn.isOpen = true;

		if ('filename' in options) {
			// Get some basic info on the image before we go barrelling in...
			mwPopcorn.fetchInfo(options.filename, function(imageinfo) {
				mw.popcorn.openEditor(options, imageinfo);
			});
		} else {
			mw.popcorn.openEditor(options, {});
		}
	},

	/**
	 * @access private
	 */
	openEditor: function(options, imageinfo) {
		var url = mw.config.get('wgPopcornEditorUrl');

		var filename = options.filename || null;
		var replace = options.replace || null;
		var onclose = options.onclose || null;
		var leaveopen = options.leaveopen || false;

		var popcorn = null; // to be filled out when iframe is loaded
		var saved = false;

		var origWidth = parseInt(imageinfo.width, 10) || 640;
		var origHeight = parseInt(imageinfo.height, 10) || 480;
		if (origWidth && origHeight) {
			// Initialize the canvas dimensions to the image's defined size...
			url += '?dimensions=' + origWidth + ',' + origHeight;
		}

		var preferredHeight = origHeight + 180; // leave space for toolbars and UI inside the iframe
		var windowHeight = $(window).height() - 40; // leave space for our toolbar outside the iframe
		var minHeight = Math.min(windowHeight, preferredHeight);
		var initHeight = Math.max(minHeight, minHeight);

		// @fixme
		var orig = $(replace);

		orig.hide();
		orig.before('<div id="mw-popcorn">' +
					'<div id="mw-popcorn-toolbar">' +
						'<label id="mw-popcorn-summary-label"></label> ' +
						'<input id="mw-popcorn-summary" size="60" /> ' +
						'<button id="mw-popcorn-save"></button> ' +
						'<button id="mw-popcorn-close"></button>' +
					'</div>' +
					'<div id="mw-popcorn-frame-holder" style="width: 100%; height: ' + initHeight + 'px">' +
					//'<iframe id="mw-popcorn-frame" width="100%" height="100%"></iframe>' +
					'</div>' +
					'</div>');

		//var frame = $('#mw-popcorn-frame');
		var frameHolder = $('#mw-popcorn-frame-holder');
		/*
		$('#mw-popcorn-frame-holder').resizable({
			handles: 's',
			helper: 'mw-popcorn-resize',
			minHeight: minHeight
		});
		*/

		$('body').append('<div id="mw-popcorn-spinner"></div>');
		var spinner = $('#mw-popcorn-spinner');

		/**
		 * Close the editor when we're ready.
		 * Alert the caller's callback if provided.
		 */
		var closeEditor = function() {
			// Always remove the frame; not sure how to shut it up
			// with its beforeunload handler.
			$('#mw-popcorn-frame').replaceWith('<div style="height: ' + initHeight + 'px"></div>');
			if (!(saved && leaveopen)) {
				// Clean up editor UI unless we've been asked to leave
				// things open for caller after successful save.
				$('#mw-popcorn').remove();
				spinner.remove();
				orig.show();
				mw.popcorn.isOpen = false;
			}
			if (onclose) {
				onclose(saved ? filename : null);
			}
		};

		var spinnerOn = function() {
			$('#mw-popcorn-summary').attr('disabled', 'disabled');
			$('#mw-popcorn-save').attr('disabled', 'disabled');
			var offset = frameHolder.offset();
			spinner
				.css('left', offset.left)
				.css('top', offset.top)
				.width(frameHolder.width())
				.height(frameHolder.height())
				.show();
		};
		var spinnerOff = function() {
			$('#mw-popcorn-summary').removeAttr('disabled');
			$('#mw-popcorn-save').removeAttr('disabled');
			spinner.hide();
		};

		$('#mw-popcorn-summary-label')
			.text(mediaWiki.msg('popcorn-summary-label'));

		$('#mw-popcorn-summary')
			.val(mediaWiki.msg('popcorn-summary-default') + ' ');

		$('#mw-popcorn-save')
			.text(mediaWiki.msg('popcorn-editor-save-close'))
			.click(function() {
				spinnerOn();
				PopcornEditor.listen(PopcornEditor.events.save, function(message) {
					PopcornEditor.unlisten(PopcornEditor.events.save);

					console.log('SAVE', message)

					var comment = $('#mw-popcorn-summary').val();
					var projectJSON = JSON.stringify(message);

					mwPopcorn.saveJSON(filename, projectJSON, comment, function(data) {
						if (data.upload && data.upload.result === 'Success') {
							saved = true;
							closeEditor();
						} else if (data.error && data.error.info) {
							spinnerOff();
							alert('Error saving file: ' + data.error.info);
						} else {
							spinnerOff();
							alert('Possible error saving file...');
						}
					});
				});
				PopcornEditor.requestSave();
			});

		$('#mw-popcorn-close')
			.text(mediaWiki.msg('popcorn-editor-close'))
			.click(function() {
				closeEditor();
			});

		// Ok, let's load up the goodies!
		spinnerOn();
        PopcornEditor.listen(PopcornEditor.events.loaded, function () {
        	console.log('loaded');
			// Load up the original file!
			if (filename && imageinfo && imageinfo.url) {
				mwPopcorn.fetchFile(imageinfo.url, function(xmlSource, textStatus, xhr) {
					if (xmlSource === '' &&
						(xhr.responseXML === null || xhr.responseXML === undefined)
					) {
						alert('failllll');
					}
					spinnerOff();
					PopcornEditor.loadInfo(JSON.parse(xmlSource));
				});
			} else {
				spinnerOff();
			}
        });
		PopcornEditor.init(frameHolder[0].id, url);

		// Check if the editor is fully in view; if not, scroll to the top.
		var win = $(window);
		var scrollTop = win.scrollTop();
		var scrollBottom = scrollTop + windowHeight;
		var top = $('#mw-popcorn').offset().top;
		var bottom = top + $('#mw-popcorn').height();
		if (top < scrollTop || bottom > scrollBottom) {
			win.scrollTop(top);
		}
	}
};

})(jQuery, mediaWiki);
