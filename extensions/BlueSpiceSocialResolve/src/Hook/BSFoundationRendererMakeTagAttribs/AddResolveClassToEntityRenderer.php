<?php

namespace BlueSpice\Social\Resolve\Hook\BSFoundationRendererMakeTagAttribs;

use BlueSpice\Hook\BSFoundationRendererMakeTagAttribs;
use BlueSpice\Social\Renderer\Entity;

class AddResolveClassToEntityRenderer extends BSFoundationRendererMakeTagAttribs {

	protected function skipProcessing() {
		if ( !$this->renderer instanceof Entity ) {
			return true;
		}
		$factory = $this->getServices()->getService( 'BSSocialResolveFactory' );
		$resolveItem = $factory->newFromEntity( $this->renderer->getEntity() );
		if ( !$resolveItem ) {
			return true;
		}
		if ( !$resolveItem->isResolved() ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		if ( empty( $this->attribs['class'] ) ) {
			$this->attribs['class'] = '';
		}
		$this->attribs['class'] .= ' resolved';
	}

}
