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
 * EventList Component Attendees Controller
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventControllerAttendees extends RedEventController
{
	/**
	 * Constructor
	 *
	 *@since 0.9
	 */
	function __construct() {
		parent::__construct();
		$this->registerTask( 'addattendee', 'attendees' );
	}
	
	public function Attendees() {
		/* Create the view object */
		$view = $this->getView('attendees', 'html');
		
		/* Standard model */
		$view->setModel( $this->getModel( 'attendees', 'RedeventModel' ), true );
		$view->setModel( $this->getModel( 'waitinglist', 'RedeventModel' ) );
		$view->setLayout('default');
		
		/* Now display the view */
		$view->display();
	}
	
	public function Submitters() {
		global $mainframe;
		$mainframe->redirect('index.php?option=com_redform&controller=submitters&task=submitters&integration=redevent&xref='.JRequest::getInt('xref').'&form_id='.JRequest::getInt('form_id').'&filter='.JRequest::getInt('filter'));
		
		/* Create the view object */
		$view = $this->getView('submitters', 'html');
		
		/* Standard model */
		JController::addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_redform'.DS.'models');
		$view->setModel( $this->getModel( 'submitters', 'RedformModel' ), true);
		$view->setModel( $this->getModel( 'redform', 'RedformModel' ));
		$view->setLayout('submitters');
		
		/* Now display the view */
		$view->display();
	}
	
	/**
	 * Delete attendees
	 *
	 * @return true on sucess
	 * @access private
	 * @since 0.9
	 */
	function remove()
	{
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		$xref 	= JRequest::getInt('xref');
		$total 	= count( $cid );
		$db = JFactory::getDBO();
		$formid = JRequest::getInt('form_id');
		
		/* Check if anything is selected */
		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to delete' ) );
		}
		
		/* Get all submitter ID's */
		$model = $this->getModel('attendees');
				
		/* Delete the redFORM entry first */
		/* Submitter answers first*/
		//TODO: put this in the model !
		$q = "DELETE FROM #__rwf_forms_".JRequest::getInt('form_id')."
			WHERE id IN (".implode(', ', $cid).")";
		$db->setQuery($q);
		$db->query();
		
		/* Submitter second */
		$q = "DELETE FROM #__rwf_submitters
      WHERE answer_id IN (".implode(', ', $cid).")
			AND form_id = ".$formid;
		$db->setQuery($q);
		$db->query();
		
		// all the redevent_register records in redevent without an associated record in redform submitters can be deleted
		$q =  ' SELECT r.id FROM #__redevent_register AS r '
        . ' LEFT JOIN #__rwf_submitters AS s ON s.submit_key = r.submit_key '
        . ' WHERE s.id IS NULL '
        ;
    $db->setQuery($q);
    $register_ids = $db->loadResultArray();		
    if (!empty($register_ids))
    {
			if(!$model->remove($register_ids)) {
	      RedeventError::raiseWarning(0, JText::_( "CANT DELETE REGISTRATIONS" ) . ': ' . $model->getError() );
				echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
			}
    }
		
		/* Check if we have space on the waiting list */
		$model_wait = $this->getModel('waitinglist');
		$model_wait->setXrefId($xref);
		$model_wait->UpdateWaitingList();
		
		$cache = JFactory::getCache('com_redevent');
		$cache->clean();

		$msg = $total.' '.JText::_( 'REGISTERED USERS DELETED');

		$this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg );
	}
	
	/**
	 * confirm an attendee registration
	 * 
	 * @return unknown_type
	 */
	function confirmattendees()
	{
    $cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
    $xref   = JRequest::getInt('xref');
    
    $model = $this->getModel('attendees');
    
    if ($model->confirmattendees($cid))
    {
  	  $msg = JText::_( 'REGISTRATION CONFIRMED');
      $this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg );
  	}
  	else
  	{
      $msg = JText::_( 'ERROR REGISTRATION CONFIRM') . ': ' . $model->getError();
      $this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg, 'error' );  	  
  	}
    return true;
	}
	
  /**
   * remove confirm status from an attendee registration
   * 
   * @return unknown_type
   */
  function unconfirmattendees()
  {
    $cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
    $xref   = JRequest::getInt('xref');
    
    $model = $this->getModel('attendees');
    
    if ($model->unconfirmattendees($cid))
    {
      $msg = JText::_( 'REGISTRATION UNCONFIRMED');
      $this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg );
    }
    else
    {
      $msg = JText::_( 'ERROR REGISTRATION UNCONFIRM') . ': ' . $model->getError();
      $this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg, 'error' );      
    }
    return true;
  }
  
  function onwaiting()
  {
    $cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
    $xref   = JRequest::getInt('xref');
    
    $model = $this->getModel('waitinglist');
    $model->setXrefId($xref);
    
    if ($model->putOnWaitingList($cid))
    {
      $msg = JText::_( 'PUT ON WAITING SUCCESS');
      $this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg );
    }
    else
    {
      $msg = JText::_( 'PUT ON WAITING FAILURE') . ': ' . $model->getError();
      $this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg, 'error' );      
    }
    return true;    
  }

  function offwaiting()
  {
    $cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
    $xref   = JRequest::getInt('xref');
    
    $model = $this->getModel('waitinglist');
    $model->setXrefId($xref);
    
    if ($model->putOffWaitingList($cid))
    {
      $msg = JText::_( 'PUT OFF WAITING SUCCESS');
      $this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg );
    }
    else
    {
      $msg = JText::_( 'PUT OFF WAITING FAILURE') . ': ' . $model->getError();
      $this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg, 'error' );      
    }
    return true;    
  }
		
	/* Obsolete */
	function export()
	{
		global $mainframe;

		$model = $this->getModel('attendees');

		$datas = $model->getData();

		header('Content-Type: text/x-csv');
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Content-Disposition: attachment; filename=attendees.csv');
		header('Pragma: no-cache');

		$k = 0;
		$export = '';
		$col = array();

		for($i=0, $n=count( $datas ); $i < $n; $i++)
		{
			$data = &$datas[$i];

    		$col[] = str_replace("\"", "\"\"", $data->name);
    		$col[] = str_replace("\"", "\"\"", $data->username);
    		$col[] = str_replace("\"", "\"\"", $data->email);
    		$col[] = str_replace("\"", "\"\"", JHTML::Date( $data->uregdate, JText::_( 'DATE_FORMAT_LC2' ) ));
			
   	 		for($j = 0; $j < count($col); $j++)
    		{
        		$export .= "\"" . $col[$j] . "\"";

        		if($j != count($col)-1)
       	 		{
            		$export .= ";";
        		}
    		}
    		$export .= "\r\n";
    		$col = '';

			$k = 1 - $k;
		}

		echo $export;

		$mainframe->close();
	}
}
?>