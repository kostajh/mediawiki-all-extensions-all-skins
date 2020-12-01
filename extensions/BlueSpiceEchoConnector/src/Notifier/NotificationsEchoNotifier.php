<?php

namespace BlueSpice\EchoConnector\Notifier;

use BlueSpice\BaseNotification;
use BlueSpice\EchoConnector\EchoEventPresentationModel;
use BlueSpice\EchoConnector\Notification\EchoNotification;
use BlueSpice\INotification;

/**
 * This class has unfortunate naming, since Echo uses similar naming
 * for its notifiers. This is BlueSpiceNotifications notifier,
 * not override of Echo default notifier
 */
class NotificationsEchoNotifier implements \BlueSpice\INotifier {

	/**
	 *
	 * @var array
	 */
	protected $echoNotifications;

	/**
	 *
	 * @var array
	 */
	protected $echoNotificationCategories;

	/**
	 *
	 * @var array
	 */
	protected $echoIcons;

	/**
	 *
	 * @var \Config
	 */
	protected $config;

	/**
	 *
	 * @param \Config $config
	 */
	public function __construct( \Config $config ) {
		$this->config = $config;
	}

	/**
	 *
	 * @param string $key
	 * @param \User $agent
	 * @param \Title|null $title
	 * @param array|null $params
	 * @return EchoNotification
	 */
	public function getNotificationObject( $key, $agent, $title = null, $params = [] ) {
		return new BaseNotification( $key, $agent, $title, $params );
	}

	public function init() {
		global $wgEchoNotifications, $wgEchoNotificationCategories, $wgEchoNotifiers,
				$wgEchoNotificationIcons;

		$wgEchoNotifiers = [
			'web' => [
				\BlueSpice\EchoConnector\Notifier\EchoNotifier::class,
				'notifyWithNotification'
			],
			'email' => [
				\BlueSpice\EchoConnector\Notifier\EchoNotifier::class,
				'notifyWithEmail'
			]
		];

		$this->echoNotifications = &$wgEchoNotifications;
		$this->echoNotificationCategories = &$wgEchoNotificationCategories;
		$this->echoIcons = &$wgEchoNotificationIcons;

		$this->registerIconsFromAttribute();
	}

	/**
	 *
	 * @param INotification $notification
	 * @return null|\Status
	 */
	public function notify( $notification ) {
		if ( $notification instanceof INotification == false ) {
			return;
		}

		if ( isset( $this->echoNotifications[$notification->getKey()] ) == false ) {
			// Notification not registered
			return;
		}

		$echoNotif = [
			'type' => $notification->getKey(),
			'agent' => $notification->getUser(),
			'title' => $notification->getTitle(),
			'extra' => $notification->getParams()
		];

		if ( !empty( $notification->getAudience() ) ) {
			$echoNotif['extra']['affected-users'] = $notification->getAudience();
		}

		if ( $notification->sendImmediateEmail() == true ) {
			$echoNotif['extra']['immediate-email'] = true;
		}

		if ( !empty( $notification->getSecondaryLinks() ) ) {
			$echoNotif['extra']['secondary-links'] = $notification->getSecondaryLinks();
		}

		if ( $this->checkUseJobQueue( $notification ) ) {
			$job = new \BlueSpice\EchoConnector\Job\SendNotification(
				$notification->getTitle(),
				$echoNotif
			);
			\JobQueueGroup::singleton()->push( $job );
		} else {
			\EchoEvent::create( $echoNotif );
		}

		return \Status::newGood();
	}

	protected function registerIconsFromAttribute() {
		$icons = \ExtensionRegistry::getInstance()->getAttribute(
			'BlueSpiceEchoConnectorNotificationIcons'
		);

		foreach ( $icons as $key => $params ) {
			$this->registerIcon( $key, $params );
		}
	}

	/**
	 *
	 * @param string $key
	 * @param array $params
	 */
	public function registerIcon( $key, $params ) {
		$this->echoIcons[$key] = $params;
	}

	/**
	 *
	 * @param string $key
	 * @param array $params
	 */
	public function registerNotification( $key, $params ) {
		$extraParams = [];
		if ( !empty( $params[ 'extra-params' ] ) ) {
			$extraParams = $params[ 'extra-params' ];
		}

		if ( !isset( $params['user-locators'] ) || !is_array( $params['user-locators'] ) ) {
			$params['user-locators'] = [ self::class . '::setUsersToNotify' ];
		} else {
			$params['user-locators'][] = self::class . '::setUsersToNotify';
		}

		$section = \EchoAttributeManager::ALERT;
		if ( isset( $params['section'] ) && $params['section'] == 'message' ) {
			$section = \EchoAttributeManager::MESSAGE;
		}

		$notificationConfig = $extraParams + [
			'category' => $params[ 'category' ],
			'section' => $section,
			'user-locators' => $params['user-locators']
		];

		if ( !isset( $params[ 'presentation-model' ] ) ) {
			$notificationConfig += [
				'presentation-model' => EchoEventPresentationModel::class,
				'title-message' => $params[ 'summary-message' ],
				'title-params' => $params[ 'summary-params' ],
				'web-body-message' => $params[ 'web-body-message' ],
				'web-body-params' => $params[ 'web-body-params' ],
				'email-subject-message' => $params[ 'email-subject-message' ],
				'email-subject-params' => $params[ 'email-subject-params' ],
				'email-body-message' => $params[ 'email-body-message' ],
				'email-body-params' => $params[ 'email-body-params' ],
			];
		} else {
			$notificationConfig['presentation-model'] = $params['presentation-model'];
		}

		$this->echoNotifications[$key] = $notificationConfig;
	}

	/**
	 * Registeres Echo notification category
	 *
	 * @param string $key
	 * @param array $params
	 */
	public function registerNotificationCategory( $key, $params = [] ) {
		$this->echoNotificationCategories[$key] = $params;
	}

	/**
	 *
	 * @param string $key
	 */
	public function unRegisterNotification( $key ) {
		if ( isset( $this->echoNotifications[$key] ) ) {
			unset( $this->echoNotifications[$key] );
		}
	}

	/**
	 * Converts user list from extra params to users to be notified
	 *
	 * @param \EchoEvent $event
	 * @return array
	 */
	public static function setUsersToNotify( $event ) {
		$users = $event->getExtraParam( 'affected-users', [] );

		$res = [];
		foreach ( $users as $user ) {
			if ( $user instanceof \User ) {
				$res[$user->getId()] = $user;
				continue;
			}
			$res[$user] = \User::newFromId( $user );
		}

		return $res;
	}

	/**
	 * No-op for now
	 *
	 * @param \EchoEvent $event
	 */
	public static function filterUsersToNotify( $event ) {
	}

	/**
	 *
	 * @param INotification $notification
	 * @return bool
	 */
	protected function checkUseJobQueue( $notification ) {
		$echoNotificationConfig = $this->echoNotifications[$notification->getKey()];

		if ( $notification->getTitle() instanceof \Title == false ) {
			// If notification has no Title object set, we cannot use JQ
			return false;
		}

		if ( $notification->useJobQueue() == true ) {
			return true;
		}

		// Setting immediate-email will override default settings for using job queue.
		// If job queue is really necessary in conjuction with this param it must be set
		// explicitly when calling notify
		if ( $notification->sendImmediateEmail() == true ) {
			return false;
		}

		if ( isset( $echoNotificationConfig['use-job-queue'] )
			&& $echoNotificationConfig['use-job-queue'] == true ) {
			return true;
		}

		if ( $this->config->get( 'UseJobQueueForNotifications' ) == true ) {
			return true;
		}

		$audienceCount = count( $notification->getAudience() );
		if ( $audienceCount > $this->config->get( 'ForceJobQueueForLargeAudienceThreshold' ) ) {
			// Force JQ if there are too many users to send notif to
			return true;
		}

		return false;
	}

}
