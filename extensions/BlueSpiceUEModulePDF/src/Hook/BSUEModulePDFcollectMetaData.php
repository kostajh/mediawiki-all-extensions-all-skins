<?php
/**
 * Hook handler base class for BlueSpice hook BSUEModulePDFcollectMetaData
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
 * @author     Patric Wirth
 * @package    BlueSpiceUEModulePDF
 * @copyright  Copyright (C) 2018 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\UEModulePDF\Hook;

abstract class BSUEModulePDFcollectMetaData extends \BlueSpice\Hook {

	/**
	 *
	 * @var \Title
	 */
	protected $title = null;

	/**
	 *
	 * @var \DOMDocument
	 */
	protected $pageDOM = null;

	/**
	 *
	 * @var array
	 */
	protected $params = null;

	/**
	 *
	 * @var \DOMXPath
	 */
	protected $DOMXPath = null;

	/**
	 *
	 * @var array
	 */
	protected $meta = null;

	/**
	 *
	 * @param \Title $title
	 * @param array $pageDOM
	 * @param array &$params
	 * @param \DOMXPath $DOMXPath
	 * @param array &$meta
	 * @return bool
	 */
	public static function callback( $title, $pageDOM, &$params, $DOMXPath, &$meta ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$title,
			$pageDOM,
			$params,
			$DOMXPath,
			$meta
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param \Title $title
	 * @param \DOMDocument $pageDOM
	 * @param array &$params
	 * @param \DOMXPath $DOMXPath
	 * @param array &$meta
	 */
	public function __construct( $context, $config, $title, $pageDOM, &$params, $DOMXPath, &$meta ) {
		parent::__construct( $context, $config );

		$this->title = $title;
		$this->pageDOM = $pageDOM;
		$this->params =& $params;
		$this->DOMXPath = $DOMXPath;
		$this->meta =& $meta;
	}
}
