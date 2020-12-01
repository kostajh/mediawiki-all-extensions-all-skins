<?php

wfLoadExtension( "ParentPage" );

//register new field for parent page, use BlueSpiceSMWConnector Extension
//https://www.mediawiki.org/wiki/Extension:BlueSpiceSMWConnector
$GLOBALS["bssDefinitions"]["_PARENTPAGE"] = array(
	"id" => "___PARENTPAGE",
	"type" => 9,
	"show" => false,
	"msgkey" => "prefs-parentpage",
	"alias" => "prefs-parentpage",
	"label" => "Parent page",
	"mapping" => "ParentPage::addPropertyValue"
);
