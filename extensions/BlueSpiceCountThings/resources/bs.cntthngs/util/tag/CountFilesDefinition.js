bs.util.registerNamespace( 'bs.cntthngs.util.tag' );

bs.cntthngs.util.tag.CountFilesDefinition = function BsVecUtilTagCountFilesDefinition() {
	bs.cntthngs.util.tag.CountFilesDefinition.super.call( this );
};

OO.inheritClass( bs.cntthngs.util.tag.CountFilesDefinition, bs.vec.util.tag.Definition );

bs.cntthngs.util.tag.CountFilesDefinition.prototype.getCfg = function() {
	var cfg = bs.cntthngs.util.tag.CountFilesDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, {
		classname : 'CountFiles',
		name: 'countFiles',
		tagname: 'bs:countfiles',
		menuItemMsg: 'bs-countthings-ve-countfiles-title',
		descriptionMsg: 'bs-countthings-tag-countfiles-desc'
	});
};

bs.vec.registerTagDefinition(
	new bs.cntthngs.util.tag.CountFilesDefinition()
);
