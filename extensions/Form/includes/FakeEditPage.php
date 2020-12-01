<?php
/**
 * Form extension -- Use a form-based interface to start new articles
 * Copyright 2007 Vinismo, Inc. (http://vinismo.com/)
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @file
 * @ingroup Extensions
 * @author Evan Prodromou <evan@vinismo.com>
 */

# Dummy class for extensions that support EditFilter hook
// Why isn't this just extending the real EditPage? --ashley, 12 November 2017
class FakeEditPage {
	public $mTitle;
	public $summary;
	public $textbox1;

	function __construct( &$nt ) {
		$this->mTitle = $nt;
	}

	function getTitle() {
		return $this->mTitle;
	}
}
