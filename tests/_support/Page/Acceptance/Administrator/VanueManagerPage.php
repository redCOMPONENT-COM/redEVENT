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
	public static $URL = 'administrator/index.php?option=com_redevent&view=venues';

	public static $venueTitle = "Venues - redEVENT";
	public static $venueTitleNew= "Add/edit venue - redEVENT";
	public static $fieldName = '#jform_venue';
	public static $fieldDescription ='#jform_description';
	public static $messageSaveSuccess ='Item saved.';
	public static $categoryVanueSelect = "#jform_categories_chzn";
	public static $categoryVanueItem = 'jform_categories';
	public static $buttonSave ='//button[contains(@onclick, "venue.save")]';

}