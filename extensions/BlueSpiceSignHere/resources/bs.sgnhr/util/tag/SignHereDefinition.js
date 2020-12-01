bs.util.registerNamespace( 'bs.sgnhr.util.tag' );

bs.sgnhr.util.tag.SignHereDefinition = function BsVecUtilTagSignHereDefinition() {
	bs.sgnhr.util.tag.SignHereDefinition.super.call( this );
};

OO.inheritClass( bs.sgnhr.util.tag.SignHereDefinition, bs.vec.util.tag.Definition );

bs.sgnhr.util.tag.SignHereDefinition.prototype.getCfg = function() {
	var cfg = bs.sgnhr.util.tag.SignHereDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, {
		classname : 'SignHere',
		name: 'signHere',
		tagname: 'bs:signhere',
		menuItemMsg: 'bs-signhere-tag-signhere-title',
		descriptionMsg: 'bs-signhere-tag-signhere-desc'
	});
};

bs.vec.registerTagDefinition(
	new bs.sgnhr.util.tag.SignHereDefinition()
);
