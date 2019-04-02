<?php
/**
 * @package     Redevent
 * @subpackage  Page
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Page\Acceptance\Administrator;

class FrontendJoomlaManagerPage extends AbstractPage
{
	/**
	 * Include url of current page
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $URL = '/';

	/**
	 * Title of this page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $title = "Home";

	/**
	 * Images of Registration.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $imagesRegistration = "//img[@src='http://localhost/redEvent/media/com_redevent/images/form_icon.gif']";

	/**
	 * Images of Registration.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $submit = "#regularsubmit";


	/**
	 * @param $placeholder
	 * @return string
	 */
	public static function returnInput($placeholder)
	{
		$path = "//input[@placeholder='".$placeholder."']";
		return $path;
	}

}