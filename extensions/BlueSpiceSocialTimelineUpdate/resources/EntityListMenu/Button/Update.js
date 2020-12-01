bs.social.EntityListMenu.Button.Update = function ( EntityListMenu ) {
	bs.social.EntityListMenu.Button.call( this, EntityListMenu );
	OO.EventEmitter.call( this );
	var me = this;

};

OO.initClass( bs.social.EntityListMenu.Button.Update );
OO.inheritClass( bs.social.EntityListMenu.Button.Update, bs.social.EntityListMenu.Button );

bs.social.EntityListMenu.Button.Update.prototype.init = function () {
	bs.social.EntityListMenu.Button.Update.super.prototype.init.apply( this );

	var me = this;
	var data = me.EntityListMenu.getData();

	BSPing.registerListener(
		'BlueSpiceSocial.TimelineUpdate.Button.Update',
		1000,
		{ 'data': me.EntityListMenu.entityList.makeTaskData( data ) },
		this.PingListener()
	);
};

bs.social.EntityListMenu.Button.Update.prototype.onClick = function () {
	var dateFilter = this.EntityListMenu.filters[ 'timestampcreated' ];
	if( dateFilter ) {
		var date = new Date();
		var curr_year = date.getFullYear();
		var curr_month = date.getMonth() + 1;
		if( curr_month < 10 ) {
			curr_month = "0" + curr_month;
		}
		var curr_date = date.getDate();
		if( curr_date < 10) {
			curr_date = "0" + curr_date;
		}
		var val = curr_year + '-' + curr_month + '-' + curr_date;
		dateFilter.field.setValue( val.toString() );
	}
	var data = this.EntityListMenu.getData();
	this.EntityListMenu.entityList.getEntities( 'replace', data );
};

bs.social.EntityListMenu.Button.Update.prototype.getTemplate = function() {
	return mw.template.get(
		'ext.bluespice.social.timelineupdate.templates',
		this.getTemplateName()
	);
};

bs.social.EntityListMenu.Button.Update.prototype.getTemplateName = function () {
	return 'BlueSpiceSocialTimelineUpdate.UpdateButton.mustache';
};

bs.social.EntityListMenu.Button.Update.prototype.getTemplateVars = function () {
	return {
		classes: 'bs-entitylist-menu-item-update',
		tooltip: mw.message( 'bs-socialtimelineupdate-update-button-tooltip' ).plain()
	};
};

bs.social.EntityListMenu.Button.Update.prototype.PingListener = function () {
	var me = this;

	return function( result ) {
		if( result.success === true && result.updates !== 0 ) {
			$( '.bs-social-entitylist-menu-item.bs-entitylist-menu-item-update .bs-social-entitylist-menu-item-actions-btn .updateCounter' ).html( result.updates );
		}

		var data = me.EntityListMenu.getData();
		BSPing.registerListener(
			'BlueSpiceSocial.TimelineUpdate.Button.Update',
			1000,
			{ 'data': me.EntityListMenu.entityList.makeTaskData( data ) },
			me.PingListener()
		);
	};
};