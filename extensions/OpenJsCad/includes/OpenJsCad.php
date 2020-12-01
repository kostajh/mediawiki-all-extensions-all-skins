<?php
/**
 * Main file for extension OpenJsCad.
 *
 * @file
 * @ingroup Extensions
 *
 * Syntax:
 * <jscad>
 *   function main() {
 *     return cube();
 *   }
 * </jscad>
 *
 */

class OpenJsCad
{

    /**
     * @param Parser &$parser
     */
    public static function onParserFirstCallInit(Parser &$parser)
    {
        $parser->setHook('jscad', [ 'OpenJsCad', 'render' ]);
    }

    /**
     * Renders a JSCAD design
     * <jscad title='MyDesign'>function main() { return cube ...}</jscad>
     *
     * @tagcontent code
     *  The code is javascript in JSCAD format
     *
     * @param title
     *  The title is is the title e.g. 'previous'
     *
     * @return string
     *  The necessary html code to render the design
     */
    public static function render($input, $params, $parser)
    {
        if (isset($params["nocache"])) {
            $parser->getOutput()->updateCacheExpiry( 0 );
        }
        // in case WikiMarkup / Templates have been used to specify page ..
        $code = $parser->recursiveTagParse($input);
        $title= "OpenJSCAD design";
        $target="_self";
        // title part
        if (isset($args["title"])) {
            $title=$args["title"];
            $title=$parser->recursiveTagParse($title);
        }
        global $wgScriptPath;
        $result ="<script src='".$wgScriptPath."/extensions/OpenJsCad/lightgl.js'></script>\n";
        $result.="<script src='".$wgScriptPath."/extensions/OpenJsCad/csg.js'></script>\n";
        $result.="<script src='".$wgScriptPath."/extensions/OpenJsCad/openjscad.js'></script>\n";
        $result.="<link rel='stylesheet' href='".$wgScriptPath."/extensions/OpenJsCad/openjscad.css' type='text/css'>\n";
        $result.="<script>\n";
        $result.="  var gProcessor=null;\n";
        $result.="  // Show all exceptions to the user:\n";
        $result.="  OpenJsCad.AlertUserOfUncaughtExceptions();\n";
        $result.="\n";
        $result.="  function renderJscad()\n";
        $result.="  {\n";
        $result.="    let viewer = document.getElementById('jscadviewer');\n";
        $result.="    gProcessor = new OpenJsCad.Processor(viewer);\n";
        $result.="    updateSolid();\n";
        $result.="  }\n";
        $result.="\n";
        $result.="  function updateSolid()\n";
        $result.="  {\n";
        $result.="    gProcessor.setJsCad(document.getElementById('jscadcode').value);\n";
        $result.="  }\n";
        $result.="</script>\n";
        $result.="<div id='jscadviewer'></div>\n";
        $result.="<h2>Playground</h2>\n";
        // wfMessage( 'myextension-addition', '1', '2', '3' )->parse()
        $result.="You can try out modifications of the source code right here.\n"; // !i18n
        $result.="<textarea style='height: 500px' class='openjscad' id='jscadcode'>".$code."</textarea>\n";
        $result.="<input type='submit' value='Update' onclick='updateSolid(); return false;'>\n"; // i18n
        $result.="<script>\n";
        // call javascript in middle of page
        // https://stackoverflow.com/a/19869671/1497139
        $result.="  renderJscad();\n";
        $result.="</script>\n";

        //if (!$ok) {
        //    return self::error('onjscad_error');
        //}

        // make sure our result is not parsed
        return array($result,"markerType"=>'nowiki');
    }

    /**
     * @param string $name
     * @param string|int|bool $line
     * @return string HTML
     */
    public static function error($name, $line = false)
    {
        return '<p class="error">' . wfMessage($name, $line)->parse() . '</p>';
    }
}
