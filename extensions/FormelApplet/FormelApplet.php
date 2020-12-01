<?php

use MediaWiki\MediaWikiServices;

/**
 * FormelApplet MediaWiki extension
 *
 * @author Rudolf Grossmann
 */

$fa_version = "1.4.0";

// This MediaWiki extension is based on the Java Applet extension by Phil Trasatti
// see: https://www.mediawiki.org/wiki/Extension:Java_Applet

$wgHooks['ParserFirstCallInit'][] = 'fa_AppletSetup';
$wgMessagesDirs['FormelApplet'] = __DIR__ . '/i18n';

$wgExtensionCredits['parserhook'][] = array(
    'path'           => __FILE__,
    'name'           => 'FormelApplet',
    'author'         => 'Rudolf Grossmann',
    'url'            => 'https://www.mediawiki.org/wiki/Extension:FormelApplet',
    'descriptionmsg' => 'formelapplet-desc',
    'version'        => $fa_version
);

function fa_AppletSetup( $parser ) {
        $parser->setHook( 'formelapplet', 'get_fa_AppletOutput' );
        return true;
}

function get_fa_AppletOutput( $input, $args, $parser ) {
        global $wgServer; // URL of the WIKI's server
        global $fa_version; // see line 9 of this file

        $error_message = "no error"; //will be overwritten, if error occurs
        $debug = 'Debug: ';
        $CRLF = "\r\n";
        $quot='"';
        $appletBinary = "gf03.jar" ;
        $codeBase = "http://www.formelapplet.de/classes/";

        // Special parameters, not for parameter (name - value) tags. Use lowercase for sake of comparison!
        $special_parameters = array('width', 'height', 'solution', 'term', 'uselocaljar', 'substimage', 'name', 'debug', 'usegf04');
        $noJavaText = wfMessage( 'formelapplet-nojava', '[http://java.com Java]' )->parse();

        //Look for parameter 'usegf04'.
        $usegf04 = isset($args['usegf04']) ? $args['usegf04'] : 'false';
        if ($usegf04=='true') $appletBinary = "gf04.jar";

        //Look for parameter 'useLocalJar'. Will be overwritten with 'true', if parameter 'filename is used'
        $useLocalJar = isset($args['uselocaljar']) ? $args['uselocaljar'] : '';
        $printDebug = isset($args['debug']) ? $args['debug'] : 'false';

        // Look for required parameters width, height,  term/solution
        if( !isset( $args['width'] )   ||
            !isset( $args['height'] )  ||
            !(isset( $args['solution'] ) || isset( $args['term'] ) ) )
            $error_message = wfMessage( 'formelapplet-missing-parameter' )->escaped();

        if ($error_message == 'no error') {
            // the following code is of use, if MediaWiki is installed on a local fileserver (wamp, server2go,...)
            if ( isset( $args['uselocaljar'] ) && $args['uselocaljar'] == 'true' ) {
             // If use of local JAR is wanted (e.g. for testing purposes)
             // The following line is code from http://code.activestate.com/recipes/576595/   "A more reliable DOCUMENT_ROOT"
             $docroot = realpath((getenv('DOCUMENT_ROOT') && ereg('^'.preg_quote(realpath(getenv('DOCUMENT_ROOT'))), realpath(__FILE__))) ? getenv('DOCUMENT_ROOT') : str_replace(dirname(@$_SERVER['PHP_SELF']), '', str_replace(DIRECTORY_SEPARATOR, '/', dirname(__FILE__))));
             $delta = substr(dirname(__FILE__), strlen($docroot));
             $codeBase = $wgServer . $delta;
             # replace backslash by slash
             $codeBase=str_replace('\\','/',$codeBase);
             # add slash at ending
             if (substr($codeBase, strlen($codeBase)-1) != '/') {
               $codeBase = $codeBase . "/";
             }
           }

            $output = "<!-- FormelApplet Mediawiki extension " . $fa_version ." by R. Grossmann -->" . $CRLF;  // Output the opening applet tag
             // Add code value to tag
            $is_inputapplet=false; //default
            if (isset( $args['solution'] )) {
               // MAYSCRIPT necessary for allowing applet to write Cookies. Cookies necessary for localization.
               $output = $output . "<applet MAYSCRIPT code=".$quot."gut.InputApplet".$quot;
               $solution_or_term = $args['solution'];
               $is_inputapplet = true;
            } else {
               $output = $output . "<applet MAYSCRIPT code=".$quot."gut.OutputApplet".$quot;
               $solution_or_term = $args['term'];
            }

            if (isset( $args['name'] )) {
               $output = $output . " name=" . $quot . htmlspecialchars(strip_tags($args['name'])) . $quot; // Add name value to tag
             }

            $output = $output . " codebase=" . $quot . $codeBase . $quot; // Add codebase value to tag
            $output = $output . " width=" . $quot . htmlspecialchars(strip_tags($args['width'])) . $quot; // Add width value to tag
            $output = $output . " height=" . $quot . htmlspecialchars(strip_tags($args['height'])) . $quot; // Add height value to tag
            $output = $output . " archive=" . $quot. $appletBinary . $quot. " >"; // Add archive value to tag

            $head = substr($solution_or_term, 0, 4);
            if (strtoupper($head)!='ZIP-') {
              //Magic head "ZIP-" not found. Value of parameter solution/term does not contain ZIP-file but filename.
              $filename = $solution_or_term;
              $debug .= '<p>Parameter solution/term contains filename: ' . $filename . '</p>' . $CRLF;
              if ( method_exists( MediaWikiServices::class, 'getRepoGroup' ) ) {
                // MediaWiki 1.34+
                $image= MediaWikiServices::getInstance()->getRepoGroup()->getLocalRepo()
                	->newFile( $filename );
              } else {
                $image= wfLocalFile($filename) ; // Compatibility with MediaWiki >= 1.18.x
              }

              // Get the MediaWiki path of the file
              if (isset( $image )) {
                $fileURL = $image->getURL();
                $pathAndFilename = $wgServer . $fileURL;
                $solution_or_term = file_get_contents($pathAndFilename);
                if (strlen($solution_or_term) < 4)
                   $error = wfMessage( 'formelapplet-file-not-found' )->rawParams( $filename )->escaped();
              } else {
                 $error = wfMessage( 'formelapplet-file-not-found' )->rawParams( $filename )->escaped();
              }
            }
        }

        if ($error_message == 'no error') {
        // Assemble the applet tag
            if ($is_inputapplet) {
              $output = $output . "<param name=" . $quot . "solution" . $quot;
            } else {
              $output = $output . "<param name=" . $quot . "term" . $quot;
            }
            $output = $output . " value=" . $quot . htmlspecialchars(strip_tags($solution_or_term)) . $quot . ">\n";

            // Add code for  non-special parameters
            foreach($args as $par_name => $par_value) {
              if (! in_array(strtolower($par_name), $special_parameters)){
                $parameter = htmlspecialchars(strip_tags($par_name));
                $value = htmlspecialchars(strip_tags($par_value));
                $debug .= '<p>Allgemein: ' . $par_name . ' => ' . $par_value . '</p>' . $CRLF;
                if(strlen($value) > 0) {
                   $output .= '<param name="' . $parameter .'" value="' . $value . '">' . $CRLF;
                }
              }
            }
            // Next line is commented out for sake of compatibility with Java Version >= 1.7 (also known as Java 7)
            // $output .= '<param name="java_arguments" value="-Djnlp.packEnabled=true">'. $CRLF; // Use packed JAR if available
            // Close applet tag
            $output .= $noJavaText . $CRLF; // Message if Java is not installed
            $output .= "</applet>" . $CRLF; // The closing applet tag
        } else {
          $output = wfMessage( 'formelapplet-error' )->rawParams( $error_message )->parseAsBlock() . $CRLF;
        }
        if ($printDebug == 'true') {
           $output .= '<p>' . $debug . '</p>' . $CRLF;
        }
        // Send the output to the browser
        return $output;
} // missing php end tag to avoid troubles.
