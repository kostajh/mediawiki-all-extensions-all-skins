<?php
namespace PhpTagsObjects;

use PhpTags\Renderer;
use Title;

/**
 * Description of WidgetLink
 *
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @licence GNU General Public Licence 2.0 or later
 */
class WidgetMediaViewer extends \PhpTags\GenericWidget {

	protected $element = 'a';
	protected $image;

	/**
	 * @param $image
	 * @param $text
	 * @param $properties
	 * @return bool
	 * @throws \PhpTags\HookException
	 * @throws \PhpTags\PhpTagsException
	 */
	public function m___construct( $image = null, $text = null, $properties = null ) {
		if ( $image instanceof \PhpTagsObjects\WidgetImage ) {
			$this->image = $image;
			$title = $image->getImageTitle();
		} else {
			$title = null;
		}

		if ( $title && $title->getNamespace() === NS_FILE ) {
			$url = Renderer::getParser()->getTitle()->getLinkURL();
			$this->value[self::GENERAL_ATTRIBS]['href'] = "$url#/media/File:" . $title->getDBkey();
			if ( !$text ) {
				$text = $title->getText();
			}
		} else {
			$this->value[self::GENERAL_ATTRIBS]['href'] = '#';
		}

		$this->value[self::DATA] = $text;
		return parent::m___construct( $properties );
	}

	public function getString() {
		return $this->value[self::DATA];
	}

//	public function getAttribs() {
//		$attribs = parent::getAttribs();
//		self::addToAttrib( $attribs, 'class', 'image' );
//		return $attribs;
//	}

	public function getCssClassName() {
		return 'image phptagsMediaViewer';
	}

	protected function getContent() {
		$content = parent::getContent();
		if ( $this->image instanceof WidgetImage ) {
			$clone = clone $this->image;
			$clone->b_style( 'display: none;' );
			$content .= $clone->toString();
		}
		return $content;
	}

}
