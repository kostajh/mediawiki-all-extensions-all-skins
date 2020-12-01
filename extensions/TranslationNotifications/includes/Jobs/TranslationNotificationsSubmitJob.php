<?php
/*
* @file
* @license GPL-2.0-or-later
*/

use MediaWiki\MediaWikiServices;

/**
 * Handles a notification request. Uses the TranslationNotifyUsers to create the necessary jobs
 * to deliver users messages based on their preferences.
 * @since 2019.11
 * @ingroup JobQueue
 */
class TranslationNotificationsSubmitJob extends GenericTranslationNotificationsJob {

	/**
	 * Id of the current wiki
	 * @var string
	 */
	private $currentWikiId;

	/**
	 * Returns an instance of the TranslationNotificationsSubmitJob
	 * @param Title $title
	 * @param array $requestData
	 * @param int $notifierId
	 * @param string $translatorLang
	 * @return self
	 */
	public static function newJob(
		Title $title, $requestData, $notifierId, $translatorLang
	) {
		return new TranslationNotificationsSubmitJob(
			$title,
			[
				'requestData' => $requestData,
				'notifierId' => $notifierId,
				'translatorLanguage' => $translatorLang
			]
		);
	}

	public function __construct( $title, $params ) {
		parent::__construct( __CLASS__, $title, $params );
		$this->currentWikiId = WikiMap::getCurrentWikiDbDomain()->getId();
	}

	/**
	 * Execute the job
	 * @return bool
	 */
	public function run() {
		$this->logInfo( 'Processing translation notification submit request.' );

		$params = $this->params;
		$translatableTitle = $this->title;
		$notifier = User::newFromId( $params['notifierId'] );
		$sourceLanguage = $this->getSourceLanguage( $translatableTitle );
		$translatorLangCode = $params['translatorLanguage'];

		// Request information
		$notificationText = $params['requestData']['notificationText'];
		$priority = $params['requestData']['priority'];
		$languagesToNotify = $params['requestData']['languagesToNotify'];
		$deadlineDate = $params['requestData']['deadlineDate'];

		$translatorsToNotify = $this->fetchTranslators( $languagesToNotify, $sourceLanguage );

		$this->logInfo(
			'Found ' . $translatorsToNotify->numRows() .
			' translators to notify with the given conditions.',
			[
				'languagesToNotify' => $languagesToNotify
			]
		);

		$frequencies = [
			'always' => 0,
			'week' => 604800,  // seconds in week
			'month' => 2678400, // seconds in month
			'weekly' => 604800, // seconds in week
			'monthly' => 2678400, // seconds in month
		];
		$currentUnixTime = wfTimestamp();
		$currentDBTime = wfGetDB( DB_REPLICA )->timestamp( $currentUnixTime );

		$timestampOptionName = 'translationnotifications-timestamp';

		$config = MediaWikiServices::getInstance()->getMainConfig();

		$notifyUser = new TranslationNotifyUser(
			$translatableTitle,
			$notifier,
			$config->get( 'LocalInterwikis' ),
			$config->get( 'NoReplyAddress' ),
			$config->get( 'TranslationNotificationsAlwaysHttpsInEmail' ),
			$sourceLanguage,
			[
				'text' => $notificationText,
				'priority' => $priority,
				'deadline' => $deadlineDate,
				'languagesToNotify' => $languagesToNotify
			]
		);

		$this->logInfo( 'Starting notification job creation for translators...' );

		$jobsByTarget = [];
		$stats = [
			'jobNoPref' => 0,
			'jobEmail' => 0,
			'jobEmailDisabled' => 0,
			'jobTalkPage' => 0,
			'jobTalkPageOther' => 0,
			'tooEarly' => 0,
			'sendingFailed' => 0,
			'processedUsers' => 0
		];

		foreach ( $translatorsToNotify as $translator ) {
			$user = User::newFromID( $translator->up_user )->getInstanceForUpdate();

			$userTimestamp = $user->getOption( $timestampOptionName, null );
			$userUnixTimestamp = ( $userTimestamp == null ) ?
				wfTimestamp( TS_UNIX, '20120101000000' ) : // An old timestamp
				wfTimestamp( TS_UNIX, $userTimestamp );

			$timeSinceNotification = (int)$currentUnixTime - (int)$userUnixTimestamp;
			$userTranslationFrequency =
				$frequencies[$user->getOption( 'translationnotifications-freq' )];

			if ( $timeSinceNotification > $userTranslationFrequency ) {
				$this->logDebug( "Deciding notification to be sent to user: {$user->getId()}" );

				try {
					$userJobs = $this->getJobsForUser( $user, $notifyUser, $this->currentWikiId );
					$this->addUserJobsToList( $userJobs, $jobsByTarget, $stats );
					$user->setOption( $timestampOptionName, $currentDBTime );
					$user->saveSettings();
					$stats['processedUsers']++;
				} catch ( Exception $e ) {
					$stats['sendingFailed']++;
					$this->logError(
						"Error while generating notification for user - {$user->getId()}.\n Exception: {$e}."
					);
				}
			} else {
				$this->logDebug( "Skipping sending of notification to user: {$user->getId()}" );
				$stats['tooEarly']++;
			}

			$this->logDebug( "Finished processing user: {$user->getId()}." );
		}

		foreach ( $jobsByTarget as $wiki => $jobs ) {
			$this->logInfo( "Wiki: $wiki, Jobs: " . count( $jobs ) );
			JobQueueGroup::singleton( $wiki )->push( $jobs );
		}

		// Add a log entry
		$languagesForLog = '';
		if ( count( $languagesToNotify ) ) {
			$languagesForLog = Language::factory( $translatorLangCode )->commaList( $languagesToNotify );
		}

		$logEntry = new ManualLogEntry( 'notifytranslators', 'sent' );
		$logEntry->setPerformer( $notifier );
		$logEntry->setTarget( $translatableTitle );
		$logEntry->setParameters( [
			'4::languagesForLog' => $languagesForLog,
			'5::deadlineDate' => $deadlineDate,
			'6::priority' => $priority,
			'7::sentSuccess' => $stats['processedUsers'],
			'8::sentFail' => $stats['sendingFailed'],
			'9::tooEarly' => $stats['tooEarly']
		] );

		$logId = $logEntry->insert();
		$logEntry->publish( $logId );

		// Log the stats
		$stats[ 'jobTotal' ] = $this->getCurrentTotalJobs( $stats );
		$stats[ 'jobTotalWithSkipped' ] = $this->getCurrentTotalJobs( $stats, true );
		$this->logInfo(
			"Finished processing. Overall info: " .
			"Too early: {tooEarly}, Sending failed: {sendingFailed}, Users processed: {processedUsers}. " .
			"Jobs info: " .
			"Total: {jobTotal}, Total with skipped: {jobTotalWithSkipped}, Email: {jobEmail}, " .
			"Email disabled: {jobEmailDisabled}, Talk page: {jobTalkPage}, " .
			"Talk page other wiki: {jobTalkPageOther}, No preference: {jobNoPref}.",
			$stats
		);

		return true;
	}

	private function getSourceLanguage( Title $translatablePageTitle ) {
		$translatablePage = TranslatablePage::newFromTitle( $translatablePageTitle );
		return $translatablePage->getMessageGroup()->getSourceLanguage();
	}

	/**
	 * Returns translators who match the given language criteria.
	 * @param array $languagesToNotify
	 * @param string $sourceLanguage
	 * @return Wikimedia\Rdbms\IResultWrapper
	 */
	private function fetchTranslators( $languagesToNotify, $sourceLanguage ) {
		$langPropertyPrefix = 'translationnotifications-lang-';

		$dbr = wfGetDB( DB_REPLICA );
		$propertyLikePattern = $dbr->buildLike( $langPropertyPrefix, $dbr->anyString() );
		$translatorsConditions = [
			"up_property $propertyLikePattern",
		];

		if ( count( $languagesToNotify ) ) {
			$translatorsConditions['up_value'] = $languagesToNotify;
		} else {
			$translatorsConditions[] = 'up_value <> ' . $dbr->addQuotes( $sourceLanguage );
		}

		$translatorsToNotify = $dbr->select(
			'user_properties',
			'up_user',
			$translatorsConditions,
			__METHOD__,
			'DISTINCT'
		);

		return $translatorsToNotify;
	}

	/**
	 * Return jobs for a user based on the user's preferences.
	 * @param User $user
	 * @param TranslationNotifyUser $notifyUser
	 * @param string $currentWikiId
	 * @return array
	 */
	private function getJobsForUser(
		User $user, TranslationNotifyUser $notifyUser, string $currentWikiId
	): array {
		$jobs = [];

		// Email notification
		if ( $user->getOption( 'translationnotifications-cmethod-email' ) ) {
			if ( $user->getOption( 'disablemail' ) ) {
				// For some reason the user signed up to receive Translation Notifications emails,
				// but receiving email is disabled in the user's preferences.
				// To be on the safe side, disable the email contact method.
				$user->setOption( 'translationnotifications-cmethod-email', false );
				$jobs[] = [ $currentWikiId, 'jobEmailDisabled', null ];
			} elseif ( $user->getOption( 'translationnotifications-freq' ) === 'always' ) {
				$jobs[] = [
					$currentWikiId, 'jobEmail', $notifyUser->sendTranslationNotificationEmail( $user )
				];
			}
		}

		// Talk page in current wiki
		if ( $user->getOption( 'translationnotifications-cmethod-talkpage' ) ) {
			$jobs[] = [
				$currentWikiId,
				'jobTalkPage',
				$notifyUser->leaveUserMessage( $user, 'talkpageHere' )
			];
		}

		// Talk page in another wiki
		if ( $user->getOption( 'translationnotifications-cmethod-talkpage-elsewhere' ) ) {
			$wiki =
				$user->getOption( 'translationnotifications-cmethod-talkpage-elsewhere-loc' );
			$jobs[] = [
				$wiki,
				'jobTalkPageOther',
				$notifyUser->leaveUserMessage( $user, 'talkpageInOtherWiki' )
			];
		}

		return $jobs;
	}

	/**
	 * Add jobs for a user to the list of all jobs, also updates the stats.
	 * @param array $userJobs
	 * @param array &$jobList
	 * @param array &$stats
	 * @return void
	 */
	private function addUserJobsToList(
		array $userJobs,
		array &$jobList,
		array &$stats
	): void {
		if ( !count( $userJobs ) ) {
			$stats[ 'jobNoPref' ]++;
		}

		foreach ( $userJobs as $userJob ) {
			list( $wikiId, $jobType, $job ) = $userJob;
			$stats[ $jobType ]++;
			if ( $job === null ) {
				continue;
			}

			if ( !isset( $jobList[ $wikiId ] ) ) {
				$jobList[ $wikiId ] = [];
			}

			$jobList[ $wikiId ][] = $job;
		}
	}

	private function getCurrentTotalJobs( array $stats, bool $withSkipped = false ): int {
		$total = $stats[ 'jobTalkPageOther' ] + $stats[ 'jobTalkPage' ]
			+ $stats[ 'jobEmail' ];

		if ( $withSkipped ) {
			$total += $stats[ 'jobEmailDisabled' ] + $stats[ 'jobNoPref' ];
		}

		return $total;
	}
}
