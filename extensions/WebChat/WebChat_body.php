<?php
/**
 * WebChat extension special page class.
 */

class WebChat extends SpecialPage {
	public function __construct() {
		parent::__construct( 'WebChat', 'webchat' );
	}

	public function execute( $par ) {
		global $wgWebChatServer, $wgWebChatChannel, $wgWebChatClient, $wgWebChatClients;

		$this->setHeaders();
		$this->getOutput()->addWikiMsg( 'webchat-header' );

		if ( !array_key_exists( $wgWebChatClient, $wgWebChatClients ) ) {
			throw new MwException( 'Unknown web chat client specified.' );
		}

		$query = [];

		foreach ( $wgWebChatClients[$wgWebChatClient]['parameters'] as $parameter => $value ) {
			switch ( $value ) {
				case '$$$nick$$$':
					if ( $this->getUser()->isLoggedIn() ) {
						$value = str_replace( ' ', '_', $this->getUser()->getName() );
					}
					break;
				case '$$$channel$$$':
					$value = $wgWebChatChannel;
					break;
				case '$$$server$$$':
					$value = $wgWebChatServer;
					break;
			}
			$query[] = $parameter . '=' . urlencode( $value );
		}
		$query = implode( '&', $query );

		$this->getOutput()->addHTML( Xml::openElement( 'iframe', [
			'width'     => '100%',
			'height'    => '500',
			'scrolling' => 'no',
			'border'    => '0',
			'onLoad'    => 'webChatExpand( this )',
			'src'       => $wgWebChatClients[$wgWebChatClient]['url'] . '?' . $query
		] ) . Xml::closeElement( 'iframe' ) );

		// Hack to make the chat area a reasonable size.
		$this->getOutput()->addHTML( Xml::tags( 'script',
			[ 'type' => 'text/javascript' ],
'/* <![CDATA[ */
function webChatExpand( elem ) {
	height = elem.height;
	elem.height = screen.height - 500;
}
/* ]]> */'
			) );
	}

	protected function getGroupName() {
		return 'wiki';
	}
}
