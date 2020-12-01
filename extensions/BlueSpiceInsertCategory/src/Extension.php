<?php

/**
 * InsertCategory extension for BlueSpice
 *
 * Dialogbox to enter a category link.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Sebastian Ulbricht
 * @author     Stefan Widmann <widmann@hallowelt.com>
 * @package    BlueSpiceInsertCategory
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

// Last review RBV (30.06.11 8:40)

namespace BlueSpice\InsertCategory;

use IContextSource;
use WikiPage;
use WikiTextContent;

/**
 * Class for category management assistent
 * @package BlueSpiceInsertCategory
 */
class Extension extends \BlueSpice\Extension {
	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public static function flyoutModuleSkip( IContextSource $context ) {
		if ( !$context->getTitle() || !$context->getTitle()->exists() ) {
			return false;
		}
		$wikiPage = WikiPage::factory( $context->getTitle() );
		if ( !$wikiPage ) {
			return false;
		}
		if ( !$wikiPage->getContent() ) {
			return false;
		}
		return $wikiPage->getContent() instanceof WikiTextContent;
	}
}
