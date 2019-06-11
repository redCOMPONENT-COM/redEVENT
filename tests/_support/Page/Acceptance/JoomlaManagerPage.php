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
	 * @since 3.2.8
	 */
	public static $messageSuccess                    = '.alert-message';

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
	 * @since 3.2.8
	 */
	public static $userURL = "/administrator//index.php?option=com_users";

	/**
	 * @var string
	 * @since 3.2.8
	 *
	 */
	public static $newButton = '.button-new';

	/**
	 * @var string
	 * @since 3.2.8
	 */
	public static $fieldName = '#jform_name';

	/**
	 * @var string
	 * @since 3.2.8
	 */
	public static $loginName = '#jform_username';

	/**
	 * @var string
	 * @since 3.2.8
	 */
	public static $password = '#jform_password';

	/**
	 * @var string
	 * @since 3.2.8
	 */
	public static $confirmPassword = '#jform_password2';

	/**
	 * @var string
	 * @since 3.2.8
	 */
	public static $email = '#jform_email';

	/**
	 * @var string
	 * @since 3.2.8
	 */
	public static $assignedUser = '//a[text()="Assigned User Groups"]';

	/**
	 * @var string
	 * @since 3.2.8
	 */
	public static $superuserRole = '//input[@id="1group_8"]';

	/**
	 * @var string
	 * @since 3.2.8
	 */
	public static $saveButton = '.button-apply';

	/**
	 * @var string
	 * @since 3.2.8
	 */
	public static $messageSaveSuccess = '.alert-success';

	/**
	 * @var string
	 * @since 3.2.8
	 */
	public static $searchField = '//input[@id="filter_search"]';

	/**
	 * @var string
	 * @since 3.2.8
	 */
	public static $buttonTrash = '//div[@id="toolbar-trash"]/button';

	/**
	 * @var string
	 * @since 3.2.8
	 */
	public static $messageUserSuccess = 'User saved.';

	/**
	 * @var string
	 * @since 3.2.8
	 */
	public static $messageDelMenuItemSuccess = '1 menu item trashed.';
	
	/**
	 * @var string
	 * @since 3.2.8
	 */
	public static $messageDelUserSuccess = '1 user deleted.';

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