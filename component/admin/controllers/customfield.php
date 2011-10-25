<?php
/**
 * @version 1.0 $Id: cleanup.php 30 2009-05-08 10:22:21Z roland $
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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Joomla redEVENT Component Controller
 *
 * @package		redEVENT
 * @since 2.0
 */
class RedeventControllerCustomfield extends JController
{
  
  function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'add',  'display' );
		$this->registerTask( 'edit', 'display' );
		$this->registerTask( 'apply', 'save' );
	}
  
  
	function display() {
	
	  switch($this->getTask())
		{
			case 'add'     :
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'layout', 'form'  );
				JRequest::setVar( 'view'  , 'customfield');
				JRequest::setVar( 'edit', false );

				// Checkout the project
				$model = $this->getModel('customfield');
				$model->checkout();
			} break;
			case 'edit'    :
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'layout', 'form'  );
				JRequest::setVar( 'view'  , 'customfield');
				JRequest::setVar( 'edit', true );

				// Checkout the project
				$model = $this->getModel('customfield');
				$model->checkout();
			} break;
		}
		parent::display();
	}
	
  function save()
	{
		$post	= JRequest::get('post');
		$cid	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$post['id'] = (int) $cid[0];
		$msgtype = 'message';

		$model = $this->getModel('customfield');

		if ($returnid = $model->store($post)) {
			$msg = JText::_('COM_REDEVENT_Custom_field_Saved' );
		} else {
			$msg = JText::_('COM_REDEVENT_Error_Saving_Custom_field' ).'<br/>'.$model->getError();
			$msgtype = 'error';
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		
		if ( !$returnid || $this->getTask() == 'save' ) {
			$link = 'index.php?option=com_redevent&view=customfields';
		}
		else {
			$link = 'index.php?option=com_redevent&controller=customfield&task=edit&cid[]='.$returnid;
		}
		$this->setRedirect($link, $msg, $msgtype);
	}

	function remove()
	{
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_delete' ) );
		}

		$model = $this->getModel('customfield');
		
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_redevent&view=customfields' );
	}


	function publish()
	{
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_publish' ) );
		}

		$model = $this->getModel('customfield');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
    $link = 'index.php?option=com_redevent&view=customfields';
		$this->setRedirect($link);
	}


	function unpublish()
	{
		
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_unpublish' ) );
		}

		$model = $this->getModel('customfield');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
    $link = 'index.php?option=com_redevent&view=customfields';
		$this->setRedirect($link);
	}

	function cancel()
	{
		// Checkin the project
		$model = $this->getModel('customfield');
		$model->checkin();

		$this->setRedirect( 'index.php?option=com_redevent&view=customfields' );
	}


	function orderup()
	{
		$model = $this->getModel('customfield');
		$model->move(-1);

		$this->setRedirect( 'index.php?option=com_redevent&view=customfields');
	}

	function orderdown()
	{
		$model = $this->getModel('customfield');
		$model->move(1);

		$this->setRedirect( 'index.php?option=com_redevent&view=customfields');
	}

	function saveorder()
	{
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$order 	= JRequest::getVar( 'order', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		$model = $this->getModel('customfield');
		$model->saveorder($cid, $order);

		$msg = 'New ordering saved';
		$this->setRedirect( 'index.php?option=com_redevent&view=customfields', $msg );
	}
	

  
	/**
	 * start venues export screens
	 * 
	 */
	function import()
	{
		JRequest::setVar( 'view', 'customfields' );
		JRequest::setVar( 'layout', 'import' );
		parent::display();
	}

	function export()
	{
		$app			=& JFactory::getApplication();
				
		$model = $this->getModel('customfields');
		$rows = $model->export();

		header('Content-Type: text/x-csv');
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Content-Disposition: attachment; filename=customfields.csv');
		header('Pragma: no-cache');

		$k = 0;
		$export = '';
		$col = array();
				
		if (count($rows))
		{		
			$header = current($rows);
			$export .= redEVENTHelper::writecsvrow(array_keys($header));

			$current = 0; // current event
			foreach($rows as $data)
			{			
				$export .= redEVENTHelper::writecsvrow($data);
			}
	
			echo $export;
		}

		$app->close();
	}
	
	function doimport()
	{		
    $replace = JRequest::getVar('replace', 0, 'post', 'int');
    
    $msg = '';
    if ( $file = JRequest::getVar( 'import', null, 'files', 'array' ) )
    {
      $handle = fopen($file['tmp_name'],'r');
      if(!$handle) 
      {
        $msg = JText::_('COM_REDEVENT_Cannot_open_uploaded_file.');  
        $this->setRedirect( 'index.php?option=com_redevent&controller=customfield&task=import', $msg, 'error' ); 
        return;   
      }
           
      // get fields, on first row of the file
      $fields = array();
      if ( ($data = fgetcsv($handle, 0, ',', '"')) !== FALSE ) 
      {
        $numfields = count($data);
        for ($c=0; $c < $numfields; $c++) 
        {
        	$fields[$c]=$data[$c];
        }
      }
      // If there is no validated fields, there is a problem...
      if ( !count($fields) ) {
        $msg .= "<p>Error parsing column names. Are you sure this is a proper csv export ?<br />try to export first to get an example of formatting</p>\n";
        $this->setRedirect( 'index.php?option=com_redevent&controller=customfield&task=import', $msg, 'error' );
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
            $r->$v = $data[$k];
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
        $model = $this->getModel('customfields');
        $result = $model->import($records, $replace);
        $msg .= "<p>total added records: ".$result['added']."<br /></p>\n";
        $msg .= "<p>total updated records: ".$result['updated']."<br /></p>\n";
      }
      $this->setRedirect( 'index.php?option=com_redevent&controller=customfield&task=import', $msg ); 
    }
    else {
      parent::display();
    }
	}
}
?>
