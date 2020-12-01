<?php
/**
 * Dusk MediaWiki skin by Gregory S. Hayes
 * Based on WordPress "Dusk" theme by Becca Wei
 *
 * @file
 * @ingroup Skins
 */

class DuskTemplate extends BaseTemplate {
	/**
	 * Template filter callback for the Dusk skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 */
	public function execute() {
		$this->data['pageLanguage'] = $this->getSkin()->getTitle()->getPageViewLanguage()->getHtmlCode();

		$this->html( 'headelement' );
?><div id="globalWrapper">
		<div id="header">
			<div id="title">
				<h1><a href="<?php echo htmlspecialchars( $this->data['nav_urls']['mainpage']['href'] ) ?>"><?php $this->msg( 'sitetitle' ); ?></a></h1>
			</div>
		</div>
		<div id="column-content">
			<div id="content" class="mw-body-primary">
				<a id="contentTop"></a>
				<?php if ( $this->data['sitenotice'] ) { ?><div id="siteNotice"><?php $this->html( 'sitenotice' ) ?></div><?php } ?>
				<h1 id="firstHeading" class="firstHeading" lang="<?php $this->text( 'pageLanguage' ); ?>"><?php $this->html( 'title' ) ?></h1>
				<div id="bodyContent" class="mw-body-content">
					<h3 id="siteSub"><?php $this->msg( 'tagline' ) ?></h3>
					<div id="contentSub"><?php $this->html( 'subtitle' ) ?></div>
					<?php if ( $this->data['undelete'] ) { ?><div id="contentSub"><?php $this->html( 'undelete' ) ?></div><?php } ?>
					<?php if ( $this->data['newtalk'] ) { ?><div class="usermessage"><?php $this->html( 'newtalk' ) ?></div><?php } ?>
					<div id="jump-to-nav"></div>
					<a class="mw-jump-link" href="#column-one"><?php $this->msg( 'dusk-jump-to-navigation' ) ?></a>
					<a class="mw-jump-link" href="#searchInput"><?php $this->msg( 'dusk-jump-to-search' ) ?></a>
					<!-- start content -->
					<?php
					$this->html( 'bodytext' );
					if ( $this->data['catlinks'] ) {
						$this->html( 'catlinks' );
					}
					?>
					<!-- end content -->
					<?php if ( $this->data['dataAfterContent'] ) { $this->html( 'dataAfterContent' ); } ?>
					<div class="visualClear"></div>
				</div>
			</div>
		</div>
		<div id="column-one"<?php $this->html( 'userlangattributes' ) ?>>
			<?php $this->cactions(); ?>

			<div class="portlet" id="p-personal">
				<h5><?php $this->msg( 'personaltools' ) ?></h5>
				<div class="pBody">
					<ul<?php $this->html( 'userlangattributes' ) ?>>
<?php				foreach ( $this->getPersonalTools() as $key => $item ) {
						echo $this->makeListItem( $key, $item );
					}
?>
					</ul>
				</div>
			</div>

			<div class="portlet" id="p-logo">
				<?php
					echo Html::element( 'a', array(
						'href' => $this->data['nav_urls']['mainpage']['href'],
						'style' => "background-image: url({$this->data['logopath']});"
					) + Linker::tooltipAndAccesskeyAttribs( 'p-logo' ) ); ?>
			</div>

			<?php
				// Parse [[MediaWiki:Sidebar]] and render all the portlets
				$this->renderPortals( $this->data['sidebar'] );
			?>
		</div><!-- end of the left (by default at least) column -->
		<div class="visualClear"></div>
		<div id="footer" class="noprint">
			<p></p>
			<?php
			foreach ( $this->getFooterIcons( 'icononly' ) as $blockName => $footerIcons ) { ?>
			<div id="f-<?php echo htmlspecialchars( $blockName ); ?>ico">
<?php 			foreach ( $footerIcons as $icon ) {
					echo $this->getSkin()->makeFooterIcon( $icon );
				}
?>
			</div>
<?php 		} ?>
			<ul id="f-list">
			<?php
			foreach ( $this->getFooterLinks( 'flat' ) as $aLink ) {
				if ( isset( $this->data[$aLink] ) && $this->data[$aLink] ) {
?>				<li id="<?php echo $aLink ?>"><?php $this->html( $aLink ) ?></li>
<?php 			}
			}
?>
			</ul>
		</div>
	</div>
<?php
		$this->printTrail();
		echo Html::closeElement( 'body' );
		echo Html::closeElement( 'html' );
	} // execute()

	/**
	 * @param $sidebar array
	 */
	protected function renderPortals( $sidebar ) {
		if ( !isset( $sidebar['SEARCH'] ) ) {
			$sidebar['SEARCH'] = true;
		}
		if ( !isset( $sidebar['TOOLBOX'] ) ) {
			$sidebar['TOOLBOX'] = true;
		}
		if ( !isset( $sidebar['LANGUAGES'] ) ) {
			$sidebar['LANGUAGES'] = true;
		}

		foreach ( $sidebar as $boxName => $content ) {
			if ( $content === false ) {
				continue;
			}

			if ( $boxName == 'SEARCH' ) {
				$this->searchBox();
			} elseif ( $boxName == 'TOOLBOX' ) {
				$this->toolbox();
			} elseif ( $boxName == 'LANGUAGES' ) {
				$this->languageBox();
			} else {
				$this->customBox( $boxName, $content );
			}
		}
	}

	/**
	 * Outputs the search box HTML.
	 */
	function searchBox() {
?>
	<div id="p-search" class="portlet">
		<h5><label for="searchInput"><?php $this->msg( 'search' ) ?></label></h5>
		<div class="pBody">
			<form name="searchform" action="<?php $this->text( 'wgScript' ) ?>" id="searchform">
				<input type='hidden' name="title" value="<?php $this->text( 'searchtitle' ) ?>"/>
				<?php
					echo $this->makeSearchInput( array( 'id' => 'searchInput' ) );
					echo $this->makeSearchButton( 'go', array( 'id' => 'searchGoButton', 'class' => 'searchButton' ) );
				?>
			</form>
		</div>
	</div>
<?php
	} // searchBox()

	/**
	 * Prints the content actions bar.
	 */
	function cactions() {
?>
	<div id="p-cactions" class="portlet">
		<h5><?php $this->msg( 'views' ) ?></h5>
		<div class="pBody">
			<ul><?php
				foreach ( $this->data['content_actions'] as $key => $tab ) {
					echo $this->makeListItem( $key, $tab );
				}
				?>
			</ul>
		</div>
	</div>
<?php
	} // cactions()

	/**
	 * Outputs the toolbox HTML.
	 */
	function toolbox() {
?>
	<div class="portlet" id="p-tb">
		<h5><?php $this->msg( 'toolbox' ) ?></h5>
		<div class="pBody">
			<ul>
<?php
		foreach ( $this->getToolbox() as $key => $tbitem ) {
			echo $this->makeListItem( $key, $tbitem );
		}

		// Avoid PHP 7.1 warning of passing $this by reference
		$template = $this;
		Hooks::run( 'SkinTemplateToolboxEnd', array( &$template, true ) );
?>
			</ul>
		</div>
	</div>
<?php
		// Hook point for the ShoutWiki Ads extension
		Hooks::run( 'DuskAfterToolbox', array( $this ) );
	} // toolbox()

	/**
	 * Outputs the box holding interlanguage links, if there are any.
	 */
	function languageBox() {
		if ( $this->data['language_urls'] ) {
?>
	<div id="p-lang" class="portlet">
		<h5<?php $this->html( 'userlangattributes' ) ?>><?php $this->msg( 'otherlanguages' ) ?></h5>
		<div class="pBody">
			<ul>
<?php		foreach ( $this->data['language_urls'] as $key => $langlink ) {
				echo $this->makeListItem( $key, $langlink );
			}
?>
			</ul>
		</div>
	</div>
<?php
		}
	} // languageBox()

	/**
	 * Outputs the HTML for a custom, user-defined (via MediaWiki:Sidebar)
	 * sidebar portlet.
	 *
	 * @param $bar string
	 * @param $cont array|string
	 */
	function customBox( $bar, $cont ) {
		$portletAttribs = array(
			'class' => 'generated-sidebar portlet',
			'id' => Sanitizer::escapeIdForAttribute( "p-$bar" ),
			'role' => 'navigation'
		);
		$tooltip = Linker::titleAttrib( "p-$bar" );
		if ( $tooltip !== false ) {
			$portletAttribs['title'] = $tooltip;
		}
		echo '	' . Html::openElement( 'div', $portletAttribs );
		$msgObj = wfMessage( $bar );
?>

		<h5><?php echo htmlspecialchars( $msgObj->exists() ? $msgObj->text() : $bar ); ?></h5>
		<div class="pBody">
<?php   if ( is_array( $cont ) ) { ?>
			<ul>
<?php 			foreach ( $cont as $key => $val ) {
					echo $this->makeListItem( $key, $val );
				}
?>
			</ul>
<?php   } else {
			// allow raw HTML block to be defined by extensions (such as NewsBox)
			echo $cont;
		}
?>
		</div>
	</div>
<?php
	} // customBox()
}
