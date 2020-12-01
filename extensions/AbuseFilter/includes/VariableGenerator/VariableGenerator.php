<?php

namespace MediaWiki\Extension\AbuseFilter\VariableGenerator;

use AbuseFilterVariableHolder;
use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterHookRunner;
use RecentChange;
use Title;
use User;
use WikiPage;

/**
 * Class used to generate variables, for instance related to a given user or title.
 */
class VariableGenerator {
	/**
	 * @var AbuseFilterVariableHolder
	 */
	protected $vars;

	/** @var AbuseFilterHookRunner */
	private $hookRunner;

	/**
	 * @param AbuseFilterVariableHolder $vars
	 */
	public function __construct( AbuseFilterVariableHolder $vars ) {
		$this->vars = $vars;

		// TODO this class is constructed in other extensions; make this a parameter
		$this->hookRunner = AbuseFilterHookRunner::getRunner();
	}

	/**
	 * @return AbuseFilterVariableHolder
	 */
	public function getVariableHolder() : AbuseFilterVariableHolder {
		return $this->vars;
	}

	/**
	 * Computes all variables unrelated to title and user. In general, these variables may be known
	 * even without an ongoing action.
	 *
	 * @param RecentChange|null $rc If the variables should be generated for an RC entry,
	 *   this is the entry. Null if it's for the current action being filtered.
	 * @return $this For chaining
	 */
	public function addGenericVars( RecentChange $rc = null ) : self {
		// These are lazy-loaded just to reduce the amount of preset variables, but they
		// shouldn't be expensive.
		$this->vars->setLazyLoadVar( 'wiki_name', 'get-wiki-name', [] );
		$this->vars->setLazyLoadVar( 'wiki_language', 'get-wiki-language', [] );

		$this->hookRunner->onAbuseFilterGenerateGenericVars( $this->vars, $rc );
		return $this;
	}

	/**
	 * @param User $user
	 * @param RecentChange|null $rc If the variables should be generated for an RC entry,
	 *   this is the entry. Null if it's for the current action being filtered.
	 * @return $this For chaining
	 */
	public function addUserVars( User $user, RecentChange $rc = null ) : self {
		$this->vars->setLazyLoadVar(
			'user_editcount',
			'simple-user-accessor',
			[ 'user' => $user, 'method' => 'getEditCount' ]
		);

		$this->vars->setVar( 'user_name', $user->getName() );

		$this->vars->setLazyLoadVar(
			'user_emailconfirm',
			'simple-user-accessor',
			[ 'user' => $user, 'method' => 'getEmailAuthenticationTimestamp' ]
		);

		$this->vars->setLazyLoadVar(
			'user_age',
			'user-age',
			[ 'user' => $user, 'asof' => wfTimestampNow() ]
		);

		$this->vars->setLazyLoadVar(
			'user_groups',
			'simple-user-accessor',
			[ 'user' => $user, 'method' => 'getEffectiveGroups' ]
		);

		$this->vars->setLazyLoadVar(
			'user_rights',
			'simple-user-accessor',
			[ 'user' => $user, 'method' => 'getRights' ]
		);

		$this->vars->setLazyLoadVar(
			'user_blocked',
			'user-block',
			[ 'user' => $user ]
		);

		$this->hookRunner->onAbuseFilterGenerateUserVars( $this->vars, $user, $rc );

		return $this;
	}

	/**
	 * @param Title $title
	 * @param string $prefix
	 * @param RecentChange|null $rc If the variables should be generated for an RC entry,
	 *   this is the entry. Null if it's for the current action being filtered.
	 * @return $this For chaining
	 */
	public function addTitleVars(
		Title $title,
		string $prefix,
		RecentChange $rc = null
	) : self {
		$this->vars->setVar( $prefix . '_id', $title->getArticleID() );
		$this->vars->setVar( $prefix . '_namespace', $title->getNamespace() );
		$this->vars->setVar( $prefix . '_title', $title->getText() );
		$this->vars->setVar( $prefix . '_prefixedtitle', $title->getPrefixedText() );

		// We only support the default values in $wgRestrictionTypes. Custom restrictions wouldn't
		// have i18n messages. If a restriction is not enabled we'll just return the empty array.
		$types = [ 'edit', 'move', 'create', 'upload' ];
		foreach ( $types as $action ) {
			$this->vars->setLazyLoadVar( "{$prefix}_restrictions_$action", 'get-page-restrictions',
				[ 'title' => $title->getText(),
					'namespace' => $title->getNamespace(),
					'action' => $action
				]
			);
		}

		$this->vars->setLazyLoadVar( "{$prefix}_recent_contributors", 'load-recent-authors',
			[
				'title' => $title->getText(),
				'namespace' => $title->getNamespace()
			] );

		$this->vars->setLazyLoadVar( "{$prefix}_age", 'page-age',
			[
				'title' => $title->getText(),
				'namespace' => $title->getNamespace(),
				'asof' => wfTimestampNow()
			] );

		$this->vars->setLazyLoadVar( "{$prefix}_first_contributor", 'load-first-author',
			[
				'title' => $title->getText(),
				'namespace' => $title->getNamespace()
			] );

		$this->hookRunner->onAbuseFilterGenerateTitleVars( $this->vars, $title, $prefix, $rc );

		return $this;
	}

	/**
	 * @param Title $title
	 * @param WikiPage $page
	 * @return $this For chaining
	 */
	public function addEditVars( Title $title, WikiPage $page ) : self {
		$this->vars->setLazyLoadVar( 'edit_diff', 'diff-array',
			[ 'oldtext-var' => 'old_wikitext', 'newtext-var' => 'new_wikitext' ] );
		$this->vars->setLazyLoadVar( 'edit_diff_pst', 'diff-array',
			[ 'oldtext-var' => 'old_wikitext', 'newtext-var' => 'new_pst' ] );
		$this->vars->setLazyLoadVar( 'new_size', 'length', [ 'length-var' => 'new_wikitext' ] );
		$this->vars->setLazyLoadVar( 'old_size', 'length', [ 'length-var' => 'old_wikitext' ] );
		$this->vars->setLazyLoadVar( 'edit_delta', 'subtract-int',
			[ 'val1-var' => 'new_size', 'val2-var' => 'old_size' ] );

		// Some more specific/useful details about the changes.
		$this->vars->setLazyLoadVar( 'added_lines', 'diff-split',
			[ 'diff-var' => 'edit_diff', 'line-prefix' => '+' ] );
		$this->vars->setLazyLoadVar( 'removed_lines', 'diff-split',
			[ 'diff-var' => 'edit_diff', 'line-prefix' => '-' ] );
		$this->vars->setLazyLoadVar( 'added_lines_pst', 'diff-split',
			[ 'diff-var' => 'edit_diff_pst', 'line-prefix' => '+' ] );

		// Links
		$this->vars->setLazyLoadVar( 'added_links', 'link-diff-added',
			[ 'oldlink-var' => 'old_links', 'newlink-var' => 'all_links' ] );
		$this->vars->setLazyLoadVar( 'removed_links', 'link-diff-removed',
			[ 'oldlink-var' => 'old_links', 'newlink-var' => 'all_links' ] );
		$this->vars->setLazyLoadVar( 'new_text', 'strip-html',
			[ 'html-var' => 'new_html' ] );

		$this->vars->setLazyLoadVar( 'all_links', 'links-from-wikitext',
			[
				'namespace' => $title->getNamespace(),
				'title' => $title->getText(),
				'text-var' => 'new_wikitext',
				'article' => $page
			] );
		$this->vars->setLazyLoadVar( 'old_links', 'links-from-wikitext-or-database',
			[
				'namespace' => $title->getNamespace(),
				'title' => $title->getText(),
				'text-var' => 'old_wikitext'
			] );
		$this->vars->setLazyLoadVar( 'new_pst', 'parse-wikitext',
			[
				'namespace' => $title->getNamespace(),
				'title' => $title->getText(),
				'wikitext-var' => 'new_wikitext',
				'article' => $page,
				'pst' => true,
			] );
		$this->vars->setLazyLoadVar( 'new_html', 'parse-wikitext',
			[
				'namespace' => $title->getNamespace(),
				'title' => $title->getText(),
				'wikitext-var' => 'new_wikitext',
				'article' => $page
			] );

		return $this;
	}
}
