<?php
namespace PhpTagsObjects;

/**
 * Description of WidgetsSlick
 * @see http://kenwheeler.github.io/slick/
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @licence GNU General Public Licence 2.0 or later
 */
class WidgetSlick extends \PhpTags\GenericWidget {

	protected static $trueCase = array(
		'adaptiveheight' => 'adaptiveHeight',
		'autoplayspeed' => 'autoplaySpeed',
		'centermode' => 'centerMode',
		'centerpadding' => 'centerPadding',
		'cssease' => 'cssEase',
		'initialslide' => 'initialSlide',
		'focusonselect' => 'focusOnSelect',
		'pauseonhover' => 'pauseOnHover',
		'pauseondotshover' => 'pauseOnDotsHover',
		'slidestoshow' => 'slidesToShow',
		'slidestoscroll' => 'slidesToScroll',
		'swipetoslide' => 'swipeToSlide',
		'touchmove' => 'touchMove',
		'touchthreshold' => 'touchThreshold',
		'usecss' => 'useCSS',
		'variablewidth' => 'variableWidth',
		'waitforanimate' => 'waitForAnimate',
	);

	public static function f_slick() {
		return \PhpTags\Hooks::createObject( func_get_args(), 'Slick' );
	}

	public function m___construct( $value = null, $properties = null ) {
		$this->value[self::DATA] = $value;
		return parent::m___construct( $properties );
	}

	public function getString() {
		$this->addModules( 'ext.PhpTagsWidgets.slick', 'slick' );
		$this->addData( array( $this->value[self::PROP] ) );
		$data = $this->value[self::DATA];
		if ( is_array( $data ) ) {
			$data = '<div>' . implode( '</div><div>', $this->value[self::DATA] ) . '</div>';
		}
		return $data;
	}

	public function __call( $name, $arguments ) {
		list ( $callType, $tmp ) = explode( '_', $name, 2 );
		$subname = strtolower( $tmp );
		$property = $subname;

		switch ( $subname ) {
			case 'adaptiveheight':
			case 'autoplayspeed':
			case 'centermode':
			case 'centerpadding':
			case 'cssease':
			case 'focusonselect':
			case 'initialslide':
			case 'pauseonhover':
			case 'pauseondotshover':
			case 'slidestoshow':
			case 'slidestoscroll':
			case 'swipetoslide':
			case 'touchmove':
			case 'touchthreshold':
			case 'usecss':
			case 'variablewidth':
			case 'waitforanimate':
// @todo	case 'appendarrows':
// @todo	case 'prevarrow':
// @todo	case 'nextarrow':
// @???todo case 'custompaging': ???
				$property = self::$trueCase[$subname];
				// break is not necessary here
// @todo	case 'easing':
// @todo	case 'responsive':
			case 'accessibility':
			case 'autoplay':
			case 'arrows':
			case 'dots':
			case 'draggable':
			case 'fade':
//			case 'easing':
			case 'infinite':
			case 'swipe':
			case 'vertical':
			case 'rtl':
			case 'slide':
			case 'speed':
				if ( $callType === 'p' ) { // get property
					return isset( $this->value[self::PROP][$property] ) ? $this->value[self::PROP][$property] : null;
				} elseif ( $callType === 'b' ) { // set property
					if ( $arguments[0] === null ) {
						unset( $this->value[self::PROP][$property] );
					} else {
						$this->value[self::PROP][$property] = $arguments[0];
					}
					return $this;
				}
				break;
		}
		return parent::__call( $name, $arguments );
	}

	/**
	 * Set property centerPadding
	 * @param string $value
	 * @return \PhpTagsObjects\WidgetSlick
	 */
	public function b_centerPadding( $value ) {
		$matches = array();
		if ( $value === null ) {
			unset( $this->value[self::PROP]['centerPadding'] );
		} else {
			if ( preg_match( '/\d+(?:px|%)/i', $value, $matches ) ) {
				$this->value[self::PROP]['centerPadding'] = $matches[0];
			} else {
				// @todo error message
			}
		}
		return $this;
	}

	/**
	 * Set property rtl
	 * @param bool $value
	 */
	public function b_rtl( $value ) {
		if ( $value === null ) {
			unset( $this->value[self::PROP]['rtl'] );
			$this->b_dir( null );
		} else {
			$this->value[self::PROP]['rtl'] = $value;
			if ( $value ) {
				$this->b_dir( 'rtl' );
			}
		}
		return $this;
	}

	/**
	 *
	 * @param \PhpTagsObjects\WidgetSlick $value
	 * @return \PhpTagsObjects\WidgetSlick
	 */
	public function b_asNavFor( $value ) {
		$oldValue = isset( $this->value[self::PRIVATE_PROP]['asNavFor'] ) ? $this->value[self::PRIVATE_PROP]['asNavFor'] : null;
		if ( $oldValue === $value ) {
			return;
		}

		$this->setAsNavFor( $value );

		if ( $oldValue instanceof WidgetSlick ) {
			$oldValue->setAsNavFor( null );
			if ( $value !== null ) {
				$oldValue->setAsNavFor( $this );
			}
		}
		if ( $value instanceof WidgetSlick ) {
			$value->setAsNavFor( $this );
		}
		return $this;
	}

	public function p_asNavFor() {
		return isset($this->value[self::PRIVATE_PROP]['asNavFor']) ? $this->value[self::PRIVATE_PROP]['asNavFor'] : null;
	}

	public function setAsNavFor ( $value ) {
		if ( $value === null ) {
			unset( $this->value[self::PRIVATE_PROP]['asNavFor'] );
			unset( $this->value[self::PROP]['asNavFor'] );
		} else {
			$this->value[self::PRIVATE_PROP]['asNavFor'] = $value;
			$this->value[self::PROP]['asNavFor'] = '.' . $value->getCssClassName();
		}
	}

	// Never allow it (it is XSS door)
	public function b_lazyLoad() {}

}
