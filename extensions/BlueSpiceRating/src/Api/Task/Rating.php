<?php
/**
 * Provides the base api for Rating.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Patric Wirth
 * @package    Bluespice_pro
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

namespace BlueSpice\Rating\Api\Task;

use BlueSpice\Rating\Data\Record;

/**
 * Api base class for simple tasks in BlueSpice
 * @package BlueSpice_pro
 */
class Rating extends \BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = [
		'vote',
		'reload',
	];

	/**
	 * Returns an array of tasks and their required permissions
	 * array('taskname' => array('read', 'edit'))
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'vote' => [ 'read' ],
			'reload' => [ 'read' ],
		];
	}

	/**
	 *
	 * @param \stdClass $taskData
	 * @param array $aParams
	 * @return \stdClass
	 */
	public function task_vote( $taskData, $aParams ) {
		$result = $this->makeStandardReturn();
		$this->checkPermissions();

		$ratingFactory = $this->getServices()->getService( 'BSRatingFactory' );
		$status = $ratingFactory->ensureBasicParams( $taskData );
		if ( !$status->isOK() ) {
			$result->message = $status->getHTML();
			return $result;
		}
		$rating = $ratingFactory->newFromObject( $taskData );
		$title = null;
		if ( !isset( $taskData->{Record::VALUE} ) ) {
			$taskData->{Record::VALUE} = false;
		}
		if ( !empty( $taskData->articleid ) ) {
			$title = \Title::newFromID( $taskData->articleid );
		}
		if ( !empty( $taskData->titletext ) ) {
			$title = \Title::newFromText( $taskData->titletext );
		}

		$status = $rating->vote(
			$this->getUser(),
			$taskData->{Record::VALUE},
			$this->getUser(),
			0,
			$title
		);
		if ( !$status->isOK() ) {
			$result->message = $status->getHTML();
			return $result;
		}

		if ( $title ) {
			$title->invalidateCache();
		}

		$result->success = true;
		$result->payload['data'] = \FormatJson::encode( $rating );

		return $result;
	}

	/**
	 *
	 * @param \stdClass $taskData
	 * @param array $aParams
	 * @return \stdClass
	 */
	public function task_reload( $taskData, $aParams ) {
		$result = $this->makeStandardReturn();
		$this->checkPermissions();

		$ratingFactory = $this->getServices()->getService( 'BSRatingFactory' );
		$status = $ratingFactory->ensureBasicParams( $taskData );
		if ( !$status->isOK() ) {
			$result->message = $status->getHTML();
			return $result;
		}
		$rating = $ratingFactory->newFromObject( $taskData );

		$result->success = true;
		$result->payload['data'] = \FormatJson::encode( $rating );

		return $result;
	}

	/**
	 * Returns an array of allowed parameters
	 * @return array
	 */
	protected function getAllowedParams() {
		return parent::getAllowedParams() + [

		];
	}

	/**
	 * Returns the basic param descriptions
	 * @return array
	 */
	public function getParamDescription() {
		return parent::getParamDescription() + [

		];
	}

}
