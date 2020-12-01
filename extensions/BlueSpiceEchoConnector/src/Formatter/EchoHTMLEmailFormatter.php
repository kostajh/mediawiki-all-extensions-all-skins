<?php

namespace BlueSpice\EchoConnector\Formatter;

use BlueSpice\EchoConnector\EchoEventPresentationModel as BSEchoPresentationModel;
use MediaWiki\MediaWikiServices;

class EchoHTMLEmailFormatter extends \EchoHtmlEmailFormatter {
	const PRIMARY_LINK = 'primary_link';
	const SECONDARY_LINK = 'secondary_link';

	/**
	 *
	 * @var \Config
	 */
	protected $config;

	/**
	 *
	 * @var string
	 */
	protected $sitename;

	/**
	 *
	 * @var \TemplateParser
	 */
	protected $templateParser;

	/**
	 *
	 * @var array
	 */
	protected $templateNames;

	/**
	 *
	 * @param \User $user
	 * @param \Language $language
	 */
	public function __construct( \User $user, \Language $language ) {
		parent::__construct( $user, $language );
		global $wgSitename;

		$this->sitename = $wgSitename;
		$this->config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );

		$path = $this->config->get( 'EchoHtmlMailTemplatePath' );
		$this->templateParser = new \TemplateParser( $path );

		$this->templateNames = $this->config->get( 'EchoHtmlMailTemplateNames' );
	}

	/**
	 *
	 * @param \EchoEventPresentationModel $model
	 * @return array
	 */
	protected function formatModel( \EchoEventPresentationModel $model ) {
		if ( $model instanceof BSEchoPresentationModel ) {
			$model->setDistributionType( 'email' );
		}

		$subject = $model->getSubjectMessage()->parse();

		$realname = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getUserHelper( $model->getUser() )->getDisplayName();

		$greeting = $this->msg(
			'bs-notifications-htmlmail-greeting', $realname
		)->parse();
		$senderMessage = $this->msg(
			'bs-notifications-htmlmail-sender-info', $this->sitename
		)->plain();
		$messageHeader = $model->getHeaderMessage()->parse();

		$bodyMsg = $model->getBodyMessage();
		$summary = $bodyMsg ? $bodyMsg->parse() : '';

		$actions = [
			'primary' => [],
			'secondary_label' => $this->msg(
				'bs-notifications-mail-additional-links-label'
			)->plain(),
			'secondary' => []
		];

		$primaryLink = $model->getPrimaryLinkWithMarkAsRead();
		if ( $primaryLink ) {
			$actions['primary'][] = $this->renderLink( $primaryLink, self::PRIMARY_LINK );
		}

		foreach ( array_filter( $model->getSecondaryLinks() ) as $secondaryLink ) {
			$actions['secondary'][] = $this->renderLink( $secondaryLink, self::SECONDARY_LINK );
		}

		$iconUrl = wfExpandUrl(
			\EchoIcon::getUrl( $model->getIconType(), $this->language->getCode() ),
			PROTO_CANONICAL
		);

		$actions['primary'] = implode( '</br>', $actions['primary'] );
		$actions['secondary'] = implode( '</br>', $actions['secondary'] );

		$body = $this->renderBody(
			$this->language,
			$iconUrl,
			$summary,
			$actions,
			$greeting,
			$senderMessage,
			$messageHeader,
			$this->getFooter()
		);

		return [
			'body' => $body,
			'subject' => $subject,
		];
	}

	/**
	 *
	 * @param \Language $lang
	 * @param string $emailIcon
	 * @param string $summary
	 * @param array $actions
	 * @param string $greeting
	 * @param string $senderMessage
	 * @param string $messageHeader
	 * @param string $footer
	 * @return string
	 */
	protected function renderBody( \Language $lang, $emailIcon, $summary, $actions, $greeting,
		$senderMessage, $messageHeader, $footer ) {
		$html = $this->templateParser->processTemplate(
			$this->templateNames['single'],
			[
				'icon_url' => $emailIcon,
				'header' => $messageHeader,
				'body' => $summary,
				'actions' => $actions,
				'footer' => $footer,
				'greeting' => $greeting,
				'sender-info' => $senderMessage
			]
		);

		return $html;
	}

	/**
	 *
	 * @param array $link
	 * @param string $type
	 * @return string
	 */
	public function renderLink( $link, $type ) {
		$html = $this->templateParser->processTemplate(
			$this->templateNames[$type],
			[
				'url' => wfExpandUrl( $link['url'], PROTO_CANONICAL ),
				'label' => $link['label']
			]
		);

		return $html;
	}

	/**
	 *
	 * @return string
	 */
	public function getFooter() {
		$special = \SpecialPage::getTitleFor(
			'Preferences',
			false,
			'mw-prefsection-echo'
		);
		$preferenceLink = $this->renderLink(
			[
				'label' => $this->msg( 'echo-email-html-footer-preference-link-text', $this->user )->text(),
				'url' => $special->getFullURL( '', false, PROTO_CANONICAL ),
			],
			self::SECONDARY_LINK
		);

		$footer = $this->msg( 'echo-email-html-footer-with-link' )
			->rawParams( $preferenceLink )
			->params( $this->user )
			->parse();

		return $footer;
	}
}
