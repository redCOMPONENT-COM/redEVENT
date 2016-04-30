<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

$loader = JPATH_LIBRARIES . '/redeventb2b/bootstrap.php';

if (!file_exists($loader))
{
	throw new Exception(JText::_('COM_redeventb2b_LIB_INIT_FAILED'), 404);
}

include_once $loader;

// Bootstraps redEVENTB2B
Redeventb2bBootstrap::bootstrap();

$language = JFactory::getLanguage();
$language->load('com_redevent', JPATH_SITE . '/components/com_redevent', $language->getTag(), true);

try
{
	$controller = JControllerLegacy::getInstance('Redeventb2b');
	$controller->execute(JFactory::getApplication()->input->get('task'));
	$controller->redirect();
}
catch (Exception $e)
{
	if (JDEBUG || 1)
	{
		echo 'Exception:' . $e->getMessage();
		echo "<pre>" . $e->getTraceAsString() . "</pre>";
		exit(0);
	}

	throw $e;
}
