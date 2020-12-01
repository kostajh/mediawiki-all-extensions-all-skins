<?php

namespace BlueSpice\Social\WikiPage\Special;

use BlueSpice\Context;
use BlueSpice\Entity;
use BlueSpice\Renderer\Params;
use BlueSpice\Social\WikiPage\EntityListContext\SpecialStash;
use MediaWiki\MediaWikiServices;
use MWException;
use Title;

class Stash extends \BlueSpice\SpecialPage {

	public function __construct() {
		parent::__construct( 'WikiPageStash', 'read' );
	}

	/**
	 *
	 * @param string $par
	 * @return void
	 */
	public function execute( $par ) {
		$this->checkPermissions();

		$this->getOutput()->setPageTitle(
			wfMessage( 'bs-socialwikipage-special-stash-heading' )->plain()
		);

		$title = $entiy = null;
		if ( !empty( $par ) ) {
			$title = Title::newFromText( $par );
			if ( !$title ) {
				throw new MWException( "Invalid Title '$par'" );
			}
			if ( !$title->exists() ) {
				throw new MWException( "Title '$par' must exist" );
			}
			$this->getOutput()->addBacklinkSubtitle( $title );
			$factory = MediaWikiServices::getInstance()->getService(
				'BSSocialWikiPageEntityFactory'
			);
			$entiy = $factory->newFromTitle( $title );
			if ( !$entiy->exists() ) {
				$out = $this->renderNewWikiPageEntity( $entiy, $title );
				$this->getOutput()->addHTML( $out );
				return;
			}
		}

		$context = new SpecialStash(
			new Context(
				$this->getContext(),
				$this->getConfig()
			),
			$this->getConfig(),
			$this->getContext()->getUser(),
			$entiy,
			$title
		);
		$renderer = MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			'entitylist',
			new Params( [ 'context' => $context ] )
		);

		$this->getOutput()->addHTML( $renderer->render() );
	}

	/**
	 *
	 * @param Entity $entiy
	 * @param Title $title
	 * @return string
	 */
	protected function renderNewWikiPageEntity( $entiy, $title ) {
		$context = new SpecialStash(
			new Context(
				$this->getContext(),
				$this->getConfig()
			),
			$this->getConfig(),
			$this->getContext()->getUser(),
			$entiy,
			$title
		);
		$renderer = MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			'social-wikipage-entitylist-newwikipageentity',
			new Params( [ 'context' => $context ] )
		);
		return $renderer->render();
	}

}
