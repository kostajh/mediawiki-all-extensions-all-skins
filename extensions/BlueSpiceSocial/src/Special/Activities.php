<?php

namespace BlueSpice\Social\Special;

use BlueSpice\Context;
use BlueSpice\Renderer\Params;
use BlueSpice\Social\EntityListContext\SpecialActivities;
use MediaWiki\MediaWikiServices;

/**
 * Activities SpecialPage
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
class Activities extends \BlueSpice\SpecialPage {

	public function __construct() {
		parent::__construct( 'Activities', 'read', true );
	}

	/**
	 *
	 * @param string $param
	 */
	public function execute( $param ) {
		$this->checkPermissions();

		$this->getOutput()->setPageTitle(
			wfMessage( 'bs-social-special-activities-heading' )->plain()
		);

		$context = new SpecialActivities(
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
