<?php
namespace PhpTagsObjects;

/**
 * Description of PhpTagsFuncNativeObject
 *
 * @author pastakhov
 */
class PhpTagsFuncDatePeriod extends PhpTagsFuncNativeObject {

	public function m___construct() {
		$arguments = \func_get_args();

		switch ( func_num_args() ) {
			case 1:
				if ( false === is_string( $arguments[0] ) ) {
					return self::pushExceptionExpectsParameter( 1, 'string', $arguments[0] );
				}
				break;
			case 2:
				if ( false === is_string( $arguments[0] ) ) {
					return self::pushExceptionExpectsParameter( 1, 'string', $arguments[0] );
				}
				if ( false === is_int( $arguments[1] ) ) {
					return self::pushExceptionExpectsParameter( 2, 'int', $arguments[1] );
				}
				break;
			case 3:
			case 4:
				$p1 = $arguments[0];
				if ( false === ( $p1 instanceof \PhpTags\GenericObject && $p1->isInstanceOf( 'DateTimeInterface' ) ) ) {
					return self::pushExceptionExpectsParameter( 1, 'DateTimeInterface', $arguments[0] );
				}
				$p2 = $arguments[1];
				if ( false === ( $p2 instanceof \PhpTags\GenericObject && $p2->isInstanceOf( 'DateInterval' ) ) ) {
					return self::pushExceptionExpectsParameter( 2, 'DateInterval', $arguments[1] );
				}
				$p3 = $arguments[2];
				if ( false === ( is_int( $p3 ) || ( $p2 instanceof \PhpTags\GenericObject && $p2->isInstanceOf( 'DateTimeInterface' ) ) ) ) {
					return self::pushExceptionExpectsParameter( 2, 'int or DateTimeInterface', $arguments[1] );
				}
				break;
		}

		return parent::m___construct( $arguments );
	}

}
