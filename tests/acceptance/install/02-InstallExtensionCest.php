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
		$path = $I->getConfiguration('install packages url');
		$buildPath = dirname(dirname(dirname(__DIR__))) . '/build';

		$I->installExtensionFromUrl('https://github.com/redCOMPONENT-COM/redCORE/releases/download/1.8.1/redCORE-v1.8.1.zip');
		$I->installExtensionFromFolder($buildPath . '/node_modules/redform/component');
		$I->installExtensionFromUrl($path . 'redevent.zip');
	}
}
