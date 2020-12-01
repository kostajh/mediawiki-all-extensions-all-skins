/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityListMenuFilterTimestampCreated = function( key, mVal, EntityListMenu ) {
	var me = this;
	me.fullDate = '';
	bs.social.EntityListMenuFilter.call( this, key, mVal, EntityListMenu );
};

OO.initClass( bs.social.EntityListMenuFilterTimestampCreated );
OO.inheritClass(
	bs.social.EntityListMenuFilterTimestampCreated,
	bs.social.EntityListMenuFilter
);

bs.social.EntityListMenuFilterTimestampCreated.prototype.makeField = function( mVar ) {
	var date = new Date();
	if( mVar ) {
		date = bs.util.convertMWTimestampToDate( mVar.value );
	}
	this.fullDate = bs.util.convertDateToMWTimestamp( date );
	var field = new mw.widgets.datetime.DateTimeInputWidget( {
		formatter: {
			format: '${day|0}.${month|0}.${year|0}'
		},
		clearable: false,
		value: date,
		max: date,
		type: 'date',
		label: 'yolo'
	});
	//because - classes: ['bs-social-timeline-filter'] dosent work -.-
	this.$element = $(
		"<label class='bs-social-timeline-filter'>"
		+ this.getVarLabel( 'timestampcreated' )
		+ "</label>"
	);
	this.makeQuickFilterButtons();
	this.$element.append( field.$element );
	return field;
};

bs.social.EntityListMenuFilterTimestampCreated.prototype.init = function( mVar ) {
	if( this.initDone ) {
		return;
	}
	var me = this;
	me.field.on(
		'change',
		me.change,
		[],
		this
	);
	this.$removeAllButton.on( 'click', function( e ) {
		var date = new Date();
		me.field.setValue( date );
		return false;
	});
	this.initDone = true;
};

bs.social.EntityListMenuFilterTimestampCreated.prototype.makeQuickFilterButtons = function() {
	var me = this;

	var msg = mw.message(
		'bs-social-entitylistmenufilter-quickfilter-removeall-date'
	).plain();
	this.$removeAllButton = $(
		'<span title="' + msg + '" class="bs-socialentitylist-menufilter-removeall">'
		+ '&nbsp;'
		+ '</span>'
	);

	this.$element.append( this.$removeAllButton );
};

bs.social.EntityListMenuFilterTimestampCreated.prototype.getVarLabel = function( varType, direction ) {
	var msgKey = 'bs-social-filter-date-label-to';
	if( direction === 'from' ) {
		msgKey = 'bs-social-filter-date-label-from';
	}
	var varLabel = bs.social.EntityListMenuFilterTimestampCreated.super.prototype.getVarLabel( varType );
	return mw.message( msgKey, varLabel ).plain();
};

bs.social.EntityListMenuFilterTimestampCreated.prototype.compareDatesSameDay = function( d1, d2 ) {
	return d1.getFullYear() === d2.getFullYear() &&
		d1.getMonth() === d2.getMonth() &&
		d1.getDate() === d2.getDate();
};

bs.social.EntityListMenuFilterTimestampCreated.prototype.change = function( mVal ) {
	mVal = bs.util.convertISOToMWTimestamp( mVal + " 23:59:59" );
	var newdate = bs.util.convertMWTimestampToDate( mVal );
	if( !newdate ) {
		newdate = new Date();
	}
	if( this.compareDatesSameDay( new Date(), newdate ) ) {
		mVal = bs.util.convertDateToMWTimestamp( new Date() );
	}
	this.fullDate = mVal;
	//close calendar manually, as this is not triggerd by
	//mw.widgets.datetime.DateTimeInputWidget
	this.field.calendar.toggle( false );
	return bs.social.EntityListMenuFilterTimestampCreated.super.prototype
		.change.apply( this, [ mVal ] );
};

bs.social.EntityListMenuFilterTimestampCreated.prototype.getData = function( data ) {
	data.filter = data.filter || [];
	data.filter.push({
		property: 'timestampcreated',
		value: this.fullDate,
		type: 'date',
		comparison: 'lt'
	});
	return data;
};

bs.social.EntityListMenuFilters.timestampcreated = bs.social.EntityListMenuFilterTimestampCreated;