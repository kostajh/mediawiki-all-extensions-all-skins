<?php

namespace BlueSpice\EchoConnector\Formatter;

use MediaWiki\MediaWikiServices;

class EchoHtmlDigestEmailFormatter extends \EchoHtmlDigestEmailFormatter {
	protected $config;

	protected $sitename;
	protected $templateParser;
	protected $templateNames;

	/**
	 *
	 * @param \User $user
	 * @param \Language $language
	 * @param string $digestMode
	 */
	public function __construct( \User $user, \Language $language, $digestMode ) {
		parent::__construct( $user, $language, $digestMode );
		global $wgSitename;

		$this->sitename = $wgSitename;
		$this->config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		$path = $this->config->get( 'EchoHtmlMailTemplatePath' );
		$this->templateParser = new \TemplateParser( $path );

		$this->templateNames = $this->config->get( 'EchoHtmlMailTemplateNames' );
	}

	/**
	 *
	 * @param array $models
	 * @return array
	 */
	protected function formatModels( array $models ) {
		$greeting = $this->msg( 'echo-email-batch-body-intro-' . $this->digestMode )
				->params( $this->user->getName() )
				->parse();
		$greeting = nl2br( $greeting );

		$senderMessage = $this->msg(
			'bs-notifications-htmlmail-sender-info', $this->sitename
		)->plain();

		$eventsByCategory = $this->groupByCategory( $models );
		ksort( $eventsByCategory );
		$digestList = $this->renderDigestList( $eventsByCategory );

		$htmlFormatter = new EchoHTMLEmailFormatter( $this->user, $this->language );

		$action = $htmlFormatter->renderLink(
			[
				'label' => $this->msg( 'echo-email-batch-link-text-view-all-notifications' )->text(),
				'url' => \SpecialPage::getTitleFor( 'Notifications' )->getFullURL( '', false, PROTO_CANONICAL ),
			],
			$htmlFormatter::PRIMARY_LINK
		);

		$body = $this->renderBody(
				$this->language, $greeting, $senderMessage, $digestList, $action, $htmlFormatter->getFooter()
		);

		$subject = $this->msg( 'echo-email-batch-subject-' . $this->digestMode )
				->numParams( count( $models ), count( $models ) )
				->text();

		return [
			'subject' => $subject,
			'body' => $body,
		];
	}

	/**
	 *
	 * @param \Language $language
	 * @param string $greeting
	 * @param string $senderMessage
	 * @param string $digestList
	 * @param string $action
	 * @param string $footer
	 * @return string
	 */
	protected function renderBody( \Language $language, $greeting, $senderMessage, $digestList,
		$action, $footer ) {
		$html = $this->templateParser->processTemplate(
			$this->templateNames['digest'], [
				'greeting' => $greeting,
				'sender-info' => $senderMessage,
				'digest-list' => $digestList,
				'action' => $action,
				'footer' => $footer
			]
		);

		return $html;
	}

	/**
	 * @param EchoEventPresentationModel[] $models
	 * @return array [ 'category name' => EchoEventPresentationModel[] ]
	 */
	protected function groupByCategory( $models ) {
		$eventsByCategory = [];
		foreach ( $models as $model ) {
			$eventsByCategory[$model->getCategory()][] = $model;
		}
		return $eventsByCategory;
	}

	/**
	 *
	 * @param array $eventsByCategory
	 * @return string
	 */
	protected function renderDigestList( $eventsByCategory ) {
		$result = [];
		// build the html section for each category
		foreach ( $eventsByCategory as $category => $models ) {
			$events = [];
			foreach ( $models as $model ) {
				$events[] = $this->getEventParams( $model );
			}

			$output = $this->templateParser->processTemplate(
				$this->templateNames['digest_list'],
				[
					'category' => $this->getCategoryTitle( $category, count( $models ) ),
					'events' => $events
				]
			);
			$result[] = $output;
		}

		return trim( implode( "\n", $result ) );
	}

	/**
	 * @param string $type Notification type
	 * @param int $count Number of notifications in this type's section
	 * @return string Formatted category section title
	 */
	protected function getCategoryTitle( $type, $count ) {
		return $this->msg( "echo-category-title-$type" )
			->numParams( $count )
			->parse();
	}

	/**
	 *
	 * @param \EchoEventPresentationModel $model
	 * @return array
	 */
	protected function getEventParams( $model ) {
		$iconUrl = wfExpandUrl(
			\EchoIcon::getUrl( $model->getIconType(), $this->language->getCode() ),
			PROTO_CANONICAL
		);

		$iconUrl = \Sanitizer::encodeAttribute( $iconUrl );

		return [
			'icon-url' => $iconUrl,
			'text' => $model->getHeaderMessage()->parse(),
			'url' => $model->getPrimaryLink(),
		];
	}
}
