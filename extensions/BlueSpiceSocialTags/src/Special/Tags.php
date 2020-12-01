<?php
/**
 * Renders the BlueSpiceSocialGroups special page.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Patric Wirth
 * @package    BlueSpiceSocial
 * @subpackage BSSocialBlueSpiceSocialGroups
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */
namespace BlueSpice\Social\Tags\Special;

use BlueSpice\Context;
use BlueSpice\Renderer\Params;
use BlueSpice\Social\Tags\EntityListContext\SpecialTags;
use BlueSpice\SpecialPage;
use MediaWiki\MediaWikiServices;

class Tags extends SpecialPage {

	public function __construct() {
		parent::__construct( 'SocialTags', 'read', true );
	}

	/**
	 *
	 * @param string $param
	 */
	public function execute( $param ) {
		parent::execute( $param );

		$this->getOutput()->setPageTitle(
			\wfMessage( 'bs-socialtags-special-heading' )->plain()
		);

		$context = new SpecialTags(
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
