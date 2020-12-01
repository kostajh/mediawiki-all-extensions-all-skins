<?php

class ApiGetMarkAsHelpfulItem extends ApiBase {

	public function execute() {
		$user = $this->getUser();
		$params = $this->extractRequestParams();

		$page = Title::newFromText( $params['page'] );

		if ( !$page ) {
			throw new MWApiGetMarkAsHelpfulItemInvalidPageException( 'Invalid page!' );
		}

		// check if current user has permission to mark the item,
		$isAbleToMark = false;
		// check if the page has permission to request the item
		$isAbleToShow = false;

		Hooks::run( 'onMarkItemAsHelpful', [ $params['type'], $params['item'], $user, &$isAbleToMark, $page, &$isAbleToShow ] );

		if ( $isAbleToShow ) {
			$HelpfulUserList = MarkAsHelpfulItem::getMarkAsHelpfulList( $params['type'], $params['item'] );

			if ( $params['prop'] == 'metadata' ) {
				$data = $HelpfulUserList;
				$format = 'metadata';
			} else {
				$data = MarkAsHelpfulUtil::getMarkAsHelpfulTemplate(
					$user, $isAbleToMark, $HelpfulUserList, $params['type'],
					$params['item']
				);
				$format = 'formatted';
			}
		} else {
			$data = '';

			if ( $params['prop'] == 'metadata' ) {
				$format = 'metadata';
			} else {
				$format = 'formatted';
			}
		}

		$result = [ 'result' => 'success', $format => $data ];
		$this->getResult()->addValue( null, $this->getModuleName(), $result );
	}

	public function getAllowedParams() {
		global $wgMarkAsHelpfulType;

		return [
			'type' => [
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => $wgMarkAsHelpfulType,
			],
			'item' => [
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => 'integer'
			],
			'prop' => [
				ApiBase::PARAM_TYPE => [ 'metadata', 'formatted' ],
			],
			'page' => [
				ApiBase::PARAM_REQUIRED => true,
			],
		];
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return [
			'type' => 'The object type that is being marked as helpful',
			'item' => 'The object item that is being marked as helpful',
			'prop' => 'Which property to get',
			'page' => 'The page which is requesting the item',
		];
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Get a list of all helpful status for an object item';
	}

}

class MWApiGetMarkAsHelpfulItemInvalidPageException extends Exception {
}
