<?php

use MediaWiki\MediaWikiServices;

class ArticleToCategory2Hooks {

	/**
	 * Add category to the new page
	 * The category name is escaped to prevent JavaScript injection
	 *
	 * @param string &$text The text to prefill edit form with
	 * @return bool true
	 */
	public static function wfAddCategory( &$text ) {
		global $wgContLang;

		if ( array_key_exists( 'category', $_GET ) && array_key_exists( 'new', $_GET ) ) {
			$cname = $_GET['category'];
			if ( $_GET['new'] == 1 ) {
				$text = "\n\n[[" . $wgContLang->getNsText( NS_CATEGORY ) . ":" .
						htmlspecialchars( $cname ) . "]]";
			}
		}
		return true;
	}

	/**
	 * Function to get the excluded categories list (blacklist)
	 * the list is retrieved from Add Article to Category 2 excluded categories page.
	 *
	 * @return string $excludedCategories
	 */
	public static function getExcludedCategories() {
		global $wgRequest;

		$excludedCategories = [];
		$specialcatpage = 'Add Article to Category 2 excluded categories';

		if ( $wgRequest->getVal( 'action' ) == 'edit' ) {
			return true;
		}
		$rev = Revision::newFromTitle( Title::makeTitle( 8, $specialcatpage ) );
		if ( $rev ) {
			$content = $rev->getText();
			if ( $content != "" ) {
				$changed = false;
				$c = explode( "\n", $content );
				foreach ( $c as $entry ) {
					if ( $entry[0] == ';' ) {
						$cat = trim( substr( $entry, 1 ) );
						$excludedCategories[] = $cat;
					}
				}
			}
		} else {
			echo ( " Page : \"" . $specialcatpage . "\" does not exist !" );
		}
		return $excludedCategories;
	}

	/**
	 * Generate the input box
	 *
	 * @param string $catpage The category article
	 * @return bool true to do the default behavior of CategoryPage::view
	 */
	public static function wfCategoryChange( $catpage ) {
		global $wgArticleToCategory2ConfigBlacklist,
			$wgOut, $wgScript, $wgContLang, $wgUser;
			$action = htmlspecialchars( $wgScript );

		$title = $catpage->mTitle;
		if ( class_exists( 'MediaWiki\Permissions\PermissionManager' ) ) {
			// MW 1.33+
			$permManager = MediaWikiServices::getInstance()->getPermissionManager();
			if ( !$permManager->quickUserCan( 'edit', $wgUser, $title ) ||
				!$permManager->quickUserCan( 'create', $wgUser, $title ) ) {
				return true;
			}
		} else {
			if ( !$title->quickUserCan( 'edit' )
				|| !$title->quickUserCan( 'create' ) ) {
				return true;
			}
		}
		if ( !$wgUser->isAllowed( 'ArticleToCategory2' ) ) {
			return true;
		}

		if ( $wgArticleToCategory2ConfigBlacklist ) {
			$excludedCategories = self::getExcludedCategories();
			foreach ( $excludedCategories as $value ) {
				if ( $catpage->mTitle->getText() == $value ) {
					return true;
				}
			}
		}
	$boxtext  = wfMessage( 'articletocategory2-create-article-under-category-text' )->escaped();
	$btext = wfMessage( 'articletocategory2-create-article-under-category-button' )->escaped();
	$boxtext2 = wfMessage( 'articletocategory2-create-category-under-category-text' )->escaped();
	$btext2 = wfMessage( 'articletocategory2-create-category-under-category-button' )->escaped();

	$cattitle = $wgContLang->getNsText( NS_CATEGORY );

	/** javascript blocks */
	$formstart = <<<FORMSTART
<!-- Add Article Extension Start -->
<script type="text/javascript">
function clearText(thefield) {
	if (thefield.defaultValue==thefield.value)
		thefield.value = ""
}
function addText(thefield) {
	if (thefield.value=="")
		thefield.value = thefield.defaultValue
}

function addTextTitle(thefield) {
	if (thefield.value=="") {
		thefield.value = thefield.defaultValue;
	} else {
		thefield.value = '{$cattitle}:'+thefield.value;
	}
}

function isemptyx(form) {
	if (form.title.value=="" || form.title.value==form.title.defaultValue) {
		<!-- alert(.title.value); -->
		return false;
	}
	return true;
}
</script>

<table border="0" align="right" width="423" cellspacing="0" cellpadding="0">
	<tr>
	<td width="100%" align="right" bgcolor="">
	<form name="createbox" action="{$action}" onsubmit="return isemptyx(this);" method="get" class="createbox">
		<input type='hidden' name="action" value="edit">
		<input type='hidden' name="new" value="1">
		<input type='hidden' name="category" value="{$catpage->mTitle->getText()}">

		<input class="createboxInput" name="title" type="text" value="{$boxtext}" size="38" style="color:#666;" onfocus="clearText(this);" onblur="addText(this);"/>
		<input type='submit' name="create" class="createboxButton" value="{$btext}"/>
	</form>
FORMSTART;
	$formcategory = <<<FORMCATEGORY
	<form name="createbox" action="{$action}" onsubmit="return isemptyx(this);" method="get" class="createbox">
		<input type='hidden' name="action" value="edit">
		<input type='hidden' name="new" value="1">
		<input type='hidden' name="category" value="{$catpage->mTitle->getText()}">

		<input class="createboxInput" name="title" type="text" value="{$boxtext2}" size="38" style="color:#666;" onfocus="clearText(this);" onblur="addTextTitle(this);"/>
		<input type='submit' name="create" class="createboxButton" value="{$btext2}"/>
	</form>
FORMCATEGORY;
	$formend = <<<FORMEND
	</td>
	</tr>
</table>
<!-- Add Article Extension End -->
FORMEND;
		/** javascript blocks end */
		$wgOut->addHTML( $formstart );
		if ( $wgUser->isAllowed( 'ArticleToCategory2AddCat' ) ) {
			$wgOut->addHTML( $formcategory );
		}
		$wgOut->addHTML( $formend );
		return true;
	}
}
