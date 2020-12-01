<?php

namespace BlueSpice\Calumma\Renderer;

use BlueSpice\Calumma\IFlyout;
use BlueSpice\Calumma\IPanel;
use BlueSpice\Renderer\Params;
use BlueSpice\TemplateFactory;
use BlueSpice\TemplateRenderer;
use BlueSpice\Utility\CacheHelper;
use Config;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;

class Panel extends TemplateRenderer {

	public const PARAM_INSTANCE = 'instance';

	/**
	 *
	 * @var IPanel
	 */
	protected $panelInterface = null;

	/**
	 *
	 * @var array
	 */
	protected $conditionalArgs = [ 'body', 'tools', 'badges' ];

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | '',
	 * @param CacheHelper|null $cacheHelper
	 * @param TemplateFactory|null $templateFactory
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '', CacheHelper $cacheHelper = null,
		TemplateFactory $templateFactory = null ) {
		parent::__construct(
			$config,
			$params,
			$linkRenderer,
			$context,
			$name,
			$cacheHelper,
			$templateFactory
		);

		$this->params = $params;

		$this->panelInterface = $params->get( self::PARAM_INSTANCE, null );
		if ( $this->panelInterface instanceof IPanel === false ) {
			throw new \Exception( 'No IPanel provided!' );
		}
		$this->args = array_merge( $this->args, $this->getTemplateArgs() );
	}

	/**
	 *
	 * @return string
	 */
	public function getTemplateName() {
		return 'BlueSpiceCalumma.Calumma.Components.Panel';
	}

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$args = [];
		$args['id'] = $this->panelInterface->getHtmlId();

		$args['title'] = $this->panelInterface->getTitleMessage();
		$args['badge'] = $this->panelInterface->getBadge();
		$args['tool'] = $this->panelInterface->getTool();

		$args['body'] = $this->panelInterface->getBody();

		$args['classes'] = $this->panelInterface->getContainerClasses();
		$args['data'] = $this->panelInterface->getContainerData();

		$args['data'] += [
			'trigger-callback' =>
				$this->panelInterface->getTriggerCallbackFunctionName(),
			'trigger-rl-deps' => \FormatJson::encode(
				$this->panelInterface->getTriggerRLDependencies()
			)
		];
		$args['trigger-type'] = 'body';
		$args['toggle-collapse'] = true;
		$args['collapse'] = $this->panelInterface->getPanelCollapseState();

		if ( $this->panelInterface instanceof IFlyout ) {
			$this->addIFlyoutArgs( $args, $this->panelInterface );
		}

		$this->reformatDataAttributes( $args );
		$this->setConditionalFlags( $args );

		return $args;
	}

	/**
	 *
	 * @param array &$args
	 */
	protected function setConditionalFlags( &$args ) {
		foreach ( $this->conditionalArgs as $argName ) {
			if ( !empty( $args[$argName] ) ) {
				$args["has$argName"] = true;
			}
		}
	}

	/**
	 *
	 * @param array &$args
	 * @param IFlyout $flyout
	 */
	protected function addIFlyoutArgs( &$args, IFlyout $flyout ) {
		$title = $flyout->getFlyoutTitleMessage();
		if ( $title instanceof \Message ) {
			$title = $title->text();
		}
		$intro = $flyout->getFlyoutIntroMessage();
		if ( $intro instanceof \Message ) {
			$intro = $intro->text();
		}

		$args['data'] += [
			'flyout-title' => $title,
			'flyout-intro' => $intro,
		];

		$args['trigger-type'] = 'flyout';
		$args['toggle-collapse'] = false;
	}

	/**
	 *
	 * @param array &$args
	 */
	protected function reformatDataAttributes( &$args ) {
		$newData = [];
		foreach ( $args['data'] as $key => $value ) {
			$newData[] = [
				'key' => $key,
				'value' => $value
			];
		}

		$args['data'] = $newData;
	}

}
