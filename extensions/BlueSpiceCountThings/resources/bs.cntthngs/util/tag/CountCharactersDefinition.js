bs.util.registerNamespace( 'bs.cntthngs.util.tag' );
bs.cntthngs.util.tag.CountCharactersDefinition = function BsVecUtilTagCountCharactersDefinition() {
	bs.cntthngs.util.tag.CountCharactersDefinition.super.call( this );
};

OO.inheritClass( bs.cntthngs.util.tag.CountCharactersDefinition, bs.vec.util.tag.Definition );

bs.cntthngs.util.tag.CountCharactersDefinition.prototype.getCfg = function() {
	var cfg = bs.cntthngs.util.tag.CountCharactersDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, {
		classname : 'CountCharacters',
		name: 'countCharacters',
		tagname: 'bs:countcharacters',
		hideMainInput: false,
		descriptionMsg: 'bs-countthings-tag-countcharacters-desc',
		menuItemMsg: 'bs-countthings-ve-countcharacters-title',
		attributes: [{
			name: 'mode',
			labelMsg: 'bs-countthings-ve-countthingsinspector-mode',
			helpMsg: 'bs-countthings-tag-countcharacters-desc-param-mode',
			type: 'dropdown',
			default: 'all',
			options: [
				{
					data: 'all',
					label: mw.message( 'bs-countthings-ve-countcharacters-mode-all' ).plain()
				},
				{
					data: 'chars',
					label: mw.message( 'bs-countthings-ve-countcharacters-mode-charsonly' ).plain()
				},
				{
					data: 'words',
					label: mw.message( 'bs-countthings-ve-countcharacters-mode-wordsonly' ).plain()
				},
				{
					data: 'chars words',
					label: mw.message( 'bs-countthings-ve-countcharacters-mode-wordsandchars' ).plain()
				},
				{
					data: 'pages',
					label: mw.message( 'bs-countthings-ve-countcharacters-mode-pagesonly' ).plain()
				}
			]
		}]
	});
};

bs.vec.registerTagDefinition(
	new bs.cntthngs.util.tag.CountCharactersDefinition()
);
