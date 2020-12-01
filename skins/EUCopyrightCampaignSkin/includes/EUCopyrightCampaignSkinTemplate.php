<?php
/**
 * EUCopyrightCampaignSkin
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Skins
 */

/**
 * QuickTemplate subclass for EUCopyrightCampaignSkin
 * @ingroup Skins
 */
class EUCopyrightCampaignSkinTemplate extends BaseTemplate {

	public function execute() {
		$this->data['pageLanguage'] =
			$this->getSkin()->getTitle()->getPageViewLanguage()->getHtmlCode();

		$this->html( 'headelement' );
		// @codingStandardsIgnoreStart
		?>
<div id="eucc-page">
	<div id="header">
		<div class="inner">
			<div id="wikimedia-header">
				<?php $this->printWikimediaPublicPolicyHeader(); ?>
			</div>
			<div id="buttons">
				<?php $this->printLanguageSelector(); ?>
				<?php $this->printTakeActionButton( [ 'desktop' ] ); ?>
			</div>
		</div>
	</div>
	<div id="intro-content">
		<div class="inner">
			<div id="call-to-action-section">
				<h1><?php echo $this->getMsg( 'euccs-call-to-action-heading-html' )->parse() ?></h1>
				<?php $this->printTakeActionButton( [ 'mobile' ] ); ?>
				<?php $this->msg( 'euccs-call-to-action-text' ) ?>
			</div>
		</div>
	</div>
	<div id="form-content">
		<div class="inner">
			<div id="take-action-section">
				<a name="take-action" id="take-action"></a>
				<h1><?php $this->msg( 'euccs-take-action-heading' ) ?></h1>
				<?php $this->msg( 'euccs-take-action-text' ) ?>
			</div>
			<?php $this->printStandardContent(); ?>
		</div>
	</div>
	<div id="footer">
		<div class="inner">
			<a href="https://wikimediafoundation.org/" title="<?php $this->msg( 'euccs-wikimedia-foundation-link-title' ) ?>">
				<?php $this->printWikimediaLogo() ?>
			</a>
			<p><?php $this->msg( 'euccs-footer-links' ) ?></p>
			<hr />
			<p><?php $this->msg( 'euccs-trademarks-hint' ) ?></p>
			<p><?php $this->msg( 'euccs-licence-hint' ) ?></p>
		</div>
	</div>
</div>
		<?php $this->printTrail(); ?>
	</body>
</html>
<?php
		// @codingStandardsIgnoreEnd
	}

	private function printLanguageSelector() {
		$labels = array_map( 'strtoupper', $this->getSkin()->euMemberLanguagesLanguageCodes );
		$options = [];

		foreach ( $this->getSkin()->euMemberLanguagesLanguageCodes as $idx => $languageCode ) {
			$options[] = [
				'label' => $labels[ $idx ],
				'data' => $languageCode
			];
		}

		usort( $options, function ( $a, $b ) {
			return $a['label'] <=> $b['label'];
		} );
		// In anonymous context this is chosen by "Accept-Language" header,
		// within the Extension:UniversalLanguageSelector
		$currentLangCode = $this->getSkin()->getLanguage()->getCode();
		// @phan-suppress-next-line PhanPossiblyUndeclaredVariable Guaranteed to be set
		if ( !in_array( $languageCode, $this->getSkin()->euMemberLanguagesLanguageCodes ) ) {
			// Fallback if unsupported language
			$currentLangCode = 'en';
		}

		$languageSelect = new OOUI\DropdownInputWidget( [
			'id' => 'language-select-button',
			'infusable' => true,
			'options' => $options,
			'value' => $currentLangCode
		] );
		$languageSelect->addClasses( [ 'dd-languages' ] );

		echo $languageSelect;
	}

	private function printTakeActionButton( $additionalClasses = [] ) {
		$classes = array_merge( [ 'take-action-button' ], $additionalClasses );
		$btn = new OOUI\ButtonWidget( [
			'label' => $this->getMsg( 'euccs-take-action-label' )->text(),
			'href' => '#take-action'
		] );
		$btn->addClasses( $classes );

		echo $btn;
	}

	private function printWikimediaPublicPolicyHeader() {
		// @codingStandardsIgnoreStart
		?>
		<a href="https://policy.wikimedia.org/" title="<?php $this->msg( 'euccs-wikimedia-public-policy-title' ); ?>">
			<img height="35px" alt="" src="<?php $this->text( 'stylepath' ) ?>/EUCopyrightCampaignSkin/resources/images/Wikimedia-logo_black.svg" />
			<span><?php $this->msg( 'euccs-wikimedia-public-policy-text' ) ?></span>
		</a>
		<?php
		// @codingStandardsIgnoreEnd
	}

	private function printStandardContent() {
		?>
			<div id="content" class="mw-body" role="main">
				<div id="bodyContent" class="mw-body-content">
					<?php $this->html( 'bodycontent' ); ?>
					<div class="visualClear"></div>
					<?php $this->html( 'debughtml' ); ?>
				</div>
			</div>
		<?php
	}

	private function printWikimediaLogo() {
		echo \Html::element(
			'img',
			[
				'height' => "44",
				'alt' => '',
				'src' => $this->get( 'stylepath' ) .
				'/EUCopyrightCampaignSkin/resources/images/Wikimedia-Foundation-trademarked-logo.png'
			]
		);
	}

}
