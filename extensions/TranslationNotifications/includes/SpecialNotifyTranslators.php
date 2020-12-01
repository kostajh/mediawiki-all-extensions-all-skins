<?php
/**
 * Form for translation managers to send a notification
 * to registered translators.
 *
 * @file
 * @author Amir E. Aharoni
 * @author Santhosh Thottingal
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2012, Amir E. Aharoni, Santhosh Thottingal
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

/**
 * Form for translation managers to send a notification
 * to registered translators.
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialNotifyTranslators extends FormSpecialPage {
	public static $right = 'translate-manage';
	private $notificationText = '';
	/**
	 * @var Title
	 */
	private $translatablePageTitle;
	private $deadlineDate = '';
	private $priority = '';
	private $sourceLanguageCode = '';

	public function __construct() {
		parent::__construct( 'NotifyTranslators', self::$right );
	}

	public function doesWrites() {
		return true;
	}

	protected function getGroupName() {
		return 'translation';
	}

	protected function getMessagePrefix() {
		return 'translationnotifications';
	}

	protected function alterForm( HTMLForm $form ) {
		$form->setId( 'notifytranslators-form' );
		$form->setSubmitID( 'translationnotifications-send-notification-button' );
		$form->setSubmitTextMsg( 'translationnotifications-send-notification-button' );
		$form->setWrapperLegend( false );
	}

	/**
	 * Get the form fields for use by the HTMLForm.
	 * We also set up the JavaScript needed on the form here.
	 *
	 * @return array
	 * @throws ErrorPageError if there is no translatable page on this wiki
	 */
	protected function getFormFields() {
		$this->getOutput()->addModules( 'ext.translationnotifications.notifytranslators' );

		$formFields = [];

		$pages = $this->getTranslatablePages();
		if ( count( $pages ) === 0 ) {
			throw new ErrorPageError( 'notifytranslators',
				'translationnotifications-error-no-translatable-pages' );
		}

		$formFields['TranslatablePage'] = [
			'name' => 'tpage',
			'type' => 'select',
			'label-message' => [ 'translationnotifications-translatablepage-title' ],
			'options' => $pages,
		];

		$contLang = MediaWikiServices::getInstance()->getContentLanguage();
		// Dummy dropdown, will be invisible. Used as data source for language name autocompletion.
		// @todo Implement a proper field with everything needed for this and make this less hackish
		$languageSelector = Xml::languageSelector(
			$contLang->getCode(),
			false,
			$this->getLanguage()->getCode(),
			[ 'style' => 'display: none;' ]
		);
		$formFields['LanguagesList'] = [
			'type' => 'info',
			'raw' => true,
			'default' => $languageSelector[1],
		];
		// Languages to notify input box
		$formFields['LanguagesToNotify'] = [
			'type' => 'text',
			'rows' => 20,
			'cols' => 80,
			'label-message' => 'translationnotifications-languages-to-notify-label',
			'help-message' => 'translationnotifications-languages-to-notify-label-help-message',
		];

		// Priority dropdown
		$priorityOptions = [];
		$priorities = [ 'unset', 'high', 'medium', 'low' ];

		foreach ( $priorities as $priority ) {
			$priorityMessage =
				NotificationMessageBuilder::getPriorityMessage( $priority )
					->setContext( $this->getContext() )->text();
			$priorityOptions[$priorityMessage] = $priority;
		}

		$formFields['Priority'] = [
			'type' => 'select',
			'label-message' => [ 'translationnotifications-priority' ],
			'options' => $priorityOptions,
			'default' => 'unset',
		];

		// Deadline date input box with datepicker
		$formFields['DeadlineDate'] = [
			'type' => 'text',
			'size' => 20,
			'label-message' => 'translationnotifications-deadline-label',
		];

		// Custom text
		$formFields['NotificationText'] = [
			'type' => 'textarea',
			'rows' => 20,
			'cols' => 80,
			'label-message' => 'emailmessage',
		];

		return $formFields;
	}

	/**
	 * @return array
	 */
	protected function getTranslatablePages() {
		$translatablePages = MessageGroups::getGroupsByType( 'WikiPageMessageGroup' );
		usort( $translatablePages, [ 'MessageGroups', 'groupLabelSort' ] );

		$titles = [];
		// Retrieving article id requires doing DB queries.
		// Make it more efficient by batching into one query.
		$lb = new LinkBatch();
		/**
		 * @var WikiPageMessageGroup $page
		 */
		foreach ( $translatablePages as $page ) {
			if ( MessageGroups::getPriority( $page ) === 'discouraged' ) {
				continue;
			}
			'@phan-var WikiPageMessageGroup $page';
			$title = $page->getTitle();
			$lb->addObj( $title );
			$titles[] = $title;
		}
		$lb->execute();

		$translatablePagesOptions = [];
		foreach ( $titles as $title ) {
			$translatablePagesOptions[$title->getPrefixedText()] = $title->getArticleID();
		}

		return $translatablePagesOptions;
	}

	private function getSourceLanguage() {
		if ( $this->sourceLanguageCode === '' ) {
			$translatablePage = TranslatablePage::newFromTitle( $this->translatablePageTitle );
			$this->sourceLanguageCode = $translatablePage->getMessageGroup()->getSourceLanguage();
		}

		return $this->sourceLanguageCode;
	}

	/**
	 * Callback for the submit button.
	 *
	 * @param array $formData
	 * @return Status|bool
	 * @todo Document
	 */
	public function onSubmit( array $formData ) {
		$this->translatablePageTitle = Title::newFromID( $formData['TranslatablePage'] );
		$this->notificationText = $formData['NotificationText'];
		$this->priority = $formData['Priority'];
		$this->deadlineDate = $formData['DeadlineDate'];
		$languagesToNotify = explode( ',', $formData['LanguagesToNotify'] );
		$languagesToNotify = array_filter( array_map( 'trim', $languagesToNotify ) );

		$pageSourceLangCode = $this->getSourceLanguage();
		$notificationLanguages = [];

		// The default is not to specify any languages and to send the notification to speakers of
		// all the languages except the source language. When no languages are specified,
		// an empty string will be sent here and an appropriate message will be shown in the log.
		if ( count( $languagesToNotify ) ) {
			// Filter out the source language
			foreach ( $languagesToNotify as $langCode ) {
				if ( $langCode !== $pageSourceLangCode ) {
					$notificationLanguages[] = $langCode;
				}
			}

			if ( $notificationLanguages === [] ) {
				return Status::newFatal( 'translationnotifications-sourcelang-only' );
			}
		}

		$requestData = [
			'notificationText' => $this->notificationText,
			'priority' => $this->priority,
			'languagesToNotify' => $notificationLanguages,
			'deadlineDate' => $this->deadlineDate
		];

		$job = TranslationNotificationsSubmitJob::newJob(
			$this->translatablePageTitle,
			$requestData,
			$this->getUser()->getId(),
			$this->getLanguage()->getCode()
		);

		JobQueueGroup::singleton()->push( $job );
		return true;
	}

	public function onSuccess() {
		$this->getOutput()->addWikiMsg( 'translationnotifications-submit-ok' );
	}
}
