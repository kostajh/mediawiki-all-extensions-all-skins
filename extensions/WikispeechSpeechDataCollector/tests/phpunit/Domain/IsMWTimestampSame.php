<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Domain;

use Hamcrest\BaseMatcher;
use Hamcrest\Description;
use MWTimestamp;

/**
 * This is a PHPUnit matcher for testing the content of two {@link MWTimestamp} equals,
 * or if both of them are undefined.
 *
 * The class is meant to be used by implementations of PHPUnit constraints for executing assertions.
 *
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests
 * @since 0.1.0
 */
class IsMWTimestampSame extends BaseMatcher {

	/** @var MWTimestamp|null */
	private $timestamp;

	/**
	 * @since 0.1.0
	 * @param MWTimestamp|null $timestamp
	 */
	public function __construct( ?MWTimestamp $timestamp ) {
		$this->timestamp = $timestamp;
	}

	/**
	 * @since 0.1.0
	 * @param mixed|null $item
	 * @return bool <code>true</code> if <var>$item</var> matches,
	 *   otherwise <code>false</code>.
	 */
	public function matches( $item ) {
		if ( $this->timestamp === null ) {
			return $item === null;
		} elseif ( !( $item instanceof MWTimestamp ) ) {
			return false;
		}
		return $this->timestamp->getTimestamp() == $item->timestamp->getTimestamp();
	}

	/**
	 * @since 0.1.0
	 * @param Description $description
	 */
	public function describeTo( Description $description ) {
		$description->appendText( 'a MWTimestamp set to ' . $this->timestamp );
	}

}
