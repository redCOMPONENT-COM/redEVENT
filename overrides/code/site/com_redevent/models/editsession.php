<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Venue
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventModelEditsession extends RedeventModelEditsessionDefault
{
	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		if ($source == 'session')
		{
			$language = JFactory::getLanguage();
			$language->load('redevent_maersk' , JPATH_SITE . '/administrator/code/com_redevent', $language->getTag(), true);
			$language->load('com_redevent' , JPATH_SITE . '/administrator/components/com_redevent', $language->getTag(), true);

			$source = 'sessionoverride';

			RForm::addFormPath(JPATH_SITE . '/code/com_redevent/models/forms');
		}

		return parent::loadForm($name, $source, $options, $clear, $xpath);
	}
}
