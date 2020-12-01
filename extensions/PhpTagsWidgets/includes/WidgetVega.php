<?php
namespace PhpTagsObjects;

/**
 * Description of WidgetVega
 * @see http://kenwheeler.github.io/slick/
 *
 * @author pastakhov
 */
class WidgetVega extends \PhpTags\GenericWidget {

	public static function f_vega() {
		return \PhpTags\Hooks::createObject( func_get_args(), 'Vega' );
	}

	public function getString() {
		global $wgPhpTagsWidgetVegaDomainWhiteList;
		static $domainWhiteListWasAdded = false;

		if ( false === $domainWhiteListWasAdded ) {
			$domainWhiteListWasAdded = true;
			\PhpTags\Renderer::setScriptVariable( [ 'Widgets', 'vega', 'domainWhiteList' ], $wgPhpTagsWidgetVegaDomainWhiteList );
		}
		$this->addModules( 'ext.PhpTagsWidgets.vega', 'extpwVega' );
		$this->addData( array( $this->value[self::PROP] ) );
	}

	public function __call( $name, $arguments ) {
		list ( $callType, $tmp ) = explode( '_', $name, 2 );
		$property = strtolower( $tmp );

		switch ( $property ) {
			case 'axes':
			case 'data':
			case 'height':
			case 'legends':
			case 'marks':
			case 'name':
			case 'padding':
			case 'scales':
			case 'viewport':
			case 'width':
				if ( $callType === 'p' ) { // get property
					return isset( $this->value[self::PROP][$property] ) ? $this->value[self::PROP][$property] : null;
				} elseif ( $callType === 'b' ) { // set property
					if ( $arguments[0] === null ) {
						unset( $this->value[self::PROP][$property] );
					} else {
						$this->value[self::PROP][$property] = $arguments[0];
					}
					return;
				}
				break;
		}
		return parent::__call( $name, $arguments );
	}

}
