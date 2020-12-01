<?php

class OfflineImportLexicon extends SpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'OfflineImportLexicon' );
	}

	/**
	 * Execute
	 * @param $par Subpage of the wiki page
	 *
	 */
	public function execute( $par ) {
		global $wgOut, $wgRequest;

		$user = $this->getUser();

		$this->setHeaders();
		$this->outputHeader();
		$selfTitle = $this->getPageTitle();
		$wgOut->setPageTitle( $this->msg('offlineimportlexicon') );
		$actionUrl = htmlspecialchars( $selfTitle->getLocalURL() );
		$arrFormats = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16");

		$wgOut->addHTML( $this->msg('offlineimportlexicon-initial-text')->escaped() );

		$metadataType = $wgRequest->getVal( 'metadataType');
		$metadataTitle = $wgRequest->getVal( 'metadataTitle');
		$metadataShortTitle = $wgRequest->getVal( 'metadataShortTitle' );
		$metadataSubtitle = $wgRequest->getVal( 'metadataSubtitle' );
		$metadataPlaceofPublication = $wgRequest->getVal( 'metadataPlaceofPublication' );
		$metadataEditor = $wgRequest->getVal( 'metadataEditor' );
		$metadataPublisher = $wgRequest->getVal( 'metadataPublisher' );
		$metadataLanguage = $wgRequest->getVal( 'metadataLanguage' );
		$metadataYearofPublication = $wgRequest->getVal( 'metadataYearofPublication' );
		$metadataVolumeCount = $wgRequest->getVal( 'metadataVolumeCount' );
		$metadataHasVolume = $wgRequest->getVal( 'metadataHasVolume' );
		$metadataEdition =  $wgRequest->getVal( 'metadataEdition' );
		$volumeTitle = $wgRequest->getVal( 'volumeTitle' );
		$volumeSubtitle = $wgRequest->getVal( 'volumeSubtitle' );
		$volumePlaceofPublication = $wgRequest->getVal( 'volumePlaceofPublication' );
		$volumeEditor = $wgRequest->getVal( 'volumeEditor' );
		$volumePublisher = $wgRequest->getVal( 'volumePublisher' );
		$volumeLanguage = $wgRequest->getVal( 'volumeLanguage' );
		$volumeYearofPublication = $wgRequest->getVal( 'volumeYearofPublication' );
		$volumeDescription = $wgRequest->getVal( 'volumeDescription');
		$volumeNumber = $wgRequest->getVal( 'volumeNumber' );
		$lemmataTitle = $wgRequest->getVal( 'lemmataTitle' );
		$lemmataSubtitle = $wgRequest->getVal( 'lemmataSubtitle' );
		$lemmataAuthor = $wgRequest->getVal( 'lemmataAuthor' );
		$lemmataLanguage = $wgRequest->getVal( 'lemmataLanguage' );
		$lemmataFirstPage = $wgRequest->getVal( 'lemmataFirstPage' );
		$lemmataLastPage = $wgRequest->getVal( 'lemmataLastPage' );
		$lemmataFirstPageN = $wgRequest->getVal( 'lemmataFirstPageN' );
		$lemmataLastPageN = $wgRequest->getVal( 'lemmataLastPageN' );
		$lemmataImages = $wgRequest->getVal( 'lemmataImages' );
		$lemmataRights = $wgRequest->getVal( 'lemmataRights' );

		$output =  $wgRequest->getVal( 'output');
		$addVolume =  $wgRequest->getVal( 'addVolume');
		$addLemmata =  $wgRequest->getVal( 'addLemmata');
		$addDigitized = $wgRequest->getVal( 'addDigitized');

		$selectcompare = $wgRequest->getVal( 'selectcompare' );
		$selectoutput = $wgRequest->getVal( 'selectoutput' );

		if ( !($user->isLoggedIn() ) ) {
			$wikitext = "User must be logged in.";
			if ( method_exists( $wgOut, 'addWikiTextAsInterface' ) ) {
				// MW 1.32+
				$wgOut->addWikiTextAsInterface( $wikitext );
			} else {
				$wgOut->addWikiText( $wikitext );
			}
			return false;
		} else {
			$formfields =  "<div class = 'import-init' id='import-init'>
			<div class = 'header' id='lexicon'>
			<fieldset id='field0'><legend>".$this->msg('offlineimportlexicon-lexicon')->escaped()."</legend>
			<label>".$this->msg('offlineimportlexicon-lexicons')->escaped().": </label>
			<select  id='selectLexicon'  size='1' />";
			$formfields .= "<option value=''>Select lexicon...</option>";
			$formfields .= "</select><br/><br/><button id='addLexicon'>".$this->msg('offlineimportlexicon-new-lexicon')->escaped()."</button> </fieldset></div>
			<div class = 'header' id='volume'>
			<fieldset id='field00'><legend>".$this->msg('offlineimportlexicon-volume')->escaped()."</legend>
			<label>".$this->msg('offlineimportlexicon-volumes')->escaped().": </label>
			<select  id='selectVolume'  size='1' />";
			$formfields .= "</select><br/><br/><button id='addVolume'>".$this->msg('offlineimportlexicon-new-volume')->escaped()."</button> </fieldset></div>
			<div  class='rightArea'><ul id='lexiconList'></ul><button id='addlemmatabutton'>".$this->msg('offlineimportlexicon-new-lemma')->escaped()."</button><button id='addimagesbutton'>Add media metadata</button></div>
			</div>
			<div class='import-new' id='import-new'>
			<div id='lexicon_template'>
			<fieldset id='field1'><legend>".$this->msg('offlineimportlexicon-new-lexicon')."</legend>
			<form id='Lexicon' >
			<div class = 'lexiconInForm' >
			<table  id='lexicon_table' >
			<tr>
				<div class='tooltip-content' id='cora-content-1'>".$this->msg('offlineimportlexicon-content-1')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-1'>".$this->msg('offlineimportlexicon-title')->escaped()."<em>*</em>:&nbsp;</td><td><input tabindex='1' type='text' id='metadataTitle' name = 'metadataTitle'  value='".$metadataTitle."'  maxlength='200' size='50' /> <td ><label class='error' for='metadataTitle' id='title_error'>This field is required.</label> </td>
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-2'>".$this->msg('offlineimportlexicon-content-2')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-2'>".$this->msg('offlineimportlexicon-short')->escaped()."<em>*</em>:&nbsp;</td><td><input tabindex='1' type='text' id='metadataShortTitle' name='metadataShortTitle' value='".$metadataShortTitle."' maxlength='200' size='50' /> <td ><label class='error' for='metadataShortTitle' id='shortTitle_error'>This field is required.</label> </td>
				</td>
			</tr>

			<tr>
				<div class='tooltip-content' id='cora-content-3'>".$this->msg('offlineimportlexicon-content-3')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-3'>".$this->msg('offlineimportlexicon-subtitle')->escaped().":&nbsp;</td><td><input tabindex='1' type='text' id='metadataSubtitle' name='metadataSubtitle' value='".$metadataSubtitle."' maxlength='200' size='50' />  <td >&nbsp;</td>
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-4'>".$this->msg('offlineimportlexicon-content-4')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-4'>".$this->msg('offlineimportlexicon-place-of-publication')->escaped()."<em>*</em>:&nbsp;</td><td><input tabindex='1' type='text' id='metadataPlaceofPublication' name='metadataPlaceofPublication' value='".$metadataPlaceofPublication."' maxlength='200' size='50' /> <td ><label class='error' for='metadataPlaceofPublication' id='place_error'>This field is required.</label> </td>
				</td>
			</tr>

			<tr>
				<div class='tooltip-content' id='cora-content-5'>".$this->msg('offlineimportlexicon-content-5')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-5'>".$this->msg('offlineimportlexicon-year-of-publication')->escaped()."<em>*</em>:&nbsp;</td><td><input tabindex='1' type='text' id='metadataYearofPublication' name='metadataYearofPublication' value='".$metadataYearofPublication."' maxlength='200' size='50' /><td ><label class='error' for='metadataYearofPublication' id='year_error'>This field is required.</label> </td>
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-6'>".$this->msg('offlineimportlexicon-content-6')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-6'>".$this->msg('offlineimportlexicon-editor')->escaped()."<em>*</em>:&nbsp;</td><td><input tabindex='1' type='text' id='metadataEditor' name='metadataEditor' value='".$metadataEditor."' maxlength='200' size='50' /><td ><label class='error' for='metadataEditor' id='editor_error'>This field is required.</label> </td>
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-7'>".$this->msg('offlineimportlexicon-content-7')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-7'>".$this->msg('offlineimportlexicon-publisher')->escaped().":&nbsp;</td><td><input tabindex='1' type='text' id='metadataPublisher' name='metadataPublisher' value='".$metadataPublisher."' maxlength='200' size='50' />
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-8'>".$this->msg('offlineimportlexicon-content-8')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-8'>".$this->msg('offlineimportlexicon-edition')->escaped().":&nbsp;</td><td><input tabindex='1' type='text' id='metadataEdition' name='metadataEdition' value='".$metadataEdition."' maxlength='200' size='50' />
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-9'>".$this->msg('offlineimportlexicon-content-9')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-9'>".$this->msg('offlineimportlexicon-language')->escaped().":&nbsp;</td><td><input tabindex='1' type='text' id='metadataLanguage' name='metadataLanguage' value='".$metadataLanguage."' maxlength='200' size='50' />
				</td>
			</tr>
			<!--<tr>
				<td >Has Volume:&nbsp;</td><td><input tabindex='1' type='text' id='metadataHasVolume' name='metadataHasVolume' value='".$metadataHasVolume."' maxlength='200' size='50' />
				</td>
			</tr>-->
			<tr>
				<div class='tooltip-content' id='cora-content-10'>".$this->msg('offlineimportlexicon-content-10')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-10'>".$this->msg('offlineimportlexicon-number-of-volumes')->escaped().":&nbsp;</td>
				<td ><select  id = 'selectoutput' name='selectoutput'  size='1' />";

			foreach( $arrFormats as $format ) {
				$formfields .= "<option>".$format."</option>";
			}
			$formfields .= "</select>
			</td>
			<td >&nbsp;</td>
			</tr>
			<input type='hidden' name='output' id='output' />
			<tr>
			<td colspan='3'><button id='lexiconbutton'>".$this->msg('offlineimportlexicon-create-lexicon')->escaped()."</button><button id='lexiconcancel'>".$this->msg('cancel')->escaped()."</button></td>
			</tr>
			</table>
			</div>
			</form>
			</fieldset>
			</div>\n
			<br />

			";
			$formfields .=  "<div id='volume_template' >
			<fieldset id='field2'><legend>".$this->msg('offlineimportlexicon-volume')->escaped()."</legend>
			<form id='Volume' >
			<div class = 'volumeInForm' >
			<table  id='volume_table' >
			<tr>
				<div class='tooltip-content' id='cora-content-21'>".$this->msg('offlineimportlexicon-content-21')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-21'>".$this->msg('offlineimportlexicon-title')->escaped()."<em>*</em>:&nbsp;</td><td><input tabindex='1' type='text' id='volumeTitle' name='volumeTitle' value='".$volumeTitle."' maxlength='200' size='50' /> <td ><label class='error' for='volumeTitle' id='title_error'>This field is required.</label> </td>
				</td>
			</tr>

			<tr>
				<div class='tooltip-content' id='cora-content-22'>".$this->msg('offlineimportlexicon-content-22')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-22'>".$this->msg('offlineimportlexicon-subtitle')->escaped().":&nbsp;</td><td><input tabindex='1' type='text' id='volumeSubtitle' name='volumeSubtitle' value='".$volumeSubtitle."' maxlength='200' size='50' />
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-23'>".$this->msg('offlineimportlexicon-content-23')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-23'>".$this->msg('offlineimportlexicon-place-of-publication')->escaped()."<em>*</em>:&nbsp;</td><td><input tabindex='1' type='text' id='volumePlaceofPublication' name='volumePlaceofPublication' value='".$volumePlaceofPublication."' maxlength='200' size='50' /><td ><label class='error' for='volumePlaceofPublication' id='vplace_error'>This field is required.</label> </td>
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-24'>".$this->msg('offlineimportlexicon-content-24')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-24'>".$this->msg('offlineimportlexicon-year-of-publication')->escaped()."<em>*</em>:&nbsp;</td><td><input tabindex='1' type='text' id='volumeYearofPublication' name='volumeYearofPublication' value='".$volumeYearofPublication."' maxlength='200' size='50' /> <td ><label class='error' for='volumeYearofPublication' id='vyear_error'>This field is required.</label> </td>
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-25'>".$this->msg('offlineimportlexicon-content-25')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-25'>".$this->msg('offlineimportlexicon-editor')->escaped()."<em>*</em>:&nbsp;</td><td><input tabindex='1' type='text'  id='volumeEditor' name='volumeEditor' value='".$volumeEditor."' maxlength='200' size='50' /> <td ><label class='error' for='volumeEditor' id='veditor_error'>This field is required.</label> </td>
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-26'>".$this->msg('offlineimportlexicon-content-26')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-26'>".$this->msg('offlineimportlexicon-publisher')->escaped().":&nbsp;</td><td><input tabindex='1' type='text' id='volumePublisher' name='volumePublisher' value='".$volumePublisher."' maxlength='200' size='50' />
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-27'>".$this->msg('offlineimportlexicon-content-27')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-27'>".$this->msg('offlineimportlexicon-language')->escaped().":&nbsp;</td><td><input tabindex='1' type='text' id='volumeLanguage' name='volumeLanguage' value='".$volumeLanguage."' maxlength='200' size='50' />
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-28'>".$this->msg('offlineimportlexicon-content-28')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-28'>".$this->msg('offlineimportlexicon-volume-number')->escaped().":&nbsp;</td><td><input tabindex='1' type='text' id='volumeNumber' name='volumeNumber' value='".$volumeNumber."' maxlength='200' size='50' />
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-29'>".$this->msg('offlineimportlexicon-content-29')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-29'>".$this->msg('offlineimportlexicon-physical-description')->escaped().":&nbsp;</td><td><input tabindex='1' type='text' id='volumeDescription' name='volumeDescription' value='".$volumeDescription."' maxlength='200' size='50' />
				</td>
			</tr>
			<input type='hidden' name='add' id='add' />
			<tr>
			<td>
			<td colspan='3'><button id='volumebutton'>".$this->msg('offlineimportlexicon-create-volume')->escaped()."</button><button id='volumecancel'>".$this->msg('offlineimportlexicon-cancel')->escaped()."</button></td>
			</td>
			</tr>
			</table>
			</div>
			</form>
			</fieldset>
			</div>\n
			<br />

			";

			$formfields .=  "<div id='lemmata_template' >
			<fieldset id='field3'><legend>".$this->msg('offlineimportlexicon-lemmata')->escaped()."</legend>
			<form id='Lemmata' >
			<table  id='lemmata_table' >
			<tr><div class='tooltip-content' id='cora-content-31'>".$this->msg('offlineimportlexicon-content-31')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-31'>".$this->msg('offlineimportlexicon-title')->escaped()."<em>*</em>:&nbsp;</td><td><input tabindex='1' type='text' id='lemmataTitle' name='lemmataTitle' value='".$lemmataTitle."' maxlength='200' size='50' /><td ><label class='error' for='lemmataTitle' id='ltitle_error'>This field is required.</label> </td>
				</td>
			</tr>
			<tr><div class='tooltip-content' id='cora-content-32'>".$this->msg('offlineimportlexicon-content-32')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-32'>".$this->msg('offlineimportlexicon-type')->escaped()."<em>*</em>:&nbsp;</td>
				<td><select  id='metadataType' name='metadataType'  size='1' />".
					"<option id='Lemma' value='Lemma' selected='selected'>Lemma</option>".
					"<option id='Article' value='Article'>Article</option>".
					"<option id='Preface' value='Preface'>Preface</option>".
					"<option id='TOC' value='TOC'>TOC</option>".
					"<option id='TitlePage' value='TitlePage'>TitlePage</option>".
					"</select></td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-33'>".$this->msg('offlineimportlexicon-content-33')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-33'>".$this->msg('offlineimportlexicon-subtitle')->escaped().":&nbsp;</td><td><input tabindex='1' type='text' id='lemmataSubtitle' name='lemmataSubtitle' value='".$lemmataSubtitle."' maxlength='200' size='50' />
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-34'>".$this->msg('offlineimportlexicon-content-34')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-34'>".$this->msg('offlineimportlexicon-author')->escaped().":&nbsp;</td><td><input tabindex='1' type='text'  id='lemmataAuthor' name='lemmataAuthor' value='".$lemmataAuthor."' maxlength='200' size='50' />
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-355'>".$this->msg('offlineimportlexicon-content-355')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-355'>".$this->msg('offlineimportlexicon-language')->escaped().":&nbsp;</td><td><input tabindex='1' type='text' id='lemmataLanguage' name='lemmataLanguage' value='".$lemmataLanguage."' maxlength='200' size='50' />
				</td>
			</tr>
			<tr>
			<div class='tooltip-content' id='cora-content-37'>".$this->msg('offlineimportlexicon-content-37')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-37'>".$this->msg('offlineimportlexicon-first-page-g')->escaped().":&nbsp;</td><td><input tabindex='1' type='text'  id='lemmataFirstPageN' name='lemmataFirstPageN' value='".$lemmataFirstPageN."' maxlength='200' size='50' /> <td ><label class='error' for='lemmataFirstPageN' id='lfirstN_error'>This field is required.</label> </td>
				</td>
			</tr>
			<tr><div class='tooltip-content' id='cora-content-38'>".$this->msg('offlineimportlexicon-content-38')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-38'>".$this->msg('offlineimportlexicon-last-page-g')->escaped().":&nbsp;</td><td><input tabindex='1' type='text' id='lemmataLastPageN' name='lemmataLastPageN' value='".$lemmataLastPageN."' maxlength='200' size='50' /> <td ><label class='error' for='lemmataLastPageN' id='llastN_error'>This field is required.</label> </td>
				</td>
			</tr>
			<tr>
			<div class='tooltip-content' id='cora-content-35'>".$this->msg('offlineimportlexicon-content-35')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-35'>".$this->msg('offlineimportlexicon-first-page-n')->escaped()."<em>*</em>:&nbsp;</td><td><input tabindex='1' type='text'  id='lemmataFirstPage' name='lemmataFirstPage' value='".$lemmataFirstPage."' maxlength='200' size='50' /> <td ><label class='error' for='lemmataFirstPage' id='lfirst_error'>This field is required.</label> </td>
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-36'>".$this->msg('offlineimportlexicon-content-36')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-36'>".$this->msg('offlineimportlexicon-last-page-n')->escaped()."<em>*</em>:&nbsp;</td><td><input tabindex='1' type='text' id='lemmataLastPage' name='lemmataLastPage' value='".$lemmataLastPage."' maxlength='200' size='50' /> <td ><label class='error' for='lemmataLastPage' id='llast_error'>This field is required.</label> </td>
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-39'>".$this->msg('offlineimportlexicon-content-39')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-39'>".$this->msg('offlineimportlexicon-alternate')->escaped().":&nbsp;</td><td><textarea id='lemmataImages' name='lemmataImages' value='".$lemmataImages."' cols='105' rows='2'> </textarea>
				</td>
			</tr>
			<tr>
				<div class='tooltip-content' id='cora-content-40'>".$this->msg('offlineimportlexicon-content-40')->escaped()."</div>
				<td class='tooltip-target' id='cora-target-40'>".$this->msg('offlineimportlexicon-rights-holder')->escaped().":&nbsp;</td><td><textarea id='lemmataRights' name='lemmataRights' value='".$lemmataRights."' cols='105' rows='2'> </textarea>
				</td>
			</tr>
			<input type='hidden' name='addLemmata' id='addLemmata' />
			<tr>
			<td colspan='4'><button id='lemmatabutton'>".$this->msg('offlineimportlexicon-create-lemma')->escaped()."</button><button id='newmediabutton'>".$this->msg('offlineimportlexicon-add-media')->escaped()."</button><button id='lemmatacancel'>".$this->msg('cancel')->escaped()."</button><button id='lemmatarefresh'>".$this->msg('refresh')->escaped()."</button></td>
			</tr>
			</table>
			</form>
			</fieldset>
			</div>
			</div>\n
			<br />

			";
			$wgOut->addHTML( $formfields );
		}
	}

	function pageExists( $title ) {
		$articleTitle = Title::newFromText($title);
		$ex = false;
		if ( $articleTitle instanceof Title ) {
			$ex = $articleTitle->exists();
		}
		return $ex;
	}

	function savePage( $title, $content, $doNotUpdate = false, $flag, $summary = "New wiki page has been created." ) {
		$flags = $flag;
		$titleObj = Title::newFromtext($title);
		$user = $this->getUser();

		$wikiPage = WikiPage::factory( $titleObj );
		$flags = $flags|EDIT_DEFER_UPDATES | EDIT_AUTOSUMMARY;
		$articleContent = ContentHandler::makeContent( $content, $titleObj );
		$status = $wikiPage->doEditContent( $articleContent, $summary, $flags, false, $user);
		$result = true;
		if ( !$status->isOK() ) {
			$result = $status->getErrorsArray();
		}
		return $result;
	}

	function createImage( $text,$file_name ) {
		$my_img = imagecreate( 200, 80 );
		$background = imagecolorallocate( $my_img, 0, 0, 255 );
		$text_colour = imagecolorallocate( $my_img, 255, 255, 0 );
		$line_colour = imagecolorallocate( $my_img, 128, 255, 0 );
		imagestring( $my_img, 4, 30, 25, $text, $text_colour );
		imagesetthickness ( $my_img, 5 );
		imageline( $my_img, 30, 45, 165, 45, $line_colour );

		// header( "Content-type: image/png" );
		//   imagepng( $my_img );

		imagepng( $my_img, $file_name.".png" );
		imagejpeg($my_img, $file_name.".jpg");
		imagecolordeallocate( $my_img, $line_colour );
		imagecolordeallocate( $my_img, $text_colour );
		imagecolordeallocate( $my_img, $background );
		imagedestroy( $my_img );
	}

	protected function getGroupName() {
		return 'smw_group';
	}
}
