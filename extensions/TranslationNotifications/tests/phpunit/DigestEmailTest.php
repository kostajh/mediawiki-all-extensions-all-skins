<?php
/**
 * Unit tests.
 *
 * @file
 * @author Santhosh Thottingal
 * @copyright Copyright © 2012, Santhosh Thottingal
 * @license GPL-2.0-or-later
 */

/**
 * Unit tests for DigestEmailer class.
 * @covers DigestEmailer
 * @group Database
 */
class DigestEmailTest extends MediaWikiTestCase {
	/**
	 * @var DigestEmailer
	 */
	private $emailer;

	/**
	 * @var array
	 */
	private $translators_conf;

	public function setUp() : void {
		parent::setUp();
		$this->translators_conf = parse_ini_file( "translators.ini", true );
		$this->emailer = new DigestEmailer();
	}

	public function tearDown() : void {
		unset( $this->emailer );
		parent::tearDown();
	}

	public function testSendEmailMonthlyValid() {
		$translators = $this->getTranslators( 'monthly' );
		$notifications = $this->getNotifications();
		$mailstatus = $this->emailer->sendEmails( $translators, $notifications );
		foreach ( $translators as $translator ) {
			$this->expectOutputRegex( '/[a-zA-Z\n]*1 notifications to send/' );
			$this->assertEquals(
				$mailstatus[$translator],
				1,
				User::newFromId( $translator ) . " should get a mail"
			);
		}
	}

	public function testSendEmailMonthlyInvalidLang() {
		$translators = $this->getTranslators( 'monthly' );
		$notifications = $this->getNotificationsForInvalidLangs();
		$mailstatus = $this->emailer->sendEmails( $translators, $notifications );
		foreach ( $translators as $translator ) {
			$this->expectOutputRegex( '/[a-zA-Z\n]*0 notifications to send/' );
			$this->assertEquals(
				$mailstatus[$translator],
				0,
				User::newFromId( $translator ) . " should not get a mail. " .
					"Notification is not for this translator"
			);
		}
	}

	public function testSendEmailWeeklyValid() {
		$translators = $this->getTranslators( 'weekly' );
		$notifications = $this->getNotifications();
		$mailstatus = $this->emailer->sendEmails( $translators, $notifications );
		foreach ( $translators as $translator ) {
			$this->expectOutputRegex( '/[a-zA-Z\n]*1 notifications to send/' );
			$this->assertEquals(
				$mailstatus[$translator],
				1,
				User::newFromId( $translator ) . " should get a mail"
			);
		}
	}

	public function testSendEmailWeeklyExpired() {
		$translators = $this->getTranslators( 'weekly' );
		$notifications = $this->getExpiredNotifications();
		$mailstatus = $this->emailer->sendEmails( $translators, $notifications );
		foreach ( $translators as $translator ) {
			$this->expectOutputRegex( '/[a-zA-Z\n]*0 notifications to send/' );
			$this->assertEquals(
				$mailstatus[$translator],
				0,
				User::newFromId( $translator ) . " should not get a mail. Notifications are expired"
			);
		}
	}

	public function testSendEmailWeeklyRepeated() {
		$translators = $this->getTranslators( 'weekly' );
		$notifications = $this->getNotifications();
		$mailstatus = $this->emailer->sendEmails( $translators, $notifications );
		foreach ( $translators as $translator ) {
			$this->expectOutputRegex( '/[a-zA-Z\n]*1 notifications to send/' );
			$this->assertEquals(
				$mailstatus[$translator],
				1,
				User::newFromId( $translator ) . " should get a mail"
			);
		}
		$mailstatus = $this->emailer->sendEmails( $translators, $notifications );
		foreach ( $translators as $translator ) {
			$this->expectOutputRegex( '/[a-zA-Z\n]*Not sending notifications/' );
			$this->assertEquals(
				$mailstatus[$translator],
				0,
				User::newFromId( $translator ) . " should not get a mail, this is a repeat"
			);
		}
	}

	private function getNotifications() {
		$notifications = [];
		$notifications[] = [
			'languages' => '', // all languages
			'deadline' => '+1 month',
			'priority' => 'medium',
			'announcedate' => '-1 day',
			'announcer' => 'me',
			'translatablepage' => Title::newFromText( 'TestTitle' ),
		];

		return $notifications;
	}

	private function getNotificationsForInvalidLangs() {
		$notifications = [];
		$notifications[] = [
			'languages' => 'invalid',
			'deadline' => '+1 month',
			'priority' => 'medium',
			'announcedate' => '-1 day',
			'announcer' => 'me',
			'translatablepage' => Title::newFromText( 'TestTitle' ),
		];

		return $notifications;
	}

	private function getExpiredNotifications() {
		$notifications = [];
		$notifications[] = [
			'languages' => '', // all languages
			'deadline' => '-1 month',
			'priority' => 'medium',
			'announcedate' => '-1 day',
			'announcer' => 'me',
			'translatablepage' => Title::newFromText( 'TestTitle' ),
		];

		return $notifications;
	}

	private function getTranslators( $freq ) {
		$translators = [];
		foreach ( $this->translators_conf as $translator_conf ) {
			if ( $translator_conf['digest_frequency'] === $freq ) {
				$translators[] = $this->getTranslator( $translator_conf );
			}
		}

		return $translators;
	}

	private function getTranslator( $translator_conf ) {
		$user = User::newFromName( $translator_conf['username'] );
		if ( $user->getID() === 0 ) {
			$user->addToDatabase();
		}
		$user->setOption( 'translationnotifications-lang-1', $translator_conf['language'] );
		// set it a time long time back
		$user->setOption( 'translationnotifications-last-digest', strtotime( '-2 month' ) );
		$user->setOption( 'translationnotifications-freq', $translator_conf['digest_frequency'] );
		$user->saveSettings();

		return $user->getId();
	}
}
