<?php

use MediaWiki\MediaWikiServices;

class PushAction extends Action {

	/**
	 * @var GlobalVarConfig
	 */
	private $pushConfig;

	public function __construct( Page $page, IContextSource $context = null ) {
		parent::__construct( $page, $context );
		// TODO get rid of eg prefix
		$this->pushConfig = new GlobalVarConfig( 'egPush' );
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return 'push';
	}

	public function getRestriction() {
		return 'push';
	}

	public function requiresWrite() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function show() {
		global $wgSitename;
		$output = $this->getOutput();
		$title = $this->getTitle();
		$pushTargets = $this->pushConfig->get( 'Targets' );

		$output->setPageTitle( wfMessage( 'push-tab-title', $title->getText() )->parse() );

		// Probably duplicate of getRestriction()
		if ( !$this->userHasRight( 'push' ) ) {
			throw new PermissionsError( 'push' );
		}

		$output->addHTML( '<p>' . wfMessage( 'push-tab-desc' )->escaped() . '</p>' );

		if ( count( $pushTargets ) == 0 ) {
			$output->addHTML( '<p>' . wfMessage( 'push-tab-no-targets' )->escaped() . '</p>' );
			return false;
		}

		$output->addModules( 'ext.push.tab' );

		$output->addHTML(
			Html::hidden( 'pageName', $title->getFullText(), [ 'id' => 'pageName' ] ) .
			Html::hidden( 'siteName', $wgSitename, [ 'id' => 'siteName' ] )
		);

		$this->displayPushList( $pushTargets );
		$this->displayPushOptions();
		return false;
	}

	/**
	 * Displays a list with all targets to which can be pushed.
	 *
	 * @param array $pushTargets
	 * @since 0.1
	 */
	private function displayPushList( array $pushTargets ) {
		$items = [
			Html::rawElement(
				'tr',
				[],
				Html::element(
					'th',
					[ 'width' => '200px' ],
					wfMessage( 'push-targets' )->text()
				) .
				Html::element(
					'th',
					[ 'style' => 'min-width:400px;' ],
					wfMessage( 'push-remote-pages' )->text()
				) .
				Html::element(
					'th',
					[ 'width' => '125px' ],
					''
				)
			)
		];

		foreach ( $pushTargets as $name => $url ) {
			$items[] = $this->getPushItem( $name, $url );
		}

		// If there is more then one item, display the 'push all' row.
		if ( count( $pushTargets ) > 1 ) {
			$items[] = Html::rawElement(
				'tr',
				[],
				Html::element(
					'th',
					[ 'colspan' => 2, 'style' => 'text-align: left' ],
					wfMessage( 'push-targets-total' )->numParams( count( $pushTargets ) )->parse()
				) .
				Html::rawElement(
					'th',
					[ 'width' => '125px' ],
					Html::element(
						'button',
						[
							'id' => 'push-all-button',
							'style' => 'width: 125px; height: 30px',
						],
						wfMessage( 'push-button-all' )->text()
					)
				)
			);
		}

		$this->getOutput()->addHTML(
			Html::rawElement(
				'table',
				[ 'class' => 'wikitable', 'width' => '50%' ],
				implode( "\n", $items )
			)
		);
	}

	/**
	 * Returns the HTML for a single push target.
	 *
	 * @param string $name
	 * @param string $url
	 *
	 * @return string
	 * @since 0.1
	 */
	private function getPushItem( string $name, string $url ) {
		static $targetId = 0;
		$targetId++;

		$title = $this->getTitle();
		return Html::rawElement(
			'tr',
			[],
			Html::element(
				'td',
				[],
				$name
			) .
			Html::rawElement(
				'td',
				[ 'height' => '45px' ],
				Html::element(
					'a',
					[
						'href' => $url . '/index.php?title=' . $title->getFullText(),
						'rel' => 'nofollow',
						'id' => 'targetlink' . $targetId
					],
					wfMessage( 'push-remote-page-link', $title->getFullText(), $name )->parse()
				) .
				Html::element(
					'div',
					[
						'id' => 'targetinfo' . $targetId,
						'style' => 'display:none; color:darkgray'
					]
				) .
				Html::element(
					'div',
					[
						'id' => 'targettemplateconflicts' . $targetId,
						'style' => 'display:none; color:darkgray'
					]
				) .
				Html::element(
					'div',
					[
						'id' => 'targetfileconflicts' . $targetId,
						'style' => 'display:none; color:darkgray'
					]
				) .
				Html::element(
					'div',
					[
						'id' => 'targeterrors' . $targetId,
						'style' => 'display:none; color:darkred'
					]
				)
			) .
			Html::rawElement(
				'td',
				[],
				Html::element(
					'button',
					[
						'class' => 'push-button',
						'pushtarget' => $url,
						'style' => 'width: 125px; height: 30px',
						'targetid' => $targetId,
						'targetname' => $name
					],
					wfMessage( 'push-button-text' )->text()
				)
			)
		);
	}

	/**
	 * Outputs the HTML for the push options.
	 *
	 * @since 0.4
	 */
	private function displayPushOptions() {
		$this->getOutput()->addHTML( '<h3>' . wfMessage( 'push-tab-push-options' )->escaped() . '</h3>' );

		$title = $this->getTitle();
		$usedTemplates = array_keys(
			PushFunctions::getTemplates(
				[ $title->getFullText() ],
				[ $title->getFullText() => true ]
			)
		);

		// Get rid of the page itself.
		array_shift( $usedTemplates );

		$this->displayIncTemplatesOption( $usedTemplates );

		if ( $this->userHasRight( 'filepush' ) ) {
			$this->displayIncFilesOption( $usedTemplates );
		}
	}

	/**
	 * Outputs the HTML for the "include templates" option.
	 *
	 * @since 0.4
	 *
	 * @param array $templates
	 */
	private function displayIncTemplatesOption( array $templates ) {
		$output = $this->getOutput();
		$output->addJsConfigVars( 'wgPushTemplates', $templates );

		foreach ( $templates as &$template ) {
			$template = "[[$template]]";
		}

		$pushIncTemplates = $this->pushConfig->get( 'IncTemplates' );
		$lang = $this->getLanguage();
		$output->addHTML(
			Html::rawElement(
				'div',
				[ 'id' => 'divIncTemplates', 'style' => 'display: table-row' ],
				Xml::check( 'checkIncTemplates', $pushIncTemplates, [ 'id' => 'checkIncTemplates' ] ) .
				Html::element(
					'label',
					[ 'id' => 'lblIncTemplates', 'for' => 'checkIncTemplates' ],
					wfMessage( 'push-tab-inc-templates' )->text()
				) .
				'&#160;' .
				Html::rawElement(
					'div',
					[ 'style' => 'display:none; opacity:0', 'id' => 'txtTemplateList' ],
					count( $templates ) > 0 ?
						wfMessage( 'push-tab-used-templates',
							$lang->listToText( $templates ), count( $templates ) )->parse() :
						wfMessage( 'push-tab-no-used-templates' )->escaped()
				)
			)
		);
	}

	/**
	 * Outputs the HTML for the "include files" option.
	 *
	 * @since 0.4
	 *
	 * @param array $templates
	 */
	private function displayIncFilesOption( array $templates ) {
		$allFiles = self::getImagesForPages( [ $this->getTitle()->getFullText() ] );
		$templateFiles = self::getImagesForPages( $templates );
		$pageFiles = [];

		foreach ( $allFiles as $file ) {
			if ( !in_array( $file, $templateFiles ) ) {
				$pageFiles[] = $file;
			}
		}

		$output = $this->getOutput();
		$pushIncFiles = $this->pushConfig->get( 'IncFiles' );
		$output->addJsConfigVars( [
			'wgPushPageFiles' => $pageFiles,
			'wgPushTemplateFiles' => $templateFiles,
			'wgPushIndexPath' => wfScript(),
		] );

		$output->addHTML(
			Html::rawElement(
				'div',
				[ 'id' => 'divIncFiles', 'style' => 'display: table-row' ],
				Xml::check( 'checkIncFiles', $pushIncFiles, [ 'id' => 'checkIncFiles' ] ) .
				Html::element(
					'label',
					[ 'id' => 'lblIncFiles', 'for' => 'checkIncFiles' ],
					wfMessage( 'push-tab-inc-files' )->text()
				) .
				'&#160;' .
				Html::rawElement(
					'div',
					[ 'style' => 'display:none; opacity:0', 'id' => 'txtFileList' ],
					''
				)
			)
		);
	}

	/**
	 * Returns the names of the images embedded in a set of pages.
	 *
	 * @param array $pages
	 *
	 * @return array
	 */
	protected static function getImagesForPages( array $pages ) {
		$images = [];

		$requestData = [
			'action' => 'query',
			'format' => 'json',
			'prop' => 'images',
			'titles' => implode( '|', $pages ),
			'imlimit' => 500
		];

		$api = new ApiMain( new FauxRequest( $requestData, true ), true );
		$api->execute();
		if ( defined( 'ApiResult::META_CONTENT' ) ) {
			$response = $api->getResult()->getResultData( null, [ 'Strip' => 'all' ] );
		} else {
			$response = $api->getResultData();
		}

		if (
			is_array( $response )
			&& array_key_exists( 'query', $response )
			&& array_key_exists( 'pages', $response['query'] )
		) {
			foreach ( $response['query']['pages'] as $page ) {
				if ( array_key_exists( 'images', $page ) ) {
					foreach ( $page['images'] as $image ) {
						$title = Title::newFromText( $image['title'], NS_FILE );

						if ( $title !== null && $title->getNamespace() == NS_FILE && $title->exists() ) {
							$images[] = $image['title'];
						}
					}
				}
			}
		}

		return array_unique( $images );
	}

	/**
	 * Testing a permission for current user
	 * @param string $action
	 * @return bool
	 */
	private function userHasRight( string $action ) {
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		return $pm->userHasRight( $this->getUser(), $action );
	}
}
