<?php
namespace MultiMaps;

/**
 * Marker class for collection of map elements
 *
 * @file Marker.php
 * @ingroup MultiMaps
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @license GPL-2.0-or-later
 * @property string $icon Icon URL of marker
 * @property array $size Size of icon
 * @property array $anchor Anchor of icon
 * @property string $shadow Shadow of icon
 * @property array $sh_size Size of shadow
 * @property array $sh_anchor Anchor of shadow
 */
class Marker extends BaseMapElement {

	function __construct() {
		parent::__construct();

		$this->availableProperties = array_merge(
			$this->availableProperties,
			[ 'icon' ]
		);
	}

	/**
	 * Returns element name
	 * return string Element name
	 */
	public function getElementName() {
		return 'Marker'; // TODO i18n?
	}

	public function setProperty( $name, $value ) {
		global $egMultiMaps_CoordinatesSeparator, $egMultiMaps_OptionsSeparator;

		if ( strtolower( $name ) != 'icon' ) {
			return parent::setProperty( $name, $value );
		}

		// Explode icon, it containt 'icon', 'size', 'anchor', 'shadow', 'sh_size', 'sh_anchor'
		$properties = array_map(
			'trim',
			explode( $egMultiMaps_CoordinatesSeparator, $value )
		);

		// Icon URL
		if ( !empty( $properties[0] ) ) {
			$v = $properties[0];
			if ( $v[0] == '/' && $GLOBALS['egMultiMaps_IconAllowFromDirectory'] === true ) {
				if ( preg_match( '#[^0-9a-zA-Zа-яА-Я./_=\+\-]#', $v ) || preg_match( '#/../#', $v ) ) {
					$this->errormessages[] = \wfMessage( 'multimaps-marker-incorrect-icon-url', $v )->escaped();
					return false;
				}
				$v = $GLOBALS['wgServer'] . $GLOBALS['egMultiMaps_IconPath'] . $v;
			} else {
				$title = \Title::newFromText( $v, NS_FILE );
				if ( $title !== null && $title->exists() ) {
					$imagePage = new \ImagePage( $title );
					$v = $imagePage->getDisplayedFile()->getURL();
				} else {
					$this->errormessages[] = \wfMessage( 'multimaps-marker-incorrect-icon', $v )->escaped();
					return false;
				}
			}
			$this->properties['icon'] = htmlspecialchars( $v, ENT_NOQUOTES );
		}

		// Icon size
		if ( !empty( $properties[1] ) ) {
			$v = array_map(
				'intval',
				explode( $egMultiMaps_OptionsSeparator, $properties[1] )
			);
			if ( count( $v ) != 2 ) {
				$this->errormessages[] = \wfMessage( 'multimaps-marker-incorrect-icon-size', $v, $value )->escaped();
				return false;
			}
			$this->properties['size'] = $v;
		}

		// Icon anchor
		if ( !empty( $properties[2] ) ) {
			$v = array_map(
				'intval',
				explode( $egMultiMaps_OptionsSeparator, $properties[2] )
			);
			if ( count( $v ) != 2 ) {
				$this->errormessages[] = \wfMessage( 'multimaps-marker-incorrect-icon-anchor', $v, $value )->escaped();
				return false;
			}
			$this->properties['anchor'] = $v;
		}

		// Shadow URL
		if ( !empty( $properties[3] ) ) {
			$v = $properties[3];
			if ( $v[0] == '/' && $GLOBALS['egMultiMaps_IconAllowFromDirectory'] === true ) {
				if ( preg_match( '#[^0-9a-zA-Zа-яА-Я./_=\+\-]#', $v ) || preg_match( '#/../#', $v ) ) {
					$this->errormessages[] = \wfMessage( 'multimaps-marker-incorrect-shadow-url', $v )->escaped();
					return false;
				}
				$v = $GLOBALS['wgServer'] . $GLOBALS['egMultiMaps_IconPath'] . $v;
			} else {
				$title = \Title::newFromText( $v, NS_FILE );
				if ( $title !== null && $title->exists() ) {
					$imagePage = new \ImagePage( $title );
					$v = $imagePage->getDisplayedFile()->getURL();
				} else {
					$this->errormessages[] = \wfMessage( 'multimaps-marker-incorrect-shadow-file', $v )->escaped();
					return false;
				}
			}
			$this->properties['shadow'] = htmlspecialchars( $v, ENT_NOQUOTES );
		}

		// Shadow size
		if ( !empty( $properties[4] ) ) {
			$v = array_map(
				'intval',
				explode( $egMultiMaps_OptionsSeparator, $properties[4] )
			);
			if ( count( $v ) != 2 ) {
				$this->errormessages[] = \wfMessage( 'multimaps-marker-incorrect-shadow-size', $v, $value )->escaped();
				return false;
			}
			$this->properties['sh_size'] = $v;
		}

		// Shadow anchor
		if ( !empty( $properties[5] ) ) {
			$v = array_map(
				'intval',
				explode( $egMultiMaps_OptionsSeparator, $properties[5] )
			);
			if ( count( $v ) != 2 ) {
				$this->errormessages[] = \wfMessage( 'multimaps-marker-incorrect-shadow-anchor', $v, $value )->escaped();
				return false;
			}
			$this->properties['sh_anchor'] = $v;
		}

		return true;
	}

}
