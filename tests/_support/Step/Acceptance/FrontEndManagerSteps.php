<?php


namespace Step\Acceptance;


use Page\Acceptance\Administrator\FrontendJoomlaManagerPage;

class FrontEndManagerSteps extends AdminRedevent
{
    /**
     * @param $menuitem
     * @param $sessionname
     * @param $eventName
     * @throws \Exception
     */
    public  function  checkEventUpcomingOfVenue($menuitem, $sessionname,$eventName,$venues,$category)
    {
        $I = $this;
        $I->doFrontEndLogin("admin","admin");
        $I->amOnPage(FrontendJoomlaManagerPage::$URL);
        $I->checkForPhpNoticesOrWarningsOrExceptions();
        $I->waitForText(FrontendJoomlaManagerPage::$title,30,FrontendJoomlaManagerPage::$H1);
        $I->waitForText($menuitem,30);
        $I->click($menuitem);
        $I->waitForText($eventName,30);
        $I->click(FrontendJoomlaManagerPage::returnLink($eventName,$sessionname));
        $I->waitForText(FrontendJoomlaManagerPage::returnLink($eventName,$sessionname),30,FrontendJoomlaManagerPage:: $titleEvent);
        $I->waitForText($venues,30,FrontendJoomlaManagerPage::$whereEvent);
        $I->waitForText($category,30,FrontendJoomlaManagerPage::$categoryEvent);
    }
}