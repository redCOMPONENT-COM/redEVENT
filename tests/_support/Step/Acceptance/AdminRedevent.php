<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance;
use \Page\Acceptance\Administrator\AbstractPage;
class AdminRedevent extends \AcceptanceTester
{
	/**
	 * Function create for item
	 *
	 * @param $URL
	 * @param $itemTitle
	 * @param $itemTitleNew
	 * @param $params
	 *
	 * @return  void
	 * @throws \Exception
	 */
	public function createItemNew($URL,$itemTitle,$itemTitleNew,$params)
	{
		$I = $this;
		$I->amOnPage($URL);
		$I->waitForText($itemTitle, 30, AbstractPage::$H1);
		$I->click(AbstractPage::$buttonNew);
		$I->waitForText($itemTitleNew, 30, AbstractPage::$label);
		$I->fillField(AbstractPage::$fieldName, $params['name']);

		if (!empty($params['description']))
		{
			$I->fillTinyMceEditorById(AbstractPage::$fieldDescription, $params['description']);
		}

		$I->click(AbstractPage::$buttonSaveClose);
	}

	/**
	 * Function search for item
	 *
	 * @param  string $URL   url of page
	 * @param  string $name  name of item
	 *
	 * @return  void
	 * @throws \Exception
	 */
	public function search($URL,$name)
	{
		$I = $this;
		$I->amOnPage($URL);
		$I->fillField(AbstractPage::$fieldSearch, $name);
		$I->click(AbstractPage::$buttonSearch);
		$I->seeElement(AbstractPage::$tableResult);
	}

	/**
	 * Function clear for item
	 *
	 * @param  string $name1   name1 of item
	 * @param  string $name2   name2 of item
	 *
	 * @return  void
	 * @throws \Exception
	 */
	public function buttonClear($name1,$name2)
	{
		$I = $this;
		$I->wantToTest(' that the reset button works');
		$I->click(AbstractPage::$buttonClear);
		$I->dontSee($name1, AbstractPage::$fieldSearch);
		$I->see($name1);
		$I->see($name2);
	}

	/**
	 * Function delete for item
	 *
	 * @param string $URL      url of page
	 * @param string $title    title of page
	 * @param string $name     name of item
	 *
	 * @return  void
	 * @throws \Exception
	 */
	public function delete($URL,$title,$name)
	{
		$I = $this;
		$I->amOnPage($URL);
		$I->checkForPhpNoticesOrWarnings();
		$I->waitForText($title, 120);
		$I->Search($URL,$name);
		$I->see($name, AbstractPage::$tableResult);
		$I->checkAllResults();
		$I->click(AbstractPage::$buttonDelete);
		$I->wantTo('Test with delete category but then cancel');
		$I->cancelPopup();
		$I->wantTo('Test with delete product then accept');
		$I->click(AbstractPage::$buttonDelete);
		$I->wait(1);
		$I->acceptPopup();
		$I->waitForText(AbstractPage::$notificationNoItem, 30);
		$I->dontSee($name);
	}

	/**
	 * @throws \Exception
	 * @since 3.2.10
	 */
	public function doAdministratorRedEventLogin()
	{
		$I = $this;
		$I->doAdministratorLogin("admin",  "admin", null);
	}
}

