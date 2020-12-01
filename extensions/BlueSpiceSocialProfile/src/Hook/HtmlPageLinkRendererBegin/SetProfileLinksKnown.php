<?php

namespace BlueSpice\Social\Profile\Hook\HtmlPageLinkRendererBegin;

use BlueSpice\Hook\HtmlPageLinkRendererBegin;
use Title;
use User;

class SetProfileLinksKnown extends HtmlPageLinkRendererBegin {

	const ATTR_PROCCESSED = 'knownProfileProccessed';

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( isset( $this->extraAttribs[static::ATTR_PROCCESSED] ) ) {
			return true;
		}

		$title = Title::newFromLinkTarget( $this->target );
		if ( !$title ) {
			return true;
		}
		if ( $title->getNamespace() !== NS_USER ) {
			return true;
		}
		if ( $title->isKnown() ) {
			return true;
		}
		if ( $title->exists() || $title->isSubpage() ) {
			return true;
		}
		$user = User::newFromName( $title->getText() );
		if ( !$user ) {
			return true;
		}
		if ( $user->isAnon() ) {
			return true;
		}

		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		// kinda hacky, but the known parameter can not be changed anymore :/
		$this->ret = $this->linkRenderer->makeKnownLink(
			$this->target,
			$this->text,
			array_merge( $this->extraAttribs, [ static::ATTR_PROCCESSED => true ] ),
			$this->query
		);
		return false;
	}

}
