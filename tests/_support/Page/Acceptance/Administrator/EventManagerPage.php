<?php
/**
 * @package     redEVENT
 * @subpackage  Page
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\acceptance\Administrator;

/**
 * Class EventManagerPage
 * @package Page\acceptance\Administrator
 */
class EventManagerPage extends AbstractPage
{
	/**
	 * Include url of current page
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $URL = 'administrator/index.php?option=com_redevent&view=events';

	/**
	 * Title of this page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $eventTitle         = "Events - redEVENT";

	/**
	 * Title  of this page new Event
	 * @var   string
	 * @since 1.0.0
	 */
	public static $eventTitleNew      = "Add/edit event - redEVENT ";

	/**
	 * @var string
	 */
	public static $tabRegistration    = "Registration";

	/**
	 * @var string
	 * @since 3.2.9
	 */
	public static $managedSessions = "Managed sessions";

	/**
	 * @var string
	 * @since 3.2.9
	 */
	public static $managedEvents = "Managed events";

	/**
	 * @var string
	 * @since 3.2.9
	 */
	public static $managedVenues = "Managed venues";

	/**
	 * @var string
	 */
	public static $enableRegistrationYes    = "//label[@for='jform_registra1']";

	/**
	 * Locator for field name
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldName          = '#jform_title';

	/**
	 * Locator for field Description
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldDescription   = '#jform_datdescription';

	/**
	 * Locator for select category
	 * @var string
	 * @since 1.0.0
	 */
	public static $categorySelect     = "#jform_categories_chzn";

	/**
	 * Locator for item category
	 * @var string
	 * @since 1.0.0
	 */
	public static $categoryItem       = 'jform_categories';

	/**
	 * Locator for select template
	 * @var string
	 * @since 1.0.0
	 */
	public static $templateSelect     = "#jform_template_id";

	/**
	 * Locator for item category
	 * @var string
	 * @since 1.0.0
	 */
	public static $templateItem       = 'jform_template_id';

	/**
	 * Button Save
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public static $buttonSave         = '//button[contains(@OnClick, "event.save")]';

}