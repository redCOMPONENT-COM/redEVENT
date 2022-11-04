<?php
/**
 * @package     Redevent
 * @subpackage  Page
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;

/**
 * Class FrontendJoomlaManagerPage
 * @package Page\Acceptance\Administrator
 */
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
	 * @since 3.2.8
	 */
	public static $componentTitle = '.componentheading';

	/**
	 * @var string
	 * @since 3.2.8
	 */
	public static $categoryVenue = 'Select Some Options';

	/**
	 * @var string
	 * @since 3.2.8
	 */
	public static $messageSuccess = '.alert-success';

	/**
	 * @var string
	 * @since 3.2.9
	 */
	public static $showEvents = 'Show Events';

	/**
	 * @var string
	 * @since 3.2.9
	 */
	public static $dayMissing = 'Currently no events are available';

	/**
	 * @var string
	 * @since 3.2.9
	 */
	public static $dismissButton = "button.dismissButton";

	/**
	 * @param $country
	 * @return string
	 * @since 3.2.9
	 */
	public static function returnVenuesMap($country)
	{
		$path = "//div[@title='".$country."']";
		return $path;
	}

	/**
	 * @var string
	 * @since 3.2.9
	 */
	public static $imageUrlId = "#f_url";

	/**
	 * @var string
	 * @since 3.2.9
	 */
	public static $eventImageThumbnails = '//a[@class="rf_img"]';

	/**
	 * @var string
	 * @since 3.2.9
	 */
	public static $joomlaImage = "images/joomla_black.png";

	/**
	 * @param $placeholder
	 * @return string
	 */
	public static function returnInput($placeholder)
	{
		$path = "//input[@placeholder='".$placeholder."']";
		return $path;
	}

	/**
	 * @param $nameCategory
	 * @return string
	 * @since 3.2.8
	 */
	public static function xPathCategoryVenues($nameCategory)
	{
		$path = "//li[contains(text(), '" . $nameCategory . "')]";
		return $path;
	}
}