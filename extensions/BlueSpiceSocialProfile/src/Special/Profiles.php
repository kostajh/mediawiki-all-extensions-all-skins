<?php

namespace BlueSpice\Social\Profile\Special;

use BlueSpice\Context;
use BlueSpice\Renderer\Params;
use BlueSpice\Social\Profile\EntityListContext\SpecialProfiles;
use MediaWiki\MediaWikiServices;

class Profiles extends \BlueSpice\SpecialPage {

	/**
	 *
	 */
	public function __construct() {
		parent::__construct( 'SocialProfiles', 'socialprofile-viewspecialpage' );
	}

	/**
	 *
	 * @param string $par
	 */
	public function execute( $par ) {
		$this->checkPermissions();

		$this->getOutput()->setPageTitle(
			wfMessage( 'bs-socialprofile-special-profiles-heading' )->plain()
		);

		$context = new SpecialProfiles(
			new Context(
				$this->getContext(),
				$this->getConfig()
			),
			$this->getConfig(),
			$this->getContext()->getUser()
		);
		$renderer = MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			'entitylist',
			new Params( [ 'context' => $context ] )
		);

		$this->getOutput()->addHTML( $renderer->render() );
	}
}
