bs.util.registerNamespace( 'bs.cntthngs.util.tag' );

bs.cntthngs.util.tag.CountUsersDefinition = function BsVecUtilTagCountUsersDefinition() {
	bs.cntthngs.util.tag.CountUsersDefinition.super.call( this );
};

OO.inheritClass( bs.cntthngs.util.tag.CountUsersDefinition, bs.vec.util.tag.Definition );

bs.cntthngs.util.tag.CountUsersDefinition.prototype.getCfg = function() {
	var cfg = bs.cntthngs.util.tag.CountUsersDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, {
		classname : 'CountUsers',
		name: 'countUsers',
		tagname: 'bs:countusers',
		menuItemMsg: 'bs-countthings-ve-countusers-title',
		descriptionMsg: 'bs-countthings-tag-countusers-desc'
	});
};

bs.vec.registerTagDefinition(
	new bs.cntthngs.util.tag.CountUsersDefinition()
);
