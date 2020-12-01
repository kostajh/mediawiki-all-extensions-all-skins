bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.TableStyleApplicator = function( cfg ) {
	this.model = cfg.model;
	this.$element = cfg.domEl;
	this.tableStyleRegistry =  bs.vec.registry.TableStyle.registry;

	this.styles = ve.cloneObject( this.model );
	delete ( this.styles.type );
};

OO.initClass( bs.vec.ui.TableStyleApplicator );

bs.vec.ui.TableStyleApplicator.prototype.apply = function () {
	var styleAttr, styleAttrValue;

	this.$element.removeAttr( 'style' );
	for( styleAttr in this.styles ) {
		if ( !this.styles.hasOwnProperty( styleAttr ) ) {
			continue;
		}
		if( !this.tableStyleRegistry.hasOwnProperty( styleAttr ) ) {
			continue;
		}

		styleAttrValue = this.styles[styleAttr];
		this.tableStyleRegistry[styleAttr].setValue( styleAttrValue );
		this.tableStyleRegistry[styleAttr].decorate( this.$element );
	}
};
