<?php

/**
 * Log formatter to account for foreign links
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

class CentralLogFormatter extends LogFormatter {
	/**
	 * Uses WikiMap to make a foreign link based on the dbname
	 * If the entry was local, use the normal method
	 * @param Title|null $title
	 * @param array $parameters
	 * @param string|null $html
	 * @return string
	 */
	protected function makePageLink( Title $title = null, $parameters = [], $html = null ) {
		global $wgDBname;
		$entry = $this->entry;
		$params = $entry->getParameters();
		$dbname = $params['dbname'];
		if ( $wgDBname == $dbname ) { // Viewing on the same wiki it was inserted in
			return parent::makePageLink( $title, $parameters, $html );
		} else {
			return WikiMap::makeForeignLink( $dbname, $title->getPartialURL() );
		}
	}

	/**
	 * Uses WikiMap to make a foreign link based on the dbname
	 * If the entry was local, use the normal method
	 * @param User $user
	 * @param int $toolFlags
	 * @return string
	 */
	function makeUserLink( User $user, $toolFlags = 0 ) {
		global $wgDBname;
		$entry = $this->entry;
		$params = $entry->getParameters();
		$dbname = $params['dbname'];
		if ( $wgDBname == $dbname ) { // Viewing on the same wiki it was inserted in
			return parent::makeUserLink( $user, $toolFlags );
		} else {
			return WikiMap::foreignUserLink( $dbname, $user->getName() );
		}
	}
}
