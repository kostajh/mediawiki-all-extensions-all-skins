<?php
/**
 * Class file for the TweetANew extension
 *
 * @addtogroup Extensions
 * @license GPL
 */
// TweetANew
class TweetANew {

	/**
	 * Register hooks depending on version
	 */
	public static function registerExtension() {
		global $wgHooks;
		if ( class_exists( MediaWiki\HookContainer\HookContainer::class ) ) {
			// MW 1.35+
			$wgHooks['PageSaveComplete'][] = 'TweetANew::TweetANewNewPageComplete';
		} else {
			$wgHooks['PageContentSaveComplete'][] = 'TweetANew::TweetANewNewPageContent';
			$wgHooks['PageContentInsertComplete'][] = 'TweetANew::TweetANewEditMade';
		}
	}

	/**
	 * Respond to the parser hook call to invoke the 'tweetanew' magic word
	 * @param Parser $parser
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setFunctionHook( 'tweetanew', [ self::class, 'doParserFunction' ] );
	}

	/**
	 * Add properties to the page based on the parser function details.
	 *
	 * @param Parser $parser
	 * @param string $twitter
	 *
	 * @return string
	 */
	public static function doParserFunction( Parser $parser, $twitter = '' ) {
		$parser->getOutput()->setExtensionData( 'tweetanew:twitter', $twitter );
		return '';
	}

	/**
	 * PageSaveComplete hook
	 *
	 * @param WikiPage $wikiPage
	 * @param User $user
	 * @param string $summary
	 * @param int $flags
	 * @param MediaWiki\Revision\RevisionRecord $revisionRecord
	 * @param MediaWiki\Storage\EditResult $editResult
	 */
	public static function TweetANewNewPageComplete( $wikiPage, $user, $summary, $flags, $revisionRecord, $editResult ) {
		self::TweetANewNewPageContent( $wikiPage, $user, null, $summary );
		self::TweetANewEditMade( $wikiPage, $user, null, $summary, (int)$revisionRecord->isMinor() );
	}

	/**
	 * Function for tweeting new wiki pages
	 *
	 * @param WikiPage $wikiPage
	 * @param User $user
	 * @param Content|null $content
	 * @param string $summary
	 *
	 * @return bool
	 */
	public static function TweetANewNewPageContent( $wikiPage, $user, $content, $summary ) {
		global $wgTweetANewTweet, $wgTweetANewText, $wgRequest;

		# Check if $wgTweetANewTweet['New'] is enabled or the Tweet checkbox was selected on the edit page
		if ( $wgRequest->getCheck( 'wpTweetANew' ) || $wgTweetANewTweet['New'] ) {
			# Check if page is in content namespace or if the Tweet checkbox was selected on the edit page
			if ( !MWNamespace::isContent( $wikiPage->getTitle()->getNamespace() )
				&& !$wgRequest->getCheck( 'wpTweetANew' )
				|| !self::isTweetANewAllowed( $wikiPage, $user )
			) {
				return true;
			}

			# Generate final url
			$finalurl = self::makeFinalUrl(
				$wikiPage->getTitle()->getFullURL()
			);

			# Generate $author based on $wgTweetANewText['RealName']
			if ( $wgTweetANewText['RealName'] ) {
				$author = $user->getRealName();
			} else {
				$author = $user->getName();
			}

			# Use metadata from template for page title
			$twitter = $wikiPage->getParserOutput( $wikiPage->makeParserOptions( 'canonical' ) )->getExtensionData( 'tweetanew:twitter' );
			if ( $twitter && $wgTweetANewTweet['TwitterAsTitle'] ) {
				$pagetitle = $twitter;
			} else {
				$pagetitle = $wikiPage->getTitle()->getText();
			}

			# Generate a random tweet texts based if $wgTweetANewText['NewRandom'] is true
			if ( $wgTweetANewText['NewRandom'] ) {
				# Setup switcher using max number set by $wgTweetANewText['NewRandomMax']
				$switcher = rand( 1, $wgTweetANewText['NewRandomMax'] );
				# Parse random text
				$tweet_text = wfMessage(
					'tweetanew-new' . $switcher,
					[ $pagetitle, $finalurl, $twitter ]
				)->text();
			} else {
				# Use default tweet message format
				$tweet_body = wfMessage(
					'tweetanew-newdefault',
					[ $pagetitle, $finalurl, $twitter ]
				)->text();
				$tweet_text = $tweet_body;
			}

			# Add author info if $wgTweetANewText['NewAuthor'] is true
			if ( $wgTweetANewText['NewAuthor'] ) {
				$tweet_text .= ' ' . wfMessage( 'tweetanew-authorcredit' )->text() . ' ' . $author;
			}

			# Add summary if $wgTweetANewText['NewSummary'] is true and summary text is entered
			if ( $summary && $wgTweetANewText['NewSummary'] ) {
				$tweet_text .= ' - ' . $summary;
			}

			# Call to function for assembling and trimming tweet (if necessary) - then connecting and sending tweet to Twitter
			self::makeSendTweet(
				$wikiPage,
				$tweet_text,
				$finalurl
			);

		}

		return true;
	}

	/**
	 * Function for tweeting edited articles
	 *
	 * @param WikiPage $wikiPage
	 * @param User $user
	 * @param Content|null $content
	 * @param string $summary
	 * @param bool $isMinor
	 *
	 * @return bool
	 */
	public static function TweetANewEditMade(
		WikiPage $wikiPage,
		$user,
		$content,
		$summary,
		$isMinor
	) {
		global $wgTweetANewTweet, $wgTweetANewText, $wgRequest;

		# Check if $wgTweetANewTweet['Edit'] is enabled or the Tweet checkbox was selected on the edit page
		if ( $wgRequest->getCheck( 'wpTweetANewEdit' ) || $wgTweetANewTweet['Edit'] ) {
			# Unless the tweet checkbox is selected, only proceeds if page is outside content namespace
			#   and if a minor edit, checks $wgTweetANewTweet['SkipMinor']
			# Also prevents new articles from processing as TweetANewNewArticle function is used instead
			if ( ( !MWNamespace::isContent( $wikiPage->getTitle()->getNamespace() )
					|| ( $isMinor !== 0 && $wgTweetANewTweet['SkipMinor'] )
					&& !$wgRequest->getCheck( 'wpTweetANewEdit' ) )
				|| $wikiPage->getTitle()->estimateRevisionCount() == 1
				|| !self::isTweetANewAllowed( $wikiPage, $user )
			) {
				return true;
			}

			# Determine the time and date of last modification - exit if newer than $wgTweetANewTweet['LessMinutesOld']
			# ToDo - there must be a cleaner way of doing this
			$dbr = wfGetDB( DB_REPLICA );
			$res = $dbr->select(
				'revision',
				[ 'rev_timestamp' ],
				[ 'rev_page' => $wikiPage->getId() ],
				__METHOD__,
				[ 'ORDER BY' => 'rev_id DESC', 'LIMIT' => '2' ]
			);
			$edittime = [];
			foreach ( $res as $row ) {
				$edittime[] = $row->rev_timestamp;
			}
			$edittimenow = mktime(
				substr( $edittime[0], 8, 2 ),
				substr( $edittime[0], 10, 2 ),
				substr( $edittime[0], 12 ),
				substr( $edittime[0], 4, 2 ),
				substr( $edittime[0], 6, 2 ),
				substr( $edittime[0], 0, 4 )
			);
			$edittimelast = mktime(
				substr( $edittime[1], 8, 2 ),
				substr( $edittime[1], 10, 2 ),
				substr( $edittime[1], 12 ),
				substr( $edittime[1], 4, 2 ),
				substr( $edittime[1], 6, 2 ),
				substr( $edittime[1], 0, 4 )
			);
			$edittimediv = $edittimenow - $edittimelast;
			if ( $edittimediv < ( $wgTweetANewTweet['LessMinutesOld'] * 60 ) ) {
				return true;
			}

			# Generate final url
			$finalurl = self::makeFinalUrl(
				$wikiPage->getTitle()->getFullURL()
			);

			# Generate $author based on $wgTweetANewText['RealName']
			if ( $wgTweetANewText['RealName'] ) {
				$author = $user->getRealName();
			} else {
				$author = $user->getName();
			}

			# Use metadata from template for $twitter entry
			$twitter = $wikiPage->getParserOutput( $wikiPage->makeParserOptions( 'canonical' ) )->getExtensionData( 'tweetanew:twitter' );

			# Use metadata from template or $twitter entry for page title
			if ( $twitter && $wgTweetANewTweet['TwitterAsTitle'] ) {
				$pagetitle = $twitter;
			} else {
				$pagetitle = $wikiPage->getTitle()->getText();
			}

			# Set $tweet_text entry
			$tweet_text = '';

			# Add prefix indication that edit is minor if enabled by $wgTweetANewText['Minor']
			if ( $isMinor !== 0 && $wgTweetANewText['Minor'] ) {
				$tweet_text = RequestContext::getMain()->msg( 'tweetanew-minoredit' )->text();
				# Add a space after the indicator if $wgTweetANewText['MinorSpace'] is true
				if ( $isMinor !== 0 && $wgTweetANewText['MinorSpace'] ) {
					$tweet_text .= ' ';
				}
			}

			# Generate a random tweet texts based if $wgTweetANewText['EditRandom'] is true
			if ( $wgTweetANewText['EditRandom'] ) {
				# Setup switcher using max number set by $wgTweetANewText['EditRandomMax']
				$switcher = rand( 1, $wgTweetANewText['EditRandomMax'] );
				# Parse random text
				$tweet_text .= RequestContext::getMain()->msg(
					'tweetanew-edit' . $switcher,
					[ $pagetitle, $finalurl, $twitter ]
				)->text();
			} else {
				# Use default tweet message format
				$tweet_body = RequestContext::getMain()->msg(
					'tweetanew-editdefault',
					[ $pagetitle, $finalurl, $twitter ]
				)->text();
				$tweet_text .= $tweet_body;
			}

			# Add author info if $wgTweetANewText['EditAuthor'] is true
			if ( $wgTweetANewText['EditAuthor'] ) {
				$tweet_text .= ' ' . RequestContext::getMain()->msg( 'tweetanew-authorcredit' )->text() . ' ' . $author;
			}

			# Add summary if $wgTweetANewText['EditSummary'] is true and summary text is entered
			if ( $summary && $wgTweetANewText['EditSummary'] ) {
				$tweet_text .= ' - ' . $summary;
			}

			# Call to function for preparing and sending tweet
			self::makeSendTweet(
				$wikiPage,
				$tweet_text,
				$finalurl
			);
		}

		return true;
	}

	/**
	 * Determine if Tweet should be sent.
	 *
	 * @param WikiPage $wikiPage
	 * @param User $user
	 *
	 * @return bool
	 */
	public static function isTweetANewAllowed( $wikiPage, $user ) {
		global $wgTweetANewTweet;
		return (
						!( $wikiPage->isRedirect() && $wgTweetANewTweet['SkipRedirect'] ) &&
						$user->isAllowed( $wgTweetANewTweet['UserGroup'] )
					 );
	}

	/**
	 * Function shortening url via outside service or leaving for t.co service if none are enabled
	 *
	 * @param string $longurl
	 *
	 * @return string
	 */
	public static function makeFinalUrl( $longurl ) {
		global $wgTweetANewBitly, $wgTweetANewGoogl;

		# Check setting to enable/disable use of bitly
		if ( $wgTweetANewBitly['Enable'] ) {
			# Generate url for bitly
			$shortened = "https://api-ssl.bitly.com/v3/shorten?longUrl="
				. urlencode( $longurl ) . "&login=" . $wgTweetANewBitly['Login']
				. "&apiKey=" . $wgTweetANewBitly['API'] . "&format=txt";

			# Get the url from bitly
			$response = Http::get( $shortened );
		} elseif ( $wgTweetANewGoogl['Enable'] ) {
			# Check setting to enable/disable use of goo.gl
			# Setup goo.gl
			$url = new GoogleURL( $wgTweetANewGoogl['API'] );

			# Generate url from goo.gl
			$response = $url->shorten( $longurl );
		} else {
			$response = $longurl;
		}

		return $response;
	}

	/**
	 * Function for connecting to Twitter, preparing and then sending tweet
	 *
	 * @param WikiPage $wikiPage
	 * @param string $tweet_text
	 * @param string $finalurl
	 *
	 * @return bool
	 */
	public static function makeSendTweet( $wikiPage, $tweet_text, $finalurl ) {
		global $wgTweetANewTwitter, $wgTweetANewBlocklist, $wgTweetANewBlacklist, $wgTweetANewCatBlocklist, $wgLang;

		# Backward compatability with old $wgTweetANewBlacklist setting
		if ( $wgTweetANewBlacklist && !$wgTweetANewBlocklist ) {
			$wgTweetANewBlocklist = $wgTweetANewBlacklist;
		}

		# Check if page is in either blocklists:
		# $wgTweetANewBlocklist for pages and $wgTweetANewCatBlocklist for categories
		if ( ( !in_array( $wikiPage->getTitle()->getText(), $wgTweetANewBlocklist ) )
			&& ( !in_array( $wikiPage->getCategories(), $wgTweetANewCatBlocklist ) ) ) {

			# Calculate length of tweet factoring in t.co
			if ( stripos( $finalurl, 'https:' ) !== false ) {
				$tweet_text_count = 280 - 23 + mb_strlen( $finalurl );
			} elseif ( stripos( $finalurl, 'http:' ) !== false ) {
				$tweet_text_count = 280 - 22 + mb_strlen( $finalurl );
			} else {
				$tweet_text_count = 280;
			}

			# Check if length of tweet is beyond 280 characters and shorten if necessary
			if ( mb_strlen( $tweet_text ) > $tweet_text_count ) {
				$tweet_text = $wgLang->mb_substr( $tweet_text, 0, $tweet_text_count - 3 ) . '...';
			}

			# Make connection to Twitter
			$tmhOAuth = new tmhOAuth(
				[
					'consumer_key' => $wgTweetANewTwitter['ConsumerKey'],
					'consumer_secret' => $wgTweetANewTwitter['ConsumerSecret'],
					'user_token' => $wgTweetANewTwitter['AccessToken'],
					'user_secret' => $wgTweetANewTwitter['AccessTokenSecret'],
				]
			);

			# Make tweet message
			$tmhOAuth->request(
				'POST',
				$tmhOAuth->url( '1.1/statuses/update' ),
				[ 'status' => $tweet_text ]
			);

			return true;
		}
	}

	/**
	 * Function for tweeting about new or edited articles when auto-tweet if disabled
	 *
	 * @param EditPage $editpage
	 * @param array &$checkboxes
	 * @param null|int &$tabindex
	 *
	 * @return bool
	 */
	public static function TweetANewEditCheckBox( $editpage, &$checkboxes, &$tabindex = null ) {
		global $wgTweetANewEditpage, $wgTweetANewTweet, $wgUser;

		$options = [
			'label-message' => null,
			'id' => null,
			'default' => $wgTweetANewEditpage['Checked'],
			'title-message' => null,
			'legacy-name' => 'twitter',
		];
		# Check if article is new, if checkboxes are enabled, if user has permission, and if auto-tweets of edits are disabled
		if ( $editpage->mTitle->exists() &&
			$wgTweetANewEditpage['Enable'] &&
			!$wgTweetANewTweet['Edit'] &&
			$wgUser->isAllowed( $wgTweetANewTweet['UserGroup'] )
		) {
			$attribs = [
				'accesskey' => wfMessage( 'tweetanew-accesskey' )->text()
			];
			$options['title-message'] = 'tweetanew-edittooltip';
			$options['label-message'] = 'tweetanew-editaction';
			$options['id'] = 'wpTweetANewEdit';
			$name = 'wpTweetANewEdit';
		} elseif ( $wgTweetANewEditpage['Enable'] && !$wgTweetANewTweet['New'] ) {
			# Check if article is new - if checkboxes are enabled and if auto-tweets of new articles are disabled
			$attribs = [
				'accesskey' => wfMessage( 'tweetanew-accesskey' )->text()
			];

			$options['title-message'] = 'tweetanew-newtooltip';
			$options['label-message'] = 'tweetanew-newaction';
			$options['id'] = 'wpTweetANew';
			$name = 'wpTweetANew';
		} else {
			return true;
		}

		if ( $tabindex === null ) {
			$checkboxes[$name] = $options;
		} else {
			$checkbox = Xml::check(
				$name,
				$options['default'],
				$attribs + [ 'tabindex' => ++$tabindex ]
			);
			$attribs = [ 'for' => $options['id'] ];
			$attribs['title'] = wfMessage( $options['title-message'] )->text();
			$label = Xml::tags( 'label', $attribs, wfMessage( $options['label-message'] )->escaped() );
			$checkboxes[ $options['legacy-name'] ] = $checkbox . '&#160;' . $label;
		}
		return true;
	}
}
