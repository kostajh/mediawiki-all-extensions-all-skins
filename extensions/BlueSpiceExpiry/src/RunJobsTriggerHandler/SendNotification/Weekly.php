<?php

namespace BlueSpice\Expiry\RunJobsTriggerHandler\SendNotification;

use BlueSpice\Expiry\Data\Record;
use BlueSpice\Expiry\Notification\Weekly as Notification;
use BlueSpice\Expiry\RunJobsTriggerHandler\SendNotification;
use BlueSpice\RunJobsTriggerHandler\Interval\OnceAWeek;
use DateInterval;
use DateTime;
use DateTimeZone;
use Title;

class Weekly extends SendNotification {

	/**
	 *
	 * @return OnceAWeek
	 */
	public function getInterval() {
		return new OnceAWeek();
	}

	/**
	 * @param Title $title
	 * @param Record $record
	 * @param array $users
	 */
	protected function sendNotifications( Title $title, Record $record, array $users ) {
		$agent = $this->util->getMaintenanceUser()->getUser();
		$comment = empty( $record->get( Record::COMMENT, '' ) )
			? '-'
			: $record->get( Record::COMMENT );
		$notification = new Notification( $agent, $title, $users, $comment );
		$this->notifier->notify( $notification );
	}

	/**
	 * @return Title[]
	 */
	protected function getExpiredTitles() {
		$from = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$to = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$from->add( DateInterval::createFromDateString( '1 week' ) );
		$to->add( DateInterval::createFromDateString( '-1 day' ) );

		return $this->factory->getExpiredTitles( $from, $to );
	}
}
