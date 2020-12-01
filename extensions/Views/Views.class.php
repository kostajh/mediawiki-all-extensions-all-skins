<?php
/**
 * Class declaration for mediawiki extension Views
 *
 * @file Views.class.php
 * @ingroup Views
 */

class Views {

	/**
	 * Parse view and return result
	 * @param string $viewName name of view placed in /views/ folder near calling script
	 * @param array $data
	 * @return string
	 * @throws Exception
	 */
	public static function forge( $viewName, $data = array() ) {

			/* Replace bad characters */
			$viewName = preg_replace('/[^a-zA-Z\-_\.]/','',$viewName);

			/* Find view */
			$backtrace = debug_backtrace();
			$viewFile = dirname( $backtrace[0]['file'] ).'/views/'.$viewName.'.php';

			/* Check view existence */
			if (!file_exists($viewFile)) {
				throw new Exception('Views extension: there is no view at '.$viewFile);
			}

			/* Load view */
			extract($data, EXTR_REFS);
			ob_start();
			try
			{
				// Load the view within the current scope
				include($viewFile);
			}
			catch (\Exception $e)
			{
				// Delete the output buffer
				ob_end_clean();
				// Re-throw the exception
				throw $e;
			}
			// Get the captured output and close the buffer
			$ret = ob_get_clean();

			return $ret;

		}

}