<?php

/*
 * This is the code that creates the special page.
 *
 * @author Clark Verbrugge
 * @Licence CC BY-SA 3.0
 */
class SpecialDebugTemplates extends SpecialPage {

	function __construct() {
		parent::__construct( 'DebugTemplates' );
	}

	/**
	 * Construct and return the special page.
	 *
	 * @param string|null $subpage Name of the subpage if any
	 */
	function execute( $subpage ) {
		global $wgOut;

		$wgOut->addModules( 'ext.debugTemplates' );

		$this->setHeaders();

		if ( $subpage != '' ) {
			$input = $this->getPage( $subpage );
			$titleStr = $subpage;
		} else {
			$input = '';
			$titleStr = '';
		}

		$out = $this->getOutput();
		$out->addWikiMsg( 'debugtemplates-intro' );
		$out->addHTML( $this->makeForm( $titleStr, $input ) );
	}

	/**
	 * Generate the overall page structure.  This is partly a form to enter the starting text etc, and
	 * partly some elements afterward that provide the interactive debugging environment.
	 *
	 * @param string $title Initial value of the context title field
	 * @param string $input Initial value of the input textarea
	 * @return string
	 */
	private function makeForm( $title, $input ) {
		global $wgScriptPath,$wgServer;
		$self = $this->getPageTitle();
		$request = $this->getRequest();
		$user = $this->getUser();

		$form = "<div><fieldset><legend>" . $this->msg( 'debugtemplates-form' )->escaped() . "</legend>\n";

		$form .= '<div style="display:inline-block;width:40%;">';

		// Create a field holding the url for api.php calls.  This is set to readonly, but still
		// presented and even used so it could be changed if ever cross-site scripting is possible.
		$form .= '<p>' . Xml::inputLabel(
			$this->msg( 'debugtemplates-api' )->plain(),
			'wpAPIPrefix',
			'dt-api',
			60,
			$wgServer . $wgScriptPath . '/api.php',
			[ 'autofocus' => '', 'class' => 'mw-ui-input-inline', 'style' => 'width:100%;', 'readonly' => 'readonly' ]
		) . '</p>';

		// Entry of the context title of the page that will be debugged.
		$form .= '<p>' . Xml::inputLabel(
			$this->msg( 'debugtemplates-title' )->plain(),
			'wpContextTitle',
			'dt-title',
			60,
			$title,
			[ 'autofocus' => '', 'class' => 'mw-ui-input-inline', 'style' => 'width:100%;' ]
		) . '</p></div>';

		// The main input area for entering and editing the text being debugged.
		$form .= '<div style="display:inline-block;width:70%;"><h2>'
			. $this->msg( 'debugtemplates-input' )->text() . '</h2>';
		$form .= Xml::textarea(
			'dt-input',
			$input,
			1,
			15,
			[ 'id' => 'dt-input' ]
		) . '</div>';

		// Next to the editable input is an array of input parameters, along with some buttons for operating on them.
		$form .= $this->makeArgTable();

		// Ok that's everything in the first main div of input data.
		$form .= "</fieldset></div>";

		// Make an error-message output pane.
		$form .= '<span class="dt-error" id="dt-error"></span>';

		// The interactive debug area starts with a few buttons that do and configure things.
		$form .= "<h2>" . $this->msg( 'debugtemplates-output' )->escaped() . "</h2>\n";
		$form .= $this->makeDebugButtons();

		// Next comes the stack trace.
		$form .= $this->makeBreadCrumbs();

		// Finally, the actual interactive main debug pane.
		$form .= $this->makeDebugPane();

		return $form;
	}

	/**
	 * Generate the interactive debug pane itself.
	 *
	 * @return string
	 */
	private function makeDebugPane() {
		return '<div class="dt-debug-output-wrapper" style="width:100%;">'
			. '<div style="width:100%;" class="dt-debug-output" id="dt-output"></div></div>';
	}

	/**
	 * Generate the parameter table and controls.
	 *
	 * @return string
	 */
	private function makeArgTable() {
		return '<div style="padding-left:10px;display:inline-block;width:25%;vertical-align:top;">'
			. '<h2>'
			. $this->msg( 'debugtemplates-args-title' )->text()
			. '<input type="button" id="dt-args-set-toggle" style="margin-left:10px;" value="'
			. $this->msg( 'debugtemplates-args-set-toggle' )->text()
			. '"><input type="button" id="dt-args-value-clear" style="margin-left:10px;" value="'
			. $this->msg( 'debugtemplates-args-value-clear' )->text()
			. '"></h2><div style="width:100%;overflow:auto;" id="dt-argtable-wrapper">'
			. '<table id="dt-argtable" cellpadding="2" cellspacing="2" style="width:100%;">'
			. '<thead><tr><th id="dt-arg-set"><span>'
			. $this->msg( 'debugtemplates-args-set' )->text()
			. '</span></th><th><span>'
			. $this->msg( 'debugtemplates-args-name' )->text()
			. '</span></th style="width:100%;"><th><span>'
			. $this->msg( 'debugtemplates-args-value' )->text()
			. '</span></th style="width:100%;"><th><span>'
			. $this->msg( 'debugtemplates-args-eval' )->text()
			. '</span></th></tr></thead></table></div></div>';
	}

	/**
	 * Generate the various buttons that control the debugging area.
	 *
	 * @return string
	 */
	private function makeDebugButtons() {
		return '<input type="button" id="dt-eval" value="'
			. $this->msg( 'debugtemplates-eval' )->text()
			. '"><input type="button" id="dt-undo" style="margin-left:10px;" disabled="disabled" value="'
			. $this->msg( 'debugtemplates-undo' )->text()
			. '"><input type="button" id="dt-reset" style="margin-left:10px;" disabled="disabled" value="'
			. $this->msg( 'debugtemplates-reset' )->text()
			. '"><span style="vertical-align:middle;margin-left:2em;"><span class="dt-radio-label">'
			. $this->msg( 'debugtemplates-radio-intro' )->text()
			. '</span><input type="radio" id="dt-radio-select" name="dt-radio-debug" class="dt-radio-buttons">'
			. '<label class="dt-radio-label" for="dt-radio-select">'
			. $this->msg( 'debugtemplates-radio-select' )->text()
			. '</label><input type="radio" checked id="dt-radio-eval" name="dt-radio-debug" class="dt-radio-buttons">'
			. '<label class="dt-radio-label" for="dt-radio-eval">'
			. $this->msg( 'debugtemplates-radio-eval' )->text()
			. '</label><input type="radio" id="dt-radio-descend" name="dt-radio-debug" class="dt-radio-buttons">'
			. '<label class="dt-radio-label" for="dt-radio-descend">'
			. $this->msg( 'debugtemplates-radio-descend' )->text()
			. '</label></span><br>';
	}

	/**
	 * Generate breadcrumbs (stack) output area.
	 *
	 * @return string
	 */
	private function makeBreadCrumbs() {
		return '<div id="dt-crumbs" class="dt-crumbs" title="'
			. $this->msg( 'debugtemplates-crumb-title' )->text()
			. '"></div>';
	}

	/**
	 * Get a page content.  Used to initialize the input if a subpage is provided.
	 *
	 * @param string $t The title of the page
	 * @return string The page content, or an empty string
	 */
	function getPage( $t ) {
		$title = Title::newFromText( $t );
		if ( is_object( $title ) ) {
			$r = Revision::newFromTitle( $title );
			if ( is_object( $r ) ) {
				return ContentHandler::getContentText( $r->getContent() );
			}
		}
		return "";
	}

	/**
	 * Returns the special page group name.  It should be in the same place as the existing
	 * ExpandTemplates special page.
	 *
	 * @return string
	 */
	protected function getGroupName() {
		return 'wiki';
	}
}
