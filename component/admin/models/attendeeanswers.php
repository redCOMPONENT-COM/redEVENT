<?php
/**
 * @version 1.0 $Id: group.php 298 2009-06-24 07:42:35Z julien $
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

//no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * EventList Component attendee answers Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedEventModelAttendeeanswers extends JModel
{
	/**
	 * Event id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Event data array
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

		$array = JRequest::getVar('submitter_id',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the identifier
	 *
	 * @access	public
	 * @param	int event identifier
	 */
	function setId($id)
	{
		// Set event id and wipe data
		$this->_id	    = $id;
		$this->_data	= null;
	}

	/**
	 * Logic for the Group edit screen
	 *
	 */
	function &getData()
	{

		if ($this->_loadData())
		{

		}
		else  $this->_initData();

		//$this->_loadData();
		return $this->_data;
	}

	/**
	 * Method to load content data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function _loadData()
	{
		//Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
		  // get form id and answer id
			$query = 'SELECT form_id, answer_id '
					. ' FROM #__rwf_submitters AS s '
					. ' WHERE id = '.$this->_id
					;
			$this->_db->setQuery($query);

			list($form_id, $answer_id) = $this->_db->loadRow();
			
			if (!$form_id || !$answer_id) {
			  Jerror::raiseError(0, JText::_('COM_REDEVENT_No_data'));
			}
			
			// get fields
      $query = 'SELECT id, field FROM #__rwf_fields '
             . ' WHERE form_id = '. $this->_db->Quote($form_id)
             ;
      $this->_db->setQuery($query);      
      $fields = $this->_db->loadObjectList();
			
			// now get the anwsers
			$query = 'SELECT * FROM #__rwf_forms_'. $form_id
			       . ' WHERE id = '. $this->_db->Quote($answer_id)
			       ;
      $this->_db->setQuery($query);      
      $answers = $this->_db->loadObject();
      
      // add the answers to fields objects
      foreach ($fields as $k => $f)
      {
        $property = 'field_'. $f->id;
        if (property_exists($answers, $property)) {
          $fields[$k]->value = $answers->$property;
        }
      }
      
			$this->_data = $fields;
			
			if (!$this->_data) {
			  echo $this->_db->getErrorMsg();
			}
			
			return (boolean) $this->_data;
			
		}
		return true;
	}
}
?>