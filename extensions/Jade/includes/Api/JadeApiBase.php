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

namespace Jade\Api;

use ApiBase;
use Jade\EntityBuilder;
use Title;

/**
 * Base Api class to be inherited by all Jade Api modules.
 *
 * @license GPL-3.0-or-later
 * @author Andy Craze < acraze@wikimedia.org >
 * @author Kevin Bazira < kbazira@wikimedia.org >
 */

abstract class JadeApiBase extends ApiBase {

	/**
	 * @inheritDoc
	 */
	public function isWriteMode() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function needsToken() {
		return 'csrf';
	}

	/**
	 * @inheritDoc
	 */
	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Extension:Jade';
	}

	/**
	 * @param \StatusValue|string $status
	 */
	private function checkErrors( $status ) {
		if ( is_string( $status ) ) {
			$this->dieWithError( $status );
		} else {
			$errors = $status->getErrors();
			if ( !empty( $errors ) ) {
				$this->dieStatus( $status );
			}
		}
	}

	/**
	 * @param array|null $warnings
	 */
	private function checkWarnings( $warnings ) {
		if ( $warnings !== null ) {
			foreach ( $warnings as $warning ) {
				$this->addWarning( $warning );
			}
		}
	}

	public function buildResult( $data ) {
		list( $status, $entity, $warnings ) = $data;
		$this->checkErrors( $status );
		$this->checkWarnings( $warnings );
		$res = $this->getResult();
		$builder = new EntityBuilder( $this->getUser() );
		$params = $this->extractRequestParams();
		$title = $builder->resolveTitle( $params );
		$entityWithParsedWikitext =
			$builder->parseWikitextInProposalNotesAndEndorsementComments(
				$entity,
				Title::newFromText( $title, $defaultNamespace = NS_MAIN )
			);
		$res->addValue( null, "data", $entityWithParsedWikitext );
	}

}
