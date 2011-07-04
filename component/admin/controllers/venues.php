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
 * EventList Component Venues Controller
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventControllerVenues extends RedEventController
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra task
		$this->registerTask( 'add',    'edit' );
		$this->registerTask( 'copy',   'edit' );
		$this->registerTask( 'apply',  'save' );
	}

	/**
	 * Logic to publish venues
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

		$model = $this->getModel('venues');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('VENUE PUBLISHED');

		$this->setRedirect( 'index.php?option=com_redevent&view=venues', $msg );
	}

	/**
	 * Logic to unpublish venues
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

		$model = $this->getModel('venues');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('VENUE UNPUBLISHED');

		$this->setRedirect( 'index.php?option=com_redevent&view=venues', $msg );
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
		
		$venue = & JTable::getInstance('redevent_venues', '');
		$venue->bind(JRequest::get('post'));
		$venue->checkin();

		$this->setRedirect( 'index.php?option=com_redevent&view=venues' );
	}

	/**
	 * logic for remove venues
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function remove()
	{
		global $option;

		$cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to delete' ) );
		}

		$model = $this->getModel('venues');

		$msg = $model->delete($cid);

		$cache = &JFactory::getCache('com_redevent');
		$cache->clean();

		$this->setRedirect( 'index.php?option=com_redevent&view=venues', $msg );
	}

	/**
	 * logic to orderup a venue
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function orderup()
	{
		$model = $this->getModel('venues');
		$model->move(-1);

		$this->setRedirect( 'index.php?option=com_redevent&view=venues');
	}

	/**
	 * logic to orderdown a venue
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function orderdown()
	{
		$model = $this->getModel('venues');
		$model->move(1);

		$this->setRedirect( 'index.php?option=com_redevent&view=venues');
	}

	/**
	 * logic to create the edit venue view
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function edit( )
	{
		JRequest::setVar( 'view', 'venue' );
		JRequest::setVar( 'hidemainmenu', 1 );

		$model 	= $this->getModel('venue');
		$user	=& JFactory::getUser();

		// Error if checkedout by another administrator
		if ($model->isCheckedOut( $user->get('id') )) {
			$this->setRedirect( 'index.php?option=com_redevent&view=venues', JText::_( 'EDITED BY ANOTHER ADMIN' ) );
		}

		$model->checkout();
		
		parent::display();
	}

	/**
	 * saves the venue in the database
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		
		$task		= JRequest::getVar('task');

		// Sanitize
		$post = JRequest::get( 'post' );
		$post['locdescription'] = JRequest::getVar( 'locdescription', '', 'post', 'string', JREQUEST_ALLOWRAW );
		$post['locdescription']	= str_replace( '<br>', '<br />', $post['locdescription'] );


		$model = $this->getModel('venue');

		if ($returnid = $model->store($post)) {

			switch ($task)
			{
				case 'apply':
					$link = 'index.php?option=com_redevent&view=venue&hidemainmenu=1&cid[]='.$returnid;
					break;

				default:
					$link = 'index.php?option=com_redevent&view=venues';
					break;
			}
			$msg	= JText::_( 'VENUE SAVED');

			$cache = &JFactory::getCache('com_redevent');
			$cache->clean();

		} else {

			$msg 	= '';
			$link 	= 'index.php?option=com_redevent&view=venue';

		}

		$model->checkin();

		$this->setRedirect( $link, $msg );
	}

	/**
	 * saves the venue in the database
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function addvenue()
	{
		// Sanitize
		$post = JRequest::get( 'post' );
		$post['locdescription'] = JRequest::getVar( 'locdescription', '', 'post', 'string', JREQUEST_ALLOWRAW );


		$model = $this->getModel('venue');
		$model->store($post);
		$model->checkin();

		$msg	= JText::_( 'VENUE SAVED');
		$link 	= 'index.php?option=com_redevent&view=event&layout=addvenue&tmpl=component';

		$this->setRedirect( $link, $msg );
	}
	

	
	/**
	 * start events export screens
	 * 
	 */
	function importexport()
	{
		JRequest::setVar( 'view', 'venues' );
		JRequest::setVar( 'layout', 'importexport' );
		parent::display();
	}
	
	function doexport()
	{
		$app			=& JFactory::getApplication();
		
		$cats = JRequest::getVar('categories', null, 'request', 'array');
		JArrayHelper::toInteger($cats);
		
		$model = $this->getModel('venues');
		$rows = $model->export($cats);

		header('Content-Type: text/x-csv');
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Content-Disposition: attachment; filename=venues.csv');
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
	
	function import()
	{
//		$tab = Jtable::getInstance('RedEvent_venues', '');
//		$f = array_keys(get_object_vars($tab));
//		exit("array('v.".implode(", v.", $f)."')");
		
    $replace = JRequest::getVar('replace', 0, 'post', 'int');
    
    $msg = '';
    if ( $file = JRequest::getVar( 'import', null, 'files', 'array' ) )
    {
      $handle = fopen($file['tmp_name'],'r');
      if(!$handle) 
      {
        $msg = JText::_('Cannot open uploaded file.');  
        $this->setRedirect( 'index.php?option=com_redevent&controller=venues&task=importexport', $msg, 'error' ); 
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
        $this->setRedirect( 'index.php?option=com_redevent&controller=venues&task=importexport', $msg, 'error' );
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
        $model = $this->getModel('venues');
        $result = $model->import($records, $replace);
        $msg .= "<p>total added records: ".$result['added']."<br /></p>\n";
        $msg .= "<p>total updated records: ".$result['updated']."<br /></p>\n";
      }
      $this->setRedirect( 'index.php?option=com_redevent&controller=venues&task=importexport', $msg ); 
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
      default:
        $field = $value;
        break;
    }
    return $field;
  }
}
?>