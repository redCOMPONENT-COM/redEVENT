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
	public static $imagesRegistration = "//div[@class='registration_method webform']//a";

	/**
	 * Images of Registration.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $submit = "#regularsubmit";

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	public static $calendar = ".jlcalendar";

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	public static $calendarToday = ".today";

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	public static $tableCategoryEvent = ".el_categoryevents";

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	public static $titleEvent = "//dd[@class='title']";

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	public static $whereEvent = "//dd[@class='where']";

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	public static $categoryEvent = "//dd[@class='category']";

	/**
	 * @var   string
	 * @since 1.0.0
	 */

	public static $titleSearchEvent = "Search events";

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	public static $tableSearch = ".container-fluid";

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	public static $fieldSearchFrontEnd = "//input[@name='filter']";

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	public static $buttonSearchFrontEnd = '//button[@onclick="document.getElementById(\'adminForm\').submit();"]';

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	public static $titleOnTable = "//td[@class='re_title']";

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	public static $eventList = ".el_eventlist";

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	public static $event = ".redevent";

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	public static $messageSaveSessionSuccess = "Item submitted.";

	/**
	 * @var string
     * @since 1.0.0
	 */
	public static $venueSubmissionTitle = '.componentheading';

	/**
	 * @var string
     * @since 1.0.0
	 */
	public static $categoryVenue = 'Select Some Options';

	/**
	 * @var string
     * @since 1.0.0
	 */
	public static $usernameField = '#modlgn-username';

	/**
	 * @var string
     * @since 1.0.0
	 */
	public static $passwordField = '#modlgn-passwd';

	/**
	 * @var string
     * @since 1.0.0
	 */
	public static $loginButton = '.login-button';

	/**
	 * @var string
     * @since 1.0.0
	 */
	public static $homeTitle = '.blog-featured';

	/**
	 * @var string
     * @since 1.0.0
	 */
	public static $saveButton = '//button[@onclick="Joomla.submitbutton(\'editvenue.save\')"]';

	/**
	 * @var string
     * @since 1.0.0
	 */
	public static $messageSuccess = '.alert-success';

	/**
	 * @param $placeholder
	 * @return string
	 */
	public static function returnInput($placeholder)
	{
		$path = "//input[@placeholder='".$placeholder."']";
		return $path;
	}

	public static function xPathCategoryVenues($t)
	{
		$path = "//li[contains(text(), '" . $t . "')]";

		return $path;
	}

}