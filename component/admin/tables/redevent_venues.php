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
 * EventList venues Model class
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEvent_venues extends JTable
{
	public function __construct(& $db)
	{
		parent::__construct('#__redevent_venues', 'id', $db);
	}

	// overloaded check function
	function check($elsettings)
	{
		// not typed in a venue name
		if(!trim($this->venue)) {
	      	$this->setError( JText::_('COM_REDEVENT_ADD_VENUE') );
	       	return false;
		}

		$alias = JFilterOutput::stringURLSafe($this->venue);

		if(empty($this->alias) || $this->alias === $alias ) {
			$this->alias = $alias;
		}

		if ( $this->map && !($this->latitude || $this->longitude))
		{
			if ((!trim($this->street)) || (!trim($this->plz)) || (!trim($this->city)) || (!trim($this->country))) {
				$this->setError( JText::_('COM_REDEVENT_ADD_ADDRESS') );
				return false;
			}
		}

		if (JFilterInput::checkAttribute(array ('href', $this->url))) {
			$this->setError( JText::_('COM_REDEVENT_ERROR_URL_WRONG_FORMAT' ) );
			return false;
		}

		if (trim($this->url)) {
			$this->url = strip_tags($this->url);
			$urllength = strlen($this->url);

			if ($urllength > 199) {
      			$this->setError( JText::_('COM_REDEVENT_ERROR_URL_LONG' ) );
      			return false;
			}
			if (!preg_match( '/^(http|https):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}'
       		.'((:[0-9]{1,5})?\/.*)?$/i' , $this->url)) {
				$this->setError( JText::_('COM_REDEVENT_ERROR_URL_WRONG_FORMAT' ) );
				return false;
			}
		}

		$this->street = strip_tags($this->street);
		$streetlength = JString::strlen($this->street);
		if ($streetlength > 50) {
     	 	$this->setError( JText::_('COM_REDEVENT_ERROR_STREET_LONG' ));
     	 	return false;
		}

		$this->plz = strip_tags($this->plz);
		$plzlength = JString::strlen($this->plz);
		if ($plzlength > 10) {
      		$this->setError(JText::_('COM_REDEVENT_ERROR_ZIP_LONG' ));
      		return false;
		}

		$this->city = strip_tags($this->city);
		$citylength = JString::strlen($this->city);
		if ($citylength > 50) {
    	  	$this->setError(JText::_('COM_REDEVENT_ERROR_CITY_LONG'));
    	  	return false;
		}

		$this->state = strip_tags($this->state);
		$statelength = JString::strlen($this->state);
		if ($statelength > 50) {
    	  	$this->setError(JText::_('COM_REDEVENT_ERROR_STATE_LONG'));
    	  	return false;
		}

		$this->country = strip_tags($this->country);
		$countrylength = JString::strlen($this->country);
		if ($countrylength > 2) {
     	 	$this->setError(JText::_('COM_REDEVENT_ERROR_COUNTRY_LONG' ));
     	 	return false;
		}

		/** check for existing venue */
		$query = ' SELECT id FROM #__redevent_venues'
		       . ' WHERE venue = ' . $this->_db->Quote($this->venue)
		       . '   AND street = ' . $this->_db->Quote($this->street)
           . '   AND city = ' . $this->_db->Quote($this->city)
		       ;
		$this->_db->setQuery($query);

		$xid = intval($this->_db->loadResult());
		if ($xid && $xid != intval($this->id)) {
			$this->setError(JText::sprintf('COM_REDEVENT_VENUE_S_ALREADY_EXIST', $this->venue));
			return false;
		}

		return true;
	}
	/**
	 * Sets categories of venue
	 *
	 * @param array $catids
	 * @return boolean true on success
	 */
	function setCats($catids = array())
	{
		if (!$this->id) {
			$this->setError('COM_REDEVENT_VENUE_TABLE_NOT_INITIALIZED');
			return false;
		}
		// update the event category xref
		// first, delete current rows for this event
		$query = ' DELETE FROM #__redevent_venue_category_xref WHERE venue_id = ' . $this->_db->Quote($this->id);
		$this->_db->setQuery($query);
		if (!$this->_db->query()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		// insert new ref
		foreach ((array) $catids as $cat_id) {
			$query = ' INSERT INTO #__redevent_venue_category_xref (venue_id, category_id) VALUES (' . $this->_db->Quote($this->id) . ', '. $this->_db->Quote($cat_id) . ')';
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
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

		return parent::bind($array, $ignore);
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form `table_name.id`
	 * where id is the value of the primary key of the table.
	 *
	 * @return      string
	 * @since       2.5
	 **/
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;
		return 'com_redevent.venue.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return      string
	 * @since       2.5
	 */
	protected function _getAssetTitle()
	{
		return $this->catname;
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

		// Find the parent-asset
		// We can't use venue categories as parent, as their can be multiple...
		// The item has the component as asset-parent
		$assetParent->loadByName('com_redevent');

		// Return the found asset-parent-id
		if ($assetParent->id)
		{
			$assetParentId=$assetParent->id;
		}
		return $assetParentId;
	}
}
