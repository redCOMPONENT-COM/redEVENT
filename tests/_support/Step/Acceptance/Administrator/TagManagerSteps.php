<?php

/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;

use \Page\Acceptance\Administrator\TagManagerPage;
use Step\Acceptance\AdminRedevent;

class TagManagerSteps extends AdminRedevent
{
	/**
	 * @param $name
	 * @param $description
	 * @param $content
	 * @throws \Exception
	 */
	public function createTagNew($name, $description, $content)
	{
		$I = $this;
		$I->amOnPage(TagManagerPage::$URL);
		$I->waitForText(TagManagerPage::$TagTitle, 30);
		$I->click(TagManagerPage::$buttonNew);
		$I->waitForText(TagManagerPage::$TagTitleNew, 30);
		$I->waitForElement(TagManagerPage::$fieldName,30);
		$I->fillField(TagManagerPage::$fieldName, $name);
		$I->waitForElement(TagManagerPage::$fieldDescription,30);
		$I->fillField(TagManagerPage::$fieldDescription, $description);
		if (!empty($content))
		{
			$I->fillTinyMceEditorById(TagManagerPage::$fieldContent, $content);
		}
		$I->click( TagManagerPage::$buttonSave);
		$I->waitForText(TagManagerPage::$messageSaveSuccess, 30,TagManagerPage::$message);
	}

	/**
	 * @param $TagName
	 */
	public function searchTag($TagName)
	{
		$I = $this;
		$I->search(TagManagerPage::$URL,$TagName);
	}
	/**
	 * @param $Tagname
	 * @throws \Exception
	 */
	public function deleteTag($Tagname)
	{
		$I = $this;
		$I->amOnPage(TagManagerPage::$URL);
		$I->waitForText(TagManagerPage::$TagTitle, 30);
		$I->searchTag($Tagname);
		$I->see($Tagname, TagManagerPage::$tableResult);
		$I->checkAllResults();
		$I->click(TagManagerPage::$buttonDelete);
		$I->wantTo('Test with delete category but then cancel');
		$I->cancelPopup();
		$I->wantTo('Test with delete product then accept');
		$I->click(TagManagerPage::$buttonDelete);
		$I->acceptPopup();
		$I->waitForText(TagManagerPage::$notificationNoItem, 60);
		$I->dontSee($Tagname);
	}

}
