<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Load the Step Object Page
$I = new \AcceptanceTester($scenario);
$I->wantToTest(' that there are no Warnings or Notices in redFORM');
$I->doAdministratorLogin();
$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent');
$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=categories');
$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=customfields');
$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=events');
$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=eventtemplates');
$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=logs');
$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=organizations');
$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=pricegroups');
$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=registrations');
$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=roles');
$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=sessions');
$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=tags');
$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=textsnippets');
$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=tools');
$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=venues');
$I->checkForPhpNoticesOrWarningsOrExceptions('administrator/index.php?option=com_redevent&view=venuescategories');
