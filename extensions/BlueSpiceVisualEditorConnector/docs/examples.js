/*
// Sample code to instanciate the editor and get wikitext and html content
bs.vec.createEditor( 'vec1', {
	renderTo: "#bs-visualeditorconnector-area",
	value: "<p>Hello, World!</p>",
	format: 'html'
}).done( function( target ) {
	console.log( 'html instance created' );
	target.getWikiText().done( function( wikiText ){
		console.log( wikiText );
	});
	target.getHtml().done( function( html ){
		console.log( html );
	});
});

bs.vec.createEditor( 'vec2', {
	renderTo: "#bs-visualeditorconnector-area1",
	value: "'''Hallo Welt!'''",
	format: 'wikitext'
}).done( function( target ) {
	console.log( 'wikitext instance created' );
	target.getWikiText().done( function( wikiText ){
		console.log( wikiText );
	});
	target.getHtml().done( function( html ){
		console.log( html );
	});
});

theWidget = new bs.ui.widget.TextInputMWVisualEditor({});
theWidget.init();
mywidget = nw bs.vec.VisualEditorWidget();
mywidget.getWikiText().done( function( wikiText ) {

})
*/