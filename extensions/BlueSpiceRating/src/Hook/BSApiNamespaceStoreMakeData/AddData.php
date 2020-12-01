<?php

namespace BlueSpice\Rating\Hook\BSApiNamespaceStoreMakeData;

use BlueSpice\NamespaceManager\Hook\BSApiNamespaceStoreMakeData;

class AddData extends BSApiNamespaceStoreMakeData {

	protected function doProcess() {
		$enabledNamespaces = $this->getConfig()->get( 'RatingArticleEnabledNamespaces' );
		$enabledLikeNamespaces = $this->getConfig()->get( 'RatingArticleLikeEnabledNamespaces' );

		foreach ( $this->results as $key => &$result ) {

			$result['rating'] = [
				'value' => in_array( $result[ 'id' ], $enabledNamespaces ),
				'disabled' => $result['isTalkNS']
			];

			$result['recommendations'] = [
				'value' => in_array( $result[ 'id' ], $enabledLikeNamespaces ),
				'disabled' => $result['isTalkNS']
			];

		}

		return true;
	}
}
