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

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Textsnippets
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		2.0
 */
class RedEventModelTextsnippets extends FOFModel
{
	/**
	 * Public class constructor
	 *
	 * @param type $config
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->table = "textsnippet";
	}
	
	/**
	 * export
	 *
	 * @return array
	 */
	public function export()
	{				
		$query = ' SELECT t.id, t.text_name, t.text_description, t.text_field  '
		       . ' FROM #__redevent_textlibrary AS t '
		;
		$this->_db->setQuery($query);

		$results = $this->_db->loadAssocList();

		return $results;
	}
	
	/**
	 * import in database
	 *
	 * @param array $records
	 * @param boolean $replace existing events with same id
	 * @return boolean true on success
	 */
	public function import($records, $replace = 0)
	{
		$count = array('added' => 0, 'updated' => 0);
		
		$current = null; // current event for sessions
		foreach ($records as $r)
		{			
			$v = Jtable::getInstance('Textlibrary', 'Table');	
			$v->bind($r);
			if (!$replace) {
				$v->id = null;
				$update = 0;
			}
			else if ($v->id) {
				$update = 1;
			}
			// store !
			if (!$v->check()) {
				JError::raiseWarning(0, JText::_('COM_REDEVENT_IMPORT_ERROR').': '.$v->getError());
				continue;
			}
			if (!$v->store()) {
				JError::raiseWarning(0, JText::_('COM_REDEVENT_IMPORT_ERROR').': '.$v->getError());
				continue;
			}
		}
		return $count;
	}
}
