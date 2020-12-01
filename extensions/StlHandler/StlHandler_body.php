<?php
/**
 *
 * Handler for stl files.
 *
 *
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
 */
 class StlHandler extends ImageHandler {
	public static function onBeforePageDisplay ( OutputPage $out, Skin $skin){
		if (substr($out->getTitle()->getPrefixedText(),0)==="file:"){ //is file page
			if ( strtolower ($imagepage->getDisplayedFile()->getExtension()) === 'stl' ){
					$out->addModules('ext.StlHandler');
			}
		}
	}

	public static function onImageOpenShowImageInlineBefore( $imagepage, $out ){
		if ( strtolower($imagepage->getDisplayedFile()->getExtension()) === 'stl' ){
			$full_url = $imagepage->getDisplayedFile()->getFullURL();

			$out->addHtml(StlHandler::generateHTMLByConfiguration());
			$out->addHtml(ResourceLoader::makeInlineScript("mw.loader.using( 'ext.StlHandler',
									function(){
										initScene('$full_url');
									});"
			) );
		}
	}
	static function generateHTMLByConfiguration(){
		global $wgStlCanvasWidth, $wgStlCanvasHeight, $wgStlBackgroundImage, $wgStlBackgroundColor;
		$HTML = "<div class='fullMedia'><div id='viewer'><canvas id='stlCanvas'";
		if ( isset($wgStlCanvasWidth) && intval($wgStlCanvasWidth) > 0 ){
			$HTML .= " width='$wgStlCanvasWidth'";
		}else {
			$HTML .= " width='600'";
		}
		if ( isset($wgStlCanvasHeight) && intval($wgStlCanvasHeight) > 0){
			$HTML .= " height='$wgStlCanvasHeight'";
		}else{
			$HTML .= " height='480'";
		}
		    $HTML .= " style='";
		if(isset($wgStlBackgroundColor) ){
			$HTML .= "background-color:$wgStlBackgroundColor;";
		}else{
			$HTML .= "background-color:lightgrey;";
		}
		if (isset($wgStlBackgroundImage) ){
			$HTML .= "background-image:url($wgStlBackgroundImage);'";
		}else{
			$HTML .= "'";
		}
		$HTML .= "></canvas></div><span class='fileInfo'></span></div>";
		return $HTML;
	}
	function doTransform( $image, $dstPath, $dstUrl, $params, $flags = 0){
		 //TODO
		 
		
	
	}
}