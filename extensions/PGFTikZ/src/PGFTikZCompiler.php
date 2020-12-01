<?php

class PGFTikZCompiler {

	/**
	 * Temporary folder name
	 */
	private $_foldName = "";

	/**
	 * Error message
	 */
	private $_errorMsg = "";

	/**
	 * Destructor (delete temporary folder)
	 */
	public function __destruct() {
		self::rmTempFiles( $this->_foldName );
	}

	/**
	 * Return the temporary folder name (to get the final image)
	 */
	public function getFolder() {
		return $this->_foldName;
	}

	/**
	 * Delete temporary files and folder used for LaTeX compilation
	 */
	private static function rmTempFiles ($dir) {
		if ( is_dir( $dir ) ) {
			wfRecursiveRemoveDir( $dir );
		}
	}

	/**
	 * Return latest error message (always escaped text)
	 */
	public function getError() {
		return $this->_errorMsg;
	}

	/**
	 * Report error with content of log
	 *
	 * @param $msg Message Message object
	 * @param $log string Raw error detail text (e.g. exception message)
	 * @return string Raw error message text
	 */
	private function errorMsgLog( $msg, $log, $nLines = -1 ) {
		$log = explode( PHP_EOL, $log );
		if ( $nLines != -1 ) {
			$nLinesLog = count( $log );
			$log = array_slice( $log, $nLinesLog - $nLines + 1, $nLinesLog);
		}
		// Obfuscate folder name in error message
		$fold = preg_replace( '/\\\\/', '.', wfTempDir() );
		$log = preg_replace( "#" . $fold . "#", "", $log );
		$log = implode ( "<br />", $log );
		return $msg->escaped() . "<br />" . $log;
	}

	/**
	 * Helper function to run shell command and handle errors
	 */
	private function shellCmdHelper( $cmd, $expected_output, $errType ) {

		switch ( strtolower( $errType ) ) {
		case 'latex':
			$errNoOutput = wfMessage( 'pgftikz-error-latexnoout' );
			$errOutput   = wfMessage( 'pgftikz-error-latexcompil' );
			break;
		case 'dvips':
			$errNoOutput = wfMessage( 'pgftikz-error-dvipsnoout' );
			$errOutput   = wfMessage( 'pgftikz-error-dvipscompil' );
			break;
		case 'epstool':
			$errNoOutput = wfMessage( 'pgftikz-error-epstoolnoout' );
			$errOutput   = wfMessage( 'pgftikz-error-epstoolrun' );
			break;
		case 'convert':
			$errNoOutput = wfMessage( 'pgftikz-error-convertnoout' );
			$errOutput   = wfMessage( 'pgftikz-error-convertrun' );
			break;
		case 'ghostscript':
			$errNoOutput = wfMessage( 'pgftikz-error-ghostscriptnoout' );
			$errOutput   = wfMessage( 'pgftikz-error-ghostscriptrun' );
			break;
		}

		$retVal = 0;
		$stdouterr = wfShellExecWithStderr( $cmd, $retVal );
		if ( !file_exists( $expected_output ) || $retVal != 0 ) {
			if ( $stdouterr == '' ) {
				$this->_errorMsg = $errNoOutput->escaped();
				return false;
			}
			$this->_errorMsg = $this->errorMsgLog( $errOutput, $stdouterr, 10 );
			return false;
		}
		return true;
	}

	/**
	 * Compile image from latex code
	 */
	public function generateImage( $preambleStr, $latexContent, $imgFname,
	                               $dpi, $TEXLR ) {

		// Global parameters
		global $wgPGFTikZDefaultDPI;
		global $wgPGFTikZLaTeXPath;
		global $wgPGFTikZLaTeXOpts;
		global $wgPGFTikZdvipsPath;
		global $wgPGFTikZepstoolPath;
		global $wgImageMagickConvertCommand;
		global $wgPGFTikZuseghostscript;
		global $wgPGFTikZghostScriptPath;
		global $wgPGFTikZLaTeXStandalone;

		// 1 - Check ability to compile LaTeX file
		// ---------------------------------------
		// Check if latex is present and if the desired file format can be
		// generated (might require imagemagick/epstool for tight bounding box).
// TODO
		// Commands
		$LATEX       = $wgPGFTikZLaTeXPath;
		$LATEX_OPTS  = $wgPGFTikZLaTeXOpts;
		$DVIPS       = $wgPGFTikZdvipsPath;
		$EPSTOOL     = $wgPGFTikZepstoolPath;
		$CONVERT     = $wgImageMagickConvertCommand;
		$GHOSTSCRIPT = $wgPGFTikZghostScriptPath;

		// 2 - Create .tex file
		// --------------------
		// Store in temporary location (ensure writeable)

		// Build latex string
		// (see http://heinjd.wordpress.com/2010/04/28/
		// creating-eps-figures-using-tikz/)
		if ( !$wgPGFTikZLaTeXStandalone ) {
			$latexStr = '\documentclass{article}' . $TEXLR;
			$latexStr .= '\usepackage{nopageno}' . $TEXLR;
		} else {
			$latexStr = '\documentclass[varwidth=true, border=10pt]' .
			            '{standalone}' . $TEXLR;
		}
		if ( !$wgPGFTikZuseghostscript ) {
			$latexStr .= '\def\pgfsysdriver{pgfsys-dvips.def}' . $TEXLR;
		}
		$latexStr .= '\usepackage[usenames]{color}' . $TEXLR;
		$latexStr .= $preambleStr . $TEXLR;
		$latexStr .= '\begin{document}' . $TEXLR;
		$latexStr .= '\thispagestyle{empty}' . $TEXLR;
		$latexStr .= $latexContent;
		$latexStr .= '\end{document}' . $TEXLR;

		// Write to file
		$latexTmpDir = wfTempDir() . "/tmp_latex_" . rand(1,999999999);
		$this->_foldName = $latexTmpDir;
		if ( !is_dir( $latexTmpDir ) ) {
			if ( !mkdir( $latexTmpDir, 0700, true ) ) {
				$this->_errorMsg =
				    wfMessage ( 'pgftikz-error-tmpdircreate' )->escaped();
				return false;
			}
		}
		$latexBaseFname = $latexTmpDir . "/tikz";
		$latexFname = $latexBaseFname . ".tex";
		$latexWriteRet = file_put_contents( $latexFname, $latexStr );
		if ( !$latexWriteRet ) {
			$this->_errorMsg =
			    wfMessage( 'pgftikz-error-texfilecreate' )->escaped();
			return false;
		}

		// 3 - Generate image (compilation)
		// --------------------------------

		// External calls
		$opt_latex = ( $LATEX_OPTS == '' )? '': wfEscapeShellArg( $LATEX_OPTS );
		$cmd_latex = wfEscapeShellArg( $LATEX ) . " " . $opt_latex .
		    " -output-directory=$latexTmpDir " . wfEscapeShellArg( $latexFname );
		$out_latex = "$latexBaseFname.dvi";
		//print ("Running latex on tikz code\n(<$cmd_latex>)..." . "\n");
		if ( !$this->shellCmdHelper( $cmd_latex, $out_latex, 'latex' ) ) {
			// Error message already set in helper function
			return false;
		}

		// Generate EPS
		$eps_dvips = ( $wgPGFTikZuseghostscript )? "": "-E";
		$cmd_dvips = wfEscapeShellArg( $DVIPS ) . " -R -K0 " . $eps_dvips .
		    " " . wfEscapeShellArg( $latexBaseFname ) . ".dvi " .
		    "-o $latexTmpDir/out.ps";
		$out_dvips = "$latexTmpDir/out.ps";
		//print ("Running dvips on dvi\n(<$cmd_dvips>)..." . "\n");
		if ( !$this->shellCmdHelper( $cmd_dvips, $out_dvips, 'dvips' ) ) {
			return false;
		}

		if ( !$wgPGFTikZuseghostscript ) {
			// Fix bounding box
			$cmd_eps = wfEscapeShellArg( $EPSTOOL ) . " --copy --bbox " .
			    "$latexTmpDir/out.ps $latexTmpDir/out_bb.eps";
			$out_eps = "$latexTmpDir/out_bb.eps";
			//print ("Fixing bounding box\n(<$cmd_eps>)..." . "\n");
			if ( !$this->shellCmdHelper( $cmd_eps, $out_eps, 'epstool' ) ) {
				return false;
			}
			// Convert to desired output
			$cmd_convert = wfEscapeShellArg( $CONVERT ) . " -density $dpi " .
			    "$latexTmpDir/out_bb.eps $latexTmpDir/" .
			    wfEscapeShellArg( $imgFname );
			$out_convert = "$latexTmpDir/$imgFname";
			wfDebug("", "PGFconvert: " . $cmd_convert );
			//print ("Converting file\n(<$cmd_convert>)..." . "\n");
			if ( !$this->shellCmdHelper( $cmd_convert, $out_convert,
			                             'convert' ) ) {
				return false;
			}
		} else {
			$cmd_ghs = wfEscapeShellArg( $GHOSTSCRIPT ) . " -dBATCH -dNOPAUSE" .
			    " -sDEVICE=pngalpha -r" . $dpi ." -dEPSCrop -sOutputFile=" .
			    "$latexTmpDir/" . wfEscapeShellArg( $imgFname ) .
			    " $latexTmpDir/out.ps";
			$out_ghs = "$latexTmpDir/$imgFname";
			if ( !$this->shellCmdHelper( $cmd_ghs, $out_ghs, 'ghostscript' ) ) {
				return false;
			}
		}

		return true;
	}


}

