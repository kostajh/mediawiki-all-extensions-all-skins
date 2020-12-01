<?php

namespace BlueSpice\WikiExplorer;

use MediaWiki\MediaWikiServices;

class Extension extends \BlueSpice\Extension {

	/**
	 * Adds Special:WikiExplorer link to wiki wide widget
	 * @param UserSidebar $oUserSidebar
	 * @param User $oUser
	 * @param array &$aLinks
	 * @param string &$sWidgetTitle
	 * @return bool
	 */
	public function onBSUserSidebarGlobalActionsWidgetGlobalActions( UserSidebar $oUserSidebar,
		User $oUser, &$aLinks, &$sWidgetTitle ) {
		$oSpecialWikiExplorer = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getPage( 'WikiExplorer' );
		if ( !$oSpecialWikiExplorer ) {
			return true;
		}
		$aLinks[] = [
			'target' => $oSpecialWikiExplorer->getPageTitle(),
			'text' => $oSpecialWikiExplorer->getDescription(),
			'attr' => [],
			'position' => 900,
			'permissions' => [
				'read'
			],
		];
		return true;
	}

	/**
	 *
	 * @param unused $start
	 * @param unused $limit
	 * @param unused $sort
	 * @param unused $dir
	 * @return string
	 */
	public static function getMetadata( $start, $limit, $sort, $dir ) {
		$output = [
			'idProperty' => 'page_id',
			'root' => 'results',
			'totalProperty' => 'total',
			'successProperty' => 'success',
			'fields' => [
				[ 'name' => 'page_id', "type" => "int" ],
				[ 'name' => 'page_title', "type" => "string" ],
				[ 'name' => 'page_namespace', "type" => "int" ],
				[ 'name' => 'page_created', "type" => "string" ],
				[ 'name' => 'page_is_redirect', "type" => "boolean" ],
				[ 'name' => 'rev_comment', "type" => "string" ],
				[ 'name' => 'page_touched', "type" => "string" ],
				[ 'name' => 'page_len', "type" => "int" ],
				[ 'name' => 'rev_user_text', "type" => "string" ],
				[ 'name' => 'page_categories', "type" => "auto" ],
				[ 'name' => 'page_linked_files', "type" => "auto" ],
				[ 'name' => 'page_links', "type" => "auto" ],
			],
			'sortInfo' => [
				'field' => 'page_title',
				'direction' => 'ASC'
			],
			'columns' => self::getColumnDefinitions(),
		];

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'WikiExplorer::getFieldDefinitions',
			[
				&$output['fields'],
			]
		);

		return $output;
	}

	/**
	 *
	 * @return array
	 */
	public static function getColumnDefinitions() {
		$aColumns = [ [
				'text' => wfMessage( 'bs-wikiexplorer-page-title' )->plain(),
				'id' => 'page_title',
				'width' => 200,
				'dataIndex' => 'page_title',
				'filter' => [ 'type' => 'string' ],
				'sortable' => true,
			], [
				'text' => wfMessage( 'bs-wikiexplorer-page-namespace' )->plain(),
				'id' => 'page_namespace',
				'dataIndex' => 'page_namespace',
				'filter' => [ 'type' => 'string' ],
				'sortable' => true,
			], [
				'text' => wfMessage( 'bs-wikiexplorer-creation-date' )->plain(),
				'id' => 'page_created',
				'dataIndex' => 'page_created',
				'hidden' => true,
				'filter' => [ 'type' => 'date', 'dataFormat' => 'YmdHis' ],
				'sortable' => true,
			], [
				'text' => wfMessage( 'bs-wikiexplorer-is-redirect' )->plain(),
				'id' => 'page_is_redirect',
				'dataIndex' => 'page_is_redirect',
				'hidden' => true,
				'filter' => [ 'type' => 'boolean' ],
				'sortable' => true,
			], [
				'text' => wfMessage( 'bs-wikiexplorer-last-comment' )->plain(),
				'id' => 'rev_comment',
				'dataIndex' => 'rev_comment',
				'hidden' => true,
				'filter' => [ 'type' => 'string' ],
				'sortable' => true,
			], [
				'text' => wfMessage( 'bs-wikiexplorer-last-edit-time' )->plain(),
				'id' => 'page_touched',
				'dataIndex' => 'page_touched',
				'hidden' => true,
				'filter' => [ 'type' => 'date', 'dataFormat' => 'YmdHis' ],
				'sortable' => true,
			], [
				'text' => wfMessage( 'bs-wikiexplorer-page-size' )->plain(),
				'id' => 'page_len',
				'dataIndex' => 'page_len',
				'filter' => [ 'type' => 'numeric' ],
				'sortable' => true,
			], [
				'text' => wfMessage( 'bs-wikiexplorer-last-author' )->plain(),
				'id' => 'rev_user_text',
				'dataIndex' => 'rev_user_text',
				'hidden' => true,
				'filter' => [ 'type' => 'string' ],
				'sortable' => true,
			], [
				'text' => wfMessage( 'bs-wikiexplorer-categories' )->plain(),
				'id' => 'page_categories',
				'dataIndex' => 'page_categories',
				'hidden' => true,
				'filter' => [ 'type' => 'string' ],
				'sortable' => true,
			], [
				'text' => wfMessage( 'bs-wikiexplorer-linked-files' )->plain(),
				'id' => 'page_linked_files',
				'dataIndex' => 'page_linked_files',
				'hidden' => true,
				'filter' => [ 'type' => 'string' ],
				'sortable' => true,
			], [
				'text' => wfMessage( 'bs-wikiexplorer-linked-pages' )->plain(),
				'id' => 'page_links',
				'dataIndex' => 'page_links',
				'hidden' => true,
				'filter' => [ 'type' => 'string' ],
				'sortable' => true,
			] ];

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'WikiExplorer::getColumnDefinitions',
			[
				&$aColumns,
			]
		);

		return $aColumns;
	}

}
