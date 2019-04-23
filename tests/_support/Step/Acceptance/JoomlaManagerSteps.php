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
		$I->waitForElement(JoomlaManagerPage::$menuTypeModal, 5);
		$I->switchToIFrame(JoomlaManagerPage::$menuItemType);

		$I->wantTo("Open the menu category: $menuCategory");
		$I->waitForElement(JoomlaManagerPage::getMenuCategory($menuCategory), 5);
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
		$I->click(JoomlaManagerPage::$buttonNew);
		$I->waitForText(JoomlaManagerPage::$menuNewItemTitle, 5, JoomlaManagerPage::$H1);
		$I->checkForPhpNoticesOrWarnings();
		$I->fillField(JoomlaManagerPage::$menItemTitle, $menuTitle);

		$I->wantTo("Open the menu types iframe");
		$I->click(JoomlaManagerPage::$buttonSelect);
		$I->waitForElement(JoomlaManagerPage::$menuTypeModal, 5);
		$I->switchToIFrame(JoomlaManagerPage::$menuItemType);

		$I->wantTo("Open the menu category: $menuCategory");
		$I->waitForElement(JoomlaManagerPage::getMenuCategory($menuCategory), 5);
		$I->click(JoomlaManagerPage::getMenuCategory($menuCategory));

		$I->wantTo("Choose the menu item type: $menuItem");
		$I->wait(0.5);
		$I->waitForElement(JoomlaManagerPage::returnMenuItem($menuItem),5);
		$I->click(JoomlaManagerPage::returnMenuItem($menuItem));
		$I->wantTo('I switch back to the main window');
		$I->switchToIFrame();
		$I->wantTo('I leave time to the iframe to close');
		$I->selectOptionInChosenById(JoomlaManagerPage::$idSelectCategory, $nameCategory);
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
		$I->click(JoomlaManagerPage::$buttonNew);
		$I->waitForText(JoomlaManagerPage::$menuNewItemTitle, 5, JoomlaManagerPage::$H1);
		$I->checkForPhpNoticesOrWarnings();
		$I->fillField(JoomlaManagerPage::$menItemTitle, $menuTitle);

		$I->wantTo("Open the menu types iframe");
		$I->click(JoomlaManagerPage::$buttonSelect);
		$I->waitForElement(JoomlaManagerPage::$menuTypeModal, 5);
		$I->switchToIFrame(JoomlaManagerPage::$menuItemType);

		$I->wantTo("Open the menu category: $menuCategory");
		$I->waitForElement(JoomlaManagerPage::getMenuCategory($menuCategory), 5);
		$I->click(JoomlaManagerPage::getMenuCategory($menuCategory));

		$I->wantTo("Choose the menu item type: $menuItem");
		$I->wait(0.5);
		$usePage = new JoomlaManagerPage();
		$I->waitForElement($usePage->returnMenuItem($menuItem),5);
		$I->click($usePage->returnMenuItem($menuItem));
		$I->switchToIFrame();

		$I->waitForElement(JoomlaManagerPage::$selectArticleLbl, 30);
		$I->waitForElementVisible(JoomlaManagerPage::$selectArticle, 30);
		$I->click(JoomlaManagerPage::$selectArticle);
		$I->wait(3);
		$I->executeJS('jQuery("iframe").attr("name", "session")');
		$I->pauseExecution();
		$I->switchToIFrame("session");
		$I->waitForElement(JoomlaManagerPage::$searchArticleId, 30);
		$I->fillField(JoomlaManagerPage::$searchArticleId, $nameCategory);
		$I->waitForElement(JoomlaManagerPage::$searchIcon);
		$I->click(JoomlaManagerPage::$searchIcon);
		$I->pauseExecution();
		$I->waitForElementVisible("//td[5]/a", 30);
		$I->click("//td[5]/a");
		$I->wait(0.5);
		$I->switchToIFrame();
		$I->wait(2);
		$I->selectOptionInChosen(JoomlaManagerPage::$labelLanguage, $language);

		$I->waitForText(JoomlaManagerPage::$menuNewItemTitle, '30',JoomlaManagerPage::$H1);
		$I->wantTo('I save the menu');
		$I->click(JoomlaManagerPage::$buttonSave);

		$I->waitForText(JoomlaManagerPage::$messageMenuItemSuccess, 5, JoomlaManagerPage::$idInstallSuccess);
	}
}