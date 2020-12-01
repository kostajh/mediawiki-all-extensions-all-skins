<?php
namespace PhpTagsObjects;

/**
 *
 *
 * @file PhpTagsWidgetConstants.php
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @licence GNU General Public Licence 2.0 or later
 */
class PhpTagsWidgetsConstants extends \PhpTags\GenericObject {

	public static function getConstantValue( $constantName ) {
		switch ( $constantName ) {
			case 'PHPTAGS_WIDGETS_VERSION':
				return \ExtensionRegistry::getInstance()->getAllThings()['PhpTags Widgets']['version'];
		}
		parent::getConstantValue( $constantName );
	}
}
