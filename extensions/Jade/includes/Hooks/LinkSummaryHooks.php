<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Jade\Hooks;

use Jade\EntitySummarizer;
use Jade\JadeServices;
use Jade\TitleHelper;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Storage\EditResult;
use MediaWiki\User\UserIdentity;
use WikiPage;

class LinkSummaryHooks {

	/**
	 * Update link tables after a new entity page is inserted.
	 * Update link summary after an entity page is edited.
	 *
	 * @param WikiPage $entityPage WikiPage modified
	 * @param UserIdentity $userIdentity User performing the modification
	 * @param string $summary Edit summary/comment
	 * @param int $flags Flags passed to WikiPage::doEditContent()
	 * @param RevisionRecord $revisionRecord revision of the saved content.
	 * @param EditResult $editResult
	 */
	public static function onPageSaveComplete(
		WikiPage $entityPage,
		UserIdentity $userIdentity,
		string $summary,
		int $flags,
		RevisionRecord $revisionRecord,
		EditResult $editResult
	) {
		$status = TitleHelper::parseTitleValue( $entityPage->getTitle()->getTitleValue() );
		if ( !$status->isOK() ) {
			return;
		}
		$target = $status->value;

		if ( $flags & EDIT_NEW ) {
			JadeServices::getEntityIndexStorage()->insertIndex( $target, $entityPage );
		}

		$content = $entityPage->getContent();
		$status = EntitySummarizer::getSummaryFromContent( $content );
		if ( !$status->isOK() ) {
			LoggerFactory::getInstance( 'Jade' )->warning(
				'Failed to extract entity summary: {status}',
				[ 'status' => $status ]
			);

			return;
		}
		$summaryValues = $status->value;
		$status = JadeServices::getEntityIndexStorage()->updateSummary(
			$target,
			$summaryValues
		);

		if ( !$status->isOK() ) {
			LoggerFactory::getInstance( 'Jade' )->warning(
				'Failed to update entity summary: {status}',
				[ 'status' => $status ]
			);
		}
	}

}
