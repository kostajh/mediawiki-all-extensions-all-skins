<?php
/**
 * VirtualKeyboard class
 *
 * @file
 * @ingroup Extensions
 * @author Ike Hecht, 2015
 * @license GNU General Public Licence 2.0 or later
 */
class VirtualKeyboard {
	/**
	 * Easy mode shows a small clickable keyboard icon when an input receives focus
	 */
	const EASY = 1;

	/**
	 * Popup mode pops up a keyboard in a new window
	 */
	const POPUP = 2;

	/**
	 * Iframe mode loads a keyboard at the bottom of the content area
	 */
	const IFRAME = 3;
	/**
	 * Which mode to use - should be one of the mode constants
	 *
	 * @var int
	 */
	private $mode;

	/**
	 * Relative path to the VirtualKeyboard module base directory
	 *
	 * @var string
	 */
	private $basePath;

	/**
	 * The VirtualKeyboard skin to display
	 *
	 * @var string
	 */
	private $skin;

	/**
	 *
	 * @param int $mode
	 * @param string $basePath
	 * @param string $skin
	 */
	function __construct( $mode, $basePath, $skin = null ) {
		$this->mode = $mode;
		$this->basePath = $basePath;
		if ( $skin ) {
			$this->skin = $skin;
		}
	}

	/**
	 * Get the Relative URL of the required script
	 *
	 * @return string URL
	 */
	public function getScriptFile() {
		switch ( $this->mode ) {
			case self::EASY:
				$file = "vk_easy.js";
				break;
			case self::IFRAME:
				$file = "vk_iframe.js";
				break;
			default:
				$file = "vk_popup.js";
		}
		$query = array( 'vk_skin' => $this->skin );
		/** @todo Try to pass User Language to the script. Won't be simple. */
		return $this->basePath . $file . '?' . http_build_query( $query );
	}

	/**
	 * Get a script that attaches a Virtual Keyboard to the input elements
	 *
	 * @return string HTML Script, including <script> tags
	 */
	public function getScript() {
		$selector = "input:not([type=image],[type=button],[type=submit]), textarea, div[contenteditable]";

		switch ( $this->mode ) {
			case self::EASY:
				$script = <<<END
$("$selector").addClass('keyboardInput');
END;
				break;
			case self::IFRAME:
				$script = <<<END
$("$selector").focus(function() { IFrameVirtualKeyboard.attachInput(this); } );
END;
				break;
			default:
				$script = <<<END
$("$selector").focus(function() { PopupVirtualKeyboard.attachInput(this); } );
END;
		}
		return Html::rawElement( 'script', array(), $script );
	}

	/**
	 * Given a VirtualKeyboard mode, determine which VirtualKeyboard class to use
	 *
	 * @param int $mode
	 * @return boolean|string
	 * @todo make non-static and convert this class to use singleton so that hooks work
	 */
	public static function getVirtualKeyboardClassName( $mode ) {
		switch ( $mode ) {
			case VirtualKeyboard::POPUP:
				return 'PopupVirtualKeyboard';
			case VirtualKeyboard::IFRAME:
				return 'IFrameVirtualKeyboard';
			default:
				// Easy mode has no class
				return false;
		}
	}
}
