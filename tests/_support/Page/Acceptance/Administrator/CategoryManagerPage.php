<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;

/**
 * Class CategoryManagerPage
 * @package Page\Acceptance\Administrator
 */
class CategoryManagerPage extends AbstractPage
{
	/**
	 * Include url of current page
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $URL               = 'Administrator/index.php?option=com_redevent&view=categories';

	/**
	 * Title of this page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $categoryTitle      = "Categories";

	/**
	 * Title of this page new category.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $categoryTitleNew   = "Category";

	/**
	 * Locator for field name
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldName          = '#jform_name';

	/**
	 * Button new
	 * @var string
	 */
	public static $buttonNew          = '//button[contains(@onclick, "category.add")]';
}
