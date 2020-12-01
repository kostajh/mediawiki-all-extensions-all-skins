/**
 * This file contains JavaScript for the TranslateSvg extension, relating to
 * file description pages.
 *
 * @file
 * @author Harry Burt
 * @copyright Copyright © 2012 Harry Burt
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
( function ( mw, $ ) {
	var TranslateSvgFilepage = function () {
		// Placeholder; note that 'this' can (and should) be used here safely
	};
	TranslateSvgFilepage.prototype = {
		init: function () {
			if ( !mw.config.exists( 'wgFileCanBeTranslated' ) ) {
				// This script relies on certain globals being exposed. If they're not,
				// we shouldn't try to go on.
				return;
			}

			var translationStarted = mw.config.get( 'wgFileTranslationStarted' );
			var full = mw.config.get( 'wgFileFullTranslations' );
			var partial = mw.config.get( 'wgFilePartialTranslations' );

			// p.SVGThumbs is a helper paragraph available on Wikimedia Commons and a few
			// other wikis.
			var parent = ( $( 'p.SVGThumbs' ).length > 0 )
				? $( 'p.SVGThumbs' ) : $( 'div.fullMedia' );
			if ( !translationStarted ) {
				if ( mw.config.get( 'wgFileCanBeTranslated' ) && mw.config.get( 'wgUserCanTranslate' ) ) {
					// No existing translations, can translate
					// TODO: suggest "log in to translate" if not?
					parent.append( '<br />' + this.getNoTranslationsString() );
				}
			} else {
				if ( full.length > 0 || partial.length > 0 ) {
					// Existing translations, show view link and/or translate links
					parent.append( '<br />' + this.getHasTranslationsString( full, partial ) );
				} else {
					// TODO: Awkward
				}
			}
		},

		getHasTranslationsString: function ( full, partial ) {
			// Prepare a string to append to the file description page
			// of a file with existing translations
			var canTranslate = mw.config.get( 'wgUserCanTranslate' );
			var userLangCode = mw.config.get( 'wgUserLanguage' );
			var userLangName = mw.config.get( 'wgUserLanguageName' );

			var langCodesUsed = [];
			var existing = [];
			var fullLength = full.length;
			var languages = $.merge( full, partial );
			for( var i = 0; i < languages.length; i++ ) {
				langCodesUsed.push( languages[i].code );
				var item = this.makeViewLink( languages[i].code, languages[i].name );
				if ( canTranslate ) {
					if ( i < fullLength ) {
						var label = mw.msg( 'translate-svg-filepage-edit' );
					} else {
						var label = mw.msg( 'translate-svg-filepage-finish' );
					}
					var link = this.makeTranslateLink( languages[i].code, label );
					item = mw.msg( 'translate-svg-filepage-item', item, link );
				}
				existing.push( item );
			}

			existing = existing.join( mw.msg( 'comma-separator' ) );

			var invites = [];
			if ( langCodesUsed.indexOf( userLangCode ) === -1 ) {
				invites.push( this.makeTranslateLink( userLangCode, userLangName ) );
			}
			invites.push( this.makeTranslateLink(
				false,
				mw.msg( 'translate-svg-filepage-another' )
			) );

			invites = invites.join( mw.msg( 'comma-separator' ) );

			if ( canTranslate ) {
				return mw.msg(
					'translate-svg-filepage-caption-translator',
					existing,
					invites
				);
			} else {
				return mw.msg( 'translate-svg-filepage-caption', existing );
			}
		},

		getNoTranslationsString: function () {
			// Prepare a string to append to the file description page
			// of a file with no existing translations but where translation
			// is possible.
			var filename = mw.config.get( 'wgTitle' );
			var uri = new mw.Uri(
				mw.util.getUrl( 'Special:TranslateNewSvg' )
			).extend( { 'group' : filename } ).toString();
			var link = mw.html.element(
				'a',
				{ 'href': uri },
				mw.msg( 'translate-svg-filepage-other' )
			);
			return mw.msg( 'translate-svg-filepage-invite', link );
		},

		makeViewLink: function ( langCode, langName ) {
			// Make a link to view the file in the language with the given
			// langCode and langName
			var pageName = mw.config.get( 'wgPageName' );
			var uri = new mw.Uri( window.location.href )
				.extend( { 'lang': langCode } ).toString();
			return mw.html.element(
				'a',
				{ 'href': uri },
				langName
			);
		},

		makeTranslateLink: function ( langCode, label ) {
			// Makes a link (with given label) to translate the file into
			// the language with given langCode
			var filename = mw.config.get( 'wgTitle' );
			var opts = { 'group' : filename };

			if ( langCode !== false ) {
				opts.language = langCode;
			} else {
				opts.chooselanguage = true;
			}
			var uri = new mw.Uri(
				mw.util.getUrl( 'Special:Translate' )
			).extend( opts ).toString();
			return mw.html.element(
				'a',
				{ 'href': uri },
				label
			);
		}
	};

	$( function () {
		var tsvgFilepage = new TranslateSvgFilepage();
		tsvgFilepage.init();
	} );
}( mediaWiki, jQuery ) );
