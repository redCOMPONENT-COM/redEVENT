<?php
/**
 * @package     redEVENT
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class InstallJoomlaCest
{
	public function installJoomla(\AcceptanceTester $I)
	{
		$I->wantToTest('Joomla 3 Installation');
		$I->installJoomlaRemovingInstallationFolder();
		$I->doAdministratorLogin();
		$I->disableStatistics();
		$I->setErrorReportingToDevelopment();

        $I->amOnPage('/administrator/index.php?option=com_installer');
        $I->waitForText('Extensions: Install', 60, ['css' => 'H1']);

        $pathredCORE = $I->getConfiguration('packages url').'redCORE.zip';
        $I->installExtensionFromUrl($pathredCORE);

        $I->click(['link' => 'Install from Folder']);
        $I->comment('I enter the path');

        $path = $I->getConfiguration('extension folder') . 'tests/extension/redFORM';
        $I->installExtensionFromFolder($path);

        $pathEvent = $I->getConfiguration('packages url').'redevent.zip';
        $I->installExtensionFromUrl($pathEvent);
	}
}
