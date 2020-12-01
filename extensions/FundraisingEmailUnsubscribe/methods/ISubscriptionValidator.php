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
 * Interface for subscription class to validate that an inbound request is processable.
 */
interface ISubscriptionValidator {
	/**
	 * Get the URI parameter names required to be passed to validateRequest()
	 * @abstract
	 * @return array Key/value part of URI parameter name => validation regex that shall be used to
	 * clean the input before passing to this function.
	 */
	public function getRequiredValidationParameters();

	/**
	 * Validates that an (un)subscribe request is valid and able to be processed. Should not perform
	 * any API calls (esp. ones that could cost money). The parameters required for this call, and
	 * regex for cleaning should be obtained from getRequiredValidationParameters().
	 * @abstract
	 *
	 * @param array $params Key/value map of URI parameter names => cleaned values
	 *
	 * @return bool TRUE if the request is valid and may be performed by calling unsubscribe().
	 */
	public function validateRequest( array $params );
}
