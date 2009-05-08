<?php
/**
 * @version 1.0 $Id: admin.class.php 662 2008-05-09 22:28:53Z schlu $
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
		$mainframe->redirect('index.php?option=com_redform&controller=submitters&task=submitters&xref='.JRequest::getInt('xref').'&form_id='.JRequest::getInt('form_id').'&filter='.JRequest::getInt('filter'));
		
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
		$cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );
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
		$cids = 'id=' . implode( ' OR id=', $cid );
		$q = "DELETE FROM #__rwf_forms_".JRequest::getInt('form_id')."
			WHERE (".$cids.")";
		$db->setQuery($q);
		$db->query();
		
		/* Submitter second */
		$cids = 'answer_id=' . implode( ' OR answer_id=', $cid );
		$q = "DELETE FROM #__rwf_submitters
			WHERE (".$cids.")
			AND xref = ".$xref."
			AND form_id = ".$formid;
		$db->setQuery($q);
		$db->query();
		
		if(!$model->remove($cid, $xref)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
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