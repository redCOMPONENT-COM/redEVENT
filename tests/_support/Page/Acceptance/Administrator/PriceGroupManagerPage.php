<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\acceptance\administrator;


class PriceGroupManagerPage extends AbstractPage
{
	/**
	 * Include url of current page
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $URL                  = 'administrator/index.php?option=com_redevent&view=pricegroups';

	/**
	 * Title of this page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $priceGroupTitle      = "Price groups - redEVENT";

	/**
	 * Title of this page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $priceGroupTitleNew   = "Add/edit pricegroup";

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public static $tableResult          ='//table[@id=\'table-items\']/tbody/tr[1]/td[4]';
}