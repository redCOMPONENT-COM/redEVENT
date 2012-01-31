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
 * @subpackage EventList
 * @since 0.9
 */
class RedEvent_events extends JTable
{
	/**
	 * Primary Key
	 * @var int
	 */
	var $id 						= null;
	/** @var string */      		
	var $title 						= '';
	/** @var string */      		
	var $alias	 					= '';
	/** @var int */         		
	var $created_by					= null;
	/** @var int */         		
	var $modified 					= 0;
	/** @var int */         		
	var $modified_by 				= null;
	/** @var string */         		
	var $summary 				= null;
	/** @var string */      		
	var $datdescription 			= null;
	/** @var int */         		
	var $details_layout 					= 0;
	/** @var string */      		
	var $meta_description 			= null;
	/** @var string */      		
	var $meta_keywords				= null;
	/** @var string */      		
	var $datimage 					= '';
	/** @var string */      		
	var $author_ip 					= null;
	/** @var date */        		
	var $created	 				= null;
	/** @var int */         		
	var $published 					= null;
	/** @var int */         		
	var $registra 					= null;
	/** @var int */         		
	var $unregistra 				= null;
	/** @var int */         		
	var $checked_out 				= null;
	/** @var date */        		
	var $checked_out_time 			= 0;
	/** @var boolean */     		
	var $notify 					= 0;
	/** @var string */      		
	var $notify_subject 			= null;
	/** @var string */      		
	var $notify_body 				= null;
	/** @var boolean */     		
	var $redform_id					= null;
	/** @var boolean */     		
	var $juser 						= 1;
	/** @var string */
	var $notify_on_list_body		= null;
	/** @var string */
	var $notify_off_list_body		= null;
	/** @var string */
	var $notify_on_list_subject		= null;
	/** @var string */
	var $notify_off_list_subject	= null;
	/** @var string */
	var $show_names	= 0;
	/** @var string */
	var $notify_confirm_subject 	= null;
	/** @var string */
	var $notify_confirm_body 		= null;
	/** @var string */
	var $review_message 			= null;
	/** @var string */
	var $confirmation_message 		= null;
	/** @var string */
	var $activate 					= null;
	/** @var string */
	var $showfields 				= null;
	/** @var string */
	var $submission_types			= null;
	/** @var string */
	var $course_code				= null;
	/** @var string */
	var $submission_type_email		= null;
	/** @var string */
	var $submission_type_external	= null;
	/** @var string */
	var $submission_type_phone		= null;
	/** @var string */
	var $submission_type_webform	= null;
  /** @var boolean */
  var $show_submission_type_webform_formal_offer = 0;
	/** @var string */
	var $submission_type_webform_formal_offer = null;
	/** @var int */
	var $max_multi_signup			= null;
	/** @var string */
	var $submission_type_formal_offer		= null;
	/** @var string */
	var $submission_type_formal_offer_subject		= null;
	/** @var string */
	var $submission_type_formal_offer_body	= null;
	/** @var string */
	var $submission_type_email_body			= null;
	/** @var string */
	var $submission_type_email_subject		= null;
	/** @var string */
	var $submission_type_email_pdf			= null;
	/** @var string */
	var $submission_type_formal_offer_pdf	= null;
	/** @var int */
	var $send_pdf_form			= null;
	/** @var int */
	var $pdf_form_data			= null;
	/** @var string */
	var $paymentaccepted	= null;
	/** @var string */
	var $paymentprocessing	= null;	
	/** @var int */
	var $enable_ical			= null;
	
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

	/**
	 * override for custom fields
	 */
	function bind( $from, $ignore=array() )
	{
		$fromArray	= is_array( $from );
		$fromObject	= is_object( $from );

		if (!$fromArray && !$fromObject)
		{
			$this->setError( get_class( $this ).'::bind failed. Invalid from argument' );
			return false;
		}
		if (!is_array( $ignore )) {
			$ignore = explode( ' ', $ignore );
		}
		foreach ($this->getProperties() as $k => $v)
		{
			// internal attributes of an object are ignored
			if (!in_array( $k, $ignore ))
			{
				if ($fromArray && isset( $from[$k] )) {
					$this->$k = $from[$k];
				} else if ($fromObject && isset( $from->$k )) {
					$this->$k = $from->$k;
				}
			}
		}
		$customs = $this->_getCustomFieldsColumns();
		foreach ($customs as $c)
		{
			if ($fromArray && isset( $from[$c] )) 
			{				
				$this->$c = is_array($from[$c]) ? implode("\n", $from[$c]) : $from[$c];
			} else if ($fromObject && isset( $from->$c )) {
				$this->$c = is_array($from->$c) ? implode("\n", $from->$c) : $from->$c;
			}
			else {
				$this->$c = '';
			}
		}
		return true;
	}
	
	function _getCustomFieldsColumns()
	{
		$query = ' SELECT CONCAT("custom", id) ' 
		       . ' FROM #__redevent_fields ' 
		       . ' WHERE object_key = ' . $this->_db->Quote('redevent.event');
		$this->_db->setQuery($query);
		$res = $this->_db->loadResultArray();
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
}
