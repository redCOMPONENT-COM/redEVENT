<?php
/**
 * @package     redEVENT
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
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
	 * @var string
	 */
	public static $menuItemURL = '/administrator/index.php?option=com_menus&view=menus';

	/**
	 * Title of this page.
	 * @var   string
	 * @since 1.0.0
	 */
	public static $extensionsTitle            = "Extensions: Manage";

	/**
	 * @var string
	 */
	public static $menuTitle   = 'Menus';

	/**
	 * @var string
	 */
	public static $menuItemsTitle   = 'Menus: Items';

	/**
	 * @var string
	 */
	public static $menuNewItemTitle   = 'Menus: New Item';

	/**
	 * @var string
	 */
	public static $menuEditItemTittle = 'Menus: Edit Item';

	/**
	 * Menu item title
	 * @var string
	 */
	public static $menItemTitle = "#jform_title";

	/**
	 * @var   string
	 */
	public static $buttonSelect = "Select";

	/**
	 * Menu Type Modal
	 * @var string
	 */
	public static $menuTypeModal = "#menuTypeModal";

	/**
	 * @var string
	 */
	public static $menuItemType   = 'Menu Item Type';

	/**
	 * @var string
	 */
	public static $labelLanguage = "Language";

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
	public static $buttonUninstall            = '//div[@id="toolbar-delete"]/button';

	/**
	 * Message Uninstall component Success
	 *
	 * @var string
	 */
	public static $messageUninstall           = 'Uninstalling the component was successful.';

	/**
	 * @var string
	 */
	public static $message                    = '.alert-message';

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
	public static $messageFailed              = '.alert-no-items';

	/**
	 * @var string
	 */
	public static $messageMenuItemSuccess = 'Menu item saved';

	/**
	 * @var array
	 */
	public static $idInstallSuccess =  "#system-message-container";

	/**
	 * @var string
	 */
	public static $idSelectCategory ="jform_request_id";

	/**
	 * @var string
	 */
	public static $selectSessionLbl = "#jform_request_xref_name";

	/**
	 * @var string
	 */
	public static $selectSession = "//a[@title='Select session']";

	/**
	 * @var string
	 */
	public static $searchSessionId  = "//input[@id='filter_search']";


	/**
	 * @var string
	 */
	public static $searchIcon = "//button[@data-original-title=\"Search\"]";

	/**
	 * @var string
	 */
	public static $locatorEvent = "//td[5]/a";

	/**
	 * @var string
	 */
	public static $userURL = "/administrator//index.php?option=com_users";

	/**
	 * @var string
	 */
	public static $newButton = '.button-new';

	/**
	 * @var string
	 */
	public static $userName = '#jform_name';

	/**
	 * @var string
	 */
	public static $loginName = '#jform_username';

	/**
	 * @var string
	 */
	public static $password = '#jform_password';

	/**
	 * @var string
	 */
	public static $confirmPassword = '#jform_password2';

	/**
	 * @var string
	 */
	public static $email = '#jform_email';

	/**
	 * @var string
	 */
	public static $assignedUser = '//a[text()="Assigned User Groups"]';

	/**
	 * @var string
	 */
	public static $superuserRole = '//input[@id="1group_8"]';

	/**
	 * @var string
	 */
	public static $saveButton = '.button-apply';

	/**
	 * @var string
	 */
	public static $messageSaveSuccess = '.alert-success';

	/**
	 * @var string
	 */
	public static $checkboxAll = '//input[@name="checkall-toggle"]';

	/**
	 * @var string
	 */
	public static $searchField = '//input[@id="filter_search"]';

	/**
	 * @var string
	 */
	public static $buttonTrash = '//div[@id="toolbar-trash"]/button';

	/**
	 * @param $menuCategory
	 * @return array
	 */
	public static function getMenuCategory($menuCategory)
	{
		$menuCate = ["link" => $menuCategory];

		return $menuCate;
	}

	/**
	 * @param $menuItem
	 * @return string
	 */
	public static function returnMenuItem($menuItem)
	{
		$path = "//a[contains(text()[normalize-space()], '$menuItem')]";
		return $path;
	}
}