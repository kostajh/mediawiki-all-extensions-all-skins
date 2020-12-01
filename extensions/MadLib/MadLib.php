<?php
if ( ! defined( 'MEDIAWIKI' ) )
    die();

/**
 * A parser extension that enables "madlib" style constructions.  It provides
 * the #madlib magic word, which converts a pattern string into a new string
 * performing replacements on tagged words choosing new text 
 * from corresponding wiki pages containing possible replacements.
 *
 * Options:
 *
 * $wgMadLibMaxSets
 *           - maximum set value for sets with identical randomization.
 *             Default is 9
 */

$wgMadLibMaxSets = 9; 

// Template stuff
$wgExtensionCredits['parserhook'][] = array(
    
    // The full path and filename of the file. This allows MediaWiki
    // to display the Subversion revision number on Special:Version.
    'path' => __FILE__,
    
    // The name of the extension, which will appear on Special:Version.
    'name' => 'MadLib Parser Function',
    
    // Alternatively, you can specify a message key for the description.
    'descriptionmsg' => 'madlib-desc',
    
    // The version of the extension, which will appear on Special:Version.
    // This can be a number or a string.
    'version' => "1.0.1", 
    
    // Your name, which will appear on Special:Version.
    'author' => 'Clark Verbrugge',
    
    // The URL to a wiki page/web page with information about the extension,
    // which will appear on Special:Version.
    'url' => 'https://www.mediawiki.org/wiki/Extension:MadLib',
    
    // License: I really don't care.
    'license-name' => "CC0-1.0",
    );

// Specify the function that will initialize the parser function.
$wgHooks['ParserFirstCallInit'][] = 'MadLibExtensionSetupParserFunction';

// Allow translation of the parser function name
$wgExtensionMessagesFiles['MadLibExtensionMagic'] = __DIR__ . '/MadLib.i18n.magic.php';
$wgMessagesDirs['MadLibExtension'] = __DIR__ . '/i18n';

// Tell MediaWiki that the parser function exists.
function MadLibExtensionSetupParserFunction( &$parser ) {
    
    // Create a function hook associating the "madlib" magic word with the
    // MadLibExtensionRenderParserFunction() function. 
    $parser->setFunctionHook( 'madlib', 'MadLibExtensionRenderParserFunction' );
    
    // Return true so that MediaWiki continues to load extensions.
    return true;
}

// Render the output of the parser function.
function MadLibExtensionRenderParserFunction($parser,$param1 = '',$param2 = '',$param3 = '') {
    global $wgMadLibMaxSets;
    
    $parser->getOutput()->updateCacheExpiry( 0 );
    
    // first arg is the base text 
    $s = $param1;
    // second arg is a comma-separated list of tags to replace
    $fmt = preg_split("/\s*,\s*/",trim($param2));
    // third arg is optional, and is a prefix applied to the matching page names of replaced tags
    
    // do a replacement for each of the indicated madlib tags
    foreach($fmt as $f) {
        if ($f === '')
            continue;
        $fs = MadLibExtensionCleanPage(MadLibExtensionGetPage($param3 . $f)); // get page of texts for the given tag
        $fa = explode("\n",$fs); // line-separated values
        
        // separate each line in $fa into an array of tag => content pairs
        $possibles = array();
        $i = 0;
        foreach($fa as $line) {
            $possibles[$i] = MadLibExtensionTagline($line);
            $i = $i + 1;
        }
        
        // each <$f> we replace with a distinct random choice of line, but the empty tag's contents
        $needle = "<" . $f . ">";
        $pos = strpos($s,$needle);
        while ($pos !== false) {
            $repl = $possibles[mt_rand(0,count($possibles)-1)][""];
            $s = substr_replace($s,$repl,$pos,strlen($needle));
            $pos = strpos($s,$needle,$pos+strlen($repl));
        }
        
        // each <$f#tag> we replace with a distinct random choice of line, but the tag's contents
        $needle = "<" . $f . "#";
        $pos = strpos($s,$needle);
        while ($pos !== false) {
            $tagend = strpos($s,">",$pos+strlen($needle));
            if ($tagend === false) {
                break;
            }
            $tag = substr($s,$pos+strlen($needle),$tagend-($pos+strlen($needle)));
            $repl = $possibles[mt_rand(0,count($possibles)-1)][$tag];
            $s = substr_replace($s,$repl,$pos,strlen($needle)+strlen($tag)+1);
            $pos = strpos($s,$needle,$pos+strlen($repl));
        }
        
        // each <$f-n> we replace with the same random choice of line, but the empty tag's contents
        $i = 1;
        do {
            $needle = "<" . $f . "-" . $i . ">";
            $repl = $possibles[mt_rand(0,count($possibles)-1)][""];
            $ok = 0;
            $s = str_replace($needle,$repl,$s,$ok);
            $i = $i + 1;
        } while ($ok>0 && $i<=$wgMadLibMaxSets);
        
        // each <$f-n#tag> we replace with the same random choice of line, but the tag's contents
        $i = 1;
        do {
            $ok = false;
            $needle = "<" . $f . "-" . $i . "#";
            $repli = mt_rand(0,count($possibles)-1);
            $pos = strpos($s,$needle);
            while ($pos !== false) {
                $tagend = strpos($s,">",$pos+strlen($needle));
                if ($tagend === false) {
                    break;
                }
                $tag = substr($s,$pos+strlen($needle),$tagend-($pos+strlen($needle)));
                $repl = $possibles[$repli][$tag];
                $s = substr_replace($s,$repl,$pos,strlen($needle)+strlen($tag)+1);
                $pos = strpos($s,$needle,$pos+strlen($repl));
                $ok = true;
            }
            $i = $i + 1;
        } while ($ok && $i<=$wgMadLibMaxSets);
    }
    
    return array( $s, 'noparse' => false );
}

// Lines are assumed to start with a # and be overall formatted as 
// #tag content #tag content ..etc..
// This function parses the given line.  Returns an array where keys are tags
// and values are the tag values (tag => value).
// A tag can be an empty string if there are no tags specified.
function MadLibExtensionTagline($line) {
    $mm = array();
    if (strpos($line,"#")!=0 || !preg_match_all("/#([\w]*)\s+([^#]*)/",$line,$mm,PREG_SET_ORDER)) {
        $m = array("" => trim($line));
    } else {
        $m = array();
        foreach($mm as $f) {
            $m[$f[1]] = trim($f[2]);
        }
    }
    return $m;
}


// Clean up the extracted page:
// * remove noinclude stuff
// * delete includeonly and onlyinclude markers
// * trim
function MadLibExtensionCleanPage($p) {
    // remove <noinclude></noinclude>
    $rc = "";
    $j = strpos($p,"<noinclude>");
    $i = 0;
    while ($j!==false) {
        $rc = $rc . substr($p,$i,$j);
        $i = strpos($p,"</noinclude>",$j);
        if ($i===false) {
            break;
        }
        $i = $i + 12;
        $j = strpos($p,"<noinclude>",$i);
    }
    if ($i!==false) {
        $rc = $rc . substr($p,$i);
    }
    $rc = str_replace("<includeonly>","",$rc);
    $rc = str_replace("</includeonly>","",$rc);
    $rc = str_replace("<onlyinclude>","",$rc);
    $rc = str_replace("</onlyinclude>","",$rc);
    return trim($rc);
}

// Get a page content, or empty string if page not found
function MadLibExtensionGetPage($t) {
    $title = Title::newFromText($t);
    if(is_object($title)) {
        $r = Revision::newFromTitle($title);
        if(is_object($r))
            return ContentHandler::getContentText($r->getContent());
    }
    return "";
}