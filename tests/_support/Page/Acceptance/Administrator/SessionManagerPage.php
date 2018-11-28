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
}