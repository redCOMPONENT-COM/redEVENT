<?php

/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;


class SessionManagerPage extends AbstractPage
{
	/**
	 * Include url of current page
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $URL               = 'administrator/index.php?option=com_redevent&view=sessions';

	/**
	 * Title of this page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $sessionTitle      = 'Sessions';

	/**
	 * Title  of this page new session
	 * @var   string
	 * @since 1.0.0
	 */
	public static $sessionTitleNew   = "Event";

	/**
	 * Locator for field name
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldName         = '#jform_title';

	/**
	 * Locator for field Description
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldDescription  = '#jform_description';

	/**
	 * Locator for field Description
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldDate = '#jform_dates_v';

	/**
	 * Locator for field Description
	 * @var array
	 * @since 1.0.0
	 */
	public static $endDate = '#jform_enddates_v';

	/**
	 * Locator for select venue
	 * @var string
	 * @since 1.0.0
	 */
	public static $venueSelect      = 'jform_venueid';

	/**
	 * Locator for select event
	 * @var string
	 * @since 1.0.0
	 */
	public static $eventSelect      = 'jform_eventid';

	/**
	 * Locator for select featured
	 * @var string
	 * @since 1.0.0
	 */
	public static $featuredSelect = "jform_featured";

	/**
	 * Button Save
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public static $buttonSave       = '//button[contains(@onclick, "session.save")]';

	/**
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public static $tableResult      = '//table[@id=\'table-items\']/tbody/tr/td[9]';

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public static $statusPublished = '//label[@for="jform_published0"]';

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public static $statusUnpublished = '//label[@for="jform_published1"]';

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public static $statusArchived = '//label[@for="jform_published2"]';

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public static $searchTools = "//button[contains(text(),'Search tools')]";

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public static $filterPublished = "//div[@id='filter_published_chzn']";

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public static $filterPublishedID = "filter_published";
}