<?php

/**
 * The container where items will be loaded and played
 */
class MultimediaPlayerContainer {
	/**
	 * @return string ID of the HTML element to load the player code into
	 */
	public static function getContainerID() {
		return 'multimediaplayer-container';
	}

	/**
	 * @return string ID of the HTML element that surrounds the player
	 */
	public static function getFrameID() {
		return 'multimediaplayer-frame';
	}

	/**
	 * @return string ID of the HTML element that contains the clicked item's linktext
	 */
	public static function getDescriptionID() {
		return 'multimediaplayer-description';
	}

	/**
	 * @return string HTML of the container to load with the player
	 */
	public function getOutput() {
		$container = Html::rawElement( 'div', array( 'id' => self::getContainerID() ) );
		$frame = Html::rawElement( 'div', array( 'id' => self::getFrameID() ), $container );
		return $frame;
	}

	public function __toString() {
		return $this->getOutput();
	}
}
