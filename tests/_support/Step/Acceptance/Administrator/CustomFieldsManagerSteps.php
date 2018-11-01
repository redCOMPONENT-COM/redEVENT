<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;
use Step\Acceptance\AdminRedevent;
use \Page\Acceptance\Administrator\CustomFieldsManagerPage;
class CustomFieldsManagerSteps extends AdminRedevent
{
	public function createCustomFieldNew($params)
	{
		$I = $this;
		$I->amOnPage(CustomFieldsManagerPage::$URL);
		$I->waitForText(CustomFieldsManagerPage::$customFieldsTitle, 30);
		$I->click(CustomFieldsManagerPage::$buttonNew);
		$I->waitForText(CustomFieldsManagerPage::$customFieldsTitleNew, 30);
		$I->fillField(CustomFieldsManagerPage::$fieldName, $params['name']);
		$I->fillField(CustomFieldsManagerPage::$fieldTag, isset($params['tag']) ? $params['tag'] : $params['name']);

		$object = isset($params['object']) ? $params['object'] : 'event';
		$type = isset($params['type']) ? $params['type'] : 'text';

		$I->selectOptionInChosenByIdUsingJs(CustomFieldsManagerPage::$fieldObject, $object);
		$I->selectOptionInChosenByIdUsingJs(CustomFieldsManagerPage::$fieldType, $type);

		$I->click(CustomFieldsManagerPage::$buttonSaveClose);

	}

	/**
	 * @param $nameCustomFields
	 * @throws \Exception
	 */
	public function SearchCustomField($nameCustomFields)
	{
		$I = $this;
		$I->Search(CustomFieldsManagerPage::$URL,$nameCustomFields);
	}

	/**
	 * @param $nameCustomFields
	 * @throws \Exception
	 */
	public function deleteCustomFields($nameCustomFields)
	{
		$I = $this;
        $I->amOnPage(CustomFieldsManagerPage::$URL);
        $I->waitForText(CustomFieldsManagerPage::$customFieldsTitle, 30);
        $I->SearchCustomField($nameCustomFields);
        $I->see($nameCustomFields, CustomFieldsManagerPage::$tableResult);
        $I->click(CustomFieldsManagerPage::$checkAll);
        $I->click(CustomFieldsManagerPage::$buttonDelete);
        $I->wantTo('Test with delete category but then cancel');
        $I->cancelPopup();
        $I->wantTo('Test with delete product then accept');
        $I->click(CustomFieldsManagerPage::$buttonDelete);
        $I->acceptPopup();
        $I->waitForText(CustomFieldsManagerPage::$messageDeleteProductSuccess, 60, CustomFieldsManagerPage::$message);
        $I->dontSee($nameCustomFields);
	}
}