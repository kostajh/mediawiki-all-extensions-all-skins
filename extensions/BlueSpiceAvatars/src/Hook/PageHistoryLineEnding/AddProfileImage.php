<?php

namespace BlueSpice\Avatars\Hook\PageHistoryLineEnding;

use BlueSpice\Hook\PageHistoryLineEnding;
use BlueSpice\Renderer\Params;
use BlueSpice\Renderer\UserImage as DFDImage;

class AddProfileImage extends PageHistoryLineEnding {
	protected function doProcess() {
		$this->history->getOutput()->addModuleStyles(
			'ext.bluespice.avatars.history.styles'
		);

		$user = \User::newFromName( $this->row->rev_user_text );
		if ( $user instanceof \User === false ) {
			return true;
		}
		$factory = $this->getServices()->getService( 'BSRendererFactory' );
		$params = [
			DFDImage::PARAM_WIDTH => 32,
			DFDImage::PARAM_HEIGHT => 32,
			DFDImage::PARAM_USER => $user,
		];
		$renderer = $factory->get( 'userimage', new Params( $params ) );

		$this->s = preg_replace(
			"#(<span class='history-user'>)#",
			'$1' . $renderer->render(),
			$this->s
		);

		return true;
	}
}
