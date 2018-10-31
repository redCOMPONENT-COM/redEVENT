<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;

use \Page\Acceptance\Administrator\VenueCategoryManagerPage;
use Step\Acceptance\AdminRedevent;
class VenueCategoryManagerSteps extends AdminRedevent
{
    /**
     * @param $params
     * @throws \Exception
     */
    public function createVenueCategoryNew($params)
    {
        $I = $this;
        $I->createItemNew(VenueCategoryManagerPage::$URL,VenueCategoryManagerPage::$venueCategoryTitle,VenueCategoryManagerPage::$venueCategoryTitleNew,$params);
    }
    /**
     * @param $nameVenueCategory
     * @throws \Exception
     */
    public function searchVenueCategory($nameVenueCategory)
    {
        $I = $this;
        $I->search(VenueCategoryManagerPage::$URL,$nameVenueCategory);
    }

    /**
     * @param $nameVenueCategory
     * @throws \Exception
     */
    public function deleteVenueCategory($nameVenueCategory)
    {
        $I = $this;
        $I->delete(VenueCategoryManagerPage::$URL,VenueCategoryManagerPage::$venueCategoryTitle,$nameVenueCategory);
    }
}