bs = bs || {};
bs.ui = bs.ui || {};
bs.ui.widget = bs.ui.widget || {};

bs.ui.widget.TextInputVisualEditor = function ( config ) {
	OO.ui.MultilineTextInputWidget.call( this, config );
	var me = this;
	me.selector = config.selector || '.bs-visualeditor';
	me.visualEditor = null;
	me.toolbar1 = config.toolbar1 || [
		'undo',
		'redo',
		'|',
		'bold',
		'italic',
		'underline',
		'strikethrough',
		'|',
		'bslink',
		'unlink',
		'bsimage',
		'bsfile',
		'|',
		'removeformat',
		'|',
		'bullist',
		'numlist',
		'outdent',
		'indent'
	];
	me.config = config;
};
OO.initClass( bs.ui.widget.TextInputVisualEditor );
OO.inheritClass( bs.ui.widget.TextInputVisualEditor, OO.ui.MultilineTextInputWidget );

bs.ui.widget.TextInputVisualEditor.prototype.init = function() {
	var decodeHTMLEntities = function (text) {
		var entities = [
			['amp', '&'],
			['apos', '\''],
			['#x27', '\''],
			['#x2F', '/'],
			['#39', '\''],
			['#47', '/'],
			['lt', '<'],
			['gt', '>'],
			['nbsp', ' '],
			['quot', '"']
		];

		for (var i = 0, max = entities.length; i < max; ++i) 
			text = text.replace(new RegExp('&'+entities[i][0]+';', 'g'), entities[i][1]);

		return text;
	};
	this.$input.val( decodeHTMLEntities( this.config.value ) );
	
	this.makeVisualEditor( this.config );
};

bs.ui.widget.TextInputVisualEditor.prototype.getValue = function() {
	if( !this.visualEditor ) {
		return '';
		return bs.ui.widget.TextInputVisualEditor.super.prototype.getValue
			.apply( this );
	}
	return this.visualEditor.getContent( {save: true} );
};

bs.ui.widget.TextInputVisualEditor.prototype.setValue = function( value ) {
	if( !this.visualEditor ) {
		return;
		return bs.ui.widget.TextInputVisualEditor.super.prototype.setValue
			.apply( this, value );
	}
	return this.visualEditor.setContent( value );
};

bs.ui.widget.TextInputVisualEditor.prototype.makeVisualEditor = function( config ) {
	var me = this;
	var visialEditorModules = [
		'ext.bluespice.visualEditor.tinymce',
		'ext.bluespice.visualEditor',
		'ext.bluespice.social'
	];
	var plugins = this.makePluginsList();
	var currentSiteCSS = [];
	$('link[rel=stylesheet]').each(function(){
		var cssBaseURL = '';
		var cssUrl = $(this).attr('href');
		//Conditionally make urls absolute to avoid conflict with tinymce.baseURL
		if( cssUrl.indexOf('/') === 0 ) {
			cssBaseURL = mw.config.get('wgServer');
		}
		//need to check, if the stylesheet is already included
		if (jQuery.inArray(cssBaseURL + cssUrl, currentSiteCSS) === -1)
			currentSiteCSS.push( cssBaseURL + cssUrl );
	});

	mw.loader.using( visialEditorModules ).done( function() {
		var BsVisualEditorLoaderUsingDeps = mw.config.get(
			'BsVisualEditorLoaderUsingDeps'
		);
		mw.loader.using( BsVisualEditorLoaderUsingDeps ).done( function(){
			if( me.visualEditor ) {
				return;
			}
			var veDefaultConfig = me.getVisualEditorDefaultConfig();

			//tonyMCE 4.6 https://gerrit.wikimedia.org/r/#/c/360618/2
			//window.tinyMCE.suffix = '.min';

			// set the appropriate base url for the TinyMCE installation
			tinymce.baseURL = me.makeTinyMCEBaseUrl();

			//tinymce.dom.Event.domLoaded = true;
			tinymce.init({
				selector: me.selector,
				menubar: false,
				statusbar: false,
				toolbar1: me.toolbar1.join( ' ' ),
				formats: veDefaultConfig.formats,
				bs_heading_formats: veDefaultConfig.bs_heading_formats,
				bs_table_function_formats: veDefaultConfig.bs_table_function_formats,
				bs_table_formats: veDefaultConfig.bs_table_formats,
				plugins: plugins, //needed!
				paste_word_valid_elements: 'b,strong,i,em,h1,h2,h3,h4,h5,table,thead,tfoot,tr,th,td,ol,ul,li,a,sub,sup,strike,br,del,div,p',
				paste_retain_style_properties: 'color text-decoration text-align',
				content_css: currentSiteCSS.join( ',' ),
				external_plugins: {
					'bswikicode': '../tiny_mce_plugins/bswikicode/plugin.js',
					'bsbehaviour': '../tiny_mce_plugins/bsbehaviour/plugin.js',
					'bsactions': '../tiny_mce_plugins/bsactions/plugin.js'
				},
				setup: function ( editor ) {
					me.visualEditor = editor;
				},
				language: me.getEditorLanguage(),
				branding: false
			});
		});
	});
};

bs.ui.widget.TextInputVisualEditor.prototype.makePluginsList = function() {
	var pluginNames = this.getPluginNames();
	return pluginNames.join( ' ' );
};

bs.ui.widget.TextInputVisualEditor.prototype.getPluginNames = function() {
	return [ 'lists', 'paste' ];
};

bs.ui.widget.TextInputVisualEditor.prototype.getVisualEditorDefaultConfig = function() {
	return mw.config.get(
		'BsVisualEditorConfigDefault',
		{}
	);
}

bs.ui.widget.TextInputVisualEditor.prototype.makeTinyMCEBaseUrl = function() {
	return mw.config.get( 'wgScriptPath', '' ) + '/extensions/BlueSpiceExtensions/VisualEditor/resources/tinymce';
};

bs.ui.widget.TextInputVisualEditor.prototype.getEditorLanguage = function() {
	return mw.config.get( 'wgUserLanguage', 'en' )
		.split( '-' )
		.slice( 0, 1 );
};