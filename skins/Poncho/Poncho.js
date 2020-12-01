/* global mw, jQuery */

var Poncho = {

    /**
     * Initialization script
     */
    init: function () {
        Poncho.bind();
        Poncho.ring();
    },

    /**
     * Bind events
     */
    bind: function () {
        jQuery( '#bell-icon' ).mouseenter( Poncho.readNotifications ); 
    },

    /**
     * Mark all notifications of the current user as read
     * Also unmark the bell icon
     */
    readNotifications: function () {
    	new mw.Api().postWithEditToken({
    		'action': 'echomarkread',
    		'all': true,
    	});
        jQuery( '#bell-icon' ).parent().removeClass( 'active' );
    },

    /**
     * Mark the bell icon if the current user has unread notifications
     */
    ring: function () {
        var notificationsItem = jQuery( '#bell-icon' ).parent();
        if ( jQuery( 'li.active', notificationsItem ).length ) {
            notificationsItem.addClass( 'active' );
        }
    }
};

mw.loader.using([
	'mediawiki.api',
], Poncho.init );