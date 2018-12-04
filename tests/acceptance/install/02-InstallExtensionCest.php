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

//		$pathredFORM = $I->getConfiguration('packages redFORM'). 'redFORM';
//		$I->see($pathredFORM);
		$pathredFORM = $I->getConfiguration('extension folder') . 'redFORM/build/redCORE/extensions';
//		$I->installExtensionFromFolder($buildPath . '/redFORM/build/redCORE/extensions');
		$I->installExtensionFromFolder($pathredFORM);
		$I->installExtensionFromUrl($path . 'redevent.zip');
        $I->installExtensionFromFolder($path);
	}
}
