<?php

$wgExtensionCredits['parserhook'][] = array(
	'path'           => __FILE__,
	'name'           => 'ToolTip',
	'author'         => 'Paul Grinberg',
	'url'            => 'https://www.mediawiki.org/wiki/Extension:Tooltip',
	'descriptionmsg' => 'tooltip-desc',
	'version'        => '0.6.1',
);

$dir = dirname( __FILE__ ) . '/';
$wgMessagesDirs['Tooltip'] = $dir . 'i18n';
$wgExtensionMessagesFiles['TooltipMagic'] = $dir . 'Tooltip.i18n.magic.php';

$wgHooks['ParserFirstCallInit'][] = 'wfToolTipRegisterParserHooks';
$wgHooks['BeforePageDisplay'][] = 'wfTooltipBeforePageDisplay';

$wgResourceModules += array(
	'ext.tooltip' => [
		'scripts' => 'Tooltip.js',
		'styles' => 'Tooltip.css',
		'localBasePath' => __DIR__ . '/modules',
		'remoteExtPath' => 'Tooltip/modules',
] );

function wfToolTipRegisterParserHooks( $parser ) {
    $parser->setHook( 'tooltip', 'renderToolTip' );
    $parser->setFunctionHook( 'tooltip', 'wfTooltipParserFunction_Render' );
    return true;
}

function wfTooltipBeforePageDisplay( $out ) {
	$out->addModules( 'ext.tooltip' );
	return true;
}

function wfTooltipParserFunction_Render( $parser, $basetext = '', $tooltiptext = '', $x = 0, $y = 0) {
    $output = renderToolTip($tooltiptext, array('text'=>$basetext, 'x'=>$x, 'y'=>$y), $parser);
    return array($output, 'nowiki' => false, 'noparse' => true, 'isHTML' => false);
}

function renderToolTip($input, $argv, &$parser) {
    $text = 'see tooltip';
    $xoffset = 0;
    $yoffset = 0;
    foreach ($argv as $key => $value) {
        switch ($key) {
            case 'text':
                $text = $value;
                break;
            case 'x':
                $xoffset = intval($value);
                break;
            case 'y':
                $yoffset = intval($value);
                break;
            default :
                wfDebug( __METHOD__ . ": Requested '$key ==> $value'\n" );
                break;
        }
    }

    $output = '';

    if ($input != '') {
        $tooltipid = uniqid( 'tooltipid' );
        $parentid = uniqid( 'parentid' );
        $output .= "<span id='$tooltipid' class='xstooltip'>" . $parser->unstrip($parser->recursiveTagParse($input),$parser->mStripState) . "</span>";
        $output .= "<span id='$parentid' class='xstooltip_body' onmouseover=\"xstooltip_show('$tooltipid', '$parentid', $xoffset, $yoffset);\" onmouseout=\"xstooltip_hide('$tooltipid');\">" . $parser->unstrip($parser->recursiveTagParse($text),$parser->mStripState) . "</span>";
    }

    return $output;
}

