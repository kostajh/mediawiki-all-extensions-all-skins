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
 * Handler for opt-in links sent to re-permission lists
 */
class FundraiserSubscribe
	extends SubscriptionMethod
	implements ISubscriptionValidator
{

	public function __construct() {
		$this->addRequiredParameter( 'email', '/.*@.*/' );
	}

	public function getRequiredValidationParameters() {
		return $this->mRequiredParameters;
	}

	public function validateRequest( array $params ) {
		Logger::pushLabel( 'OptIn' );

		$email = $params['email'];
		$result = Sanitizer::validateEmail( $email );

		// Clean up and return
		Logger::popLabel();
		return $result;
	}

	public function update( $requestID, $process, array $params ) {
		Logger::pushLabel( 'OptIn' );

		$email = $params['email'];

		$message = [
			'process' => $process,
			'email' => $email,
			'variant' => $params['variant']
		];

		// Send to the queue
		Logger::log( 'Placing message in queue for email ' . json_encode( $email ) );
		FundraiserEmailQueue::get( 'opt-in' )->push( $message );

		// Clean up and return
		Logger::popLabel();
		return true;
	}
}
