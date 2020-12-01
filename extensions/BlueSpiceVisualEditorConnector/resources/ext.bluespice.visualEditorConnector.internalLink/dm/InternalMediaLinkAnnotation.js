
bs.util.registerNamespace( 'bs.vec.dm' );

bs.vec.dm.InternalMediaLinkAnnotation = function() {
	// Parent constructor
	bs.vec.dm.InternalMediaLinkAnnotation.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( bs.vec.dm.InternalMediaLinkAnnotation, ve.dm.MWInternalLinkAnnotation );

/* Static Properties */

bs.vec.dm.InternalMediaLinkAnnotation.static.name = 'link/internalMedia';
bs.vec.dm.InternalMediaLinkAnnotation.static.matchRdfaTypes = [ 'mw:MediaLink' ];

bs.vec.dm.InternalMediaLinkAnnotation.static.toDataElement = function ( domElements, converter ) {
	var targetData, data,
		resource = domElements[ 0 ].getAttribute( 'resource' );
	if ( resource ) {
		data = mw.libs.ve.parseParsoidResourceName( resource );

		targetData = {
			title: data.title,
			rawTitle: data.rawTitle,
			hrefPrefix: data.hrefPrefix
		};
	} else {
		targetData = this.getTargetDataFromHref(
			domElements[ 0 ].getAttribute( 'href' ),
			converter.getTargetHtmlDocument()
		);
	}

	return {
		type: this.name,
		attributes: {
			hrefPrefix: targetData.hrefPrefix,
			title: targetData.title,
			normalizedTitle: this.normalizeTitle( targetData.title ),
			lookupTitle: this.getLookupTitle( targetData.title ),
			origTitle: targetData.rawTitle
		}
	};
};

bs.vec.dm.InternalMediaLinkAnnotation.static.newFromImageInfo = function ( imageInfo, rawTitle ) {
	var title = imageInfo.title || imageInfo.canonicaltitle,
		titleObject = mw.Title.newFromText( title ),
		element;

	if ( titleObject.getNamespaceId() !== bs.ns.NS_FILE ) {
		return null;
	}

	titleObject = mw.Title.makeTitle( bs.ns.NS_MEDIA, titleObject.getMainText() );
	element = {
		type: 'link/internalMedia',
		attributes: {
			title: titleObject.toText(),
			normalizedTitle: ve.dm.MWInternalLinkAnnotation.static.normalizeTitle( titleObject ),
			imageInfo: imageInfo
		}
	};

	if ( rawTitle ) {
		element.attributes.origTitle = rawTitle;
	}

	return new bs.vec.dm.InternalMediaLinkAnnotation( element );
};

bs.vec.dm.InternalMediaLinkAnnotation.static.newFromTitle = function ( title, rawTitle ) {
	var element,
		target = title.toText();

	if ( title.getNamespaceId() !== bs.ns.NS_MEDIA ) {
		return null;
	}

	element = {
		type: 'link/internalMedia',
		attributes: {
			title: target,
			normalizedTitle: ve.dm.MWInternalLinkAnnotation.static.normalizeTitle( title ),
			lookupTitle: ve.dm.MWInternalLinkAnnotation.static.getLookupTitle( title )
		}
	};
	if ( rawTitle ) {
		element.attributes.origTitle = rawTitle;
	}

	return new bs.vec.dm.InternalMediaLinkAnnotation( element );
};

/* Registration */

ve.dm.modelRegistry.register( bs.vec.dm.InternalMediaLinkAnnotation );
