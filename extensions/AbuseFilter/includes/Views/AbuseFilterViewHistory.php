<?php

use MediaWiki\Extension\AbuseFilter\AbuseFilterServices;
use MediaWiki\Linker\LinkRenderer;

class AbuseFilterViewHistory extends AbuseFilterView {
	/** @var int|null */
	private $filter;

	/**
	 * @param IContextSource $context
	 * @param LinkRenderer $linkRenderer
	 * @param string $basePageName
	 * @param array $params
	 */
	public function __construct(
		IContextSource $context,
		LinkRenderer $linkRenderer,
		string $basePageName,
		array $params
	) {
		parent::__construct( $context, $linkRenderer, $basePageName, $params );
		$this->filter = $this->mParams['filter'] ?? null;
	}

	/**
	 * Shows the page
	 */
	public function show() {
		$out = $this->getOutput();
		$afPermManager = AbuseFilterServices::getPermissionManager();
		$out->enableOOUI();
		$filter = $this->getRequest()->getIntOrNull( 'filter' ) ?: $this->filter;

		if ( $filter ) {
			$out->setPageTitle( $this->msg( 'abusefilter-history' )->numParams( $filter ) );
		} else {
			$out->setPageTitle( $this->msg( 'abusefilter-filter-log' ) );
		}

		if ( $filter && AbuseFilterServices::getFilterLookup()->getFilter( $filter, false )->isHidden()
			&& !$afPermManager->canViewPrivateFilters( $this->getUser() )
		) {
			$out->addWikiMsg( 'abusefilter-history-error-hidden' );
			return;
		}

		// Useful links
		$links = [];
		if ( $filter ) {
			$links['abusefilter-history-backedit'] = $this->getTitle( $filter )->getFullURL();
		}

		foreach ( $links as $msg => $title ) {
			$links[$msg] =
				new OOUI\ButtonWidget( [
					'label' => $this->msg( $msg )->text(),
					'href' => $title
				] );
		}

		$backlinks =
			new OOUI\HorizontalLayout( [
				'items' => $links
			] );
		$out->addHTML( $backlinks );

		// For user
		$user = User::getCanonicalName( $this->getRequest()->getText( 'user' ), 'valid' );
		if ( $user ) {
			$out->addSubtitle(
				$this->msg(
					'abusefilter-history-foruser',
					// We don't really need to get a user ID
					Linker::userLink( 1, $user ),
					// For GENDER
					$user
				)->text()
			);
		}

		$formDescriptor = [
			'user' => [
				'type' => 'user',
				'name' => 'user',
				'default' => $user,
				'size' => '45',
				'label-message' => 'abusefilter-history-select-user'
			],
			'filter' => [
				'type' => 'int',
				'name' => 'filter',
				'default' => $filter ?: '',
				'size' => '45',
				'label-message' => 'abusefilter-history-select-filter'
			],
		];

		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );
		$htmlForm->setSubmitTextMsg( 'abusefilter-history-select-submit' )
			->setWrapperLegendMsg( 'abusefilter-history-select-legend' )
			->setAction( $this->getTitle( 'history' )->getLocalURL() )
			->setMethod( 'get' )
			->prepareForm()
			->displayForm( false );

		$pager = new AbuseFilterHistoryPager( $filter, $this, $user, $this->linkRenderer );

		$out->addParserOutputContent( $pager->getFullOutput() );
	}
}
