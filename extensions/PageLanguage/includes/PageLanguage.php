<?php

class PageLanguage {

	private static $cache = [];

	/**
	 * @param Title $title
	 * @param Language|StubUserLang|string &$pageLang
	 * @return true
	 */
	public static function onPageContentLanguage( Title $title, &$pageLang ) {
		if ( isset( self::$cache[$title->getPrefixedDBKey()] ) ) {
			$pageLang = self::$cache[$title->getPrefixedDBKey()];
		} elseif ( $title->getArticleID() > 0 ) {
			$dbr = wfGetDB( DB_REPLICA );
			$langCode = $dbr->selectField(
				'page_props', 'pp_value', [
					'pp_page' => $title->getArticleID(),
					'pp_propname' => 'pagelanguage',
				], __METHOD__
			);

			if ( $langCode !== false && Language::isValidCode( $langCode ) ) {
				$pageLang = Language::factory( $langCode );
			}
		}

		return true;
	}

	/**
	 * Register the pagelanguage hook with the Parser.
	 *
	 * @param Parser $parser
	 * @return true
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setFunctionHook( 'pagelanguage', 'PageLanguage::funcPageLanguage', Parser::SFH_NO_HASH );

		return true;
	}

	public static function funcPageLanguage( Parser $parser, $langCode, $uarg = '' ) {
		static $magicWords = null;
		if ( $magicWords === null ) {
			$magicWords = new MagicWordArray( [ 'pagelanguage_noerror', 'pagelanguage_noreplace' ] );
		}
		$arg = $magicWords->matchStartToEnd( $uarg );

		$langCode = trim( $langCode );
		if ( strlen( $langCode ) === 0 ) {
			return '';
		}

		if ( Language::isValidCode( $langCode ) ) {
			$lang = Language::factory( $langCode );
		} else {
			return '<span class="error">' .
				wfMessage( 'pagelanguage-invalid' )->inContentLanguage()
					->params( wfEscapeWikiText( $langCode ) )->text() .
				'</span>';
		}

		$old = $parser->getOutput()->getProperty( 'pagelanguage' );
		if ( $old === false || $arg !== 'pagelanguage_noreplace' ) {
			$parser->getOutput()->setProperty( 'pagelanguage', $lang->getCode() );
			self::$cache[$parser->getTitle()->getPrefixedDBKey()] = $lang;
		}

		if ( $old === false || $old === $lang->getCode() || $arg ) {
			return '';
		} else {
			return '<span class="error">' .
				wfMessage( 'pagelanguage-duplicate' )->inContentLanguage()->params(
					wfEscapeWikiText( $old ), wfEscapeWikiText( $lang->getCode() ) )->text() .
				'</span>';
		}
	}
}
