<?php
/**
 * Script to send email notification to translators on regular intervals.
 *
 * @author Santhosh Thottingal
 * @copyright Copyright © 2012 Santhosh Thottingal
 * @license GPL-2.0-or-later
 * @file
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$IP = __DIR__ . "/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

class DigestEmailer extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Send email notification to translators on regular intervals.' );
		$this->requireExtension( 'TranslationNotifications' );
	}

	public function execute() {
		$this->lock();

		// Get the Translators with weekly or monthly notification preferences.
		$translators = $this->getTranslators();
		if ( count( $translators ) === 0 ) {
			$this->output( "No translators with digest email option found\n" );
			// release the lock.
			$this->unlock();
			exit( 0 );
		}
		// Get the Notification from last 1 month.
		$notifications = $this->getNotifications();
		// Send the mails
		$this->sendEmails( $translators, $notifications );
		// unlock the process
		$this->unlock();
	}

	public function sendEmails( $translators, $notifications ) {
		$config = $this->getConfig();
		// Initialize early to avoid calling these repeatedly within the loop
		$noReplyAddress = $config->get( 'NoReplyAddress' );
		$urlType = $config->get( 'TranslationNotificationsAlwaysHttpsInEmail' ) === false
			? PROTO_CANONICAL
			: PROTO_HTTPS;

		$mailstatus = [];
		foreach ( $translators as $translator ) {
			$notificationText = "";
			$count = 0;
			$mailstatus[$translator] = $count;
			$user = User::newFromId( $translator );
			$notificationFreq = $user->getOption( 'translationnotifications-freq' );

			$userName = $user->getName();
			$this->output( "Sending digest to: $userName\n\t" .
				"Frequency preference: $notificationFreq\n" );
			$signedUpLangCodes = [];
			foreach ( range( 1, 3 ) as $langNum ) {
				$langCode = $user->getOption( "translationnotifications-lang-$langNum" );
				if ( $langCode ) {
					$signedUpLangCodes[] =
						$user->getOption( "translationnotifications-lang-$langNum" );
				}
			}
			$firstLangCode = $user->getOption( 'translationnotifications-lang-1' );
			$firstLang = Language::fetchLanguageName( $signedUpLangCodes[0], $firstLangCode );
			$this->output( "\tSigned up for: " . implode( ', ', $signedUpLangCodes ) . "\n" );

			// Draft the mail for this user
			$digestMailSubject = wfMessage( 'translationnotifications-digestemail-subject' )
				->text();

			$offset = '';

			if ( $notificationFreq === 'weekly' ) {
				$offset = '-1 week';
			}

			if ( $notificationFreq === 'monthly' ) {
				$offset = '-1 month';
			}

			$startTimeStamp = strtotime( $offset );
			$lastSuccessfulrun = $user->getOption( 'translationnotifications-last-digest' );
			if ( $lastSuccessfulrun > $startTimeStamp ) {
				$this->output( "\tNot sending notifications, Last notification time: " .
					date( 'D M j G:i:s T Y', $lastSuccessfulrun ) . " \n" );
				continue;
			}
			$this->output( "\tSending notification since " .
				date( 'D M j G:i:s T Y', $startTimeStamp ) . " \n" );

			foreach ( $notifications as $notification ) {
				$announcedate = strtotime( $notification['announcedate'] );

				if ( $announcedate < $startTimeStamp ) {
					// Older than last successful run.
					continue;
				}
				$deadline = strtotime( $notification['deadline'] );
				if ( $deadline && $deadline < time() ) {
					// Deadline expired
					continue;
				}
				// Send the notification only if the languages to notify match with
				// translators language preference.
				if ( $notification['languages'] !== '' ) {
					$languagesToNotify = explode( ',', $notification['languages'] );
					$languagesToNotify = array_flip( array_filter( array_map( 'trim', $languagesToNotify ) ) );
					if ( !( isset( $languagesToNotify[$signedUpLangCodes[0]] )
						|| ( isset( $signedUpLangCodes[1] ) && isset( $languagesToNotify[$signedUpLangCodes[1]] ) )
						|| ( isset( $signedUpLangCodes[2] ) && isset( $languagesToNotify[$signedUpLangCodes[2]] ) ) )
					) {
						continue;
					}
				}

				$page = TranslatablePage::newFromTitle( $notification['translatablepage'] );
				$translationURL = SpecialPage::getTitleFor( 'Translate' )->getFullURL(
					[
						'group' => $page->getMessageGroupId(),
						'language' => $firstLangCode,
						'action' => 'page'
					],
					false,
					$urlType
				);

				$notificationText .= wfMessage(
					'translationnotifications-digestemail-notification-line',
					date( "Y-m-d", $announcedate ),
					$notification['announcer'],
					$notification['translatablepage'],
					$translationURL
				)->inLanguage( $firstLangCode )->text() . "\n";

				if ( $notification['priority'] !== 'unset' ) {
					$notificationText .= wfMessage(
						'translationnotifications-email-priority',
						$notification['priority']
					)->text() . "\n";
				}

				if ( $notification['deadline'] !== '' ) {
					$notificationText .= wfMessage(
						'translationnotifications-email-deadline',
						$notification['deadline']
					)->text() . "\n";
				}

				$notificationText .= "---------\n";
				$count++;
			}

			$this->output( "\t$count notifications to send.\n" );
			$mailstatus[$translator] = $count;

			if ( $count === 0 ) {
				// No notifications to send.
				continue;
			}

			$signupURL = SpecialPage::getTitleFor( 'TranslatorSignup' )->getFullURL(
				'',
				false,
				$urlType
			);

			$digestMailBody = wfMessage( 'translationnotifications-digestemail-body',
				$userName,
				$firstLang,
				$count,
				$notificationText,
				$signupURL
			)->inLanguage( $firstLangCode )->text();

			$emailFrom = new MailAddress( $noReplyAddress );
			$emailTo = MailAddress::newFromUser( $user );
			$params = [
				'to' => $emailTo,
				'from' => $emailFrom,
				'body' => $digestMailBody,
				'subj' => $digestMailSubject,
				'replyto' => $emailFrom,
			];
			$job = new EmaillingJob( null, $params );
			JobQueueGroup::singleton()->push( $job );

			$user->setOption( 'translationnotifications-last-digest', wfTimestamp() );
			$user->saveSettings();
		}

		return $mailstatus;
	}

	protected function sort( $notifications ) {
		$prioritySet = [
			'unset' => 0,
			'low' => 1,
			'medium' => 2,
			'high' => 3
		];
		foreach ( $notifications as $key => $row ) {
			$priority[$key] = $prioritySet[$row['priority']];
			$announcedate[$key] = $row['announcedate'];
		}
		if ( count( $notifications ) > 0 ) {
			array_multisort( $priority, SORT_DESC, $announcedate, SORT_DESC, $notifications );
		}

		return $notifications;
	}

	protected function lock() {
		// Lock this process to avoid multiple instances running and duplicate mails being sent.
		$cache = wfGetCache( CACHE_ANYTHING );
		$lockKey = $cache->makeKey( 'translationnotifications-digestemailer-lock' );
		if ( $cache->get( $lockKey ) == true ) {
			$this->output( "Another process is running. Please try later\n" );
			exit( 1 );
		}
		$cache->set( $lockKey, true, 3600 ); // Expires in 1 hour.
	}

	protected function unlock() {
		$cache = wfGetCache( CACHE_ANYTHING );
		$lockKey = $cache->makeKey( 'translationnotifications-digestemailer-lock' );
		// release the lock.
		$cache->delete( $lockKey );
	}

	protected function getTranslators() {
		$translators = [];
		$dbr = wfGetDB( DB_REPLICA );
		$translatorsConds = [ 'up_property' => 'translationnotifications-freq' ];
		$translatorsConds += [ 'up_value' => [ 'weekly', 'monthly' ] ];
		$result = $dbr->select(
			'user_properties',
			'up_user',
			$translatorsConds,
			__METHOD__,
			'DISTINCT'
		);
		foreach ( $result as $translator ) {
			$translators[] = $translator->up_user;
		}

		return $translators;
	}

	/**
	 * Get the notifications from last one month (monthly is the largest digest frequency)
	 * Sort the notification in the descending order of announcement date, and remove
	 * the older announcements about the pages. Also remove notifications with expired
	 * deadline.
	 * @return array of notifications
	 */
	protected function getNotifications() {
		$dbr = wfGetDB( DB_REPLICA );
		$logEntrySelectQuery = DatabaseLogEntry::getSelectQueryData();
		$logFilter = [ 'log_type' => 'notifytranslators' ];
		$lastMonthTimeStamp = $dbr->timestamp( strtotime( '-1 month' ) );
		$logFilter += [
			"log_timestamp > $lastMonthTimeStamp",
		];
		$logEntrySelectQuery['conds'] = $logFilter;
		$logs = $dbr->select(
			$logEntrySelectQuery['tables'],
			$logEntrySelectQuery['fields'],
			$logEntrySelectQuery['conds'],
			__METHOD__,
			[ 'ORDER BY' => 'log_timestamp DESC' ],
			$logEntrySelectQuery['join_conds']
		);

		$notifications = [];
		foreach ( $logs as $row ) {
			$logEntry = DatabaseLogEntry::newFromRow( $row );
			$logParams = $logEntry->getParameters();
			if ( $logParams[1] && strtotime( $logParams[1] ) < time() ) {
				// Deadline already expired
				continue;
			}
			$translatablePage = $logEntry->getTarget()->getPrefixedText();
			if ( isset( $notifications[$translatablePage] ) ) {
				// An older notification about the same page.
				continue;
			}
			$notifications[$translatablePage] = [
				'languages' => $logParams[0],
				'deadline' => $logParams[1],
				'priority' => $logParams[2],
				'announcedate' => wfTimestamp( TS_RFC2822, $logEntry->getTimestamp() ),
				'announcer' => $logEntry->getPerformer(),
				'translatablepage' => $logEntry->getTarget()
			];
		}

		return $this->sort( $notifications );
	}
}

$maintClass = 'DigestEmailer';
require_once RUN_MAINTENANCE_IF_MAIN;
