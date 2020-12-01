<?php

/**
 * Job class which submits jobs when run
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

class CentralLogJob extends Job {

	/**
	 * @param Title $title
	 * @param array $params
	 */
	public function __construct( $title, $params ) {
		parent::__construct( 'centrallogJob', $title, $params );
	}

	public function run() {
		/**
		 * @var $entry CentralLogEntry
		 */
		$entry = $this->params['data'];
		$logId = $entry->insert();
		if ( $entry->shouldWePublishEntry() ) {
			$entry->publish( $logId, $entry->publishEntryTo() );
		}

		return true;
	}
}
