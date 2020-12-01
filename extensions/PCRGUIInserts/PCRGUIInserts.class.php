<?php
/**
 * This is the class file for the PCR GUI Inserts extension
 *
 * @file
 *
 * @author David Dernoncourt (Patheticcockroach)
 *
 * @license https://creativecommons.org/licenses/by-sa/4.0/ CC-BY-SA-4.0
 */

class PCRGUIInserts
{
	public function __construct()
	{
		global $wgHooks;
		$that = $this;
		$wgHooks['BeforePageDisplay'][] = array( &$that, 'BeforePageDisplay' );
		$wgHooks['SkinAfterBottomScripts'][] = array( &$that, 'SkinAfterBottomScripts' );
		$wgHooks['SkinBuildSidebar'][] = array( &$that, 'SkinBuildSidebar' );
	}

	# addHeadItem places stuff within <head></head>
	# BeforePageDisplay places stuff just at the end of the page content frame
	public function BeforePageDisplay(&$out, &$sk)
	{
		global $wgPCRguii_Inserts;
		if($wgPCRguii_Inserts['addHeadItem']['on'])
			{
			$i=0;
			foreach($wgPCRguii_Inserts['addHeadItem']['content'] as $value)
				{
				$out->addHeadItem('PCRGUIInserts'.$i,$value);
				$i++;
				}
			}
		if($wgPCRguii_Inserts['BeforePageDisplay']['on'])
			$out->addHTML($wgPCRguii_Inserts['BeforePageDisplay']['content']);
		return true;
	}

	# SkinAfterBottomScripts places stuff really at the bottom (after the last yellow line!)
	public function SkinAfterBottomScripts($skin, &$text)
	{
		global $wgPCRguii_Inserts;
		if($wgPCRguii_Inserts['SkinAfterBottomScripts']['on'])
			$text .= $wgPCRguii_Inserts['SkinAfterBottomScripts']['content'];
		return true;
	}

	# SkinBuildSidebar places stuff at the end of the side bar
	public function SkinBuildSidebar($skin, &$bar)
	{
		global $wgPCRguii_Inserts;
		if($wgPCRguii_Inserts['SkinBuildSidebar']['on'])
			{
			foreach($wgPCRguii_Inserts['SkinBuildSidebar']['content'] as $value)
			$bar[$value[0]] = $value[1];
			}
		return true;
	}
}
