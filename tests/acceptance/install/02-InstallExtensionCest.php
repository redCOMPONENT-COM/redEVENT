<?php
/**
* @package     redFORM
* @subpackage  Cept
* @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
* @license     GNU General Public License version 2 or later; see LICENSE.txt
*/

class InstallExtensionCest
{
	public function install(\AcceptanceTester $I)
	{
		$I->wantToTest('redEVENT installation in Joomla 3');
		$I->doAdministratorLogin();

		$path = $I->getConfiguration('packages url');

		$I->installExtensionFromUrl($path . '/redFORM/build/redCORE/extensions');
		$I->installExtensionFromUrl($path . '/redFORM/component');
		$I->installExtensionFromUrl($path . 'redevent.zip');
	}
}
