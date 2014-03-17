<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  plugins.redeventsyncclient
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

/**
 * Class PlgresyncmaerskExceptionInvalidattendee
 *
 * @package     Redcomponent.redeventsync
 * @subpackage  plugins.redeventsyncclient
 * @since       1.0
 */
class PlgresyncmaerskExceptionMissinguser extends PlgresyncmaerskExceptionMismatchuser
{
	public $email;

	public $venueCode;

	/**
	 * Constructor
	 *
	 * @param   string  $email      attendee email
	 * @param   int     $venueCode  venue code
	 */
	public function __construct($email, $venueCode)
	{
		$this->email = $email;
		$this->venueCode = $venueCode;

		parent::__construct($email, $venueCode);
	}
}
