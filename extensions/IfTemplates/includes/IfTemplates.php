<?php

use MediaWiki\MediaWikiServices;

class IfTemplates {

	/**
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @param string $titletext
	 * @param string $then string
	 * @param string $else
	 * @return string
	 *
	 * Much of this code is borrowed from the ifexist parser function
	 */
	public static function iftemplatesCommon( $parser, $frame, $titletext = '', $then = '', $else = '' ) {
		global $wgContLang;
		$title = Title::newFromText( $titletext );
		$wgContLang->findVariantLink( $titletext, $title, true );
		if ( $title ) {
			if ( $title->getNamespace() == NS_SPECIAL ) {
				/* PEBCAK */
				return $else;
			} elseif ( $title->isExternal() ) {
				/* Can't check the existence of pages on other sites,
				 * so just return $else.  Makes a sort of sense, since
				 * they don't exist _locally_.
				 */
				return $else;
			} else {
				$pdbk = $title->getPrefixedDBkey();
				if ( !$parser->incrementExpensiveFunctionCount() ) {
					return $else;
				}
				$lc = MediaWikiServices::getInstance()->getLinkCache();
				$id = $lc->getGoodLinkID( $pdbk );
				if ( $id != 0 ) {
					$parser->mOutput->addLink( $title, $id );
					$text = WikiPage::newFromId( $id )->getContent( Revision::RAW );
					if ( self::isTemplates( $parser, $frame, $text ) ) {
						return $then;
					} else {
						return $else;
					}
				} elseif ( $lc->isBadLink( $pdbk ) ) {
					$parser->mOutput->addLink( $title, 0 );
					return $else;
				}
				$id = $title->getArticleID();
				$parser->mOutput->addLink( $title, $id );
				if ( $id ) {
					$text = WikiPage::newFromId( $id )->getContent( Revision::RAW );
					if ( self::isTemplates( $parser, $frame, $text ) ) {
						return $then;
					} else {
						return $else;
					}
				}
			}
		}
		return $else;
	}

	/**
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @param array $args
	 * @return string
	 */
	public static function iftemplatesObj( $parser, $frame, $args ) {
		$title = isset( $args[0] ) ? trim( $frame->expand( $args[0] ) ) : '';
		$then = isset( $args[1] ) ? $args[1] : null;
		$else = isset( $args[2] ) ? $args[2] : null;
		$result = self::iftemplatesCommon( $parser, $frame, $title, $then, $else );
		if ( $result === null ) {
			return '';
		} else {
			return trim( $frame->expand( $result ) );
		}
	}

	/**
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @param string $text
	 * @return bool
	 *
	 * copied from PPFrame_DOM::expand() - probably needs a lot of cleanup
	 */
	public static function isTemplates( $parser, $frame, $text = '' ) {
		$root = $parser->preprocessToDom( $text );

		if ( $root instanceof PPNode_DOM ) {
			$root = $root->node;
		}
		if ( $root instanceof DOMDocument ) {
			$root = $root->documentElement;
		}

		$outStack = [ '', '' ];
		$iteratorStack = [ false, $root ];
		$indexStack = [ 0, 0 ];

		while ( count( $iteratorStack ) > 1 ) {
			$level = count( $outStack ) - 1;
			$iteratorNode =& $iteratorStack[ $level ];
			$out =& $outStack[$level];
			$index =& $indexStack[$level];

			if ( $iteratorNode instanceof PPNode_DOM ) {
				$iteratorNode = $iteratorNode->node;
			}

			if ( is_array( $iteratorNode ) ) {
				if ( $index >= count( $iteratorNode ) ) {
					// All done with this iterator
					$iteratorStack[$level] = false;
					$contextNode = false;
				} else {
					$contextNode = $iteratorNode[$index];
					$index++;
				}
			} elseif ( $iteratorNode instanceof DOMNodeList ) {
				if ( $index >= $iteratorNode->length ) {
					// All done with this iterator
					$iteratorStack[$level] = false;
					$contextNode = false;
				} else {
					$contextNode = $iteratorNode->item( $index );
					$index++;
				}
			} else {
				// Copy to $contextNode and then delete from iterator stack,
				// because this is not an iterator but we do have to execute it once
				$contextNode = $iteratorStack[$level];
				$iteratorStack[$level] = false;
			}

			if ( $contextNode instanceof PPNode_DOM ) {
				$contextNode = $contextNode->node;
			}

			$newIterator = false;

			if ( $contextNode === false ) {
				// nothing to do
			} elseif ( $contextNode instanceof DOMNode ) {
				if ( $contextNode->nodeType == XML_TEXT_NODE ) {
					// If a node exists which is text, this article has more than just templates
					return false;
				} elseif ( $contextNode->nodeName == 'template' ) {
				} elseif ( $contextNode->nodeName == 'tplarg' ) {
				} elseif ( $contextNode->nodeName == 'comment' ) {
				} elseif ( $contextNode->nodeName == 'ignore' ) {
				} elseif ( $contextNode->nodeName == 'ext' ) {
				} else {
					# Generic recursive expansion
					$newIterator = $contextNode->childNodes;
				}
			} else {
				throw new MWException( __METHOD__ . ': Invalid parameter type' );
			}

			if ( $newIterator !== false ) {
				if ( $newIterator instanceof PPNode_DOM ) {
					$newIterator = $newIterator->node;
				}
				$outStack[] = '';
				$iteratorStack[] = $newIterator;
				$indexStack[] = 0;
			} elseif ( $iteratorStack[$level] === false ) {
				// Return accumulated value to parent
				// With tail recursion
				while ( $iteratorStack[$level] === false && $level > 0 ) {
					array_pop( $iteratorStack );
					$level--;
				}
			}
		}
		return true;
	}
}
