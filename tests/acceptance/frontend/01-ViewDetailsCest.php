<?php
/**
 * @package     Redevent
 * @subpackage  Tests
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class ViewDetailsCest
{
	public function viewDetails(\AcceptanceTester $I)
	{
		$I->wantTo('View an event details in frontend');
		$I->checkForPhpNoticesOrWarningsOrExceptions('index.php?option=com_redevent&view=details&id=1');
		$I->see('Event 1', 'h1');
	}
}
