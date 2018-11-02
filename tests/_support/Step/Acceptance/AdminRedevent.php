<?php
namespace Step\Acceptance;
use \Page\Acceptance\Administrator\AbstractPage;
class AdminRedevent extends \AcceptanceTester
{
	/**
	 * Create a Bundle
	 *
	 * @param   array  $params  parameters
	 *
	 * @return void
	 */
	public function createBundle($params)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_redevent&view=bundles');
		$I->waitForText('Bundles', 30, ['css' => 'H1']);
		$I->click(['xpath' => '//button[contains(@onclick, "bundle.add")]']);
		$I->waitForText('Name', 30, ['css' => 'label']);
		$I->fillField(['id' => 'jform_name'], $params['name']);

		if (!empty($params['description']))
		{
			$I->fillTinyMceEditorById('jform_description', $params['description']);
		}

		$I->click(['xpath' => '//button[contains(@onclick, "bundle.save")]']);
	}

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
		$I->waitForText($title, 120);
		$I->Search($URL,$name);
		$I->see($name, AbstractPage::$tableResult);
		$I->click(AbstractPage::$checkAll);
		$I->click(AbstractPage::$buttonDelete);
		$I->wantTo('Test with delete category but then cancel');
		$I->cancelPopup();
		$I->wantTo('Test with delete product then accept');
		$I->click(AbstractPage::$buttonDelete);
		$I->acceptPopup();
		$I->waitForText(AbstractPage::$messageDeleteProductSuccess, 120, AbstractPage::$message);
		$I->dontSee($name);
	}
}

