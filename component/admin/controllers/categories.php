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
 * EventList Component Categories Controller
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventControllerCategories extends RedEventController
{
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra task
		$this->registerTask( 'add'  ,		 	'edit' );
		$this->registerTask( 'apply', 			'save' );
		$this->registerTask( 'accesspublic', 	'access' );
		$this->registerTask( 'accessregistered','access' );
		$this->registerTask( 'accessspecial', 	'access' );
	}

	/**
	 * Logic to save a category
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

		//Sanitize
		$post = JRequest::get( 'post' );
		$post['catdescription'] = JRequest::getVar( 'catdescription', '', 'post', 'string', JREQUEST_ALLOWRAW );
		$post['catdescription']	= str_replace( '<br>', '<br />', $post['catdescription'] );

		$model = $this->getModel('category');

		if ($returnid = $model->store($post)) {

			switch ($task)
			{
				case 'apply' :
					$link = 'index.php?option=com_redevent&view=category&cid[]='.$returnid;
					break;

				default :
					$link = 'index.php?option=com_redevent&view=categories';
					break;
			}
			$msg = JText::_('COM_REDEVENT_CATEGORY_SAVED' );

			$cache = &JFactory::getCache('com_redevent');
			$cache->clean();

		} else {

			$msg 	= '';
			$link 	= 'index.php?option=com_redevent&view=category';
		}
		
		$model->checkin();

		$this->setRedirect($link, $msg);
	}

	/**
	 * Logic to publish categories
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function publish()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_publish' ) );
		}

		$model = $this->getModel('categories');

		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('COM_REDEVENT_CATEGORY_PUBLISHED');

		$this->setRedirect( 'index.php?option=com_redevent&view=categories', $msg );
	}

	/**
	 * Logic to unpublish categories
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function unpublish()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_unpublish' ) );
		}

		$model = $this->getModel('categories');

		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('COM_REDEVENT_CATEGORY_UNPUBLISHED');

		$this->setRedirect( 'index.php?option=com_redevent&view=categories', $msg );
	}

	/**
	 * Logic to orderup a category
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function orderup()
	{
		$model = $this->getModel('categories');
		$model->move(-1);

		$this->setRedirect( 'index.php?option=com_redevent&view=categories');
	}

	/**
	 * Logic to orderdown a category
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function orderdown()
	{
		$model = $this->getModel('categories');
		$model->move(1);

		$this->setRedirect( 'index.php?option=com_redevent&view=categories');
	}

	/**
	 * Logic to mass ordering categories
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function saveordercat()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$order 	= JRequest::getVar( 'order', array(0), 'post', 'array' );
		JArrayHelper::toInteger($order, array(0));

		$model = $this->getModel('categories');
		$model->saveorder($cid, $order);

		$msg = 'New ordering saved';
		$this->setRedirect( 'index.php?option=com_redevent&view=categories', $msg );
	}

	/**
	 * Logic to delete categories
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function remove()
	{
    $option = JRequest::getCmd('option');

		$cid		= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_delete' ) );
		}

		$model = $this->getModel('categories');

		$msg = $model->delete($cid);

		$cache = &JFactory::getCache('com_redevent');
		$cache->clean();

		$this->setRedirect( 'index.php?option='. $option .'&view=categories', $msg );
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
		
		$category = & JTable::getInstance('redevent_categories', '');
		$category->bind(JRequest::get('post'));
		$category->checkin();

		$this->setRedirect( 'index.php?option=com_redevent&view=categories' );
	}

	/**
	 * Logic to set the category access level
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function access( )
	{
    $option = JRequest::getCmd('option');

		$cid		= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$id			= $cid[0];
		$task		= JRequest::getVar( 'task' );

		if ($task == 'accesspublic') {
			$access = 0;
		} elseif ($task == 'accessregistered') {
			$access = 1;
		} else {
			$access = 2;
		}

		$model = $this->getModel('category');
		$model->access( $id, $access );

		$this->setRedirect('index.php?option='. $option .'&view=categories' );
	}

	/**
	 * Logic to create the view for the edit categoryscreen
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function edit( )
	{
		JRequest::setVar( 'view', 'category' );
		JRequest::setVar( 'hidemainmenu', 1 );

		$model 	= $this->getModel('category');
		$user	=& JFactory::getUser();

		// Error if checkedout by another administrator
		if ($model->isCheckedOut( $user->get('id') )) {
			$this->setRedirect( 'index.php?option=com_redevent&view=categories', JText::_('COM_REDEVENT_EDITED_BY_ANOTHER_ADMIN' ) );
		}

		$model->checkout();
		
		parent::display();
	}
	
/**
	 * start categories export screens
	 * 
	 */
	function importexport()
	{
		JRequest::setVar( 'view', 'categories' );
		JRequest::setVar( 'layout', 'importexport' );
		parent::display();
	}
	
	function doexport()
	{
		$app			=& JFactory::getApplication();
				
		$model = $this->getModel('categories');
		$rows = $model->export();

		header('Content-Type: text/x-csv');
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Content-Disposition: attachment; filename=categories.csv');
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
//		$tab = Jtable::getInstance('RedEvent_categories', '');
//		$f = array_keys(get_object_vars($tab));
//		exit("array('c.".implode(", c.", $f)."')");
		
    $replace = JRequest::getVar('replace', 0, 'post', 'int');
    
    $msg = '';
    if ( $file = JRequest::getVar( 'import', null, 'files', 'array' ) )
    {
      $handle = fopen($file['tmp_name'],'r');
      if(!$handle) 
      {
        $msg = JText::_('COM_REDEVENT_Cannot_open_uploaded_file.');  
        $this->setRedirect( 'index.php?option=com_redevent&controller=categories&task=importexport', $msg, 'error' ); 
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
        $this->setRedirect( 'index.php?option=com_redevent&controller=categories&task=importexport', $msg, 'error' );
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
        $model = $this->getModel('categories');
        $result = $model->import($records, $replace);
        $msg .= "<p>total added records: ".$result['added']."<br /></p>\n";
        $msg .= "<p>total updated records: ".$result['updated']."<br /></p>\n";
      }
      $this->setRedirect( 'index.php?option=com_redevent&controller=categories&task=importexport', $msg ); 
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
