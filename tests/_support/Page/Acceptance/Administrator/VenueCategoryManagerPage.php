<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;

/**
 * Class VenueCategoryManagerPage
 * @package Page\Acceptance\Administrator
 */
class VenueCategoryManagerPage extends AbstractPage
{
	/**
	 * Include url of current page
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $URL                     = 'administrator/index.php?option=com_redevent&view=venuescategories';

	/**
	 * Title of this page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $venueCategoryTitle      = "Venue categories";

	/**
	 * Title of this page new category.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $venueCategoryTitleNew   = "Category";

}