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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * EventList Component Events Controller
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventControllerEvents extends RedEventController
{
	private $twit;
	
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra task
		$this->registerTask( 'apply', 		'save' );
		$this->registerTask( 'copy',	 	'edit' );
		$this->registerTask( 'add',	 	'edit' );
		
		$this->twit = false;
	}

	/**
	 * Logic to publish events
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function publish()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('events');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('EVENT PUBLISHED');

		$this->setRedirect( 'index.php?option=com_redevent&view=events', $msg );
	}

	/**
	 * Logic to unpublish events
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function unpublish()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('events');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('EVENT UNPUBLISHED');

		$this->setRedirect( 'index.php?option=com_redevent&view=events', $msg );
	}

	/**
	 * Logic to archive events
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function archive()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to archive' ) );
		}

		$model = $this->getModel('events');
		if(!$model->publish($cid, -1)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('OLD EVENT DATE ARCHIVED');

		$this->setRedirect( 'index.php?option=com_redevent&view=events', $msg );
	}
	
  /**
   * Logic to archive events
   *
   * @access public
   * @return void
   * @since 0.9
   */
  function archivepast()
  {
    $cid  = JRequest::getVar( 'cid', array(0), 'post', 'array' );

    if (!is_array( $cid ) || count( $cid ) < 1) {
      JError::raiseError(500, JText::_( 'Select an item to archive' ) );
    }

    $model = $this->getModel('events');
    if(!$model->archive($cid)) {
      echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
    }

    $total = count( $cid );
    $msg  = $total.' '.JText::_('OLD EVENT DATE ARCHIVED');

    $this->setRedirect( 'index.php?option=com_redevent&view=events', $msg );
  }

	/**
	 * logic for cancel an action
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		
		$group = & JTable::getInstance('redevent_events', '');
		$group->bind(JRequest::get('post'));
		$group->checkin();

		$this->setRedirect( 'index.php?option=com_redevent&view=events' );
	}

	/**
	 * logic to create the edit event screen
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function edit( )
	{
		JRequest::setVar( 'view', 'event' );
		JRequest::setVar( 'hidemainmenu', 1 );

		$model 	= $this->getModel('event');
		$task 	= JRequest::getVar('task');

		if ($task == 'copy' || $task == 'add') {
			JRequest::setVar( 'task', $task );
		} else {
			
			$user	=& JFactory::getUser();
			// Error if checkedout by another administrator
			if ($model->isCheckedOut( $user->get('id') )) {
				$this->setRedirect( 'index.php?option=com_redevent&view=events', JText::_( 'EDITED BY ANOTHER ADMIN' ) );
			}
			$model->checkout();
		}
		parent::display();
	}

	/**
	 * logic to save an event
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function save() 
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		$db = JFactory::getDBO();
		$task		= JRequest::getVar('task');
		
		$post = JRequest::get( 'post', 4 );
		
		/* Get the form fields to display */
		$showfields = '';
		foreach ($post as $field => $value) {
			if (substr($field, 0, 9) == 'showfield' && $value == "1") {
				$showfields .= substr($field, 9).",";
			}
		}
		
		$post['showfields'] = substr($showfields, 0, -1);
		if (!isset($post['checked_out'])) $post['checked_out'] = 0;
		
		/* Fix the submission types */
		$post['submission_types'] = implode(',', $post['submission_types']);
		
		$model = $this->getModel('event');
		$model_wait = $this->getModel('waitinglist');
		
		if ($returnid = $model->store($post)) 
		{
			$msg	= JText::_( 'EVENT SAVED');
			
			if (isset($post['venueid']) && $post['venueid'])
			{
				if (!$this->_saveInitialSession($returnid)) {
					$msg .= "\n".JTExt::_('COM_REDEVENT_EVENT_FAILED_SAVING_INITIAL_SESSION').': '.$this->getError();
				} 
			}
			
			if ($this->twit == true)
			{
				//If the AutoTweet NG Component is installed 
				if (JComponentHelper::getComponent('com_autotweet', true)->enabled) {
					//If the redEVENT twitter plugin is installed
					if (JPluginHelper::isEnabled("system", "autotweetredevent"))
					{
						//Add twitter redirect
						$twitter_redirect = '&twit_id='.$returnid.'&message='.$msg;
					}
				}
			}

			switch ($task)
			{
				case 'apply' :
					$link = 'index.php?option=com_redevent&controller=events&view=event&hidemainmenu=1&cid[]='.$returnid;
					break;

				default :
					$link = 'index.php?option=com_redevent&view=events'.$twitter_redirect;
					break;
			}
						
			$cache = &JFactory::getCache('com_redevent');
			$cache->clean();

		} else {
			$msg 	= $model->getError();
			$link = 'index.php?option=com_redevent&view=events';

		}

		$model->checkin();
		
		$this->setRedirect( $link, $msg );
 	}

	function saveAndTwit()
	{
		$this->twit = true;
		$this->save();
	}

	function twitRedirect() {
		$this->setRedirect('index.php?option='.JRequest::getVar('option').'&view=events&id='.JRequest::getVar('twit_id'), JRequest::getVar('message'));
	}

	/**
	 * logic to remove an event
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
 	function remove()
	{
		$cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$total = count( $cid );

		$msgtype = "message";
		
		if (!is_array( $cid ) || count( $cid ) < 1) {
			$msg = JText::_( 'Select an item to delete' );
			$msgtype = 'error';
		}

		$model = $this->getModel('events');
		if (!$model->delete($cid)) {
			$msg = $model->getError();
			$msgtype = 'error';
		}
		else {
			$msg = $total.' '.JText::_( 'EVENTS DELETED');
			$cache = &JFactory::getCache('com_redevent');
			$cache->clean();
		}
		
		$this->setRedirect( 'index.php?option=com_redevent&view=events', $msg, $msgtype);
	}
	
	function removexref()
	{
		$id = JRequest::getVar('xref', 0, 'request', 'int');
		
		if (!$id) {
			echo '0' .':'. JText::_('NO XREF ID');
      return true;
		}
		else { 
			$model = $this->getModel('session');
			if ($model->removexref($id)) {
				echo '1' .':'. JText::_('DATE DELETED');
        return true;
			}
			else {
        echo '0' .':'. JText::_('COULDNT DELETE DATE') .' - '. $model->getError() ;
        return true;				
			}
		}
	}
	
	/**
	 * start events export screens
	 * 
	 */
	function export()
	{
		JRequest::setVar( 'view', 'events' );
		JRequest::setVar( 'layout', 'export' );
		parent::display();
	}
	
	function doexport()
	{
		$app			=& JFactory::getApplication();
		
		$cats = JRequest::getVar('categories', null, 'request', 'array');
		JArrayHelper::toInteger($cats);
		$venues = JRequest::getVar('venues', null, 'request', 'array');
		JArrayHelper::toInteger($venues);		

		$model = $this->getModel('events');
		$events = $model->exportEvents($cats, $venues);

		header('Content-Type: text/x-csv');
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Content-Disposition: attachment; filename=events.csv');
		header('Pragma: no-cache');

		$k = 0;
		$export = '';
		$col = array();
		
		$eventtablefields = array('id', 'title', 'alias', 'created_by', 'modified', 'modified_by', 
		                          'summary', 'datdescription', 'details_layout', 'meta_description', 
		                          'meta_keywords', 'datimage', 'author_ip', 'created', 'published', 
		                          'registra', 'unregistra', 'checked_out', 'checked_out_time', 'notify', 
		                          'notify_subject', 'notify_body', 'redform_id', 'juser', 'notify_on_list_body', 
		                          'notify_off_list_body', 'notify_on_list_subject', 'notify_off_list_subject', 
		                          'show_names', 'notify_confirm_subject', 'notify_confirm_body', 'review_message', 
		                          'confirmation_message', 'activate', 'showfields', 'submission_types', 'course_code', 
		                          'submission_type_email', 'submission_type_external', 'submission_type_phone', 'submission_type_webform', 
		                          'show_submission_type_webform_formal_offer', 'submission_type_webform_formal_offer', 
		                          'max_multi_signup', 'submission_type_formal_offer', 'submission_type_formal_offer_subject', 
		                          'submission_type_formal_offer_body', 'submission_type_email_body', 'submission_type_email_subject', 
		                          'submission_type_email_pdf', 'submission_type_formal_offer_pdf', 'send_pdf_form', 'pdf_form_data', 
		                          'paymentaccepted', 'paymentprocessing', 'enable_ical', '_tbl', '_tbl_key', '_db', '_errors');
		
		if (count($events))
		{		
			$header = current($events);
			$export .= redEVENTHelper::writecsvrow(array_keys($header));

			$current = 0; // current event
			foreach($events as $data)
			{			
				if ($current == $data['id']) // not the first session, do not repeat event data 
				{ 
					foreach ($data as $k => $v)
					{
						if (in_array($k, $eventtablefields)) {
							$data[$k] = null;
						}
					}
				}
				else {
					$current = $data['id']; // first event id				
				}
				$export .= redEVENTHelper::writecsvrow($data);
			}
	
			echo $export;
		}

		$app->close();
	}
	
	function import()
	{
    $replace = JRequest::getVar('replace_events', 0, 'post', 'int');
    
    $msg = '';
    if ( $file = JRequest::getVar( 'import', null, 'files', 'array' ) )
    {
      $handle = fopen($file['tmp_name'],'r');
      if(!$handle) 
      {
        $msg = JText::_('Cannot open uploaded file.');  
        $this->setRedirect( 'index.php?option=com_redevent&controller=events&task=export', $msg, 'error' ); 
        return;   
      }
           
      // get fields, on first row of the file
      $fields = array();
      if ( ($data = fgetcsv($handle, 0, ',', '"')) !== FALSE ) 
      {
        $numfields = count($data);
        for ($c=0; $c < $numfields; $c++) 
        {
          // here, we make sure that the field match one of the fields of eventlist_venues table or special fields,
          // otherwise, we don't add it
//          if ( array_key_exists($data[$c], $object_fields) ) {
            $fields[$c]=$data[$c];
//          }
        }
      }
      // If there is no validated fields, there is a problem...
      if ( !count($fields) ) {
        $msg .= "<p>Error parsing column names. Are you sure this is a proper csv export ?<br />try to export first to get an example of formatting</p>\n";
        $this->setRedirect( 'index.php?option=com_redevent&controller=events&task=export', $msg, 'error' );
        return;
      }
      else {
        $msg .= "<p>".$numfields." fields found in first row</p>\n";
        $msg .= "<p>".count($fields)." fields were kept</p>\n";
      }
      // Now get the records, meaning the rest of the rows.
      $records = array();
      $row = 1;
      while ( ($data = fgetcsv($handle, 0, ',', '"')) !== FALSE ) 
      {      	
        $num = count($data);
        if ($numfields != $num) {
          $msg .= "<p>Wrong number of fields ($num) record $row<br /></p>\n";
        }
        else {
          $r = new stdclass();
          // only extract columns with validated header, from previous step.
          foreach ($fields as $k => $v) {
            $r->$v = $this->_formatcsvfield($v, $data[$k]);
          }
          $records[] = $r;
        }
        $row++;
      }
      fclose($handle);
      $msg .= "<p>total records found: ".count($records)."<br /></p>\n";
         
      // database update
      if (count($records)) 
      {
        $model = $this->getModel('import');
        $result = $model->eventsimport($records, $replace);
        $msg .= "<p>total added records: ".$result['added']."<br /></p>\n";
        $msg .= "<p>total updated records: ".$result['updated']."<br /></p>\n";
      }
      $this->setRedirect( 'index.php?option=com_redevent&controller=events&task=export', $msg ); 
    }
    else {
      parent::display();
    }
	}
  
  /**
   * handle specific fields conversion if needed
   *
   * @param string column name
   * @param string $value
   * @return string
   */
  function _formatcsvfield($type, $value)
  {
    switch($type)
    {
      case 'dates':
      case 'enddates':
      case 'registrationend':
        if (strtotime($value)) {
          //strtotime does a good job in converting various date formats...
          $field = strftime('%Y-%m-%d', strtotime($value));
        }
        else {
          $field = null;
        }
        break;
      default:
        $field = $value;
        break;
    }
    return $field;
  }
  
  /**
   * save data of first session associated to newly created event
   * 
   * @param int $eventid
   * @return true on success
   */
  protected function _saveInitialSession($eventid)
  {
  	$model = $this->getModel('Session', 'RedeventModel');
  	
  	$post = JRequest::get( 'post' );
  	$post['eventid'] = $eventid;
    $post['details'] = JRequest::getVar('session_details', '', 'post', 'string', JREQUEST_ALLOWRAW);
    $post['icaldetails'] = JRequest::getVar('icaldetails', '', 'post', 'string', JREQUEST_ALLOWRAW);
    foreach ($post as $key => $val)
    {
    	if (strpos($key, 'session_') === 0) {
    		$post[substr($key, 8)] = $val;
    	}
    }
        
    $model = $this->getModel('session');
    if (!$returnid = $model->savexref($post)) 
    {
    	$this->setError($model->getError());
    	return false;
    }
    return true;
  	
  }
}
?>