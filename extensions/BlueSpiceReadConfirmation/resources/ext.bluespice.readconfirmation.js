(function( mw, $, bs, d, undefined ){

	function _buildMessage( curPageId ) {

		//TODO: Use Mustache template
		var html =
			'<div id="bs-readconfirmation-container">' +
				'<h3>{0}</h3>' +
				'<input type="checkbox" id="bs-rc-cb-ack"/>' +
				'<label for="bs-rc-cb-ack">{1}</label>' +
				'<button disabled id="bs-rc-btn-ack">{2}</button>' +
			'</div>';


		var $message = $( html.format(
			mw.message( 'bs-readconfirmation-confirm-read-heading' ).plain(),
			mw.message( 'bs-readconfirmation-confirm-read-checkbox-label' ).plain(),
			mw.message( 'bs-readconfirmation-confirm-read-button-label' ).plain()
		));

		var $button = $message.find( '#bs-rc-btn-ack' );
		$button.on( 'click', function() {
			mw.loader.using( 'ext.bluespice.extjs', function() {
				Ext.Loader.setPath( 'BS.ReadConfirmation', mw.config.get('wgScriptPath') + '/extensions/BlueSpiceReadConfirmation/resources/BS.ReadConfirmation' );
				Ext.require( 'BS.ReadConfirmation.action.ApiReadTaskConfirm', function() {
					var action = new BS.ReadConfirmation.action.ApiReadTaskConfirm({
						pageId: curPageId
					});
					action.execute()
						.fail(function( xhr, data, response ){
							bs.util.alert( 'bs-rc-confirm', {
								text: response.message
							});
						})
						.done(function(){
							bs.alerts.remove( 'bs-readconfirmation-info' );
						});
				} );
			} );
		} );

		var $checkbox = $message.find( '#bs-rc-cb-ack' );
		$checkbox.on( 'change', function() {
			$button.prop( 'disabled', !$(this).is(':checked') );
			if( $(this).is(':checked') ) {
				$button.css( 'visibility', 'visible');
			}
			else {
				$button.css( 'visibility', 'hidden');
			}
		});

		bs.alerts.add(
			'bs-readconfirmation-info',
			$message,
			bs.alerts.TYPE_INFO
		);
	}

	bs.util.registerNamespace( 'bs.readconfirmation' );
	bs.readconfirmation.init = function() {
		if( mw.config.get( 'wgAction', 'view' ) !== 'view' ) {
			return;
		}

		var curPageId = mw.config.get( 'wgArticleId' );
		if( curPageId < 1 ) { //SpecialPages ...
			return;
		}
		var data = {
			pageId: curPageId
		};

		mw.hook( 'readconfirmation.check.request.before' ).fire(data);

		//We use a normal BS Tasks Api abstraction here even though there is
		//a BS.ReadConfirmation.action.ApiReadTaskCheck class.
		//This is to avoid loading 'ext.bluespice.extjs' if not necessary
		bs.api.tasks.execSilent( 'readconfirmation', 'check', data )
			.done(function( response, xhr ){
				if( response.success && response.payload.userHasConfirmed === false ) {
					_buildMessage( curPageId );
				}
			});
	};

	setTimeout(function(){$(bs.readconfirmation.init)}, 1000);

})( mediaWiki, jQuery, blueSpice, document );