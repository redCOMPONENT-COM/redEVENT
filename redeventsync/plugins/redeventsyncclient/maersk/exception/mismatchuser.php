<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  plugins.redeventsyncclient
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

/**
 * Class PlgresyncmaerskExceptionMismatchuser
 *
 * @package     Redcomponent.redeventsync
 * @subpackage  plugins.redeventsyncclient
 * @since       1.0
 */
class PlgresyncmaerskExceptionMismatchuser extends Exception
{
	public $email;

	public $venueCode;

	public $firstname;

	public $lastname;

	/**
	 * Constructor
	 *
	 * @param   string  $email      attendee email
	 * @param   int     $venueCode  venue code
	 * @param   string  $firstname  attendee first name
	 * @param   string  $lastname   attendee last name
	 */
	public function __construct($email, $venueCode, $firstname = null, $lastname = null)
	{
		$this->email = $email;
		$this->venueCode = $venueCode;
		$this->firstname = $firstname;
		$this->lastname = $lastname;

		// Make sure everything is assigned properly
		parent::__construct('missing user, or mismatch firstname/lastname, for user for email ' . $email . ' at venue ' . $venueCode);
	}
}
