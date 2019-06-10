<?php

/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\acceptance\administrator;

class VanueManagerPage extends AbstractPage
{
	/**
	 * Include url of current page
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $URL                  = 'administrator/index.php?option=com_redevent&view=venues';

	/**
	 * Title of this page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $venueTitle           = "Venues - redEVENT";

	/**
	 * Title  of this page new Vanue
	 * @var   string
	 * @since 1.0.0
	 */
	public static $venueTitleNew        = "Add/edit venue - redEVENT";

	/**
	 * Locator for field name
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldName            = '#jform_venue';

	/**
	 * Locator for field description
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldDescription     = '#jform_description';

	/**
	 * Locator for Select category Vanue
	 * @var array
	 * @since 1.0.0
	 */
	public static $categoryVanueSelect  = "#jform_categories_chzn";

	/**
	 * Locator for Item category Vanue
	 * @var array
	 * @since 1.0.0
	 */
	public static $categoryVanueItem   = 'jform_categories';

	/**
	 * Button Save
	 * @var string
     * @since 1.0.0
	 */
	public static $buttonSave         = '//button[contains(@OnClick, "venue.save")]';
}
