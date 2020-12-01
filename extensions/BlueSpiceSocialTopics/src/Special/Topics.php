<?php

namespace BlueSpice\Social\Topics\Special;

use BlueSpice\Context;
use BlueSpice\Renderer\Params;
use BlueSpice\Social\Renderer\Entity as Renderer;
use BlueSpice\Social\Topics\Entity\Topic as TopicEntity;
use BlueSpice\Social\Topics\EntityListContext\SpecialTopics;
use MediaWiki\MediaWikiServices;

class Topics extends \BlueSpice\SpecialPage {

	public function __construct() {
		parent::__construct( 'Topics', 'read' );
	}

	/**
	 *
	 * @param string $par
	 * @return void
	 */
	public function execute( $par ) {
		$this->checkPermissions();

		$this->getOutput()->setPageTitle(
			wfMessage( 'bs-socialtopics-special-topics-heading' )->plain()
		);

		$context = new SpecialTopics(
			new Context(
				$this->getContext(),
				$this->getConfig()
			),
			$this->getConfig(),
			$this->getContext()->getUser()
		);

		$entity = $this->extractEntity( $par );
		if ( $entity ) {
			$this->getOutput()->addBacklinkSubtitle(
				$entity->getRelatedTitle()
			);
			$msg = $this->msg(
				'bs-socialtopics-special-topics-heading-entry'
			);
			$msg->params( strip_tags( $entity->getHeader()->parse() ) );
			$this->getOutput()->setPageTitle( $msg->text() );
			$this->getOutput()->addHTML(
				$entity->getRenderer( $context )->render(
					Renderer::RENDER_TYPE_PAGE
				)
			);
			return;
		}

		$renderer = MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			'entitylist',
			new Params( [ 'context' => $context ] )
		);

		$this->getOutput()->addHTML( $renderer->render() );
	}

	/**
	 *
	 * @param string $param
	 * @return bool|TopicEntity
	 */
	protected function extractEntity( $param = '' ) {
		if ( empty( $param ) ) {
			return false;
		}
		$title = \Title::makeTitle( NS_SOCIALENTITY, $param );
		if ( !$title || !$title->exists() ) {
			return false;
		}
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialWikiPageEntityFactory'
		);
		$entity = $factory->newFromSourceTitle( $title );
		if ( !$entity instanceof TopicEntity || !$entity->exists() ) {
			return false;
		}
		return $entity;
	}
}
