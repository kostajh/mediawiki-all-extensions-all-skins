<?php

/**
 * Sources for the player - produces an iframe for an online player
 * Some of this code and matching CSS come from embedresponsively.com
 *
 * @author Ike Hecht
 */
class MultimediaPlayerSources {
	public static function getDefaultSources() {
		return array(
			'DailyMotion',
			'Instagram',
			'SoundCloud',
			'YouTube',
			'Vimeo',
			'Vine'
		);
	}

	/**
	 * Get the names of sources whose code is already known by this class
	 * This defaults to the array returned by getDefaultSources()
	 * but may have been modified in LocalSettings.
	 *
	 * @global array $wgMultimediaPlayerKnownSources
	 * @return array Names of known sources
	 */
	public static function getKnownSources() {
		global $wgMultimediaPlayerKnownSources;
		//Restrict valid values to sources known to the class
		return array_intersect( $wgMultimediaPlayerKnownSources, self::getDefaultSources() );
	}

	/**
	 * Returns an array of HTML code of known sources
	 * The list of sources may have been modified in LocalSettings.
	 *
	 * @return array HTMLs for all the sources
	 */
	public static function getKnownScripts() {
		$allCode = array();
		foreach ( self::getKnownSources() as $source ) {
			$functionName = 'get' . $source;
			$allCode[$source] = self::$functionName();
		}
		return $allCode;
	}

	/**
	 * Produces a generic iframe suitable for many video sources
	 *
	 * @param string $src
	 * @return string HTML of iframe
	 */
	public static function getGenericVideo( $src ) {
		return "<iframe src='$src' frameborder='0' webkitAllowFullScreen"
			. "mozallowfullscreen allowFullScreen></iframe>";
	}

	/**
	 *
	 * @return string HTML of iframe
	 */
	public static function getDailyMotion() {
		return self::getGenericVideo( 'http://www.dailymotion.com/embed/video/$1?autoplay=1' );
	}

	/**
	 *
	 * @return string HTML of iframe
	 */
	public static function getInstagram() {
		return Html::element( 'iframe',
				array( 'src' => '//instagram.com/p/$1/embed/', 'frameborder' => '0',
				'scrolling' => 'no', 'allowtransparency' => 'true' )
		);
	}

	/**
	 *
	 * @return string HTML of iframe
	 */
	public static function getSoundCloud() {
		return Html::element( 'iframe',
				array(
				'src' => 'https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/$1&auto_play=true',
				'width' => '100%', 'scrolling' => 'no', 'frameborder' => 'no'
				)
		);
	}

	/**
	 *
	 * @return string HTML of iframe
	 */
	public static function getYouTube() {
		return self::getGenericVideo( 'http://www.youtube.com/embed/$1?autoplay=1' );
	}

	/**
	 *
	 * @return string HTML of iframe
	 */
	public static function getVimeo() {
		return self::getGenericVideo( 'http://player.vimeo.com/video/$1?autoplay=1' );
	}

	/**
	 *
	 * @return string HTML of iframe
	 */
	public static function getVine() {
		return "<iframe width='100%' src='https://vine.co/v/$1/embed/simple' frameborder='0' "
			. "scrolling='no' allowtransparency='true'></iframe><script async "
			. "src='//platform.vine.co/static/scripts/embed.js' charset='utf-8'><\/script>";
	}
}
