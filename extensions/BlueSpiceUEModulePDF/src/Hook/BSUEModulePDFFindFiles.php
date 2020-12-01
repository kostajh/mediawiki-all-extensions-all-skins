<?php
/**
 * Hook handler base class for BlueSpice hook BSUEModulePDFFindFiles
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
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @package    BlueSpiceUEModulePDF
 * @copyright  Copyright (C) 2018 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\UEModulePDF\Hook;

abstract class BSUEModulePDFFindFiles extends \BlueSpice\Hook {

	/**
	 *
	 * @var \BsPDFServlet
	 */
	protected $pdfServlet = null;

	/**
	 *
	 * @var \DOMElement
	 */
	protected $imageEl = null;

	/**
	 *
	 * @var string
	 */
	protected $absoluteFsPath = '';

	/**
	 *
	 * @var string
	 */
	protected $fileName = '';

	/**
	 *
	 * @param \BsPDFServlet $pdfServlet
	 * @param \DOMElement $imageEl
	 * @param string &$absoluteFsPath
	 * @param string &$fileName
	 * @return bool
	 */
	public static function callback( $pdfServlet, $imageEl, &$absoluteFsPath, &$fileName ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$pdfServlet,
			$imageEl,
			$absoluteFsPath,
			$fileName
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param \BsPDFServlet $pdfServlet
	 * @param \DOMElement $imageEl
	 * @param string &$absoluteFsPath
	 * @param string &$fileName
	 */
	public function __construct( $context, $config, $pdfServlet, $imageEl, &$absoluteFsPath,
			&$fileName ) {
		parent::__construct( $context, $config );

		$this->pdfServlet = $pdfServlet;
		$this->imageEl = $imageEl;
		$this->absoluteFsPath =& $absoluteFsPath;
		$this->fileName =& $fileName;
	}
}
