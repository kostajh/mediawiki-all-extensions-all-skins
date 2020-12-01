(function( mw, $, d, bs, undefined ){
	$( d ).on( 'click', 'a.bs-expiry-unexpire', function() {
		var exp_id = $( this ).data( 'expid' );
		bs.api.tasks.exec(
			'expiry',
			'deleteExpiry',
			{
				expiryId: exp_id || 0,
				articleId: mw.config.get( 'wgArticleId' ) || 0
			}
		)
		.done( function(){
			window.location.reload();
		} );
	});

	$( d ).on( 'click', 'a.bs-expiry-updateexpirydate', function() {
		var me = this;
		var expiry = $(this).data( 'expiry' ) || {};
		mw.loader.using( 'ext.bluespice.extjs' ).done( function() {
			Ext.onReady( function() {
				if ( !me.dlgExpiry ) {
					me.dlgExpiry = Ext.create( 'BS.Expiry.dialog.ChangeDate', {
						id: 'bs-expiry-dlg-changedate-page'
					} );
					me.dlgExpiry.on( 'ok', function() {
						expiry.date = me.dlgExpiry.getData().date;
						expiry.comment = expiry.exp_comment;
						bs.api.tasks.exec(
							'expiry',
							'saveExpiry',
							expiry
						).done( function(){
							window.location.reload();
						} );
						$( d ).trigger( "BSExpiryEditOk", [ me, expiry ] );
					}, me );
				}
				expiry.articleId = mw.config.get( 'wgArticleId' );
				me.dlgExpiry.setData( expiry );
				me.dlgExpiry.show( me );
			});
		});
	});

	$( d ).on( 'click', "#ca-expiryCreate, .ca-expiryCreate", function ( e ) {
		e.preventDefault();
		var me = this;
		mw.loader.using( 'ext.bluespice.extjs' ).done( function() {
			Ext.onReady( function() {
				if ( !me.dlgExpiry ) {
					me.dlgExpiry = Ext.create( 'BS.Expiry.Dialog', {
						id: 'bs-expiry-dlg-page'
					} );
					me.dlgExpiry.on( 'ok', function() {
						var obj = me.dlgExpiry.getData();
						bs.api.tasks.exec(
							'expiry',
							'saveExpiry',
							obj
						);
						$( d ).trigger( "BSExpiryAddOk", [ me, obj ] );
					}, me );
				}
				var obj = {
					articleId: mw.config.get( 'wgArticleId' )
				};
				me.dlgExpiry.setData( obj );
				me.dlgExpiry.show( me );
			});
		});
	} );
})( mediaWiki, jQuery, document, blueSpice );