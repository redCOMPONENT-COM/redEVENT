<?php

/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;

/**
 * Class BundleManagerPage
 * @package Page\Acceptance\Administrator
 */
class BundleManagerPage extends AbstractPage
{
	/**
	 * Include url of current page
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $URL              ='administrator/index.php?option=com_redevent&view=bundles';

	/**
	 * Title of this page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $bundleTitle      = "Bundles";

	/**
	 * Label
	 * @var   string
	 * @since 1.0.0
	 */
	public static $bundleTitleNew   = "Name";

	/**
	 * Button Save
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public static $buttonSave       = '//button[contains(@onclick,"bundle.save")]';
}