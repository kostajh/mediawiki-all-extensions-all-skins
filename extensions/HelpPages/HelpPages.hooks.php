<?php

class HelpPagesHooks {
	/**
	 * @param Article $article
	 * @return bool
	 */
	public static function onShowMissingArticle( $article ) {
		$context = $article->getContext();
		$output = $context->getOutput();
		$title = $article->getTitle();
		if ( $title->getNamespace() == NS_HELP ) {
			list( $text, $oldid ) = HelpPages::getPagePlusFallbacks( 'Help:' . $title->getText() );
			if ( $text ) {
				// Add a notice indicating that it was taken from mediawiki.org
				$output->addHTML( $context->msg( 'helppages-notice', $oldid )->parse() );
				$output->addHTML( $text );
				// Hide the "this page does not exist" notice and edit section links
				$output->addModuleStyles( 'ext.HelpPages' );
			}
		}
		return true;
	}

	/**
	 * @param SkinTemplate &$sktemplate
	 * @param array &$links
	 * @return bool
	 */
	public static function onSkinTemplateNavigationUniversal( &$sktemplate, &$links ) {
		$context = $sktemplate->getContext();
		$title = $sktemplate->getTitle();

		if ( $title->getNamespace() == NS_HELP && HelpPages::helpPageExists( $title ) ) {
			$links['namespaces']['help']['class'] = 'selected';
			$links['namespaces']['help_talk']['class'] = '';
			$links['namespaces']['help_talk']['href'] = '//www.mediawiki.org/wiki/Help talk:' . $title->getText();
			$links['views'] = []; // Kill the 'Create' button @todo make this suck less
			$links['views'][] = [
				'class' => false,
				'text' => $context->msg( 'helppages-edit-tab' ),
				'href' => wfAppendQuery(
					'//www.mediawiki.org/w/index.php',
					[
						'action' => 'edit',
						'title' => $title->getPrefixedText()
					]
				)
			];
		}
		return true;
	}

	/**
	 * Use action=purge to clear cache
	 * @param WikiPage &$article
	 * @return bool
	 */
	public static function onArticlePurge( WikiPage &$article ) {
		HelpPages::purgeCache( $article->getTitle() );

		return true;
	}

	/**
	 * If the page "exists", make blue links
	 *
	 * @param LinkRenderer $linkRenderer
	 * @param LinkTarget $target
	 * @param HtmlArmor|string|null &$text
	 * @param array &$customAttribs
	 * @param array &$query
	 * @param mixed &$ret
	 * @return bool
	 */
	public static function onHtmlPageLinkRendererBegin( $linkRenderer, $target, &$text, &$customAttribs, &$query, &$ret ) {
		if ( $target->getNamespace() === NS_HELP && HelpPages::helpPageExists( $target ) ) {
			$ret = $linkRenderer->makeKnownLink( $target, $text, $customAttribs, $query );
			return false;
		}

		return true;
	}
}
