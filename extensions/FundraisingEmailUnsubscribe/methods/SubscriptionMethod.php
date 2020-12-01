<?php
/**
 * -- License --
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
 *
 * @file
 */

/**
 * Abstract class that represents a method to update an email subscription.
 * This could be opting in to our email list or opting out via CiviCRM or a
 * bulk email service.
 */
abstract class SubscriptionMethod {
	/** @var string[] */
	protected $mRequiredParameters = [];

	protected function addRequiredParameter( $name, $regex ) {
		$this->mRequiredParameters[$name] = $regex;
	}

	/**
	 * Get the URI parameter names required to be passed to update()
	 *
	 * @return string[] Key/value part of URI parameter name => validation regex that shall be used to
	 * clean the input before passing to this function.
	 */
	public function getRequiredParameters() {
		return $this->mRequiredParameters;
	}

	/**
	 * Performs the subscription action.
	 *
	 * @param int $requestID The log ID being used for this transaction
	 * @param string $process The name of the currently executing subscription process
	 * @param array $params Key/value map of URI parameter names => cleaned values
	 *
	 * @return bool TRUE if the subscription action was successful.
	 */
	abstract public function update( $requestID, $process, array $params );
}
