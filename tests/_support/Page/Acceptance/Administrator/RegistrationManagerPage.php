<?php

/**
 * @package     redEVENT
 * @subpackage  Pages
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Page\Acceptance\Administrator;


class RegistrationManagerPage extends AbstractPage
{
	/**
	 * Include url of current  page
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $URL = "administrator/index.php?option=com_redevent&view=registrations";

	/**
	 * Title of this page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $title = "Registrations";

	/**
	 * Title of this page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $titleAttendees = "Attendees - redEVENT";

	/**
	 * Title of this page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $xpathAnswers = "//legend[contains(text(),'Answers')]";

}