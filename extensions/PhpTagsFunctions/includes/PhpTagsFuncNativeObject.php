<?php
namespace PhpTagsObjects;

/**
 * Description of PhpTagsFuncNativeObject
 *
 * @author pastakhov
 */
class PhpTagsFuncNativeObject extends PhpTagsFunc {

	public function m___construct() {
		$arguments = func_get_args();
		foreach ( $arguments as &$arg ) {
			if ( $arg instanceof \PhpTags\GenericObject ) {
				$arg = $arg->getValue();
			}
		}

		$reclass = new \ReflectionClass( $this->objectName );
		$object = $reclass->newInstanceArgs( $arguments );
		if ( is_object( $object ) ) {
			$this->value = $object;
			return true;
		}
		$this->value = null;
		return false;
	}

	public function __call( $name, $arguments ) {
		list( $callType, $subname ) = explode( '_', $name, 2 );

		switch ( $callType ) {
			case 'm': // metchod
				foreach ( $arguments as &$arg ) {
					if ( $arg instanceof \PhpTags\GenericObject ) {
						$arg = $arg->getValue();
					}
				}
				$return = call_user_func_array( [ $this->value, $subname ], $arguments );
				if ( is_object( $return ) ) {
					$return = \PhpTags\Hooks::getObjectWithValue( get_class( $return ), $return );
				}
				return $return;
			case 'p': // get property
				if ( \PhpTags\Hooks::hasProperty( $this->objectKey, $subname ) ) {
					$return = $this->value->$subname;
					if ( is_object( $return ) ) {
						$return = \PhpTags\Hooks::getObjectWithValue( get_class( $return ), $return );
					}
					return $return;
				}
				break;
			case 'b': // set property
				$this->value->$subname = $arguments[0];
				return;
		}
		return parent::__call( $name, $arguments );
	}

	public static function __callStatic( $name, $arguments ) {
		list( $callType, $subname ) = explode( '_', $name, 2 );
		$object = \PhpTags\Hooks::getCallInfo( \PhpTags\Hooks::INFO_ORIGINAL_OBJECT_NAME );

		switch ( $callType ) {
			case 'f': // function
				foreach ( $arguments as &$arg ) {
					if ( $arg instanceof \PhpTags\GenericObject ) {
						$arg = $arg->getValue();
					}
				}
				$return = call_user_func_array( "parent::$name", $arguments );
				if ( is_object( $return ) ) {
					$return = \PhpTags\Hooks::getObjectWithValue( get_class( $return ), $return );
				}
				return $return;
			case 's': // static method
				foreach ( $arguments as &$arg ) {
					if ( $arg instanceof \PhpTags\GenericObject ) {
						$arg = $arg->getValue();
					}
				}
				$return = call_user_func_array( [ $object, $subname ], $arguments );
				if ( is_object( $return ) ) {
					$return = \PhpTags\Hooks::getObjectWithValue( get_class( $return ), $return );
				}
				return $return;
			case 'c': // constant
				$reflect = new \ReflectionClass( $object );
				if ( true === $reflect->hasConstant( $subname ) ) {
					$return = $reflect->getConstant( $subname );
					if ( is_object( $return ) ) {
						$return = \PhpTags\Hooks::getObjectWithValue( get_class( $return ), $return );
					}
					return $return;
				}
				break;
			case 'q': // get static property
				$reflect = new \ReflectionClass( $object );
				try {
					$return = $reflect->getStaticPropertyValue( $subname );
					if ( is_object( $return ) ) {
						$return = \PhpTags\Hooks::getObjectWithValue( get_class( $return ), $return );
					}
					return $return;
				} catch ( ReflectionException $e ) {
				}
				break;
			case 'd': // set static property
				$reflect = new \ReflectionClass( $object );
				$reflect->setStaticPropertyValue( $subname, $arguments[0] );
				return;
		}
		return parent::__callStatic( $name, $arguments );
	}

	public function isInstanceOf( $class_name ) {
		return $this->value instanceof $class_name;
	}

}
