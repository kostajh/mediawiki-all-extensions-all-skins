<?php

/**
 * MicroBlog class for BSSocial
 *
 * add desc
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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
 * @author     Patric Wirth
 * @package    BlueSpiceSocial
 * @subpackage BSSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

namespace BlueSpice\Social\MicroBlog\EntityConfig;

use BlueSpice\Social\EntityConfig\Text;

/**
 * MicroBlog class for BSSocial extension
 * @package BlueSpiceSocial
 * @subpackage BSSocial
 */
class MicroBlog extends Text {
	/**
	 *
	 * @return array
	 */
	public function addGetterDefaults() {
		return [];
	}

	/**
	 *
	 * @return string
	 */
	protected function get_EntityClass() {
		return "\\BlueSpice\\Social\\MicroBlog\\Entity\\MicroBlog";
	}

	/**
	 *
	 * @return array
	 */
	protected function get_ModuleScripts() {
		return array_merge( parent::get_ModuleScripts(), [
			'ext.bluespice.social.entity.text',
			'ext.bluespice.social.entity.microblog',
		] );
	}

	/**
	 *
	 * @return array
	 */
	protected function get_ModuleStyles() {
		return array_merge( parent::get_ModuleStyles(), [
			'ext.bluespice.socialmicroblog.styles'
		] );
	}

	/**
	 *
	 * @return string
	 */
	protected function get_TypeMessageKey() {
		return 'bs-socialmicroblog-type';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_Notifications() {
		return [
			'socialentity-microblog-create' => "\\BlueSpice\\Social\\MicroBlog\\NotificationConfig\\Create",
		];
	}

	/**
	 *
	 * @return array
	 */
	protected function get_NotificationActions() {
		return [
			'create' => 'socialentity-microblog-create',
		];
	}

	/**
	 *
	 * @return true
	 */
	protected function get_ExtendedSearchListable() {
		return true;
	}

	/**
	 *
	 * @return true
	 */
	protected function get_EntityListSpecialTimelineTypeSelected() {
		return true;
	}
}
