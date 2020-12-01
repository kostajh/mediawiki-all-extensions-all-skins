<?php

namespace BlueSpice\CountThings;

/**
 * BlueSpice MediaWiki
 * Extension: CountThings
 * Description: Counts all kinds of things.
 * Authors: Markus Glaser, Mathias Scheer
 *
 * Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
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
 * http://www.gnu.org/copyleft/gpl.html
 *
 * For further information visit https://bluespice.com
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage CountThings
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

/**
 * CountThings adds 3 tags, used in WikiMarkup as follows:
 * absolute number of articles: <bs:countarticles />
 *
 * Count of Characters, Words, and Pages (2000 chars/page) for article 'Test':
 * <bs:countcharacters>Test</bs:countcharacters>
 *
 * <bs:countcharacters modes="words chars">Test</bs:countcharacters> shows only
 * word- and charactercount
 *
 * <bs:countcharacters>Test Test_Site</bs:countcharacters> shows counts for this two sites
 * absolute number of users: <bs:countusers />
 */
class Extension extends \BlueSpice\Extension {
}
