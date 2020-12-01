<?php

namespace BlueSpice\EchoConnector;

class EchoEventPresentationModel extends \EchoEventPresentationModel {
	protected $paramParser;
	protected $notificationConfig;

	protected $distributionType;

	/**
	 *
	 * @param \EchoEvent $event
	 * @param \Language $language
	 * @param \User $user
	 * @param string $distributionType
	 */
	public function __construct( \EchoEvent $event, $language, \User $user, $distributionType ) {
		global $wgEchoNotifications;

		parent::__construct( $event, $language, $user, $distributionType );

		$this->distributionType = $distributionType;

		$this->paramParser = new \BlueSpice\EchoConnector\ParamParser( $event );
		// TODO: Get rid of global
		$this->notificationConfig = $wgEchoNotifications[$this->type];
	}

	/**
	 * There is not way to change distribution type after object
	 * has been contstructed, and its always constructed with 'web' type
	 * @param string $type
	 */
	public function setDistributionType( $type ) {
		$this->distributionType = $type;
		$this->paramParser->setDistributionType( $type );
	}

	/**
	 *
	 * @return bool
	 */
	public function canRender() {
		// Force rendering if explicitly specified
		if ( isset( $this->notificationConfig['forceRender'] ) ) {
			return true;
		}
		return (bool)$this->event->getTitle();
	}

	/**
	 *
	 * @return string
	 */
	public function getIconType() {
		return $this->getIcon();
	}

	/**
	 *
	 * @return string
	 */
	public function getIcon() {
		if ( isset( $this->notificationConfig['icon'] ) ) {
			return $this->notificationConfig['icon'];
		}

		return 'placeholder';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getHeaderMessage() {
		$content = $this->getHeaderMessageContent();
		$msg = $this->msg( $content['key'] );

		if ( $this->isBundled() ) {
			if ( $content['bundle-key'] ) {
				$msg = $this->msg( $content['bundle-key'] );
				$msg->params( $this->getBundleCount() );
			}
		}

		$params = $content['params'];
		if ( $this->isBundled() ) {
			$params = $content['bundle-params'];
		}

		if ( empty( $params ) ) {
			return $msg;
		}

		foreach ( $params as $param ) {
			$this->paramParser->parseParam( $msg, $param );
		}

		return $msg;
	}

	/**
	 *
	 * @return \Message
	 */
	public function getBodyMessage() {
		$content = $this->getBodyMessageContent();
		$msg = $this->msg( $content['key'] );
		if ( empty( $content['params'] ) ) {
			return $msg;
		}

		foreach ( $content['params'] as $param ) {
			$this->paramParser->parseParam( $msg, $param );
		}

		return $msg;
	}

	/**
	 *
	 * @return \Message
	 */
	public function getCompactHeaderMessage() {
		// This is the header message for individual notifications
		// *inside* the bundle
		return $this->getHeaderMessage();
	}

	/**
	 *
	 * @return \Message
	 */
	public function getSubjectMessage() {
		return $this->getHeaderMessage();
	}

	/**
	 * Gets the URL to the title that notification is about
	 *
	 * @return array|false if no \Title is supplied
	 */
	public function getPrimaryLink() {
		$title = $this->event->getTitle();
		if ( $title instanceof \Title == false ) {
			return false;
		}

		$label = $title->getPrefixedText();
		if ( $this->event->getExtraParam( 'primary-link-label', false ) ) {
			$label = $this->event->getExtraParam( 'primary-link-label' );
		}

		return [
			'url' => $title->getFullURL(),
			'label' => $label
		];
	}

	/**
	 * Gets appropriate messages keys and params
	 * for header message
	 *
	 * @return array
	 */
	public function getHeaderMessageContent() {
		$bundleKey = '';
		$bundleParams = [];
		if ( isset( $this->notificationConfig['bundle'] ) ) {
			$bundleKey = $this->notificationConfig['bundle']['bundle-message'];
			$bundleParams = $this->notificationConfig['bundle']['bundle-params'];
		}

		$messageConfig = [
			'key' => 'title-message',
			'params' => 'title-params'
		];

		if ( $this->distributionType == 'email' ) {
			$messageConfig['key'] = 'email-subject-message';
			$messageConfig['params'] = 'email-subject-params';
		}

		$headerKey = $this->notificationConfig[$messageConfig['key']];
		$headerParams = $this->notificationConfig[$messageConfig['params']];

		return [
			'key' => $headerKey,
			'params' => $headerParams,
			'bundle-key' => $bundleKey,
			'bundle-params' => $bundleParams
		];
	}

	/**
	 * Gets appropriate message key and params for
	 * web notification message
	 *
	 * @return array
	 */
	public function getBodyMessageContent() {
		$messageConfig = [
			'key' => 'web-body-message',
			'params' => 'web-body-params'
		];

		if ( $this->distributionType == 'email' ) {
			$messageConfig['key'] = 'email-body-message';
			$messageConfig['params'] = 'email-body-params';
		}

		return [
			'key' => $this->notificationConfig[$messageConfig['key']],
			'params' => $this->notificationConfig[$messageConfig['params']]
		];
	}

	/**
	 *
	 * @return array
	 */
	public function getSecondaryLinks() {
		if ( $this->isBundled() ) {
			// For the bundle, we don't need secondary actions
			return [];
		}

		if ( !isset( $this->notificationConfig['secondary-links'] ) ) {
			return [];
		}

		$extra = $this->event->getExtra();
		if ( !isset( $extra['secondary-links'] ) ) {
			$extra['secondary-links'] = [];
		}

		$secondaryLinksCfg = $this->notificationConfig['secondary-links'];
		$secondaryLinks = [];
		foreach ( $secondaryLinksCfg as $key => $cfg ) {
			if ( $key == 'agentlink' ) {
				$secondaryLinks[] = $this->getAgentLink();
				continue;
			}
			if ( isset( $extra['secondary-links'][$key] ) ) {
				$slValue = $extra['secondary-links'][$key];
				$cfg['label'] = wfMessage( $cfg['label'] );

				if ( !is_array( $slValue ) ) {
					$cfg['url'] = $slValue;
				} else {
					$cfg['url'] = $slValue['url'];
					if ( isset( $slValue['label-params'] ) ) {
						$cfg['label']->params( $slValue['label-params'] );
					}
				}
				$cfg['label'] = $cfg['label']->parse();

				$secondaryLinks[] = $cfg;
			}
		}

		return $secondaryLinks;
	}

}
