<?php
namespace PhpTagsObjects;

/**
 * Description of WidgetElement
 *
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @licence GNU General Public Licence 2.0 or later
 */
class WidgetElement extends \PhpTags\GenericWidget {

	protected static $allowedElements = [ 'div', 'span' ];

	public function m___construct( $element = 'span', $content = null, $properties = null ) {
		$key = array_search( strtolower( $element ), self::$allowedElements );
		if ( $key === false ) {
			throw new \PhpTags\HookException( 'The <' . $element . '> element is not allowed to create. Allowed elements are: <' . implode( '>, <', self::$allowedElements ) . '>' );
		}

		$this->element = self::$allowedElements[$key];
		$this->value[self::DATA] = $content;
		return parent::m___construct( $properties );
	}

	public function getString() {
		return $this->value[self::DATA];
	}

	public function getCssClassName() {}

}
