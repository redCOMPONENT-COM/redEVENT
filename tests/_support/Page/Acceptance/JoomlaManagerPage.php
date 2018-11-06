<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance;


use Page\Acceptance\Administrator\AbstractPage;

class JoomlaManagerPage extends AbstractPage
{
	/**
	 * Include url of current page
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $URLUninstall              = '/administrator/index.php?option=com_installer&view=manage';

	/**
	 * Title of this page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $extensionsTitle            = "Extensions: Manage";

	/**
	 * Locator for table manage
	 *
	 * @var string
	 */
	public static $manageList                 = '#manageList';

	/**
	 *  Locator for checkbox
	 *
	 * @var string
	 */
	public static $checkbox                   = '//input[@id=\'cb0\']';

	/**
	 * Locator for button Uninstall
	 *
	 * @var string
	 */
	public static $buttonUninstall            = '//div[@id=\'toolbar-delete\']/button';

	/**
	 * Message Uninstall component Success
	 *
	 * @var string
	 */
	public static $messageUninstall           = 'Uninstalling the component was successful.l';

	/**
	 * Message not find component
	 *
	 * @var string
	 */
	public static $messageFailedSearch        = 'There are no extensions installed matching your query. ';

	/**
	 *  Locator for message failed
	 *
	 * @var string
	 */
	public static $messageFailed               = '#alert-no-items';
}