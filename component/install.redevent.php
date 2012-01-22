<?php
/**
 * @version 2.5
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 - 2010 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class com_redeventInstallerScript
{	
	/**
	* method to run after an install/update/uninstall method
	*
	* @return void
	*/
	function postflight($type, $parent)
	{
		/* Install redform plugin */
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		
		$db = &JFactory::getDBO();
		
		JFolder::copy(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_redevent'.DS.'extras'.DS.'redform', JPATH_SITE.DS.'tmp'.DS.'redform_redevent', '', true);
		$installer = new JInstaller();
		$installer->setAdapter('plugin');
		if (!$installer->install(JPATH_SITE.DS.'tmp'.DS.'redform_redevent')) {
			echo JText::_('COM_REDEVENT_Plugin_install_failed:_') . $installer->getError().'<br />';
		}
		else {
			// autopublish the plugin
			$query = ' UPDATE #__extensions SET enabled = 1 WHERE folder = '. $db->Quote('redform_integration') . ' AND element = '.$db->Quote('redevent');
			$db->setQuery($query);
			if ($db->query()) {
				echo JText::_('COM_REDEVENT_Succesfully_installed_redform_integration_plugin').'<br />';
			}
			else {
				echo JText::_('COM_REDEVENT_Error_publishing_redform_integration_plugin').'<br />';
			}
		}
	}
}
