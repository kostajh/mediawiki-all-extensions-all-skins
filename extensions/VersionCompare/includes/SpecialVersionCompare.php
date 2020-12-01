<?php

class SpecialVersionCompare extends SpecialPage {

	public function __construct() {
		parent::__construct( 'VersionCompare' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $parser ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();
		$output->addModules( 'ext.VersionCompare' );

		$url1 = trim( $request->getText( 'url1' ) );
		$url2 = trim( $request->getText( 'url2' ) );
		$diff = $request->getBool( 'diff' );

		$formDescriptor = [
			'urlfield1' => [
				'label-message' => 'version-compare-url-field-label-1',
				'help-message' => 'version-compare-url-field-help-1',
				'class' => 'HTMLTextField',
				'default' => $url1,
				'name' => 'url1'
			],
			'urlfield2' => [
				'label-message' => 'version-compare-url-field-label-2',
				'help-message' => 'version-compare-url-field-help-2',
				'class' => 'HTMLTextField',
				'default' => $url2,
				'name' => 'url2'
			],
			'difffield' => [
				'label-message' => 'version-compare-diff-field-label',
				'class' => 'HTMLCheckField',
				'default' => false,
				'name' => 'diff'
			]
		];

		$htmlForm =
			HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );
		$htmlForm->setMethod( 'get' );
		$htmlForm->prepareForm()->displayForm( false );

		if ( $url1 === '' && $url1 !== $url2 ) {
			$url1 = $GLOBALS['wgServer'] . $GLOBALS['wgScriptPath'] . '/api.php';
		} elseif ( $url2 === '' && $url1 !== $url2 ) {
			$url2 = $GLOBALS['wgServer'] . $GLOBALS['wgScriptPath'] . '/api.php';
		}

		if ( $url1 !== '' && $url2 !== '' ) {
			$info1 = $this->getVersionInfo( $url1 );
			if ( $info1 === null ) {
				$html = Html::element( 'br' ) .
					Html::element( 'p', [ 'class' => 'error' ],
						wfMessage( 'version-compare-url-error', $url1 )->text()
					);
				$output->addHTML( $html );
				return;
			}

			$info2 = $this->getVersionInfo( $url2 );
			if ( $info2 === null ) {
				$html = Html::element( 'br' ) .
					Html::element( 'p', [ 'class' => 'error' ],
						wfMessage( 'version-compare-url-error', $url2 )->text()
					);
				$output->addHTML( $html );
				return;
			}

			$html = $this->compareWikis( $info1, $info2, $diff );
			$output->addHTML( $html );
		}
	}

	private function getVersionInfo( $url ) {
		$json = [];
		$query =
			"?action=query&meta=siteinfo&siprop=general%7Cextensions%7Cskins&format=json";
		\Wikimedia\suppressWarnings();
		$ret = file_get_contents( $url . $query );
		\Wikimedia\restoreWarnings();
		if ( $ret === false ) {
			return null;
		}
		$json = json_decode( $ret );
		if ( $json === null ) {
			return null;
		}
		return $this->getVersionArray( $json );
	}

	private static $wikiProperties = [
		'logo',
		'wikiid',
		'servername',
		'generator',
		'git-hash',
		'phpversion',
		'phpsapi',
		'dbtype',
		'dbversion'
	];

	private static $extensionProperties = [
		'version',
		'vcs-system',
		'vcs-version',
		'vcs-date'
	];

	private function getVersionArray( $info ) {
		if ( !property_exists( $info, 'query' ) ||
			!property_exists( $info->query, 'general' ) ||
			!property_exists( $info->query, 'extensions' ) ) {
			return null;
		}
		$ret = [];
		foreach ( self::$wikiProperties as $property ) {
			if ( property_exists( $info->query->general, $property ) ) {
				$ret[$property] = $info->query->general->$property;
			} else {
				$ret[$property] = '';
			}
		}
		$extensions = [];
		foreach ( $info->query->extensions as $extension ) {
			$e = [];
			foreach ( self::$extensionProperties as $property ) {
				if ( property_exists( $extension, $property ) ) {
					$e[$property] = $extension->$property;
				} else {
					$e[$property] = '';
				}
			}
			$extensions[$extension->name] = $e;
		}
		$ret['extensions'] = $extensions;
		return $ret;
	}

	private function compareWikis( $info1, $info2, $diff ) {
		$html = Html::openElement( 'table', [ 'class' => 'version-compare-table' ] );

		$html .= Html::openElement( 'tr' );
		$html .= Html::openElement( 'th' );
		$html .= Html::closeElement( 'th' );
		$html .= Html::openElement( 'th',
			[ 'class' => 'version-compare-wiki-1', 'width' => '35%' ] );
		$html .= Html::element( 'img', [ 'src' => $info1['logo'] ] );
		$html .= Html::openElement( 'p' );
		$html .= $info1['wikiid'];
		$html .= Html::closeElement( 'p' );
		$html .= Html::openElement( 'p' );
		$html .= $info1['servername'];
		$html .= Html::closeElement( 'p' );
		$html .= Html::closeElement( 'th' );
		$html .= Html::openElement( 'th',
			[ 'class' => 'version-compare-wiki-2', 'width' => '35%' ] );
		$html .= Html::element( 'img', [ 'src' => $info2['logo'] ] );
		$html .= Html::openElement( 'p' );
		$html .= $info2['wikiid'];
		$html .= Html::closeElement( 'p' );
		$html .= Html::openElement( 'p' );
		$html .= $info2['servername'];
		$html .= Html::closeElement( 'p' );
		$html .= Html::closeElement( 'th' );
		$html .= Html::closeElement( 'tr' );

		$html .=
			$this->formatRow( 'MediaWiki', $info1, $info2, [ 'generator', 'git-hash' ],
			$diff );
		$html .= $this->formatRow( 'PHP', $info1, $info2, [ 'phpversion', 'phpsapi' ],
			$diff );
		$html .=
			$this->formatRow( wfMessage( 'version-compare-database-label' )->text(),
			$info1, $info2, [ 'dbtype', 'dbversion' ], $diff );

		$extensions = [];
		foreach ( $info1['extensions'] as $key => $value ) {
			$extensions[] = $key;
		}
		foreach ( $info2['extensions'] as $key => $value ) {
			$extensions[] = $key;
		}
		$extensions = array_unique( $extensions );
		asort( $extensions );
		foreach ( $extensions as $value ) {
			if ( isset( $info1['extensions'][$value] ) ) {
				$extension1 = $info1['extensions'][$value];
			} else {
				$extension1 = null;
			}
			if ( isset( $info2['extensions'][$value] ) ) {
				$extension2 = $info2['extensions'][$value];
			} else {
				$extension2 = null;
			}
			$html .= $this->formatRow( $value, $extension1, $extension2,
				self::$extensionProperties, $diff );
		}

		$html .= Html::closeElement( 'table' );

		return $html;
	}

	private function formatRow( $label, $info1, $info2, $properties, $diff ) {
		$match = $this->compareFields( $info1, $info2, $properties );
		if ( $match && $diff ) {
			return;
		}
		$html = Html::openElement( 'tr' );
		if ( $match ) {
			$html .= Html::openElement( 'th', [ 'class' => 'version-compare-same' ] );
		} elseif ( $info1 === null ) {
			$html .= Html::openElement( 'th', [ 'class' => 'version-compare-wiki-2' ] );
		} elseif ( $info2 === null ) {
			$html .= Html::openElement( 'th', [ 'class' => 'version-compare-wiki-1' ] );
		} else {
			$html .=
				Html::openElement( 'th', [ 'class' => 'version-compare-different' ] );
		}
		$html .= Html::openElement( 'p' );
		$html .= $label;
		$html .= Html::closeElement( 'p' );
		$html .= Html::closeElement( 'th' );
		if ( $match ) {
			$html .= Html::openElement( 'td', [ 'colspan' => 2 ] );
		} else {
			$html .= Html::openElement( 'td' );
		}
		if ( $info1 === null ) {
			$html .= Html::openElement( 'p' );
			$html .= '&empty;';
			$html .= Html::closeElement( 'p' );
		} else {
			foreach ( $properties as $property ) {
				if ( isset( $info1[$property] ) ) {
					$html .= Html::openElement( 'p' );
					$html .= $info1[$property];
					$html .= Html::closeElement( 'p' );
				}
			}
		}
		if ( !$match ) {
			$html .= Html::closeElement( 'td' );
			$html .= Html::openElement( 'td' );
			if ( $info2 === null ) {
				$html .= Html::openElement( 'p' );
				$html .= '&empty;';
				$html .= Html::closeElement( 'p' );
			} else {
				foreach ( $properties as $property ) {
					if ( isset( $info2[$property] ) ) {
						$html .= Html::openElement( 'p' );
						$html .= $info2[$property];
						$html .= Html::closeElement( 'p' );
					}
				}
			}
		}
		$html .= Html::closeElement( 'td' );
		$html .= Html::closeElement( 'tr' );

		return $html;
	}

	private function compareFields( $info1, $info2, $properties ) {
		foreach ( $properties as $property ) {
			if ( !isset( $info1[$property] ) || !isset( $info2[$property] ) ||
				$info1[$property] !== $info2[$property] ) {
				return false;
			}
		}
		return true;
	}
}
