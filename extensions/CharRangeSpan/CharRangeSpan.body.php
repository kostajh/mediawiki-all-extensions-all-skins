<?php
/* See COPYING file for copyright and license details. */

class CharRangeSpan {
	private $attrs;
	private $ranges;
	private $maybeChars;
	private $replacementSpan;

	/**
	 * Only public entry point of this class.
	 * Called from ParserAfterTidy
	 */
	public static function doCharRangeSpan( &$parser, &$text ) {
		global $wgCharRangeSpanSettings;

		foreach ( $wgCharRangeSpanSettings as $name => $options ) {
			$rangeSpanObj = new self( $options, $name );
			$text = $rangeSpanObj->addSpans( $text );
		}
		return true;
	}

	/**
	 * Does some checks to make sure valid parameters were given
	 *
	 * @param array $opts Options from $wgCharRangeSpanSettings
	 * @param string $name What language code is this for.
	 */
	private function __construct( $opts, $name ) {
		foreach ( [ 'attrs', 'ranges', 'maybeChars' ] as $a ) {
			if ( !isset( $opts[$a] ) ) {
				throw new MWException( "CharRangeSpan error: " . $a . " not set for " . $name );
			}
			$this->$a = $opts[$a];
		}
		$this->replacementSpan = '<span ' . $this->attrs . '>';

		// This extension runs after most parsing is done. Hence
		// the only characters left to worry about are &, <, > and U+7F (U+7F is used internally)
		if ( preg_match( '/[' . $this->maybeChars . ']/', "&<>\x7F" ) ) {
			throw new MWException( "CharRangeSpan error: " . $name . " maybeChars contains html markup"
			. " characters. These cannot be used safely, as they lead to inconsistency." );
		}
		// Note that & is 38, < is 60, > is 62, and "DEL" (aka U+7F) is 127.
		foreach ( $this->ranges as $r ) {
			if ( ( hexdec( $r[0] ) <= 38 && hexdec( $r[1] ) >= 38 ) ||
			   ( hexdec( $r[0] ) <= 60 && hexdec( $r[1] ) >= 60 ) ||
			   ( hexdec( $r[0] ) <= 62 && hexdec( $r[1] ) >= 62 ) ||
			   ( hexdec( $r[0] ) <= 127 && hexdec( $r[1] ) >= 127 ) ) {
				throw new MWException( "CharRangeSpan error: &, <, >, and <DEL> are not allowed in range."
				. " These cannot be used safely since they interfere with HTML tags." );
			}
		}
	}

	/**
	 * Actually add the spans to the text
	 *
	 * @param string $text The html content of the page.
	 */
	private function addSpans( $text ) {
		/* Don't touch anything if the desired span is already in the text,
		 * so it can safely run multiple times on the same text. This is not
		 * foolproof, but is good enough.
		 */
		if ( preg_match( '/' . preg_quote( $this->replacementSpan, '/' ) . '/', $text ) ) {
			return;
		}

		$range = "";
		foreach ( $this->ranges as $r ) {
			$range .= '\x{' . $r[0] . '}-\x{' . $r[1] . '}';
		}

		/* The craziness with maybeChars is needed so lone punctuation isn't
		 * matched, and doesn't cause a <span> to be closed.
		 *
		 * The (\<[^<]*\>) is in order to match html tags, so we can
		 * skip over them, instead of matching things inside their
		 * attributes which causes problems. Ditto for entity references
		 */
		$regex = '/(\<[^<]*\>|&\S*?;)|(?:[' . $range . ']+(?:[' . $this->maybeChars . ']+[' . $range . ']+)*)/u';

		$newtext = preg_replace_callback( $regex, [ $this, 'replacementCallback' ], $text );
		if ( $newtext !== null ) {
			return $newtext;
		} else {
			wfDebug( "Error doing replacement regex in " . __METHOD__ );
			return $text;
		}
	}

	/**
	 * Wrap matched text in a span, unless
	 * it is an html tag or an entity reference.
	 */
	private function replacementCallback( $match ) {
		if ( isset( $match[1] ) && $match[1] !== '' ) {
			// Skip over html tags. This prevents replacing
			// things like <span title="δ"> with
			// <span title="<span lang="grc">δ</span>">
			// which would be bad.
			// This also skips entity references like &lt;
			return $match[0];
		}
		// Otherwise, normal text. Add the span
		return $this->replacementSpan . $match[0] . '</span>';
	}
}
