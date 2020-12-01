( function ( mw, $ ) {
    'use strict';
 
    function renderQrCode() {
        var $dialog = $( '<div id=\'qrcode\'></div>' )
            .qrcode({
                text: window.location.href
            })
            .dialog({
                autoOpen: true,
                title: mw.message( 'quickresponse-title' ).escaped(),
                width: 'auto',
                modal: true
            });
    }
 
    $( document ).ready( function() {
 
        var link = mw.util.addPortletLink(
            'p-tb',
            '#',
            mw.message( 'quickresponse-text' ).escaped(),
            '#t-quickresponse',
            mw.message( 'quickresponse-tooltip' ).escaped()
        );
 
        $( link ).click( function( e ) {
 
            e.preventDefault();
 
            renderQrCode();
 
        });
 
    });
 
}( mediaWiki, jQuery ) );