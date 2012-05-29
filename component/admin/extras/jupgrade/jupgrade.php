<?php
/**
 * jUpgrade
 *
 * @version		$Id: 
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @copyright	Copyright 2006 - 2011 Matias Aguirre. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @author		Matias Aguirre <maguirre@matware.com.ar>
 * @link		http://www.matware.com.ar
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * jUpgrade class for RedMember migration
 *
 * This class migrates the RedMember extension
 *
 * @since		1.2.3
 */
class jUpgradeComponentRedEvent extends jUpgradeExtensions
{
	/**
	 * Check if extension migration is supported.
	 *
	 * @return	boolean
	 * @since	1.2.3
	 */
	protected function detectExtension()
	{
		return true;
	}

	/**
	 * Migrate tables
	 *
	 * @return	boolean
	 * @since	1.2.3
	 */
	public function migrateExtensionCustom()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport('joomla.application.component.helper');

		// copy attachements
		$params = JComponentHelper::getParams('com_redevent');
		$src = JPATH_ROOT.DS.$params->get('attachments_path', 'media/com_redevent/attachments');
		$dest = JPATH_ROOT.DS.'jupgrade'.DS.$params->get('attachments_path', 'media/com_redevent/attachments');
		
		if (file_exists($src) && !file_exists($dest)) 
		{
			$res = JFolder::copy($src, $dest);
			if (!$res === true) {
				return false;
			}
		}
		
		return true;
	}
}
