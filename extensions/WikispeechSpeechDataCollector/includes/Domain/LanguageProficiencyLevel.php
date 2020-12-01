<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Domain;

/**
 * Class LanguageProficiencyLevel
 *
 * How well a user pronounce a specific dialect {@link UserDialect} of a language,
 * or how well a user understands a language {@link UserLanguageProficiencyLevel}
 * in written or spoken form.
 *
 * Based on Babel levels.
 * https://www.mediawiki.org/wiki/Extension:Babel
 *
 * @package MediaWiki\WikispeechSpeechDataCollector\Domain
 * @since 0.1.0
 */
class LanguageProficiencyLevel {
	public const NONE = 0;
	public const BASIC = 1;
	public const INTERMEDIATE = 2;
	public const ADVANCED = 3;
	public const NEAR_NATIVE = 4;
	public const PROFESSIONAL = 5;
	public const NATIVE = 6;
}
