//Source: https://stackoverflow.com/questions/43236899/extjs-6-2-classic-does-not-work-with-firefox-and-a-touchscreen

Ext.define('MWExt.override.dom.Element', {
	override: 'Ext.dom.Element'
},
function(){
	var additiveEvents = this.prototype.additiveEvents,
		eventMap = this.prototype.eventMap;
	if(Ext.supports.TouchEvents && Ext.firefoxVersion >= 52 && Ext.os.is.Desktop){
		eventMap['touchstart'] = 'mousedown';
		eventMap['touchmove'] = 'mousemove';
		eventMap['touchend'] = 'mouseup';
		eventMap['touchcancel'] = 'mouseup';
		eventMap['click'] = 'click';
		eventMap['dblclick'] = 'dblclick';
		additiveEvents['mousedown'] = 'mousedown';
		additiveEvents['mousemove'] = 'mousemove';
		additiveEvents['mouseup'] = 'mouseup';
		additiveEvents['touchstart'] = 'touchstart';
		additiveEvents['touchmove'] = 'touchmove';
		additiveEvents['touchend'] = 'touchend';
		additiveEvents['touchcancel'] = 'touchcancel';
		additiveEvents['pointerdown'] = 'mousedown';
		additiveEvents['pointermove'] = 'mousemove';
		additiveEvents['pointerup'] = 'mouseup';
		additiveEvents['pointercancel'] = 'mouseup';
	}
});