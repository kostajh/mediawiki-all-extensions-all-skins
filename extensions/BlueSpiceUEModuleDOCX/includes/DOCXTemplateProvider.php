<?php
/**
 * DOCXTemplateProvider.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @version    $Id: DOCXTemplateProvider.php 8691 2013-02-21 15:56:27Z rvogel $
 * @package    BlueSpice_Extensions
 * @subpackage UEModulePDF
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

/**
 * UniversalExport DOCXTemplateProvider class.
 * @package BlueSpice_Extensions
 * @subpackage UEModulePDF
 */
class DOCXTemplateProvider {

	/**
	 * Provides a array suitable for the MediaWiki HtmlFormField class
	 * HtmlSelectField.
	 * @param array $params Has to contain the 'template-path' that has to be
	 * searched for valid templates.
	 * @return array A options array for a HtmlSelectField
	 */
	public static function getTemplatesForSelectOptions( $params ) {
		$options = [];
		try {
			$path = realpath( $params['template-path'] );
			$dirIterator = new DirectoryIterator( $path );
			foreach ( $dirIterator as $fileInfo ) {
				if ( $fileInfo->isDir() || $fileInfo->isDot() ) { continue;
				}

				$fileName = $fileInfo->getBasename();
				$fileNameParts = explode( '.', $fileName );
				$fileExtension = array_pop( $fileNameParts );
				if ( strtoupper( $fileExtension ) != 'DOCX' ) { continue;
				}

				$templateName = implode( '.', $fileNameParts );
				$options[$templateName] = $fileName;
			}
		}
		catch ( Exception $e ) {
			wfDebugLog(
				'BS::UEModuleDOCX',
				'DOCXTemplateProvider::getTemplatesForSelectOptions: Error: ' . $e->getMessage()
			);
			return [ '-' => '-' ];
		}

		return $options;
	}
}
