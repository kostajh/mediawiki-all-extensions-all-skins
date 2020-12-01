bs.util.registerNamespace( 'bs.cntthngs.util.tag' );

bs.cntthngs.util.tag.CountArticlesDefinition = function BsVecUtilTagCountArticlesDefinition() {
	bs.cntthngs.util.tag.CountArticlesDefinition.super.call( this );
};

OO.inheritClass( bs.cntthngs.util.tag.CountArticlesDefinition, bs.vec.util.tag.Definition );

bs.cntthngs.util.tag.CountArticlesDefinition.prototype.getCfg = function() {
	var cfg = bs.cntthngs.util.tag.CountArticlesDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, {
		classname : 'CountArticles',
		name: 'countArticles',
		tagname: 'bs:countarticles',
		menuItemMsg: 'bs-countthings-ve-countarticles-title',
		descriptionMsg: 'bs-countthings-tag-countarticles-desc'
	});
};

bs.vec.registerTagDefinition(
	new bs.cntthngs.util.tag.CountArticlesDefinition()
);
