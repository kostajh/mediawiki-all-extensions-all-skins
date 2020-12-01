bs.util.registerNamespace( 'bs.tgcld.util.tag' );
bs.tgcld.util.tag.TagCloudDefinition = function BsVecUtilTagCloudDefinition() {
	bs.tgcld.util.tag.TagCloudDefinition.super.call( this );
};

OO.inheritClass( bs.tgcld.util.tag.TagCloudDefinition, bs.vec.util.tag.Definition );

bs.tgcld.util.tag.TagCloudDefinition.prototype.getCfg = function() {
	var cfg = bs.tgcld.util.tag.TagCloudDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, {
		classname : 'TagCloud',
		name: 'tagCloud',
		tagname: 'bs:tagcloud',
		icon: 'bluespice',
		descriptionMsg: 'bs-tagcloud-tag-tagcloud-desc',
		menuItemMsg: 'bs-tagcloud-tag-tagcloud-title',
		tabbed: true,
		attributes: [{
			name: 'renderer',
			labelMsg: 'bs-tagcloud-tag-tagcloud-attr-renderer-label',
			helpMsg: 'bs-tagcloud-tag-tagcloud-attr-renderer-help',
			type: 'dropdown',
			default: 'list',
			tab: 'common',
			options: [
				{ data: 'text', label: mw.message( 'bs-tagcloud-tag-tagcloud-attr-renderer-option-text' ).plain() },
				{ data: 'list', label: mw.message( 'bs-tagcloud-tag-tagcloud-attr-renderer-option-list' ).plain() },
				{ data: 'canvas3d', label: mw.message( 'bs-tagcloud-tag-tagcloud-attr-renderer-option-canvas3d' ).plain() }
			]
		},{
			name: 'store',
			labelMsg: 'bs-tagcloud-tag-tagcloud-attr-store-label',
			helpMsg: 'bs-tagcloud-tag-tagcloud-attr-store-help',
			type: 'dropdown',
			default: 'category',
			tab: 'advanced',
			options: [
				{ data: 'category', label: mw.message( 'bs-tagcloud-tag-tagcloud-attr-store-option-category' ).plain() },
				{ data: 'searchstats', label: mw.message( 'bs-tagcloud-tag-tagcloud-attr-store-option-searchstats' ).plain() }
			]
		},{
			name: 'width',
			labelMsg: 'bs-tagcloud-tag-tagcloud-attr-width-label',
			helpMsg: 'bs-tagcloud-tag-tagcloud-attr-width-help',
			type: 'percent',
			default: '100',
			tab: 'common'
		},{
			name: 'showcount',
			labelMsg: 'bs-tagcloud-tag-tagcloud-attr-showcount-label',
			helpMsg: 'bs-tagcloud-tag-tagcloud-attr-showcount-help',
			type: 'toggle',
			default: true,
			tab: 'common'
		},{
			name: 'minsize',
			labelMsg: 'bs-tagcloud-tag-tagcloud-attr-minsize-label',
			helpMsg: 'bs-tagcloud-tag-tagcloud-attr-minsize-help',
			type: 'number',
			default: 5,
			tab: 'advanced'
		},{
			name: 'maxsize',
			labelMsg: 'bs-tagcloud-tag-tagcloud-attr-maxsize-label',
			helpMsg: 'bs-tagcloud-tag-tagcloud-attr-maxsize-help',
			type: 'number',
			default: 30,
			tab: 'advanced'
		},{
			name: 'exclude',
			labelMsg: 'bs-tagcloud-tag-tagcloud-attr-exclude-label',
			helpMsg: 'bs-tagcloud-tag-tagcloud-attr-exclude-help',
			type: 'text',
			default: '',
			tab: 'advanced'
		}]
	});
};

bs.vec.registerTagDefinition(
	 new bs.tgcld.util.tag.TagCloudDefinition()
);
