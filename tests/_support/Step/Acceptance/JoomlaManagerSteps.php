<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance;

use \Page\Acceptance\JoomlaManagerPage;
class JoomlaManagerSteps extends AdminRedevent
{
	/**
	 * @param $nameExtensions
	 * @throws \Exception
	 */
	public function uninstallExtension($nameExtensions)
	{
		$I = $this;
		$I->amOnPage(JoomlaManagerPage::$URLUninstall);
		$I->waitForText(JoomlaManagerPage::$extensionsTitle, 30, JoomlaManagerPage::$H1);
		$I->fillField(JoomlaManagerPage::$fieldSearch, $nameExtensions);
		$I->click(JoomlaManagerPage::$buttonSearch);
		$I->waitForElement(JoomlaManagerPage::$manageList);
		$I->click(JoomlaManagerPage::$checkbox);
		$I->click(JoomlaManagerPage::$buttonUninstall);
		$I->acceptPopup();
		$I->see(JoomlaManagerPage::$messageUninstall, JoomlaManagerPage::$message);
		$I->fillField(JoomlaManagerPage::$fieldSearch, $nameExtensions);
		$I->click(JoomlaManagerPage::$buttonSearch);
		$I->see(JoomlaManagerPage::$messageFailedSearch,JoomlaManagerPage::$messageFailed);
	}

	/**
	 * @param $menuTitle
	 * @param $menuCategory
	 * @param $menuItem
	 * @param string $menu
	 * @param string $language
	 *
	 *  @throws \Exception
	 */
	public function createNewMenuItem($menuTitle, $menuCategory, $menuItem, $menu = 'Main Menu', $language = 'All')
	{
		$I = $this;
		$I->wantTo("I open the menus page");
		$I->amOnPage(JoomlaManagerPage::$menuItemURL);
		$I->waitForText(JoomlaManagerPage::$menuTitle, 5, JoomlaManagerPage::$H1);
		$I->checkForPhpNoticesOrWarnings();

		$I->wantTo("I click in the menu: $menu");
		$I->click(array('link' => $menu));
		$I->waitForText(JoomlaManagerPage::$menuItemsTitle, 5,JoomlaManagerPage::$H1);
		$I->checkForPhpNoticesOrWarnings();

		$I->wantTo("I click new");
		$I->click(JoomlaManagerPage::$buttonNew);
		$I->waitForText(JoomlaManagerPage::$menuNewItemTitle, 5, JoomlaManagerPage::$H1);
		$I->checkForPhpNoticesOrWarnings();
		$I->fillField(JoomlaManagerPage::$menItemTitle, $menuTitle);

		$I->wantTo("Open the menu types iframe");
		$I->click(JoomlaManagerPage::$buttonSelect);
		$I->waitForElement(JoomlaManagerPage::$menuTypeModal, 30);
		$I->executeJS('jQuery(".iframe").attr("name", "Menu Item Type")');
		$I->wait(1);
		$I->switchToIFrame(JoomlaManagerPage::$menuItemType);

		$I->wantTo("Open the menu category: $menuCategory");
		$I->wait(1);
		$I->waitForElement(JoomlaManagerPage::getMenuCategory($menuCategory), 30);
		$I->click(JoomlaManagerPage::getMenuCategory($menuCategory));

		$I->wantTo("Choose the menu item type: $menuItem");
		$I->wait(0.5);
		$I->waitForElement(JoomlaManagerPage::returnMenuItem($menuItem),5);
		$I->click(JoomlaManagerPage::returnMenuItem($menuItem));
		$I->wantTo('I switch back to the main window');
		$I->switchToIFrame();
		$I->wantTo('I leave time to the iframe to close');
		$I->selectOptionInChosen(JoomlaManagerPage::$labelLanguage, $language);
		$I->waitForText(JoomlaManagerPage::$menuNewItemTitle, '30',JoomlaManagerPage::$H1);
		$I->wantTo('I save the menu');
		$I->click(JoomlaManagerPage::$buttonSave);
		$I->waitForText(JoomlaManagerPage::$messageMenuItemSuccess, 5, JoomlaManagerPage::$idInstallSuccess);
	}

	/**
	 * @param $menuTitle
	 * @param $menuCategory
	 * @param $menuItem
	 * @param string $menu
	 * @param string $language
	 *
	 *  @throws \Exception
	 */
	public function createNewMenuItemHaveCategory($menuTitle, $menuCategory, $menuItem, $nameCategory, $menu = 'Main Menu', $language = 'All')
	{
		$I = $this;
		$I->wantTo("I open the menus page");
		$I->amOnPage(JoomlaManagerPage::$menuItemURL);
		$I->waitForText(JoomlaManagerPage::$menuTitle, 5, JoomlaManagerPage::$H1);
		$I->checkForPhpNoticesOrWarnings();

		$I->wantTo("I click in the menu: $menu");
		$I->click(array('link' => $menu));
		$I->waitForText(JoomlaManagerPage::$menuItemsTitle, 5,JoomlaManagerPage::$H1);
		$I->checkForPhpNoticesOrWarnings();

		$I->wantTo("I click new");
		$I->click(JoomlaManagerPage::$newButton);
		$I->waitForText(JoomlaManagerPage::$menuNewItemTitle, 5, JoomlaManagerPage::$H1);
		$I->checkForPhpNoticesOrWarnings();
		$I->fillField(JoomlaManagerPage::$menItemTitle, $menuTitle);

		$I->wantTo("Open the menu types iframe");
		$I->click(JoomlaManagerPage::$buttonSelect);
		$I->waitForElement(JoomlaManagerPage::$menuTypeModal, 30);
		$I->switchToIFrame(JoomlaManagerPage::$menuItemType);

		$I->wantTo("Open the menu category: $menuCategory");
		$I->wait(2);
		$I->waitForElement(JoomlaManagerPage::getMenuCategory($menuCategory), 30);
		$I->click(JoomlaManagerPage::getMenuCategory($menuCategory));

		$I->wantTo("Choose the menu item type: $menuItem");
		$I->wait(0.5);
		$I->waitForElement(JoomlaManagerPage::returnMenuItem($menuItem),5);
		$I->click(JoomlaManagerPage::returnMenuItem($menuItem));
		$I->wantTo('I switch back to the main window');
		$I->switchToIFrame();
		$I->wantTo('I leave time to the iframe to close');
		$I->selectOptionInChosenById(JoomlaManagerPage::$idSelectCategory, $nameCategory);

		$I->waitForText(JoomlaManagerPage::$menuNewItemTitle, '30',JoomlaManagerPage::$H1);
		$I->wantTo('I save the menu');
		$I->click(JoomlaManagerPage::$buttonSave);

		$I->waitForText(JoomlaManagerPage::$messageMenuItemSuccess, 5, JoomlaManagerPage::$idInstallSuccess);
	}

	/**
	 * @param $menuTitle
	 * @param $menuCategory
	 * @param $menuItem
	 * @param string $menu
	 * @param string $language
	 *
	 *  @throws \Exception
	 */
	public function createNewMenuItemHaveSession($menuTitle, $menuCategory, $menuItem, $nameCategory, $menu = 'Main Menu', $language = 'All')
	{
		$I = $this;
		$I->wantTo("I open the menus page");
		$I->amOnPage(JoomlaManagerPage::$menuItemURL);
		$I->waitForText(JoomlaManagerPage::$menuTitle, 5, JoomlaManagerPage::$H1);
		$I->checkForPhpNoticesOrWarnings();

		$I->wantTo("I click in the menu: $menu");
		$I->click(array('link' => $menu));
		$I->waitForText(JoomlaManagerPage::$menuItemsTitle, 5,JoomlaManagerPage::$H1);
		$I->checkForPhpNoticesOrWarnings();

		$I->wantTo("I click new");
		$I->click(JoomlaManagerPage::$newButton);
		$I->waitForText(JoomlaManagerPage::$menuNewItemTitle, 5, JoomlaManagerPage::$H1);
		$I->checkForPhpNoticesOrWarnings();
		$I->fillField(JoomlaManagerPage::$menItemTitle, $menuTitle);

		$I->wantTo("Open the menu types iframe");
		$I->click(JoomlaManagerPage::$buttonSelect);
		$I->waitForElement(JoomlaManagerPage::$menuTypeModal, 5);
		$I->switchToIFrame(JoomlaManagerPage::$menuItemType);

		$I->wantTo("Open the menu category: $menuCategory");
		$I->wait(2);
		$I->waitForElement(JoomlaManagerPage::getMenuCategory($menuCategory), 30);
		$I->click(JoomlaManagerPage::getMenuCategory($menuCategory));

		$I->wantTo("Choose the menu item type: $menuItem");
		$I->wait(0.5);
		$usePage = new JoomlaManagerPage();
		$I->waitForElement($usePage->returnMenuItem($menuItem), 5);
		$I->click($usePage->returnMenuItem($menuItem));
		$I->switchToIFrame();

		$I->waitForElement(JoomlaManagerPage::$selectSessionLbl, 30);
		$I->waitForElementVisible(JoomlaManagerPage::$selectSession, 30);
		$I->wait(0.5);
		$I->click(JoomlaManagerPage::$selectSession);
		$I->wait(1);
		$I->executeJS('jQuery("iframe").attr("name", "session")');
		$I->wait(1);
		$I->switchToIFrame("session");
		$I->waitForElement(JoomlaManagerPage::$searchSessionId, 30);
		$I->fillField(JoomlaManagerPage::$searchSessionId, $nameCategory);
		$I->wait(0.5);
		$I->waitForElementVisible(JoomlaManagerPage::$searchIcon, 30);
		$I->click(JoomlaManagerPage::$searchIcon);
		$I->wait(0.5);
		$I->waitForText($nameCategory, 30);
		$I->waitForElementVisible(['link' => $nameCategory], 30);
		$I->click(['link' => $nameCategory]);
		$I->wait(0.5);
		$I->switchToIFrame();
		$I->wait(0.5);

		$I->waitForText(JoomlaManagerPage::$menuNewItemTitle, '30',JoomlaManagerPage::$H1);
		$I->wantTo('I save the menu');
		$I->executeJS('window.scrollTo(0,0);');
		$I->waitForText(JoomlaManagerPage::$buttonSaveClose, 10);
		$I->click(JoomlaManagerPage::$buttonSaveClose);
		$I->waitForText(JoomlaManagerPage::$messageMenuItemSuccess, 5, JoomlaManagerPage::$idInstallSuccess);
	}

	/**
	 * @param $menutitle
	 * @throws \Exception
	 * @since 3.2.7
	 */
	public function deleteNewMenuItem($menutitle, $menu = 'Main Menu')
	{
		$I = $this;
		$I->wantTo("I open the menus page");
		$I->amOnPage(JoomlaManagerPage::$menuItemURL);
		$I->waitForText(JoomlaManagerPage::$menuTitle, 5, JoomlaManagerPage::$H1);
		$I->checkForPhpNoticesOrWarnings();

		$I->wantTo("I click in the menu: $menu");
		$I->click(array('link' => $menu));
		$I->waitForText(JoomlaManagerPage::$menuItemsTitle, 5,JoomlaManagerPage::$H1);
		$I->checkForPhpNoticesOrWarnings();

		$I->wantTo("I search menu item");
		$I->fillField(JoomlaManagerPage::$searchField, $menutitle);
		$I->click(JoomlaManagerPage::$searchIcon);

		$I->wantTo("I choose all menu item");
		$I->checkAllResults();

		$I->wantTo("I delete menu item");
		$I->click(JoomlaManagerPage::$buttonTrash);
		$I->waitForText(JoomlaManagerPage::$messageDelMenuItemSuccess, 5, JoomlaManagerPage::$messageSuccess);
	}

	/**
	 * @param $name
	 * @throws \Exception
	 * @since 3.2.7
	 */
	public function deleteNewSuperUser($name)
	{
		$I = $this;
		$I->wantTo("I open the user page");
		$I->amOnPage(JoomlaManagerPage::$userURL);
		$I->waitForElementVisible(JoomlaManagerPage::$newButton, 30);
		$I->checkForPhpNoticesOrWarnings();

		$I->wantTo("I search user");
		$I->fillField(JoomlaManagerPage::$searchField, $name);
		$I->click(JoomlaManagerPage::$searchIcon);
		$I->waitForElementVisible(JoomlaManagerPage::$buttonUninstall, 30);
		$I->checkAllResults();
		$I->click(JoomlaManagerPage::$buttonUninstall);
		$I->acceptPopup();
		$I->waitForText(JoomlaManagerPage::$messageDelUserSuccess, 5, JoomlaManagerPage::$messageSuccess);
	}
}
