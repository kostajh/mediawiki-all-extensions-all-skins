<?php

namespace BlueSpice\Social\Blog\Special;

use BlueSpice\Context;
use BlueSpice\Renderer\Params;
use BlueSpice\Social\Blog\Entity\Blog as BlogEntity;
use BlueSpice\Social\Blog\EntityListContext\SpecialBlog;
use BlueSpice\Social\Renderer\Entity as Renderer;
use MediaWiki\MediaWikiServices;

class Blog extends \BlueSpice\SpecialPage {

	public function __construct() {
		parent::__construct( 'Blog', 'read' );
	}

	/**
	 *
	 * @param string $par
	 * @return null
	 */
	public function execute( $par ) {
		$this->checkPermissions();

		$this->getOutput()->setPageTitle(
			$this->msg( 'bs-socialblog-special-blog-heading' )->plain()
		);

		$context = new SpecialBlog(
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
				$this->getPageTitle()
			);
			$msg = $this->msg( 'bs-socialblog-special-blog-heading-entry' );
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
	 * @return BlogEntity|bool
	 */
	protected function extractEntity( $param = '' ) {
		if ( empty( $param ) ) {
			return false;
		}
		$entity = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )->newFromID(
			$param,
			BlogEntity::TYPE
		);
		if ( !$entity instanceof BlogEntity || !$entity->exists() ) {
			return false;
		}
		return $entity;
	}
}
