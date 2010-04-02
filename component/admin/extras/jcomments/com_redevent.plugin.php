<?php
if (!defined('_JEXEC')) die('Direct Access to this location is not allowed.');

require_once(JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'helpers'.DS.'route.php');
  
class jc_com_redevent extends JCommentsPlugin {
 
  function getObjectTitle( $id ) 
  {
    // Data load from database by given id 
    $db = & JCommentsFactory::getDBO();
    $query = ' SELECT title ' 
           . ' FROM #__redevent_events ' 
           . ' WHERE id = ' . $db->Quote($id);
    $db->setQuery($query);
    $res = $db->loadResult();
    return $res;
  }
 
  function getObjectLink( $id ) 
  { 
    // url link creation for given object by id 
    $link = JRoute::_( RedeventHelperRoute::getDetailsRoute($id) );
    return $link;
  }
 
  function getObjectOwner( $id ) 
  {
    $db = & JCommentsFactory::getDBO();
    $db->setQuery( 'SELECT created_by, id FROM #__redevent_events WHERE id = ' . $id );
    return $db->loadResult();
  }
}
?>