<?php

class MP3OutputRenderer extends MediaTransformOutput {

	/**
	 * @var string
	 */
	private $pSourceFileURL;

	/**
	 * @var string
	 */
	private $pFileName;

	/**
	 * @param string $SourceFileURL
	 * @param string $FileName
	 */
	public function __construct( $SourceFileURL, $FileName ) {
		$this->pSourceFileURL = $SourceFileURL;
		$this->pFileName = $FileName;
	}

	/**
	 * @param array $options
	 *
	 * @return string
	 */
	public function toHtml( $options = [] ) {
		$Output = '<audio controls="controls">'
				. '<source src="$1" type="audio/mp3" />'
				. $this->getFlashPlayerHTMLTemplate( '<p><a href="$1">$2</a></p>',
													 $this->pSourceFileURL )
				. '</audio>';

		$Args = [
			'$1' => $this->pSourceFileURL,
			'$2' => $this->pFileName,
		];

		return $this->expandHtml( $Output, $Args );
	}

	/**
	 * @param string $NonFlashFallback
	 * @param string $SourceFileURL
	 *
	 * @return string
	 */
	private function getFlashPlayerHTMLTemplate( $NonFlashFallback, $SourceFileURL ) {
		global $wgFlashPlayerPath, $wgFlashPlayerURLParam, $wgFlashPlayerParams;
		global $wgFlashPlayerFlashVars, $wgFlashPlayerWidth, $wgFlashPlayerHeight;

		if ( isset( $wgFlashPlayerPath ) ) {
		// A common default parameter name for the audio file to be loaded is 'url',
		// so we default to this.  Individual implementations can over-ride via
		// LocalSettings.php, if necessary.
			if ( !isset( $wgFlashPlayerURLParam ) ) {
				$wgFlashPlayerURLParam = "url";
			}

		// Initialise the arrays that may be used to configure the player.
			if ( !is_array( $wgFlashPlayerParams ) ) {
				$wgFlashPlayerParams = [];
			}

			if ( !is_array( $wgFlashPlayerFlashVars ) ) {
				$wgFlashPlayerFlashVars = [];
			}

		// Add the required 'movie' param to the set of player parameters.
			$wgFlashPlayerParams['movie'] = $wgFlashPlayerPath;

		// Add the source file URL to the list of FlashVars arguments, and build them
		// into a single FlashVars parameter to be passed into the movie.
			$wgFlashPlayerFlashVars[$wgFlashPlayerURLParam] = $SourceFileURL;
			$wgFlashPlayerParams['FlashVars'] = wfArrayToCGI( $wgFlashPlayerFlashVars );

		// Create the parameter string from the parameters array.
			$Params = "";
			foreach ( $wgFlashPlayerParams as $Param => $Value ) {
				$Params .= '<param name="' . htmlspecialchars( $Param )
							  . '" value="' . htmlspecialchars( $Value ) . '">';
			}

		// Set FlashPlayer size, if specified.
			$Sizes = "";
			if ( isset( $wgFlashPlayerWidth ) ) {
				$Sizes .= ' width="' . htmlspecialchars( $wgFlashPlayerWidth ) . '"';
			}

			if ( isset( $wgFlashPlayerHeight ) ) {
				$Sizes .= ' height="' . htmlspecialchars( $wgFlashPlayerHeight ) . '"';
			}

		// Build the final HTML.
			$HTML = '<object data="' . htmlspecialchars( $wgFlashPlayerPath )
				  . '" type="application/x-shockwave-flash"' . $Sizes . '>'
				  . $Params
				  . $NonFlashFallback
				  . '</object>';

			return $HTML;
		} else {
			return $NonFlashFallback;
		}
	}

	/**
	 * @param string $HTML
	 * @param string[] $Args
	 *
	 * @return string
	 */
	private function expandHtml( $HTML, $Args ) {
		foreach ( $Args as $Key => $Value ) {
			$Args[$Key] = htmlspecialchars( $Value );
		}

		return str_replace( array_keys( $Args ), array_values( $Args ), $HTML );
	}

}
