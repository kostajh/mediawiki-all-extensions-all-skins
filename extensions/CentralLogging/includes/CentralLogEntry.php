<?php

/**
 * Our class to handle log entries
 * Extends a normal log entry for all other functionality
 *
 * This file is part of Extension:CentralLogging
 *
 * Copyright (C) 2013, Kunal Mehta
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

class CentralLogEntry extends ManualLogEntry {

	/**
	 * @var bool
	 */
	protected $shouldWePublish;

	/**
	 * @var string
	 */
	protected $publishTo;

	/**
	 * Constructor function
	 *
	 * @param string $type
	 * @param string $subtype
	 */
	function __construct( $type, $subtype ) {
		parent::__construct( $type, $subtype );
	}

	/**
	 * Queues the log entry into the job queue. If the central wiki is
	 * the same as our current wiki, we will insert the log entry normally
	 * @param string|null $dbname Database name to insert into,
	 *   will fallback on $wgCentralWiki if not set
	 * @param bool $publish Whether to call ManualLogEntry::publish afterwards
	 * @param string $to The $to parameter in ManualLogEntry::publish
	 * @return int
	 */
	function queue( $dbname = null, $publish = true, $to = 'rcandudp' ) {
		global $wgCentralWiki, $wgDBname;
		if ( $dbname == null ) {
			$dbname = $wgCentralWiki;
		}
		// Make sure our dbname is stored in the log entry so we can use it when displaying
		$this->parameters['dbname'] = $wgDBname;

		if ( $wgDBname == $wgCentralWiki ) { // If we're on the central wiki, just log it normally
			$logid = parent::insert();
			if ( $publish ) {
				$this->publish( $logid, $to );
			}
			return $logid;
		}

		$this->shouldWePublish = $publish;
		$this->publishTo = $to;
		$this->setTimestamp( wfTimestampNow() ); // Job queue might be delayed so set the TS now
		$params = [ 'data' => $this ];
		$job = new CentralLogJob( $this->getTarget(), $params );
		JobQueueGroup::singleton( $dbname )->push( $job );
		return 0; // Better than nothing?
	}

	function shouldWePublishEntry() {
		return $this->shouldWePublish;
	}

	function publishEntryTo() {
		return $this->publishTo;
	}
}
