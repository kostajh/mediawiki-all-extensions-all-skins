<?php

/**
 * The MultimediaPlayer
 *
 * @author Ike Hecht
 */
class MultimediaPlayer {
	/**
	 * @var MultimediaPlayer
	 */
	protected static $instance;

	/**
	 * Sources to be loaded into the player
	 *
	 * @var array
	 */
	private $multimediaPlayerSources;

	/**
	 * MultimediaPlayerItems to be listed
	 *
	 * @var array
	 */
	private $multimediaPlayerItems = array();

	/**
	 * Container where player code will be loaded
	 *
	 * @var MultimediaPlayerContainer
	 */
	private $multimediaPlayerContainer;

	/**
	 * Returns a Singleton of this MultimediaPlayer
	 * This of course assumes a maximum of one player per page, which may change at some point.
	 *
	 * @return MultimediaPlayer
	 */
	public static function Singleton() {
		static $instance = null;
		if ( $instance === null ) {
			$instance = new MultimediaPlayer();
		}
		return $instance;
	}

	public function getMultimediaPlayerSources() {
		return $this->multimediaPlayerSources;
	}


	/**
	 * @param array $multimediaPlayerSources
	 * @return array
	 */
	public function setMultimediaPlayerSources( $multimediaPlayerSources ) {
		$multimediaPlayerSources = array_merge( $this->getKnownMultimediaPlayerSources(),
			$multimediaPlayerSources );

		//Substitute the $1 in each code
		foreach ( $multimediaPlayerSources as &$code ) {
			$codeReplaced = str_replace( "'", '"', $code );
			$code = $this->convertId( $codeReplaced );
		}

		return wfSetVar( $this->multimediaPlayerSources, $multimediaPlayerSources );
	}

	public function getMultimediaPlayerItems() {
		return $this->multimediaPlayerItems;
	}

	public function getMultimediaPlayerContainer() {
		return $this->multimediaPlayerContainer;
	}

	public function setMultimediaPlayerContainer( MultimediaPlayerContainer $multimediaPlayerContainer ) {
		$this->multimediaPlayerContainer = $multimediaPlayerContainer;
	}

	public function getKnownMultimediaPlayerSources() {
		return MultimediaPlayerSources::getKnownScripts();
	}

	/**
	 * Substitute the $1 for some code that will find the appropriate ID for this item
	 *
	 * @param string $code Code to convert
	 * @return string Converted code
	 */
	private function convertId( $code ) {
		$prefixLength = strlen( MultimediaPlayerItem::getItemIdPrefix() );
		/**
		 * Pull the ID by taking the parent span's ID and chopping off the prefix
		 * @todo There's gotta be a better way - maybe pass as parameter in getOnClick
		 */
		$idInsert = <<<END
' + $(this).closest("span").attr("id").substr( $prefixLength ) + '
END;
		return str_replace( '$1', $idInsert, $code );
	}

	/**
	 * JS function that should be called when a MultimediaPlayerItem is clicked
	 * There is one of these inserted for each one of $multimediaPlayerSources
	 *
	 * @param string $code HTML to load into the Multimedia container
	 * @return string JavaScript function
	 */
	private function getLoadContainerFunction( $name, $code ) {
		$frameID = MultimediaPlayerContainer::getFrameID();
		$containerID = MultimediaPlayerContainer::getContainerID();
		$descriptionID = MultimediaPlayerContainer::getDescriptionID();

		// strip newlines - otherwise jQuery is sad
		$code = str_replace( array( "\r", "\n" ), '', $code );

		/** @todo remove only the 'mm-...' class */
		return <<<END
function loadContainer{$name}(e) {
	e.preventDefault();
	$('#$descriptionID').remove();
	$('#$frameID').removeClass();
	$('#$frameID').addClass( 'mm-$name' );
	$('#$containerID').html( '$code' );
	$('#$containerID').after( '<div id="$descriptionID">' + $(this).text() + '</div>' );
}
END;
	}

	/**
	 * Attach the loadContainer function to all of this source's items
	 *
	 * @param string $name Name of the source to be loaded
	 * @return string
	 */
	private function getOnClick( $name ) {
		$itemClassSuffix = MultimediaPlayerItem::getItemClassSuffix();
		return "$('.{$name}{$itemClassSuffix} a').click( loadContainer{$name} )";
	}

	/**
	 * Get all the scripts needed for this source
	 *
	 * @param string $name Name of the source to be loaded
	 * @param string $code JavaScript code for this source
	 * @return string
	 */
	public function getScripts( $name, $code ) {
		return $this->getLoadContainerFunction( $name, $code ) . $this->getOnClick( $name );
	}

	/**
	 * @todo Only return scripts whose Item has been added to the Player
	 * @return array All scripts to be loaded, not including "<script>" tags
	 */
	public function getAllScripts() {
		$scripts = array();
		foreach ( $this->getMultimediaPlayerSources() as $name => $code ) {
			$scripts[] = $this->getScripts( $name, $code );
		}
		return $scripts;
	}

	/**
	 * Adds this Item and also returns its HTML
	 *
	 * @param MultimediaPlayerItem $multimediaPlayerItem
	 * @return string
	 */
	public function getMultimediaPlayerItemOutput( MultimediaPlayerItem $multimediaPlayerItem ) {
		$this->addMultimediaPlayerItem( $multimediaPlayerItem );
		return $multimediaPlayerItem->getOutput();
	}

	/**
	 * Add a MultimediaPlayerItem to the member array
	 *
	 * @param MultimediaPlayerItem $multimediaPlayerItem
	 */
	public function addMultimediaPlayerItem( MultimediaPlayerItem $multimediaPlayerItem ) {
		array_push( $this->multimediaPlayerItems, $multimediaPlayerItem );
	}

	/**
	 * Adds this Container and also returns its HTML
	 *
	 * @return string Container HTML
	 */
	public function getContainerOutput() {
		if ( !isset( $this->multimediaPlayerContainer ) ) {
			return '';
		}
		return $this->multimediaPlayerContainer->getOutput();
	}
}
