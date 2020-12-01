<?php

namespace BlueSpice\UserInfo\Hook\BSFoundationRendererMakeTagAttribs;

use BlueSpice\Hook\BSFoundationRendererMakeTagAttribs;
use BlueSpice\Renderer\UserImage;

class AddMetaData extends BSFoundationRendererMakeTagAttribs {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( !$this->renderer instanceof UserImage ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$user = $this->renderer->getUser();
		$this->attribs['data-bs-userinfo'] = $user->getName();
		return true;
	}
}
