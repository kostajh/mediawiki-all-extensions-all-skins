<?php

namespace BlueSpice\Social\Resolve\Hook\BSSocialEntityListRenderEntity;

use BlueSpice\Social\Hook\BSSocialEntityListRenderEntity;
use BlueSpice\Social\Renderer\Entity;

class RenderShortWhenResolved extends BSSocialEntityListRenderEntity {

	protected function skipProcessing() {
		if ( !$this->entity->exists() ) {
			return true;
		}
		if ( !$this->getConfig()->get( 'IsResolvable' ) ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$factory = $this->getServices()->getService( 'BSSocialResolveFactory' );
		$resolveItem = $factory->newFromEntity( $this->entity );
		if ( !$resolveItem->isResolved() ) {
			return true;
		}
		$this->renderType = Entity::RENDER_TYPE_SHORT;
	}

}
