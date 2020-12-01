<?php

namespace BlueSpice\Bookshelf;

use BlueSpice\ExtensionAttributeBasedRegistry;
use Config;
use FormatJson;

class TreeParser {

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var ILineProcessor[]
	 */
	protected $lineProcessors = [];

	/**
	 *
	 * @var string
	 */
	protected $wikiText = '';

	/**
	 *
	 * @var string[]
	 */
	protected $lines = [];

	/**
	 *
	 * @var array
	 */
	protected $simpleTOC = [];

	/**
	 *
	 * @var array
	 */
	protected $extendedTOC = [];

	/**
	 *
	 * @var array
	 */
	protected $tree = [];

	/**
	 *
	 * @var string
	 */
	protected $jsonStringBuffer = '';

	/**
	 *
	 * @var array
	 */
	protected $defaultParams = [
		'indent-char' => '*',
		'suppress-number-in-text' => false
	];

	/**
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 *
	 * @param Config $config
	 * @param ExtensionAttributeBasedRegistry $lineParserRegistry
	 */
	public function __construct(
			Config $config, ExtensionAttributeBasedRegistry $lineParserRegistry ) {
		$this->config = $config;
		$this->initLineProcessors( $lineParserRegistry );
	}

	private function initLineProcessors( ExtensionAttributeBasedRegistry $lineParserRegistry ) {
		foreach ( $lineParserRegistry->getAllValues() as $lineParserFactory ) {
			if ( !is_callable( $lineParserFactory ) ) {
				continue;
			}
			$this->lineProcessors[] = call_user_func_array( $lineParserFactory, [] );
		}
	}

	/**
	 * Parses a WikiText formatted hierarchical list into an object tree
	 * @param string $wikiText
	 * @param array $params
	 * @return array
	 */
	public function parseWikiTextList( $wikiText, $params = [] ) {
		$this->params = array_merge( $this->defaultParams, $params );
		$this->wikiText = $wikiText;
		$this->tree = [];

		$this->initLines();
		$this->buildSimpleTOC();
		$this->buildExtendedTOC();
		$this->buildTree();

		return $this->tree;
	}

	private function initLines() {
		$this->lines = explode( "\n", $this->wikiText );
		$this->lines = array_map( 'rtrim', $this->lines );
	}

	/**
	 * Thanks to Sebastian Ulbricht!
	 */
	private function buildSimpleTOC() {
		$this->simpleTOC = [];

		$level = 0;
		$number = [];
		$indentChar = $this->params['indent-char'];

		foreach ( $this->lines as $line ) {
			// Is line empty or does not start with a valid indent character?
			if ( empty( $line ) || $line[0] != $indentChar ) {
				continue;
			}

			$depth = 0;
			$isIndentCharacter = true;
			// Count indent characters '*', ':', or '#' into $intDepth and cut them off
			do {
				if ( isset( $line[$depth] ) && $line[$depth] == $indentChar ) {
					$depth++;
				} else {
					$isIndentCharacter = false;
				}
			} while ( $isIndentCharacter );
			$line = substr( $line, $depth );

			// Skip line processing if empty
			$line = trim( $line );
			if ( empty( $line ) ) {
				continue;
			}

			if ( $depth > $level ) {
				while ( $depth > $level ) {
					array_push( $number, 1 );
					$level++;
				}
			} elseif ( $depth < $level ) {
				while ( $depth < $level ) {
					array_pop( $number );
					$level--;
					$number[$level - 1] ++;
				}
			} else {
				$number[$level - 1] ++;
			}
			$this->simpleTOC[] = [
				'number-array' => $number,
				'text' => trim( $line )
			];
		}
	}

	private function buildExtendedTOC() {
		foreach ( $this->simpleTOC as $listEntry ) {
			$aProcessedEntry = $this->processLine( $listEntry['text'] );
			$aProcessedEntry['number'] = implode( '.', $listEntry['number-array'] );
			$this->extendedTOC[] = $aProcessedEntry;
		}
	}

	/**
	 *
	 * @param string $line
	 * @return array
	 */
	private function processLine( $line ) {
		foreach ( $this->lineProcessors as $lineProcessor ) {
			if ( $lineProcessor->applies( $line ) ) {
				$result = $lineProcessor->process( $line );
				if ( $lineProcessor->isFinal() ) {
					break;
				}
			}
		}
		return $result->toArray();
	}

	private function buildTree() {
		$this->tree = [];
		$this->jsonStringBuffer = '[';

		$previousLevel = 1;
		$lineCount = count( $this->extendedTOC ) - 1;
		for ( $lineNo = 0; $lineNo <= $lineCount; $lineNo++ ) {
			$line = $this->extendedTOC[$lineNo];
			$currentLevel = count( explode( '.', $line['number'] ) );
			// avoid undefined index error
			$nextLevel = $this->getNextLevel( $lineNo );

			$text = $line['display-title'];
			if ( !$this->params['suppress-number-in-text'] ) {
					$text = $line['number'] . '. ' . $text;
			}

			$this->jsonStringBuffer .= '{';
			$this->jsonStringBuffer .=
					// ExtJS NodeInterface properties
					// TODO: Implement reasonabe node ids:
					// <page_id>/<page_id>/Page_title_with_out_escaped_slashes/<page_id>
					'"text": ' . FormatJson::encode( $text ) . ','
					. '"id": "' . $line['number'] . '",'
					. '"articleNumber": "' . $line['number'] . '",'
					. '"articleType": "' . $line['type'] . '",'
					. '"articleTitle": ' . FormatJson::encode( $line['title'] ) . ','
					. '"articleDisplayTitle": ' . FormatJson::encode( $line['display-title'] ) . ','
					. '"articleId": ' . $line['article-id'] . ','
					. '"articleIsRedirect": ' . FormatJson::encode( $line['is-redirect'] ) . ','

					// New data container
					. '"bookshelf": ' . FormatJson::encode( $line['bookshelf'] ) . ',';

			$this->jsonStringBuffer .= '"children": [';
			// Has no children
			if ( $currentLevel > $nextLevel ) {
				$this->jsonStringBuffer .= ']}';
				$levelDifference = $currentLevel - $nextLevel;
				for ( $n = 0; $n < $levelDifference; $n++ ) {
					$this->jsonStringBuffer .= ']}';
				}
				$this->jsonStringBuffer .= ',';
			} elseif ( $currentLevel == $nextLevel ) {
				$this->jsonStringBuffer .= ']},';
			}
			$previousLevel = $currentLevel;
		}
		// Cut off trailing comma
		$this->jsonStringBuffer = substr( $this->jsonStringBuffer, 0, -1 );
		$this->jsonStringBuffer .= ']';

		$this->tree = FormatJson::decode( $this->jsonStringBuffer, true );
	}

	private function getNextLevel( $lineNo ) {
		$hasNextLevel =
			$lineNo < count( $this->extendedTOC )
			&& isset( $this->extendedTOC[$lineNo + 1] );

		$nextLevel = $hasNextLevel
				? count( explode( '.', $this->extendedTOC[$lineNo + 1]['number'] ) )
				: 0;

		$nextLevel = ( $nextLevel == 0 ) ? 1 : $nextLevel;

		return $nextLevel;
	}

}
