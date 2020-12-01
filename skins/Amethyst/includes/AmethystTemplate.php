<?php

class AmethystTemplate extends BaseTemplate {
	public function execute() {
		$this->html( 'headelement' );
		?>
		<header id="header">
			<nav>
				<ul>
					<li><a>Link 1</a></li>
					<li><a>Link 2</a></li>
					<li><a>Link 3</a></li>
					<li><a>Link 4</a></li>
				</ul>
			</nav>
			<form class="mw-search-form" action="<?php $this->text( 'wgScript' ); ?>">
				<input type="hidden" name="title" value="<?php $this->text( 'searchtitle' ) ?>" />
				<?php echo $this->makeSearchInput( [ 'class' => 'mw-search-input' ] ); ?>
			</form>
		</header>
		<?php if ( $this->data['sitenotice'] ) { ?>
			<div id="siteNotice">
				<?php $this->html( 'sitenotice' ); ?>
			</div>
		<?php } ?>
		<main>
			<header id="mw-content-heading">
				<?php echo $this->getIndicators(); ?>
				<h1><?php $this->html( 'title' ); ?></h1>
			</header>
			<article>
				<?php if ( $this->data['subtitle'] ) { ?>
					<div class="mw-content-sub" id="mw-content-sub-subtitle">
					<?php $this->html( 'subtitle' ); ?>
					</div>
				<?php } ?>

				<?php if ( $this->data['undelete'] ) { ?>
					<div class="mw-content-sub" id="mw-content-sub-undelete">
					<?php $this->html( 'undelete' ); ?>
					</div>
				<?php } ?>

				<?php
					$this->html( 'bodytext' );
					$this->html( 'dataAfterContent' );
					$this->html( 'catlinks' );
				?>
			</article>
		</main>
		<footer id="footer">
			<ul>
			<?php
				foreach ( $this->getFooterIcons( 'nocopyright' ) as $blockName => $footerIcons ) { ?>
				<li>
			<?php
					foreach ( $footerIcons as $icon ) {
						echo $this->getSkin()->makeFooterIcon( $icon, 'withoutImage' );
					}
			?>
				</li>
			<?php
				} ?>
			</ul>
		</footer>
		<?php
	}
}