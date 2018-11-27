<?php

/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;


class TagManagerPage extends AbstractPage
{
	/**
	 * Include url of current page
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $URL                = 'administrator/index.php?option=com_redevent&view=textsnippets';

	/**
	 * Title of this page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $TagTitle           = "Text library";

	/**
	 * Title  of this page new tag
	 * @var   string
	 * @since 1.0.0
	 */
	public static $TagTitleNew        = "Add/edit text snippet";

	/**
	 * Locator for field name
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldName           = '#jform_text_name';

	/**
	 * Locator for field Description
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldDescription    = '#jform_text_description';

	/**
	 * Locator for field Content
	 * @var array
	 * @since 1.0.0
	 */
	public static $fieldContent        = 'jform_text_field';

	/**
	 * Button Save
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public static $buttonSave         = '//button[contains(@onclick, "textsnippet.save")]';

	/**
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public static $tableResult        = '//table[@id=\'table-items\']/tbody/tr[1]/td[4]';
}