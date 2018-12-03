<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Faker\Generator;
use Faker\Factory;
use Step\Acceptance\Administrator\TagManagerSteps;
use Page\Acceptance\Administrator\AbstractPage;
class TagManagerCest
{
	/**
	 * @var   Generator
	 * @since 1.0.0
	 */
	protected $faker;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $tagName1;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $tagName2;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $description;

	/**
	 * @var   string
	 * @since 1.0.0
	 */
	protected $content;

	/**
	 * TagManagerSteps constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->faker       = Factory::create();
		$this->tagName1 = $this->faker->bothify("Tag Name 1 ##??");
		$this->tagName2 = $this->faker->bothify("Tag Name 2 ##??");
		$this->description = $this->faker->bothify("Description Category  ##??");
		$this->content = $this->faker->bothify("<p>the tag content goes here</p>");
	}
	public function _before(\AcceptanceTester $i)
	{
		$i->doAdministratorLogin();
	}

	/**
	 * @param TagManagerSteps $I
	 * @throws Exception
	 */
	public function addTag(TagManagerSteps $I)
	{
		$I->wantToTest('Add a tag 1 in redEVENT');
		$I->createTagNew($this->tagName1,$this->description,$this ->content);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
		$I->wantToTest('Add a tag 2 in redEVENT');
		$I->createTagNew($this->tagName2,$this->description,$this ->content);
		$I->waitForText(AbstractPage::$messageSaveSuccess, 30, AbstractPage::$message);
		$I->wantToTest('Search tag in redEVENT');
		$I->searchTag($this->tagName1);
		$I->dontSee($this->tagName2);
		$I->buttonClear($this->tagName1,$this->tagName2);
		$I->wantToTest('delete a category 1 in redEVENT');
		$I->deleteTag($this->tagName1);
		$I->wantToTest('delete a category 2 in redEVENT');
		$I->deleteTag($this->tagName2);
	}
}
