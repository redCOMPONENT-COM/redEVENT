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
		
		echo 'updating tables</br>';
		//change path for images
		
		$query = 'SELECT datimage FROM #__redevent_events';
		$this->db_new->setQuery($query);
		$res = $this->db_new->loadObjectList();
		print_r($res);
		
		$query = 'UPDATE #__redevent_events SET datimage = CONCAT("images/redevent/events/", datimage) WHERE CHAR_LENGTH(datimage) > 0';
		$this->db_new->setQuery($query);
		$res = $this->db_new->query();
		echo ($this->db_new->getQuery())."<br/>";
		
		$query = 'UPDATE #__redevent_categories SET image = CONCAT("images/redevent/categories/", image) WHERE CHAR_LENGTH(image) > 0';
		$this->db_new->setQuery($query);
		$res = $this->db_new->query();
		
		$query = 'UPDATE #__redevent_venues SET locimage = CONCAT("images/redevent/categories/", locimage) WHERE CHAR_LENGTH(locimage) > 0';
		$this->db_new->setQuery($query);
		$res = $this->db_new->query();
		echo 'tables updated</br>';
		
		return true;
	}
}
