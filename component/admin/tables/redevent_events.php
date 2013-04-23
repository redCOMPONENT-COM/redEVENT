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
 * EventList events Model class
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
*/
class RedEvent_events extends JTable
{
	function redevent_events(& $db) {
		parent::__construct('#__redevent_events', 'id', $db);
	}

	// overloaded check function
	function check($elsettings = null)
	{
		$app = &JFactory::getApplication();
		// Check fields
		$this->title = strip_tags(trim($this->title));
		$titlelength = JString::strlen($this->title);

		if ( $this->title == '' ) {
			$this->_error = JText::_('COM_REDEVENT_ADD_TITLE' );
			JError::raiseWarning('REDEVENT_GENERIC_ERROR', $this->_error );
			return false;
		}

		if ( $titlelength > 100 ) {
			$this->_error = JText::_('COM_REDEVENT_ERROR_TITLE_LONG' );
			JError::raiseWarning('REDEVENT_GENERIC_ERROR', $this->_error );
			return false;
		}

		$alias = JFilterOutput::stringURLSafe($this->title);

		if(empty($this->alias) || $this->alias === $alias ) {
			$this->alias = $alias;
		}

		// check that there is no loop with the tag inclusion
		if (preg_match('/\[[a-z]*signuppage\]/', $this->submission_type_email) > 0) {
			$this->_error = JText::_('COM_REDEVENT_ERROR_TAG_LOOP_XXXXSIGNUPPAGE');
			JError::raiseWarning(0, $this->_error);
		}

		if (preg_match('/\[[a-z]*signuppage\]/', $this->submission_type_phone) > 0) {
			$this->_error = JText::_('COM_REDEVENT_ERROR_TAG_LOOP_XXXXSIGNUPPAGE');
			JError::raiseWarning(0, $this->_error);
		}

		if (preg_match('/\[[a-z]*signuppage\]/', $this->submission_type_webform) > 0) {
			$this->_error = JText::_('COM_REDEVENT_ERROR_TAG_LOOP_XXXXSIGNUPPAGE');
			JError::raiseWarning(0, $this->_error);
		}

		if ($app->isAdmin() && !empty($this->review_message) && !strstr($this->review_message, '[redform]')) {
			$this->_error = JText::_('COM_REDEVENT_WARNING_REDFORM_TAG_MUST_BE_INCLUDED_IN_REVIEW_SCREEN_IF_NOT_EMPTY');
			JError::raiseWarning(0, $this->_error);
		}

		// prevent people from using {redform}x{/redform} inside the wysiwyg => replace with [redform]
		$this->datdescription = preg_replace('#(\{redform\}.*\{/redform\})#i', '[redform]', $this->datdescription);
		$this->review_message = preg_replace('#(\{redform\}.*\{/redform\})#i', '[redform]', $this->review_message);

		return true;
	}

	function xload($xref)
	{
		$this->reset();

		$db =& $this->getDBO();

		$query = 'SELECT e.* '
		. ' FROM #__redevent_events as e '
		. ' INNER JOIN #__redevent_event_venue_xref as x ON x.eventid = e.id '
		. ' WHERE x.id = ' . $db->Quote($xref);
		$db->setQuery( $query );

		if ($result = $db->loadAssoc( )) {
			return $this->bind($result);
		}
		else
		{
			$this->setError( $db->getErrorMsg() );
			return false;
		}
	}

	public function bind($array, $ignore = '')
	{
		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$filtered = array();
			foreach ((array) $array['rules'] as $action => $ids)
			{
				// Build the rules array.
				$filtered[$action] = array();
				foreach ($ids as $id => $p)
				{
					if ($p !== '')
					{
						$filtered[$action][$id] = ($p == '1' || $p == 'true') ? true : false;
					}
				}
			}
			$rules = new JAccessRules($filtered);
			$this->setRules($rules);
		}

		// Custom fields
		$customs = $this->_getCustomFieldsColumns();

		foreach ($customs as $c)
		{
			if (isset($array[$c]))
			{
				$array[$c] = is_array($array[$c]) ? implode("\n", $array[$c]) : $array[$c];
			}
			else {
				$array[$c] = '';
			}
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * get custom fields for table
	 *
	 * @return array
	 */
	protected function _getCustomFieldsColumns()
	{
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('CONCAT("custom", id)');
		$query->from('#__redevent_fields');
		$query->where('object_key = ' . $db->Quote('redevent.event'));

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}

	/**
	 * Sets categories of event
	 * Enter description here ...
	 * @param unknown_type $catids
	 */
	function setCats($catids = array())
	{
		if (!$this->id) {
			$this->setError('COM_REDEVENT_EVENT_TABLE_NOT_INITIALIZED');
			return false;
		}
		// update the event category xref
		// first, delete current rows for this event
		$query = ' DELETE FROM #__redevent_event_category_xref WHERE event_id = ' . $this->_db->Quote($this->id);
		$this->_db->setQuery($query);
		if (!$this->_db->query()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		// insert new ref
		foreach ((array) $catids as $cat_id) {
			$query = ' INSERT INTO #__redevent_event_category_xref (event_id, category_id) VALUES (' . $this->_db->Quote($this->id) . ', '. $this->_db->Quote($cat_id) . ')';
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form `table_name.id`
	 * where id is the value of the primary key of the table.
	 *
	 * @return      string
	 *
	 * @since       2.5
	 **/
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_redevent.event.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return      string
	 *
	 * @since       2.5
	 */
	protected function _getAssetTitle()
	{
		return $this->title;
	}

	/**
	 * Method to get the asset-parent-id of the item
	 *
	 * @return      int
	 */
	protected function _getAssetParentId()
	{
		// We will retrieve the parent-asset from the Asset-table
		$assetParent = JTable::getInstance('Asset');

		// Default: if no asset-parent can be found we take the global asset
		$assetParentId = $assetParent->getRootId();

		// The item has the component as asset-parent
		$assetParent->loadByName('com_redevent');

		// Return the found asset-parent-id
		if ($assetParent->id)
		{
			$assetParentId = $assetParent->id;
		}

		return $assetParentId;
	}
}
