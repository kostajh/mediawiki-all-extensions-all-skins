<?php
/**
 * Desk Mess Mirrored skin -- marble desktop covered with a mix of old and new
 * items, such as some vintage papers, a stainless steel pen, and, a hot cup of
 * coffee!
 *
 * @file
 * @author Edward Caissie <edward.caissie@gmail.com> -- original WordPress theme
 * @author Jack Phoenix -- MediaWiki port
 * @see http://wordpress.org/themes/desk-mess-mirrored
 * @license GPL-2.0-only
 */

/**
 * Main Desk Mess Mirrored skin class.
 * @ingroup Skins
 */
class DeskMessMirroredTemplate extends BaseTemplate {
	/**
	 * Template filter callback for the Desk Mess Mirrored skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 */
	public function execute() {
		global $wgSitename;

		$this->html( 'headelement' );
?><div id="mainwrap">
	<div id="header-container">
		<div id="header"><!-- header -->
			<div id="headerleft"></div>
			<div id="logo">
				<h2 id="site-title">
					<a href="<?php echo $this->data['nav_urls']['mainpage']['href'] ?>" title="<?php echo Linker::titleAttrib( 'p-logo', 'withaccess' ) ?>" accesskey="<?php echo Linker::accesskey( 'p-logo' ) ?>"><?php echo $wgSitename ?></a>
				</h2>
				<p id="site-description"><?php $this->msg( 'tagline' ) ?></p>
			</div><!-- #logo -->
			<div id="cup"></div>
			<div id="top-navigation-menu" class="noprint">
				<ul class="nav-menu">
				<?php
					// Yet another copy of NavigationService until NestedMenuParser makes it into core MW...
					$service = new DeskMessMirroredSkinNavigationService();
					$menuNodes = $service->parseMessage(
						'deskmessmirrored-navigation',
						[ 10, 10, 10, 10, 10, 10 ],
						60 * 60 * 3 // 3 hours
					);

					if ( is_array( $menuNodes ) && isset( $menuNodes[0] ) ) {
						$counter = 0;
						foreach ( $menuNodes[0]['children'] as $level0 ) {
							$hasChildren = isset( $menuNodes[$level0]['children'] );
					?>
					<li class="page_item<?php echo ( $hasChildren ? ' page_item_has_children' : '' ) ?>">
						<a class="nav<?php echo $counter ?>_link" href="<?php echo htmlspecialchars( $menuNodes[$level0]['href'], ENT_QUOTES ) ?>"><?php echo htmlspecialchars( $menuNodes[$level0]['text'], ENT_QUOTES ) ?></a>
						<?php if ( $hasChildren ) { ?>
						<ul class="children">
<?php
							foreach ( $menuNodes[$level0]['children'] as $level1 ) {
?>
							<li class="page_item">
								<a href="<?php echo htmlspecialchars( $menuNodes[$level1]['href'], ENT_QUOTES ) ?>"><?php echo htmlspecialchars( $menuNodes[$level1]['text'], ENT_QUOTES ) ?></a>
<?php
								if ( isset( $menuNodes[$level1]['children'] ) ) {
									echo '<ul class="children">';
									foreach ( $menuNodes[$level1]['children'] as $level2 ) {
?>
									<li class="page_item">
										<a href="<?php echo htmlspecialchars( $menuNodes[$level2]['href'], ENT_QUOTES ) ?>"><?php echo htmlspecialchars( $menuNodes[$level2]['text'], ENT_QUOTES ) ?></a>
									</li>
<?php
									}
									echo '</ul>';
									$counter++;
								}
?>
							</li>
<?php
							}
							echo '</ul>';
							$counter++;
						} // hasChildren
						echo '</li>';
						} // top-level foreach
					} // is_array( $menuNodes )
?>
				</ul>
			</div>
		</div><!-- #header -->
	</div><!-- #header-container -->
	<div id="maintop"></div>
	<div id="wrapper">
		<div id="content">
			<div id="main-blog" class="mw-body-content">
				<div class="post hentry mw-body-primary">
					<?php
					$this->cactions();
					if ( $this->data['sitenotice'] ) {
						?><div id="siteNotice"><?php $this->html( 'sitenotice' ) ?></div><?php
					}
					?>
					<?php echo $this->getIndicators(); ?>
					<h1 id="firstHeading" class="firstHeading"><?php $this->html( 'title' ) ?></h1>
					<!-- @todo FIXME: postdata class on the next two elements is just a test, need to see how it looks like... -->
					<div class="postdata" id="contentSub"<?php $this->html( 'userlangattributes' ) ?>><?php $this->html( 'subtitle' ) ?></div>
					<?php
					if ( $this->data['undelete'] ) {
						?><div class="postdata" id="contentSub2"><?php $this->html( 'undelete' ) ?></div><?php
					}
					if ( $this->data['newtalk'] ) {
						?><div class="usermessage"><?php $this->html( 'newtalk' ) ?></div><?php
					}
					?>
					<div id="jump-to-nav"></div>
					<a class="mw-jump-link" href="#top-navigation-menu"><?php $this->msg( 'deskmessmirrored-jump-to-navigation' ) ?></a>
					<a class="mw-jump-link" href="#searchInput"><?php $this->msg( 'deskmessmirrored-jump-to-search' ) ?></a>
					<!-- start content -->
					<?php
					$this->html( 'bodytext' );
					if ( $this->data['catlinks'] ) {
						$this->html( 'catlinks' );
					}
					?>
					<!-- end content -->
					<div class="clear"><!-- For inserted media at the end of the post --></div>
					<?php
					if ( $this->data['dataAfterContent'] ) {
						$this->html( 'dataAfterContent' );
					}
					?>
				</div><!-- .post #post-ID -->

				<div id="nav-global" class="navigation">
					<div class="left"></div>
					<div class="right"></div>
				</div>
			</div><!-- end #main-blog -->

			<div id="sidebar" class="noprint">
				<div id="sidebar-top"></div>
				<div id="sidebar-content">
					<div id="subcolumn">
						<ul>
							<?php $this->renderPortals( $this->data['sidebar'] ); ?>
							<li class="widget" id="p-personal">
								<h2 class="widgettitle"><?php $this->msg( 'personaltools' ) ?></h2>
								<ul>
								<?php
									foreach ( $this->getPersonalTools() as $key => $item ) {
										echo $this->makeListItem( $key, $item );
									}
								?>
								</ul>
							</li>
						</ul>
					</div><!-- #subcolumn -->
				</div><!-- #sidebar-content -->
				<div id="sidebar-bottom"></div>
			</div><!-- #sidebar -->

			<div class="clear"></div>
		</div><!-- end content -->
	</div><!-- end wrapper -->

	<div id="bottom"></div>
	<div id="bottom-extended">
		<div id="bottom-container">
			<p>
				<?php
				if ( isset( $this->data['copyright'] ) && $this->data['copyright'] ) {
				?>
				<span id="dmm-dynamic-copyright"><?php echo $this->data['copyright'] ?></span><!-- #bns-dynamic-copyright --><br /><?php
				}
				?><span id="dmm-theme-version" class="noprint"><?php
				foreach ( $this->getFooterIcons( 'nocopyright' ) as $blockName => $footerIcons ) {
					foreach ( $footerIcons as $icon ) {
						echo $this->getSkin()->makeFooterIcon( $icon, 'withoutImage' );
					}
				}
				?></span>
			</p>
		</div><!-- #bottom-container -->
	</div><!-- #bottom-extended -->
</div><!-- #mainwrap -->
<?php
		$this->printTrail();
		echo Html::closeElement( 'body' );
		echo Html::closeElement( 'html' );
	} // end of execute() method

	/*************************************************************************************************/

	/**
	 * @param array $sidebar
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
	 * Outputs the search box.
	 */
	private function searchBox() {
?>
							<li id="search-3" class="widget widget_search"><h2 class="widgettitle"></h2>
							<form role="search" method="get" id="searchform" class="searchform" action="<?php $this->text( 'wgScript' ) ?>">
								<div>
									<input type="hidden" name="title" value="<?php $this->text( 'searchtitle' ) ?>"/>
									<label class="screen-reader-text" for="searchInput"><?php $this->msg( 'search' ) ?></label>
									<?php
										echo $this->makeSearchInput( [ 'id' => 'searchInput' ] );
										# $fullText = $this->makeSearchButton( 'fulltext', [ 'id' => 'mw-searchButton', 'class' => 'searchButton' ] );
										$goButton = $this->makeSearchButton( 'go', [ 'id' => 'searchGoButton', 'class' => 'searchButton' ] );
										// Fulltext search button removed, it looks bad together w/ the
										// "Go" button since they both just don't fit in there
										# echo $fullText . "\n";
										echo $goButton . "\n";
									?>&#160;
								</div>
							</form>
							</li>
<?php
	}

	/**
	 * Outputs the content actions bar.
	 */
	private function cactions() {
?>
								<div id="p-cactions" class="noprint">
<?php
		foreach ( $this->data['content_actions'] as $key => $tab ) {
			echo $this->makeListItem( $key, $tab );
		}
		echo '</div>';
	}

	/**
	 * Outputs the toolbox.
	 */
	private function toolbox() {
?>
							<li id="p-toolbox" class="widget">
								<h2 class="widgettitle"><?php $this->msg( 'toolbox' ) ?></h2>
								<ul>
<?php
		foreach ( $this->getToolbox() as $key => $tbitem ) {
			echo $this->makeListItem( $key, $tbitem );
		}
		// Avoid PHP 7.1 warning of passing $this by reference
		$template = $this;
		Hooks::run( 'SkinTemplateToolboxEnd', [ &$template, true ] );
		echo '</ul>';
	} // toolbox()

	private function languageBox() {
		if ( $this->data['language_urls'] ) {
?>
							<li class="widget" id="p-languages">
								<h2 class="widget-title"<?php $this->html( 'userlangattributes' ) ?>><?php $this->msg( 'otherlanguages' ) ?></h2>
								<ul>
<?php
			foreach ( $this->data['language_urls'] as $key => $langLink ) {
				echo $this->makeListItem( $key, $langLink );
			} // foreach
			echo '</ul>';
		} // if
	} // languageBox()

	/**
	 * @param string $bar
	 * @param array|string $cont
	 */
	private function customBox( $bar, $cont ) {
		$portletAttribs = [
			'class' => 'generated-sidebar widget',
			'id' => Sanitizer::escapeIdForAttribute( "p-$bar" ),
			'role' => 'navigation'
		];
		$msg = wfMessage( $bar );
		echo '	' . Html::openElement( 'li', $portletAttribs );
?>
		<h2 class="widget-title"><?php echo htmlspecialchars( $msg->exists() ? $msg->text() : $bar ); ?></h2>
<?php
		if ( is_array( $cont ) ) {
			echo '<ul>';
			foreach ( $cont as $key => $val ) {
				echo $this->makeListItem( $key, $val );
			}
			echo '</ul>';
		} else {
			// allow raw HTML block to be defined by extensions (such as NewsBox)
			echo $cont;
		}
		echo '</li>';
	} // customBox()

}
