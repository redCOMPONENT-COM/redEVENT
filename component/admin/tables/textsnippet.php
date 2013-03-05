<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
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

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Table Textsnippet
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 2.0
 */
class RedeventTableTextsnippet extends FOFTable {

	/**
	* @param database A database connector object
	*/
	public function __construct($table, $key, &$db) {
		parent::__construct('#__redevent_textlibrary', 'id', $db);
	}

	public function check()
	{
		if (!$this->text_name)
		{
			$this->setError(JText::_( 'COM_REDEVENT_NAME_IS_REQUIRED'));
			return false;
		}

		// check tag unicity
		$exists = self::checkTagExists($this->text_name);

		if ($exists && !($exists->section == 'library' && $exists->id == $this->id))
		{
			$this->setError(JText::sprintf('COM_REDEVENT_ERROR_TAG_ALREADY_EXISTS', $exists->section));
			return false;
		}

		return true;
	}

	/**
	 * checks wether a tag already exists
	 *
	 * @param string $tag tag name
	 * @return mixed boolean false if doesn't exists, tag object if it does
	 */
	function checkTagExists($tag)
	{
		$db = JFactory::getDBO();
		$model = FOFModel::getAnInstance('tags', 'redeventModel');
		$core = $model->getData();
		foreach ($core as $cat)
		{
			foreach ($cat as $t)
			{
				if (strcasecmp($t->name, $tag) == 0)
				{
					return $t;
				}
			}
		}
		return false;
	}
}
