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

jimport('joomla.application.component.model');

/**
 * EventList Component Category Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedEventModelTextLibrary extends JModel
{
	/**
	 * Text id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Category data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the identifier
	 *
	 * @access	public
	 * @param	int category identifier
	 */
	function setId($id)
	{
		// Set category id and wipe data
		$this->_id	    = $id;
		$this->_data	= null;
	}

	/**
	 * Method to get content category data
	 *
	 * @access	public
	 * @return	array
	 */
	function getData() 
	{
		$mainframe = &JFactory::getApplication();
		$option = 'com_redevent';
		
		$filter_order		  = $mainframe->getUserStateFromRequest( $option.'.textlibrary.filter_order',		'filter_order',		'obj.text_name',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.textlibrary.filter_order_Dir',	'filter_order_Dir',	'',				'word' );

		if ($filter_order == 'obj.text_name'){
			$orderby 	= ' ORDER BY obj.text_name '.$filter_order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.' , obj.text_name ';
		}
		
		$db = JFactory::getDBO();
		$query = 'SELECT obj.* '
				. ' FROM #__redevent_textlibrary AS obj '
				. $orderby
				;
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	/**
    * Retrieve a field to edit
    */
   function getText() {
      $row = $this->getTable();
      $my = JFactory::getUser();
      $id = JRequest::getVar('cid');

      /* load the row from the db table */
      $row->load($id[0]);

      if ($id[0]) {
         // do stuff for existing records
         $result = $row->checkout( $my->id );
      } else {
         // do stuff for new records
         $row->published    = 1;
      }
      return $row;
   }
	
	/**
	 * Method to store the category
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function getSave() {
		global $mainframe;
		
		$row  = $this->getTable();
		$data = JRequest::get('post', 4);
		
		// bind it to the table
		if (!$row->bind($data)) {
			RedeventError::raiseError(500, $this->_db->getErrorMsg() );
			return false;
		}

		// Make sure the data is valid
		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}

		// Store it in the db
		if (!$row->store()) {
			RedeventError::raiseError(500, $this->_db->getErrorMsg() );
			return false;
		}
		else $mainframe->enqueueMessage(JText::_('TEXT_ADDED'));

		return $row->id;
	}
	
 /**
   * Method to remove a text libray element
   *
   * @access  public
   * @return  boolean True on success
   * @since 0.9
   */
  function delete($cid = array())
  {
    if (count( $cid ))
    {
      $cids = implode( ',', $cid );

      $query = 'DELETE FROM #__redevent_textlibrary'
          . ' WHERE id IN ('. $cids .')'
          ;

      $this->_db->setQuery( $query );
      
      if(!$this->_db->query()) {
        $this->setError($this->_db->getErrorMsg());
        return false;
      }
    }
    return true;
  }
}
?>