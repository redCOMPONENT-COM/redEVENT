<?php
/**
 * @package    Redevent.plugins
 * @copyright  redEVENT (C) 2008-2016 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Class PlgInstallerRedevent
 *
 * @since  3.2.2
 */
class PlgInstallerRedevent extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.2.2
	 */
	protected $autoloadLanguage = true;

	/**
	 * Intercepts redevent update url
	 *
	 * @param   string  $url      download url
	 * @param   array   $headers  headers
	 *
	 * @return boolean
	 */
	public function onInstallerBeforePackageDownload(&$url, &$headers)
	{
		if (strstr($url, 'https://www.redcomponent.com/index.php/redcomponent/redevent'))
		{
			JFactory::getApplication()->enqueueMessage(
				JText::_('PLG_INSTALLER_REDEVENT_NOTICE_MANUAL_DOWNLOAD_REQUIRED'),
				'notice'
			);
			$url = false;
		}

		return true;
	}
}
