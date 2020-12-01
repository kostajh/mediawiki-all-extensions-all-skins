bs.util.registerNamespace( 'bs.catgrychk.ui.plugin' );

bs.catgrychk.ui.plugin.MWSaveDialog = function BsCatgyChkUiPluginMWSaveDialog( component ) {
	bs.catgrychk.ui.plugin.MWSaveDialog.super.apply( this, arguments );
};

OO.inheritClass( bs.catgrychk.ui.plugin.MWSaveDialog, bs.vec.ui.plugin.MWSaveDialog );

bs.catgrychk.ui.plugin.MWSaveDialog.prototype.swapPanel = function( panel, noFocus ) {
	this.categories = bs.vec.getCategoriesFromTarget( ve.init.target );

	if( this.categories.length > 0 ) {
		this.showList();
	}
	else {
		this.showError();
	}

	this.component.getActions().setAbilities( { save: false } );
};

bs.catgrychk.ui.plugin.MWSaveDialog.prototype.showList = function() {
	if( this.$errorMessage ) {
		this.$errorMessage.hide();
	}
	if( this.$categoryList ) {
		this.$categoryList.remove();
		this.$categoryList = null;
	}

	var checkbox = this.makeCheckbox( 'bs-categorycheck-insertcategory-confirm-categories' );
	var categoryLinkList = this.makeCategoryLinkList();

	var listIntro = mw.message( 'bs-categorycheck-insertcategory-category-list' );
	this.$categoryList =
		$('<div class="alert alert-warning" role="alert">')
		.append( listIntro.plain() )
		.append( '<br />' )
		.append( categoryLinkList )
		.append( checkbox.$element );

	this.component.savePanel.$element.prepend( this.$categoryList );
};

bs.catgrychk.ui.plugin.MWSaveDialog.prototype.showError = function() {
	if( this.$categoryList ) {
		this.$categoryList.hide();
	}
	if( this.$errorMessage ) {
		this.$errorMessage.remove();
		this.$errorMessage = null;
	}

	var checkbox = this.makeCheckbox( 'bs-categorycheck-insertcategory-ignore-no-category' );

	var errorMsg = mw.message( 'bs-categorycheck-insertcategory-no-category' );
	this.$errorMessage =
		$('<div class="alert alert-danger" role="alert">')
		.append( errorMsg.plain() )
		.append( checkbox.$element );

	this.component.savePanel.$element.prepend( this.$errorMessage );
};

bs.catgrychk.ui.plugin.MWSaveDialog.prototype.toggleSaveButton = function( value ) {
	this.component.getActions().setAbilities( { save: value } );
};

bs.catgrychk.ui.plugin.MWSaveDialog.prototype.makeCheckbox = function( label ) {
	var ignore = new OO.ui.CheckboxInputWidget( {} );
	ignore.on( 'change', this.toggleSaveButton, [], this );

	var fieldSetLayout = new OO.ui.FieldsetLayout( {} );
	var labelMsg = mw.message( label );

	fieldSetLayout.addItems( [
		new OO.ui.FieldLayout( ignore, { label: labelMsg.plain(), align: 'inline' } )
	] );

	return fieldSetLayout;
};

bs.catgrychk.ui.plugin.MWSaveDialog.prototype.makeCategoryLinkList = function() {
	var links = [];
	for( var i = 0; i < this.categories.length; i++ ) {
		var categoryName = this.categories[i];
		var categoryTitle = mw.Title.makeTitle( bs.ns.NS_CATEGORY, categoryName );
		var link = mw.html.element(
			'a',
			{
				href: categoryTitle.getUrl(),
				title: categoryTitle.getName(),
				target: '_blank',
				'data-bs-title': categoryTitle.getPrefixedText()
			},
			categoryTitle.getName()
		);
		links.push( link );
	}

	return links.join( ', ' );
};
