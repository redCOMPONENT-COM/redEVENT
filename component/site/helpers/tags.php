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

require_once (JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'helpers'.DS.'countries.php');

class redEVENT_tags {
	
	private $_xref;
	private $_eventid;
	private $_venueid;
	private $_submitkey;
	private $_maxattendees;
	private $_maxwaitinglist;
  private $_published;
	protected $_eventlinks = null;
	private $_libraryTags = null;
	private $_customfields = null;
	private $_xrefcustomfields = null;
	private $_answers = null;
	private $_options = null;
	
	private $_canregister = null;
		
	/**
	 * event model
	 * @var object
	 */
	private $_event = null;
	/**
	 * instance of rfcore
	 * @var object
	 */
	private $_rfcore = null;
	
	
	public function __construct($options = null) 
	{
		if (is_array($options))
		{
			$this->addOptions($options);
		}		
		
		$eventid = JRequest::getVar('id', 0, 'request', 'int');
		$this->setEventId($eventid);
		
		$xref = JRequest::getInt('xref');
		$this->setXref($xref);
		
		// if no xref specified. try to get one associated to the event id, published if possible !
		if (!$this->_xref)
		{
			$this->_initXref();
		}
		
		if ($this->_xref) {
      $db = & JFactory::getDBO();
			$q = "SELECT eventid, venueid, maxattendees, maxwaitinglist, published FROM #__redevent_event_venue_xref WHERE id = ".$this->_xref;
			$db->setQuery($q);
			list($this->_eventid, $this->_venueid, $this->_maxattendees, $this->_maxwaitinglist, $this->_published) = $db->loadRow();
      if (!$this->_published) {
        JError::raiseError(404, JText::_('COM_REDEVENT_This_event_is_not_published'), 'this xref is not published, can\'t be displayed in venues');
      }
		}
	}
	
	function setEventId($id)
	{
		$this->_eventid = intval($id);
	}
	
	function setEventObject($object)
	{
		$this->_event = $object;
	}
	
	public function setXref($xref)
	{
		if (($this->_xref !== $xref) && intval($xref)) {
			$this->_xref = intval($xref);
			$this->_customfields = null;
			$this->_xrefcustomfields = null;
		}
	}
	
	public function getXref()
	{
		if (!$this->_xref) {
			$this->_initXref();
		}
		return $this->_xref;
	}
	
	/**
	 * tries to pull a xref from the eventid
	 * return object
	 */
	private function _initXref()
	{
		$eventid = $this->_eventid;
		if ($eventid)
		{
			$db = & JFactory::getDBO();
			$query = ' SELECT x.id FROM #__redevent_event_venue_xref AS x '
			. ' INNER JOIN #__redevent_events AS e ON e.id = x.eventid '
			. ' WHERE x.published = 1 '
			. '   AND x.eventid = '. $db->Quote($eventid)
			. ' ORDER BY x.dates ASC '
			;
			$db->setQuery($query);
			$res = $db->loadResult();
			if ($res) {
				$this->setXref($res);
			}
		}
		return $this;
	}
	
	function setSubmitkey($string)
	{
		$this->_submitkey = $string;
	}
	
	/**
	 * add options (key, value) to object
	 * 
	 * @param array $options
	 */
	function addOptions($options)
	{
		if (is_array($options)) 
		{
			if (!empty($this->_options)) {
				$this->_options = array_merge($this->_options, $options);
			}
			else {
				$this->_options = $options;
			}
		}
	}
	
	function getOption($name, $default = null)
	{		
		if (isset($this->_options) && isset($this->_options[$name])) {
			return $this->_options[$name];
		}
		else {
			return $default;
		}
	}
	
	/**
	 * Substitute tags with the correct info
	 *
	 * Supported tags are:
	 * [event_description]
	 * [event_title]
	 * [price]
	 * [credits]
	 * [code]
	 * [redform]
	 * [inputname] Writes an input box for a name
	 * [inputemail] Writes an input box for an e-mail address
	 * [submit] Writes a submit button
	 * [event_info_text]
	 * [time]
	 * [date]
	 * [duration]
	 * [venue]
	 * [city]
	 * [username]
	 * [useremail]
	 * [regurl]
	 * [eventplaces]
	 * [waitinglistplaces]
	 * [eventplacesleft]
	 * [waitinglistplacesleft] 
	 * [paymentrequest]
	 * [paymentrequestlink]
	 */
	public function ReplaceTags($text, $options = null) 
	{
		$mainframe = &JFactory::getApplication();
		$base_url  = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
		$rfcore    = $this->_getRFCore();
		$iconspath = $base_url.'administrator/components/com_redevent/assets/images/';
		if ($options) {
			$this->addOptions($options);
		}
		
		$elsettings = redEVENTHelper::config();
		$this->_submitkey = $this->_submitkey ? $this->_submitkey : JRequest::getVar('submit_key');

		$text = $this->_replace($text);
		/* Include redFORM */
		if (strstr($text, '[redform]') && $this->getEvent()->getData()->redform_id > 0)
		{
			$status = redEVENTHelper::canRegister($this->_xref);
			if ($status->canregister)
			{
				$redform = $this->getForm($this->getEvent()->getData()->redform_id);
			}
			else {
				$redform = '<span class="registration_error">'.$status->status.'</span>';
			}

			/* second replacement, add the form */
			/* if done in first one, username in the form javascript is replaced too... */
			$text = str_replace('[redform]', $redform, $text);
		}

		return $text;
	}
	
	/**
	 * recursively replace tags
	 * 
	 * @param string $text
	 * @return $text
	 */
	function _replace($text)
	{
		$replaced = false; // check if tags where replaced, in which case we sshould run it again
			
		// first, let's do the library tags replacement
		$text = $this->_replaceLibraryTags($text);

		// now get the list of all remaining tags
		if (preg_match_all('/\[([^\]\s]+)(?:\s*)([^\]]*)\]/i', $text, $alltags, PREG_SET_ORDER))
		{
			$search = array();
			$replace = array();
			foreach ($alltags as $tag)
			{
				$tag_obj = new RedeventParsedTag($tag[0]);
				
				// check for conditions tags
				if ($tag_obj->getParam('condition_hasplacesleft') === "0" && $this->getEvent()->getPlacesLeft()) 
				{
					$search[] = $tag_obj->getFull();
					$replace[] = '';
					continue;
				}
				if ($tag_obj->getParam('condition_hasplacesleft') === "1" && $this->getEvent()->getData()->maxattendees > 0 && !$this->getEvent()->getPlacesLeft()) 
				{
					$search[] = $tag_obj->getFull();
					$replace[] = '';
					continue;
				}
				
				if ($this->_submitkey && strpos($tag_obj->getName(), 'attending_') === 0) // replace with rest of tag if attending
				{
					$search[] = $tag_obj->getFull();
					if ($this->_hasAttending()) {
						$replace[] = '['.substr($tag_obj->getName(), 10).']';
						$replaced = true;
					}
					else {
						$replace[] = '';
					}
				}
				else if ($this->_submitkey && strpos($tag_obj->getName(), 'waiting_') === 0) // replace with rest of tag if not attending
				{
					$search[] = $tag_obj->getFull();
					if ($this->_hasAttending()) {
						$replace[] = '';
					}
					else {
						$replace[] = '['.substr($tag_obj->getName(), 8).']';
						$replaced = true;
					}
				}
				else if ($this->_replaceLibraryTag($tag_obj->getName()) !== false)
				{
					$search[]  = $tag_obj->getFull();
					$replace[] = $this->_replaceLibraryTag($tag_obj->getName());					
				}
				else 
				{
					$func = '_getTag_'.strtolower($tag_obj->getName());
					if (method_exists($this, $func))
					{
						$search[] = $tag_obj->getFull();
						$replace[] = $this->$func($tag_obj);
						$replaced = true;
					}
				}
			}
			// do the replace
			$text = str_replace($search, $replace, $text);
		}

		// then the custom tags
		$search = array();
		$replace = array();

		/* Load custom fields */
		$customfields = $this->getCustomFields();
		foreach ($customfields as $tag => $data)
		{
			$search[] = '['.$data->text_name.']';
			$replace[] = $data->text_field;
		}
		$text = str_ireplace($search, $replace, $text, $count);

		if ($count) {
			$replaced = true;			
		}
		
		/* Load redform fields */
		if ($alltags)
		{
			$redformfields = $this->_getFieldsTags();
			if ($redformfields && count($redformfields))
			{
				foreach ($alltags as $tag)
				{
					if (stripos($tag[1], 'answer_') === 0)
					{
						$search[] = '['.$tag[1].']';
						$replace[] = $this->_getFieldAnswer(substr($tag[1], 7));
					}
				}
				$text = str_ireplace($search, $replace, $text, $count);
			}
		}

		if ($count) {
			$replaced = true;			
		}
		
		if ($replaced) {
			$text = $this->_replace($text);
		}

		return $text;
	}
	
	/**
	 * returns array of tags and their parameters
	 * 
	 * @param string $text
	 * @return array
	 */
	protected function _getTextTags($text)
	{
		
	}
	
	/**
	 * return event helper model object
	 * 
	 * @return object
	 */
	private function getEvent()
	{
		if (empty($this->_event)) 
		{
			$this->_event = &JModel::getInstance('Eventhelper', 'RedeventModel');
			$this->_event->setId($this->_eventid);
			$this->_event->setXref($this->_xref);
		}
		return $this->_event;
	}
		
	/**
	 * Load the HTML table with signup links
	 */
	private function SignUpLinks() 
	{
		if (!$this->_xref) {
			return false;
		}
		
		$app = & JFactory::getApplication();
		$this->getEventLinks();
		$template_path = JPATH_BASE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_redevent';
		
		$lists['order_Dir'] 	= JRequest::getWord('filter_order_Dir', 'ASC');
		$lists['order'] 		= JRequest::getCmd('filter_order', 'x.dates');
		$this->lists = $lists;
		
    $uri    = &JFactory::getURI();
    $this->action = $uri->toString();
    
    $this->customs = $this->getXrefCustomFields();
    
		ob_start();
		if (JRequest::getVar('format') == 'pdf') {
			if (file_exists($template_path.DS.'details'.DS.'courseinfo_pdf.php')) {
  			include($template_path.DS.'details'.DS.'courseinfo_pdf.php');				
			}
			else {
        include(JPATH_COMPONENT.DS.'views'.DS.'details'.DS.'tmpl'.DS.'courseinfo_pdf.php'); 				
			}
		}
		else {
      if (file_exists($template_path.DS.'details'.DS.'courseinfo.php')) {
        include($template_path.DS.'details'.DS.'courseinfo.php');       
      }
      else {
        include(JPATH_COMPONENT.DS.'views'.DS.'details'.DS.'tmpl'.DS.'courseinfo.php');         
      }
		}
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
	
	
	/**
	 * Load the HTML table with signup links
	 */
	private function _attachmentsHTML() 
	{
		$app = & JFactory::getApplication();
		$template_path = JPATH_BASE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_redevent';
				    
    $this->row = $this->getEvent()->getData();
    
		ob_start();
		if (JRequest::getVar('format') == 'pdf') {
			
		}
		else {
      if (file_exists($template_path.DS.'details'.DS.'default_attachments.php')) {
        include($template_path.DS.'details'.DS.'default_attachments.php');       
      }
      else {
        include(JPATH_COMPONENT.DS.'views'.DS.'details'.DS.'tmpl'.DS.'default_attachments.php');         
      }
		}
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
	
	/**
	 * Load all venues and their signup links
	 */
	private function getEventLinks() 
	{		
		if (empty($this->_eventlinks))
		{
			$xcustoms = $this->getXrefCustomFields();
			
			$order_Dir = JRequest::getWord('filter_order_Dir', 'ASC');
			$order 		 = JRequest::getCmd('filter_order', 'x.dates');
			
			$db = JFactory::getDBO();
			$query = ' SELECT e.*, IF (x.course_credit = 0, "", x.course_credit) AS course_credit, '
			   . ' x.id AS xref, x.dates, x.enddates, x.times, x.endtimes, x.maxattendees, x.maxwaitinglist, v.venue, x.venueid, x.details, x.registrationend, '
         . ' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', e.title, x.title) ELSE e.title END as full_title, '
			   . ' x.external_registration_url, '
			   . ' v.city AS location, v.state, v.url as venueurl, v.locdescription as venue_description, '
			   . ' v.country, v.locimage, v.street, v.plz, v.map, '
			   . ' f.formname, '
			   . ' UNIX_TIMESTAMP(x.dates) AS unixdates, '
			   . ' CASE WHEN CHAR_LENGTH(e.alias) THEN CONCAT_WS(":", e.id, e.alias) ELSE e.id END as slug, '
         . ' CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug, '
			   . ' CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(":", v.id, v.alias) ELSE v.id END as venueslug '
			   ;
			
			// add the custom fields
			foreach ((array) $xcustoms as $c)
			{
				$query .= ', x.custom'. $c->id;
			}
			   
			
			$query .= ' FROM #__redevent_events AS e '
			   . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
			   . ' INNER JOIN #__redevent_venues AS v ON x.venueid = v.id '
			   . ' LEFT JOIN #__rwf_forms AS f ON f.id = e.redform_id '
			   . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = e.id '
			   . ' LEFT JOIN #__redevent_categories AS c ON xcat.category_id = c.id '
			   . ' LEFT JOIN #__users AS u ON u.id = e.created_by '
			   ;		 
			
			$query .= ' WHERE x.published = '. $db->Quote($this->getEvent()->getData()->published)
			   . ' AND e.id = '.$this->_eventid
			   . ' GROUP BY x.id '
			   . ' ORDER BY '.$order.' '.$order_Dir.', x.dates, x.times '
			   ;
			$db->setQuery($query);
			$this->_eventlinks = $db->loadObjectList();
	    $this->_eventlinks = $this->_getPlacesLeft($this->_eventlinks);
			$this->_eventlinks = $this->_getCategories($this->_eventlinks);
	    $this->_eventlinks = $this->_getUserRegistrations($this->_eventlinks);
	    $this->_eventlinks = $this->_getPrices($this->_eventlinks);
		}
		return $this->_eventlinks;
	}
	
	private function _getCategories($rows)
	{		
    $db = JFactory::getDBO();
		foreach ($rows as $k => $r) {
			$query = ' SELECT c.id, c.catname, c.image, '
             . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) ELSE c.id END as slug '
			       . ' FROM #__redevent_categories AS c '
			       . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.category_id = c.id '
			       . ' WHERE xcat.event_id = ' . $db->Quote($r->id)
			       . ' ORDER BY c.lft '
			       ;
			$db->setQuery($query);
			$rows[$k]->categories = $db->loadObjectList();
		}
		return ($rows);
	}
    
  /**
   * adds registered (int) and waiting (int) properties to rows.
   * 
   * @return array 
   */
  private function _getPlacesLeft($rows) 
  {
    $db = JFactory::getDBO();
    foreach ($rows as $k => $r) 
    {
			$q = ' SELECT r.waitinglist, COUNT(r.id) AS total '
			   . ' FROM #__redevent_register AS r '
			   . ' WHERE r.xref = '. $db->Quote($r->xref)
			   . ' AND r.confirmed = 1 '
		     . ' AND r.cancelled = 0 '
			   . ' GROUP BY r.waitinglist '
			   ;
	    $db->setQuery($q);
	    $res = $db->loadObjectList('waitinglist');
      $rows[$k]->registered = (isset($res[0]) ? $res[0]->total : 0) ;
      $rows[$k]->waiting = (isset($res[1]) ? $res[1]->total : 0) ;
    }
    return $rows;
  }

  /**
   * adds property userregistered to rows: the number of time this user is already registered for each xref
   * 
   * @return array 
   */
  private function _getUserRegistrations($rows) 
  {
    $db = JFactory::getDBO();
    $user = & JFactory::getUser();
    
    foreach ($rows as $k => $r) 
    {
	    if ($user->get('id'))
	    {
	      $q = "SELECT COUNT(r.id) AS total
	        FROM #__redevent_register AS r 
	        WHERE r.xref = ". $db->Quote($r->xref) ."
	        AND r.confirmed = 1 
	        AND r.cancelled = 0
	        AND r.uid = ". $db->Quote($user->get('id')) ."
	        ";
	      $db->setQuery($q);
	      $rows[$k]->userregistered = $db->loadResult();
	    }
	    else 
	    {
        $rows[$k]->userregistered = 0;	    	
	    }
    }
    return $rows;
  }
    
  /**
   * adds registered (int) and waiting (int) properties to rows.
   * 
   * @return array 
   */
  private function _getPrices($rows) 
  {
  	if (!count($rows)) {
  		return $rows;
  	}
    $db = JFactory::getDBO();
    $ids = array();
    foreach ($rows as $k => $r) 
    {
    	$ids[$r->xref] = $k;
    }
    
  	$query = ' SELECT sp.*, p.name, p.alias, p.image, p.tooltip, f.currency, '
	         . ' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\', p.id, p.alias) ELSE p.id END as slug ' 
  	       . ' FROM #__redevent_sessions_pricegroups AS sp '
  	       . ' INNER JOIN #__redevent_pricegroups AS p on p.id = sp.pricegroup_id '
  	       . ' INNER JOIN #__redevent_event_venue_xref AS x on x.id = sp.xref '
  	       . ' INNER JOIN #__redevent_events AS e on e.id = x.eventid '
  	       . ' LEFT JOIN #__rwf_forms AS f on e.redform_id = f.id '
  	       . ' WHERE sp.xref IN (' . implode(",", array_keys($ids)).')'
  	       . ' ORDER BY p.ordering ASC '
  	       ;
  	$db->setQuery($query);
  	$res = $db->loadObjectList();
  	
  	// sort this out
  	$prices = array();
  	foreach ((array)$res as $p)
  	{
  		if (!isset($prices[$p->xref])) {
  			$prices[$p->xref] = array($p);
  		}
  		else {
  			$prices[$p->xref][] = $p;
  		}
  	}
  	
  	// add to rows
    foreach ($rows as $k => $r) 
    {
    	if (isset($prices[$r->xref])) {
    		$rows[$k]->prices = $prices[$r->xref];
    	}
    	else {
    		$rows[$k]->prices = null;
    	}
    }
    
    return $rows;
  }
  
	/**
	 * recursively replaces all the library tags from the text
	 * 
	 * @param string
	 * @return string
	 */
	private function _replaceLibraryTags($text) 
	{
	  $tags = &$this->_getLibraryTags();
	  
	  $search = array();
	  $replace = array();
	  foreach ($tags as $tag => $data) 
	  {
	    $search[] = '['.$data->text_name.']';
	    $replace[] = $data->text_field;
	  }
	  // first replacement
	  $text = str_ireplace($search, $replace, $text, $count);
	  
	  // now, the problem that there could have been libray tags embedded into one another, so we keep replacing if $count is > 0
	  if ($count) {
	    $text = $this->_replaceLibraryTags($text);
	  }
	  return $text;
	}
  
	/**
	 * recursively replaces all the library tags from the text
	 * 
	 * @param string
	 * @return string
	 */
	private function _replaceLibraryTag($tag) 
	{
	  $tags = &$this->_getLibraryTags();
	  	  
	  if (isset($tags[$tag])) {
	  	return $tags[$tag]->text_field;
	  }
	  return false;
	}

	/**
	 * gets list of tags belonging to the text library
   * 
   * @param array
   * @return array (objects: text_name, text_field)
   */
	private function &_getLibraryTags() 
	{
	  if (empty($this->_libraryTags)) 
	  {
  		$db = JFactory::getDBO();
  		$q = "SELECT text_name, text_field
  			FROM #__redevent_textlibrary WHERE CHAR_LENGTH(text_name) > 0";
  		$db->setQuery($q);
  		
  		$this->_libraryTags = $db->loadObjectList('text_name');
	  }
	  return $this->_libraryTags;
	}
	
	/**
	 * Returns the content of comments
	 *
	 * @param object $event
	 * @return html
	 */
	private function _getComments($event)
	{
		$app = & JFactory::getApplication();
    $template_path = JPATH_BASE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_redevent';
    $contents = '';
    $this->row = $event;
    $this->row->did = $event->id;
    $this->elsettings = & redEVENTHelper::config();
    if (JRequest::getVar('format') != 'raw') {
      ob_start();
      if (file_exists($template_path.DS.'details'.DS.'default_comments.php')) {
        include($template_path.DS.'details'.DS.'default_comments.php');       
      }
      else {
        include(JPATH_COMPONENT.DS.'views'.DS.'details'.DS.'tmpl'.DS.'default_comments.php');         
      }
	    $contents = ob_get_contents();
	    ob_end_clean();
    }
    return $contents;
	}
	
	private function _getFormalOffer($event)
	{
		ob_start();
		?>
		<form name="subemail" action="<?php echo JRoute::_('index.php'); ?>" method="post">
		  <?php echo $this->ReplaceTags($event->submission_type_formal_offer); ?>
		  <input type="hidden" name="task" value="signup" />
		  <input type="hidden" name="option" value="com_redevent" />
		  <input type="hidden" name="view" value="signup" />
		  <input type="hidden" name="subtype" value="formaloffer" />
		  <input type="hidden" name="sendmail" value="1" />
		  <input type="hidden" name="xref" value="<?php echo $event->xref; ?>" />
		  <input type="hidden" name="id" value="<?php echo $event->id; ?>" />
		</form>
		<?php
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;    
	}

  private function _getEmailSubmission($event)
  {
    ob_start();
    ?>
		<form name="subemail" action="<?php echo JRoute::_('index.php'); ?>" method="post">
		  <?php echo $this->ReplaceTags($event->submission_type_email); ?>
		  <input type="hidden" name="task" value="signup" />
		  <input type="hidden" name="option" value="com_redevent" />
		  <input type="hidden" name="view" value="signup" />
		  <input type="hidden" name="subtype" value="email" />
		  <input type="hidden" name="sendmail" value="1" />
      <input type="hidden" name="xref" value="<?php echo $event->xref; ?>" />
      <input type="hidden" name="id" value="<?php echo $event->id; ?>" />
		</form>
    <?php  
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;    
  }
  
  /**
   * get custom fields and their value
   *
   * @return array tag => field
   */
  function getCustomfields()
  {
  	if (empty($this->_customfields))
  	{
  		$details = &$this->getEvent()->getData();
  		
	  	$db = & JFactory::getDBO();
	    $query = ' SELECT f.* '
	           . ' FROM #__redevent_fields AS f '
	           . ' WHERE f.published = 1 '
	           . ' AND CHAR_LENGTH(f.tag) > 0 '
	           ;
	    $db->setQuery($query);
	    $fields = $db->loadObjectList();
	        
	    $replace = array();
	    foreach ((array) $fields as $field)
	    {
	    	$prop = 'custom'.$field->id;
	    	if (isset($details->$prop)) {
	    		$field->value = $details->$prop;
	    	}
	    	else {
	    		$field->value = null;
	    	} 
	    	$obj = new stdclass();
	    	$obj->text_name = $field->tag;
	      $obj->text_field = redEVENTHelper::renderFieldValue($field);
	      $replace[$field->tag] = $obj;
	    }
	    $this->_customfields = $replace;
  	}
    return $this->_customfields;
  }
  
  /**
   * returns all custom fields for xrefs
   * 
   * @return array
   */
  function getXrefCustomFields()
  {
  	if (empty($this->_xrefcustomfields))
  	{
  		$db = & JFactory::getDBO();
	  	$query = ' SELECT f.id, f.name, f.in_lists, f.searchable, f.ordering '
	  	       . ' FROM #__redevent_fields AS f'
	  	       . ' WHERE f.published = 1'
	  	       . '   AND f.object_key = '. $db->Quote('redevent.xref')
	  	       . ' ORDER BY f.ordering ASC '
	  	       ;
	  	$db->setQuery($query);
	  	$this->_xrefcustomfields = $db->loadObjectList();
  	}
  	return $this->_xrefcustomfields;
  }
  
  function getAttendeeUniqueId($submit_key)
  {
  	$db = & JFactory::getDBO();
  	$query = ' SELECT e.title, e.alias, e.course_code, r.xref, r.id '
  	       . ' FROM #__redevent_register AS r '
  	       . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.id = r.xref '
  	       . ' INNER JOIN #__redevent_events AS e ON e.id = x.eventid '
  	       . ' WHERE r.submit_key = '. $db->Quote($submit_key)
  	       ;
  	$db->setQuery($query, 0, 1);
  	$obj = $db->loadObject();
  	if ($obj) {
  		$code = $obj->course_code .'-'. $obj->xref .'-'. $obj->id;
  	}
  	else {
  		$code = '';
  	}
  	return $code;
  }
  	
  /**
   * return answers as html text
   * 
   * @param string $submit_key
   * @return string html
   */
  private function _answersToHtml()
  {
  	if (empty($this->_submitkey)) {
  		return '';
  	}
  	$answers = $this->_getAnswers();
  	if (!$answers) {
  		return '';
  	}
  	$res = '';
  	
  	foreach ($answers as $a)
  	{
  		$res .= '<table class="formanswers">';
			foreach ($a as $field)
			{
				$res .= '<tr>';
				$res .= '<th align="left">'.$field->field.'</th>';
				$res .= '<td>'.str_replace('~~~','<br/>', $field->answer).'</td>';
				$res .= '</tr>';
			}
  		$res .= '</table>';  		
  	}
  	return $res;
  }
  
  /**
   * returns answers as array of row arrays
   * 
   * @return array
   */
  private function _getAnswers()
  { 
	  	if (!$this->getEvent()->getData()) {
	  		JError::raiseWarning(0, JText::_('COM_REDEVENT_Error_missing_data'));
	  		return false;
	  	}
	  	
	  	if (!$sids = $this->getOption('sids'))
	  	{
		  	if (!$this->_submitkey) {
		  		return false;
		  	}
		  	
		  	$db = & JFactory::getDBO();
		  	$query = ' SELECT r.sid '
		  	       . ' FROM #__redevent_register AS r '
		  	       . ' WHERE r.submit_key = '.$db->quote($this->_submitkey)
		  	       ;
				$db->setQuery($query);
				$sids = $db->loadResultArray();
		  }
		  							
			$rfcore = $this->_getRFCore();
			return $rfcore->getSidsFieldsAnswers($sids);
  }
  
  private function _getSubmissionTotalPrice()
  {
  	if (!$this->_submitkey) {
			return false;
		}
		  	
		$db = & JFactory::getDBO();
		$query =  ' SELECT SUM(s.price) '
		        . ' FROM #__rwf_submitters AS s '
		        . ' WHERE s.submit_key = '.$db->quote($this->_submitkey)
		        . ' GROUP BY s.submit_key '
		        ;
		$db->setQuery($query);
		$res = $db->loadResult();
		return $res;
  }

  private function _getFieldsTags()
  {  	
  	if (!$this->getEvent()->getData()) {
  		JError::raiseWarning(0, JText::_('COM_REDEVENT_Error_missing_data'));
  		return false;
  	}
		$rfcore = $this->_getRFCore();
  	$fields = $rfcore->getFields($this->getEvent()->getData()->redform_id);
  	
  	$tags = array();
  	foreach ((array) $fields as $f) {
  		$tags[$f->id] = 'answer_'.$f->id;
  	}
  	return $tags;
  }

  private function _getFieldAnswer($id)
  {
  	$answers = $this->_getAnswers();
  	if (!$answers) {
  		return '';
  	}
  	
  	// only take first answer...
  	$fields = reset($answers);
  	foreach ($fields as $f)
  	{
  		if ($f->id == $id) {
  			return $f->answer;
  		}
  	}
  	return '';
  }
  
  private function _getRFCore()
  {
  	if (empty($this->_rfcore))
  	{
  		$this->_rfcore = new RedFormCore();
  	}
  	return $this->_rfcore;
  }
  
  /**
   * return current number of registrations for current user to this event
   * @return int
   */
  private function _getCurrentRegs()
  {
  	$user = &JFactory::getUser();
  	if (!$user) {
  		JError::raiseError(403, 'NO_AUTH');
  	}
  		
  	$db = &JFactory::getDBO();
  	$query = ' SELECT COUNT(id) ' 
  	       . ' FROM #__redevent_register AS r ' 
  	       . ' WHERE r.uid = ' . $user->get('id')
		       . '   AND r.cancelled = 0 '
  	       . '   AND r.xref = ' . $this->_xref
  	       ;
  	$db->setQuery($query);
  	$res = $db->loadResult();
  	return $res;
  }
  
  /**
   * returns form
   * 
   * @return string
   */
  function getForm()
  {
		$app = &JFactory::getApplication();
  	$submit_key = JRequest::getVar('submit_key');
  	
  	$details = $this->getEvent()->getData();
  	$prices  = $this->getEvent()->getPrices();
  	$options = array('extrafields' => array());
  	$user = &JFactory::getUser();
  	
 		$rfcore = $this->_getRFCore();
 		if (!$rfcore->getFormStatus($this->getEvent()->getData()->redform_id)) {
			$error = $rfcore->getError();
			return '<span class="redform_error">'.$error.'</span>';
 		}
 		$form = $rfcore->getForm($this->getEvent()->getData()->redform_id);
 		
  	$action = JRoute::_(RedeventHelperRoute::getRegistrationRoute($this->getEvent()->getData()->xslug, 'register'));
  	
  	// multiple signup ?
  	$single = JRequest::getInt('single', 0);
  	$max = $this->getEvent()->getData()->max_multi_signup;
  	if ($max && ! $single && $user->get('id')) {
  		$multi = $max;
  		// we must deduce current registrations of this user !
  		$nbregs = $this->_getCurrentRegs();
  		$multi = $max - $nbregs;
  		
  		if ($multi < 1) {
  			return JText::_('COM_REDEVENT_USER_MAX_REGISTRATION_REACHED');
  		}
  	}
  	else { // single signup 
  		$multi = 1;
  	}
  	
  	// multiple pricegroup handling  
  	$selpg = null;
  	if (count($prices))
  	{
  		$currency = current($prices)->currency ? current($prices)->currency : null;
  		
	  	// is pricegroup already selected ?
	  	// if a review, we already have pricegroup_id in session
	  	$pgids = $app->getUserState('pgids'.$submit_key);	  	
	  	if (!empty($pgids)) {
	  		$pg = intval($pgids[0]);
	  	}
	  	else {
	  		$pg = JRequest::getInt('pg');
	  	}
	  	
	  	if (count($prices) == 1) {
	  		$selpg = current($prices);
	  	}
	  	else if ($pg)
	  	{
	  		foreach ($prices as $p)
	  		{
	  			if ($p->pricegroup_id == $pg)
	  			{
	  				$selpg = $p;
	  				break;
	  			}
	  		}
	  	}
  	
  		if (($multi > 1 && count($prices) > 1) || !$selpg) // multiple selection
  		{
  			$field = array();
  			$field['label'] = '<label for="pricegroup_id">'.JText::_('COM_REDEVENT_REGISTRATION_PRICE').'</label>';
  			$field['field'] = ($currency ? $currency.' ' : '').redEVENTHelper::getRfPricesSelect($prices);
  			$field['class'] = 'reg-price';
	  		$options['extrafields'][] = $field;
  		}
  		else // single selection => hidden field
  		{
  			$field = array();
  			$field['label'] = '<label for="pricegroup_id">'.JText::_('COM_REDEVENT_REGISTRATION_PRICE').'</label>';
  			$field['field'] = ELOutput::formatprice($selpg->price, $currency).(count($prices) > 1 ? ' ('.$selpg->name.')' : '') . '<input type="hidden" name="pricegroup_id[]" class="fixedprice" value="'.$selpg->pricegroup_id.'" price="'.$selpg->price.'" />';
  			$field['class'] = 'reg-price pg'.$selpg->id;
	  		$options['extrafields'][] = $field;
  		}
  	}
  	  	
  	$details->course_price = null;
  	$options['booking'] = $details;

  	$html = '<form action="'.$action.'" method="post" name="redform" enctype="multipart/form-data" onsubmit="return CheckSubmit(this);">';
  	$html .= $rfcore->getFormFields($this->getEvent()->getData()->redform_id, $submit_key, $multi, $options);
  	$html .= '<input type="hidden" name="xref" value="'.$this->_xref.'"/>';
  	if ($this->getOption('hasreview')) {
  		$html .= '<input type="hidden" name="hasreview" value="1"/>';
  	}
		$html .= '<div id="submit_button" style="display: block;" class="submitform'.$form->classname.'">';
		if (empty($submit_key)) {
			$html .= '<input type="submit" id="regularsubmit" name="submit" value="'.JText::_('COM_REDEVENT_Submit').'" />';
		}
		else {
			$html .= '<input type="submit" id="redformsubmit" name="submit" value="'.JText::_('COM_REDEVENT_Confirm').'" />';
			$html .= '<input type="submit" id="redformcancel" name="cancel" value="'.JText::_('COM_REDEVENT_Cancel').'" />';
		}			
		$html .= '</div>';
  	$html .= '</form>';
  	return $html;
  }
    	
  function absoluteUrls($url, $xhtml = true, $ssl = null)
	{
		// Get the router
		$app	= &JFactory::getApplication();
		$router = &$app->getRouter();

		// Make sure that we have our router
		if (! $router) {
			return null;
		}

		if ( (strpos($url, '&') !== 0 ) && (strpos($url, 'index.php') !== 0) ) {
            return $url;
 		}

		// Build route
		$uri = &$router->build($url);
		$url = $uri->toString(array('path', 'query', 'fragment'));

		// Replace spaces
		$url = preg_replace('/\s/u', '%20', $url);

		/*
		 * Get the secure/unsecure URLs.

		 * If the first 5 characters of the BASE are 'https', then we are on an ssl connection over
		 * https and need to set our secure URL to the current request URL, if not, and the scheme is
		 * 'http', then we need to do a quick string manipulation to switch schemes.
		 */
		$ssl	= (int) $ssl;
		if ( $ssl || 1 )
		{
			$uri	         =& JURI::getInstance();

			// Get additional parts
			static $prefix;
			if ( ! $prefix ) {
				$prefix = $uri->toString( array('host', 'port'));
				//$prefix .= JURI::base(true);
			}

			// Determine which scheme we want
			$scheme	= ( $ssl === 1 ) ? 'https' : 'http';

			// Make sure our url path begins with a slash
			if ( ! preg_match('#^/#', $url) ) {
				$url	= '/' . $url;
			}

			// Build the URL
			$url	= $scheme . '://' . $prefix . $url;
		}

		if($xhtml) {
			$url = str_replace( '&', '&amp;', $url );
		}

		return $url;
	}
	
	function formatPrices($prices)
	{
		if (!is_array($prices)) {
			return;
		}
		if (count($prices) == 1) {
			return ELOutput::formatprice($prices[0]->price);
		}
		$res = array();
		foreach ($prices as $p) 
		{
			$res[] = ELOutput::formatprice($p->price). ' ('.$p->name.')';
		}
		return implode(' / ', $res);
	}
	
	/**
	 * returns true if at least one attendee associated to current submit_key is attending
	 * 
	 * @return boolean
	 */
	private function _hasAttending()
	{
		$db = &JFactory::getDBO();
		// get how many registrations are associated to submit key, and how manyn on waiting list
		$query = ' SELECT COUNT(*) as total, SUM(r.waitinglist) as waiting '
		       . ' FROM #__redevent_register AS r ' 
		       . ' WHERE r.submit_key = '. $db->Quote($this->_submitkey)
		       . ' GROUP BY r.submit_key '
		       ;
		$db->setQuery($query);
		$res = $db->loadObject();
				
		if (!$res || !$res->total) {
			// no attendee at all for submit key... no display...
			return false;
		}
		
		if ($res->total != $res->waiting) {
			// not all registrations are on wl
			return true;
		}
		else {
			return false;
		}
	}
	
	private function _canRegister()
	{
		if ($this->_canregister === null) {
			$this->_canregister = redEVENTHelper::canRegister($this->getXref());
		}
		return $this->_canregister;
	}
	
	/*************************************************************************
	 * tags functions
	 * 
	 * name must be _getTag_xxxxx_yyy
	 * 
	 */
	
	/************ event tags **************************/
	
	/**
	 * Parses event_description tag
	 *
	 * @return string
	 */
	function _getTag_event_description()
	{
		/* Fix the tags of the event description */
		$findcourse = array('[venues]','[price]','[credits]', '[code]');
		$venues_html = $this->SignUpLinks();

		$replacecourse = array($venues_html,
		$this->formatPrices($this->getEvent()->getPrices()),
		$this->getEvent()->getData()->course_credit,
		$this->getEvent()->getData()->course_code);
		$res = str_replace($findcourse, $replacecourse, $this->getEvent()->getData()->datdescription);
		return $res;
	}
	
	function _getTag_event_info_text()
	{
		return $this->_getTag_event_description();
	}
	
	function _getTag_event_title()
	{
		return $this->getEvent()->getData()->title;
	}
	
	function _getTag_event_full_title()
	{
		return $this->getEvent()->getData()->full_title;
	}
	
	function _getTag_price()
	{
		return $this->formatPrices($this->getEvent()->getPrices());
	}
	
	function _getTag_credits()
	{
		return $this->getEvent()->getData()->course_credit;
	}
	
	function _getTag_code()
	{
		return $this->getEvent()->getData()->course_code;
	}
	
	function _getTag_date()
	{
		return ELOutput::formatdate($this->getEvent()->getData()->dates, $this->getEvent()->getData()->times);
	}
	
	function _getTag_enddate()
	{
		return ELOutput::formatdate($this->getEvent()->getData()->enddates, $this->getEvent()->getData()->endtimes);
	}
	
	function _getTag_time()
	{
		$tmp = "";
		if (!empty($this->getEvent()->getData()->times) && strcasecmp('00:00:00', $this->getEvent()->getData()->times))
		{
			$tmp = ELOutput::formattime($this->getEvent()->getData()->dates, $this->getEvent()->getData()->times);
			 
			if (!empty($this->getEvent()->getData()->endtimes) && strcasecmp('00:00:00', $this->getEvent()->getData()->endtimes)) {
				$tmp .= ' - ' .ELOutput::formattime($this->getEvent()->getData()->enddates, $this->getEvent()->getData()->endtimes);
			}
		}
		return $tmp;
	}
	
	function _getTag_startenddatetime()
	{
		$tmp = ELOutput::formatdate($this->getEvent()->getData()->dates, $this->getEvent()->getData()->times);
		if (!empty($this->getEvent()->getData()->times) && strcasecmp('00:00:00', $this->getEvent()->getData()->times)) {
			$tmp .= ' ' .ELOutput::formattime($this->getEvent()->getData()->dates, $this->getEvent()->getData()->times);
		}
		if (!empty($this->getEvent()->getData()->enddates) && $this->getEvent()->getData()->enddates != $this->getEvent()->getData()->dates)
		{
			$tmp .= ' - ' .ELOutput::formatdate($this->getEvent()->getData()->enddates, $this->getEvent()->getData()->endtimes);
		}
		if (!empty($this->getEvent()->getData()->endtimes) && strcasecmp('00:00:00', $this->getEvent()->getData()->endtimes)) {
			$tmp .= ' ' .ELOutput::formattime($this->getEvent()->getData()->dates, $this->getEvent()->getData()->endtimes);
		}
		return $tmp;
	}
	
	function _getTag_duration()
	{
		return redEVENTHelper::getEventDuration($this->getEvent()->getData());
	}
	
	function _getTag_event_image()
	{
		$eventimage = redEVENTImage::flyercreator($this->getEvent()->getData()->datimage, 'event');
		$eventimage = JHTML::image(JURI::root().'/'.$eventimage['original'], $this->getEvent()->getData()->title, array('title' => $this->getEvent()->getData()->title));
		return $eventimage;
	}
	
	function _getTag_eventimage()
	{
		return $this->_getTag_event_image();
	}
	
	function _getTag_event_thumb()
	{
		$eventimage = redEVENTImage::modalimage('events', basename($this->getEvent()->getData()->datimage), $this->getEvent()->getData()->title);
		return $eventimage;
	}
	
	function _getTag_category_image()
	{
		$cats_images = array();
		foreach ($this->getEvent()->getData()->categories as $c){
			$cats_images[] = redEVENTImage::getCategoryImage($c, false);
		}
		$categoryimage = '<span class="details-categories-images"><span class="details-categories-image">'.implode('</span><span class="details-categories-image">', $cats_images).'</span></span>';

		return $categoryimage;
	}
	
	function _getTag_categoryimage()
	{
		return $this->_getTag_category_image;
	}
	
	function _getTag_category_thumb()
	{
		$cats_images = array();
		foreach ($this->getEvent()->getData()->categories as $c){
			$cats_images[] = redEVENTImage::getCategoryImage($c);
		}
		$categoryimage = '<span class="details-categories-images"><span class="details-categories-image">'.implode('</span><span class="details-categories-image">', $cats_images).'</span></span>';

		return $categoryimage;
	}
	
	function _getTag_info()
	{
		// check that there is no loop with the tag inclusion
		if (strpos($this->getEvent()->getData()->details, '[info]') === false) {
			$info = $this->ReplaceTags($this->getEvent()->getData()->details);
		}
		else {
			JError::raiseNotice(0, JText::_('COM_REDEVENT_ERROR_TAG_LOOP_XREF_DETAILS'));
			$info = '';
		}
		return $info;
	}
	
	function _getTag_category()
	{
		// categories
		$cats = array();
		foreach ($this->getEvent()->getData()->categories as $c){
			$cats[] = JHTML::link($this->absoluteUrls(RedeventHelperRoute::getCategoryEventsRoute($c->slug)), $c->catname);
		}
		return '<span class="details-categories">'.implode(', ', $cats).'</span>';
	}
	
	function _getTag_eventcomments()
	{
		return $this->_getComments($this->getEvent()->getData());
	}
	
	function _getTag_permanentlink()
	{
		$link = JHTML::link($this->absoluteUrls(
		                        RedeventHelperRoute::getDetailsRoute($this->getEvent()->getData()->slug), 
		                        false)
		                    , JText::_('COM_REDEVENT_Permanent_link'), 'class="permalink"');
		return $link;
	}
	
	function _getTag_datelink()
	{
		$link = JHTML::link($this->absoluteUrls(
		                                  RedeventHelperRoute::getDetailsRoute($this->getEvent()->getData()->slug, 
		                                       $this->_xref), false), 
		                    JText::_('COM_REDEVENT_Event_details'), 'class="datelink"');
		
		return $link;
	}
		
	/**
	 * Parses tag ical_url
	 * returns link to session ical export
	 * @return string
	 */
	function _getTag_ical()
	{
		$ttext = JText::_('COM_REDEVENT_EXPORT_ICS');
	  $res = JHTML::link( $this->_getTag_ical_url(), 
	                      $ttext, array('class' => 'event-ics'));
		return $res;
	}
	
	/**
	 * Parses tag ical_url
	 * returns url to session ical export
	 * @return string
	 */
	function _getTag_ical_url()
	{
		$res = $this->absoluteUrls(
		                RedeventHelperRoute::getDetailsRoute($this->getEvent()->getData()->slug, 
		                         $this->getEvent()->getData()->xslug).'&format=raw&layout=ics', 
		                false);
		return $res;
	}
	
	/**
	 * Parses tag summary
	 * returns event summary
	 * @return string
	 */
	function _getTag_summary()
	{
		return $this->getEvent()->getData()->summary;
	}
	
	/**
	 * Parses tag moreinfo
	 * returns list of attachments
	 * @return string
	 */
	function _getTag_attachments()
	{
		return $this->_attachmentsHTML();
	}

	/**
	 * Parses tag moreinfo
	 * generates a modal link to a more info form for the session
	 * @return string
	 */
	function _getTag_moreinfo()
	{
		JHTML::_('behavior.modal', 'a.moreinfo');
		$link = JRoute::_(RedeventHelperRoute::getMoreInfoRoute($this->getEvent()->getData()->xslug, 
		                                                        array('tmpl' =>'component')));
		$text = '<a class="moreinfo" title="'.JText::_('COM_REDEVENT_DETAILS_MOREINFO_BUTTON_LABEL')
		      .  '" href="'.$link.'" rel="{handler: \'iframe\', size: {x: 400, y: 500}}">'
		      . JText::_('COM_REDEVENT_DETAILS_MOREINFO_BUTTON_LABEL')
		      . ' </a>'
		      ;
		return $text;
	}
	
	/**
	 * returns event creator name
	 * @return string
	 */
	function _getTag_author_name()
	{
		return $this->getEvent()->getData()->creator_name;
	}
		
	/**
	* returns event creator email
	* @return string
	*/
	function _getTag_author_email()
	{
		return $this->getEvent()->getData()->creator_email;
	}
	
	/**************  venue tags ******************/
	
	function _getTag_venue()
	{
		return $this->getEvent()->getData()->venue;
	}
	
	function _getTag_venue_title()
	{
		return $this->_getTag_venue();
	}

	function _getTag_venue_company()
	{
		return $this->getEvent()->getData()->venue_company;
	}

	function _getTag_city()
	{
		return $this->getEvent()->getData()->location;
	}
	
	function _getTag_venue_city()
	{
		return $this->_getTag_city();
	}
	
	function _getTag_venues($tag)
	{
		return $this->SignUpLinks();
	}	
	
	function _getTag_venue_street()
	{
		return $this->getEvent()->getData()->street;
	}
	
	function _getTag_venue_zip()
	{
		return $this->getEvent()->getData()->plz;
	}
	
	function _getTag_venue_state()
	{
		return $this->getEvent()->getData()->state;
	}
	
	function _getTag_venue_link()
	{
		$link = JHTML::link(
		          $this->absoluteUrls(RedeventHelperRoute::getVenueEventsRoute($this->getEvent()->getData()->venueslug)), 
		          $this->getEvent()->getData()->venue);
		return $link;
	}
	
	function _getTag_venue_website()
	{
		$res = '';
		if (!empty($this->getEvent()->getData()->venueurl)) {
			$res = JHTML::link($this->absoluteUrls(($this->getEvent()->getData()->venueurl)), 
			                   JText::_('COM_REDEVENT_Venue_website'));
		}
		return $res;
	}
	
	function _getTag_venueimage()
	{
		$venueimage = redEVENTImage::flyercreator($this->getEvent()->getData()->locimage);
		$venueimage = JHTML::image(JURI::root().'/'.$venueimage['original'], 
		                           $this->getEvent()->getData()->venue, 
		                           array('title' => $this->getEvent()->getData()->venue));
		$venueimage = JHTML::link($this->absoluteUrls(
		                            RedeventHelperRoute::getVenueEventsRoute($this->getEvent()->getData()->venueslug)), 
		                          $venueimage);
		return $venueimage;
	}
	
	function _getTag_venue_image()
	{
		return $this->_getTag_venueimage();
	}
	
	function _getTag_venue_thumb()
	{              
		$venueimage = redEVENTImage::modalimage('venues', 
		                                        basename($this->getEvent()->getData()->locimage), 
		                                        $this->getEvent()->getData()->venue);
		return $venueimage;
	}
	
	function _getTag_venue_description()
	{
		return $this->getEvent()->getData()->venue_description;
	}
	
	function _getTag_venue_country()
	{
		return redEVENTHelperCountries::getCountryName($this->getEvent()->getData()->country);
	}
	
	function _getTag_venue_countryflag()
	{
		return redEVENTHelperCountries::getCountryFlag($this->getEvent()->getData()->country);
	}
	
	function _getTag_venue_mapicon()
	{
		return ELOutput::mapicon($this->getEvent()->getData(), array('class' => 'event-map'));
	}
	
	function _getTag_venue_map()
	{
		return ELOutput::map($this->getEvent()->getData(), array('class' => 'event-full-map'));
	}
	
	/**************  registration tags ******************/
	
	function _getTag_redform_title()
	{
		return $this->getEvent()->getData()->formname;
	}
	
	function _getTag_inputname()
	{
		$text = '<div id="divsubemailname">'
		      .   '<div class="divsubemailnametext">'.JText::_('COM_REDEVENT_NAME').'</div>'
		      .   '<div class="divsubemailnameinput"><input type="text" name="subemailname" /></div>'
		      . '</div>';
		return $text;
	}
	
	function _getTag_inputemail()
	{
		$text = '<div id="divsubemailaddress">'
		      .   '<div class="divsubemailaddresstext">'.JText::_('COM_REDEVENT_EMAIL').'</div>'
		      .   '<div class="divsubemailaddressinput"><input type="text" name="subemailaddress" /></div>'
		      . '</div>';
		return $text;
	}
	
	function _getTag_submit()
	{
		$text = '<div id="disubemailsubmit"><input type="submit" value="'.JText::_('COM_REDEVENT_SUBMIT').'" /></div>';
		return $text;
	}
	
	function _getTag_registrationend()
	{
		$res = '';
		if (strtotime($this->getEvent()->getData()->registrationend))
		{
			$elsettings = redEVENTHelper::config();
			$res = strftime( $elsettings->formatdate . ' '. $elsettings->formattime, 
			                 strtotime($this->getEvent()->getData()->registrationend));
		}
		return $res;
	}
	
	function _getTag_username()
	{
		$res = '';
		$emails = $this->_getRFCore()->getSubmissionContactEmail($this->_submitkey, false);
		if (is_array($emails) && count($emails)) {
			$contact = current($emails);
			$res = isset($contact['username']) ? $contact['username'] : '';
		}
		return $res;
	}
	
	function _getTag_useremail()
	{
		$res = '';
		$emails = $this->_getRFCore()->getSubmissionContactEmail($this->_submitkey, true);
		if (is_array($emails) && count($emails)) {
			$contact = current($emails);
			$res = isset($contact['email']) ? $contact['email'] : '';
		}
		return $res;
	}
	
	function _getTag_userfullname()
	{
		$res = '';
		$emails = $this->_getRFCore()->getSubmissionContactEmail($this->_submitkey, true);
		if (is_array($emails) && count($emails)) {
			$contact = current($emails);
			$res = isset($contact['fullname']) ? $contact['fullname'] : '';
		}
		return $res;
	}
	
	/**
	 * Parses tag answers
	 * returns attendee answers to registration form
	 * @return string
	 */
	function _getTag_answers()
	{
		return $this->_answersToHtml();
	}
	
	function _getTag_regurl()
	{
		return $this->absoluteUrls($uri->toString());
	}
	
	function _getTag_eventplaces()
	{
		return $this->_maxattendees;
	}
	
	function _getTag_waitinglistplaces()
	{
		return $this->_maxwaitinglist;
	}
	
	function _getTag_eventplacesleft($params)
	{
		return $this->getEvent()->getPlacesLeft();
	}
	
	function _getTag_waitinglistplacesleft()
	{
		return $this->getEvent()->getWaitingPlacesLeft();
	}
	
	function _getTag_webformsignup()
	{
		$registration_status = $this->_canRegister();
		if (!$registration_status->canregister)
		{
			$img = JHTML::_('image', JURI::root() . 'components/com_redevent/assets/images/agt_action_fail.png', 
		                          $registration_status->status, 
		                          array('class' => 'hasTip', 'title' => $registration_status->status));
		  return $img;
		}
		$mainframe = &JFactory::getApplication();
		$base_url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
		$iconspath = $base_url.'administrator/components/com_redevent/assets/images/';
		$elsettings = redEVENTHelper::config();
		$text = '<span class="vlink webform">'
               . JHTML::_('link', 
                          $this->absoluteUrls(RedeventHelperRoute::getSignupRoute('webform', 
                                 $this->getEvent()->getData()->slug, 
                                 $this->getEvent()->getData()->xslug)), 
                          JHTML::_('image', $iconspath.$elsettings->signup_webform_img,  
                          JText::_($elsettings->signup_webform_text), 
                          'width="24px" height="24px"'))
               .'</span> ';
		return $text;
	}
	
	function _getTag_emailsignup()
	{
		$registration_status = $this->_canRegister();
		if (!$registration_status->canregister)
		{
			$img = JHTML::_('image', JURI::root() . 'components/com_redevent/assets/images/agt_action_fail.png', 
		                          $registration_status->status, 
		                          array('class' => 'hasTip', 'title' => $registration_status->status));
		  return $img;
		}
		$mainframe = &JFactory::getApplication();
		$base_url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
		$iconspath = $base_url.'administrator/components/com_redevent/assets/images/';
		$elsettings = redEVENTHelper::config();
		$text = '<span class="vlink email">'
		      . JHTML::_('link',
		                 $this->absoluteUrls(RedeventHelperRoute::getSignupRoute('email', $this->getEvent()->getData()->slug, $this->getEvent()->getData()->xslug)),
		                 JHTML::_('image', $iconspath.$elsettings->signup_email_img,
		                 JText::_($elsettings->signup_email_text),
				             'width="24px" height="24px"'))
		      .'</span> ';
		return $text;
	}
	
	function _getTag_formalsignup()
	{
		$registration_status = $this->_canRegister();
		if (!$registration_status->canregister)
		{
			$img = JHTML::_('image', JURI::root() . 'components/com_redevent/assets/images/agt_action_fail.png', 
		                          $registration_status->status, 
		                          array('class' => 'hasTip', 'title' => $registration_status->status));
		  return $img;
		}
		$mainframe = &JFactory::getApplication();
		$base_url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
		$iconspath = $base_url.'administrator/components/com_redevent/assets/images/';
		$elsettings = redEVENTHelper::config();
		$text = '<span class="vlink formaloffer">'
		      . JHTML::_('link',
		                 $this->absoluteUrls(RedeventHelperRoute::getSignupRoute('formaloffer', $this->getEvent()->getData()->slug, $this->getEvent()->getData()->xslug)),
		                 JHTML::_('image', $iconspath.$elsettings->signup_formal_offer_img,
		                 JText::_($elsettings->signup_formal_offer_text),
		                 'width="24px" height="24px"'))
		       .'</span> ';
		return $text;
	}
	
	function _getTag_externalsignup()
	{
		$registration_status = $this->_canRegister();
		if (!$registration_status->canregister)
		{
			$img = JHTML::_('image', JURI::root() . 'components/com_redevent/assets/images/agt_action_fail.png', 
		                          $registration_status->status, 
		                          array('class' => 'hasTip', 'title' => $registration_status->status));
		  return $img;
		}
		$mainframe = &JFactory::getApplication();
		$base_url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
		$iconspath = $base_url.'administrator/components/com_redevent/assets/images/';
		$elsettings = redEVENTHelper::config();
		if (!empty($this->getEvent()->getData()->external_registration_url)) {
			$link = $this->getEvent()->getData()->external_registration_url;
		}
		else {
			$link = $this->getEvent()->getData()->submission_type_external;
		}
		$text = '<span class="vlink external">'
		      . JHTML::_('link',
		                 $link,
		                 JHTML::_('image', $iconspath.$elsettings->signup_external_img,
		                 $elsettings->signup_external_text),
				             'target="_blank"')
		       .'</span> ';
		return $text;
	}
	
	function _getTag_phonesignup()
	{
		$registration_status = $this->_canRegister();
		if (!$registration_status->canregister)
		{
			$img = JHTML::_('image', JURI::root() . 'components/com_redevent/assets/images/agt_action_fail.png', 
		                          $registration_status->status, 
		                          array('class' => 'hasTip', 'title' => $registration_status->status));
		  return $img;
		}
		$mainframe = &JFactory::getApplication();
		$base_url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
		$iconspath = $base_url.'administrator/components/com_redevent/assets/images/';
		$elsettings = redEVENTHelper::config();
		$text = '<span class="vlink phone">'
		      . JHTML::_('link',
		                 $this->absoluteUrls(RedeventHelperRoute::getSignupRoute('phone', $this->getEvent()->getData()->slug, $this->getEvent()->getData()->xslug)),
		                 JHTML::_('image', $iconspath.$elsettings->signup_phone_img,
		                 JText::_($elsettings->signup_phone_text),
		                 'width="24px" height="24px"'))
		      .'</span> ';
		return $text;
	}
	
	function _getTag_webformsignuppage()
	{
		$registration_status = $this->_canRegister();
		if (!$registration_status->canregister)
		{
			$img = JHTML::_('image', JURI::root() . 'components/com_redevent/assets/images/agt_action_fail.png', 
		                          $registration_status->status, 
		                          array('class' => 'hasTip', 'title' => $registration_status->status));
		  return $img;
		}
		// check that there is no loop with the tag inclusion
		if (preg_match('/\[[a-z]*signuppage\]/', $this->getEvent()->getData()->submission_type_webform) == 0) {
			$text = $this->ReplaceTags($this->getEvent()->getData()->submission_type_webform);
		}
		else {
			JError::raiseNotice(0, JText::_('COM_REDEVENT_ERROR_TAG_LOOP_XXXXSIGNUPPAGE'));
			$text = '';
		}
		return $text;
	}
	
	function _getTag_formalsignuppage()
	{
		$registration_status = $this->_canRegister();
		if (!$registration_status->canregister)
		{
			$img = JHTML::_('image', JURI::root() . 'components/com_redevent/assets/images/agt_action_fail.png', 
		                          $registration_status->status, 
		                          array('class' => 'hasTip', 'title' => $registration_status->status));
		  return $img;
		}
		// check that there is no loop with the tag inclusion
		if (preg_match('/\[[a-z]*signuppage\]/', $this->getEvent()->getData()->submission_type_formal_offer) == 0) {
			$text = $this->_getFormalOffer($this->getEvent()->getData());
		}
		else {
			JError::raiseNotice(0, JText::_('COM_REDEVENT_ERROR_TAG_LOOP_XXXXSIGNUPPAGE'));
			$text = '';
		}
		return $text;
	}
	
	function _getTag_phonesignuppage()
	{
		$registration_status = $this->_canRegister();
		if (!$registration_status->canregister)
		{
			$img = JHTML::_('image', JURI::root() . 'components/com_redevent/assets/images/agt_action_fail.png', 
		                          $registration_status->status, 
		                          array('class' => 'hasTip', 'title' => $registration_status->status));
		  return $img;
		}
		// check that there is no loop with the tag inclusion
		if (preg_match('/\[[a-z]*signuppage\]/', $this->getEvent()->getData()->submission_type_phone) == 0) {
			$text = $this->ReplaceTags($this->getEvent()->getData()->submission_type_phone);
		}
		else {
			JError::raiseNotice(0, JText::_('COM_REDEVENT_ERROR_TAG_LOOP_XXXXSIGNUPPAGE'));
			$text = '';
		}
		return $text;
	}
	
	function _getTag_emailsignuppage()
	{
		$registration_status = $this->_canRegister();
		if (!$registration_status->canregister)
		{
			$img = JHTML::_('image', JURI::root() . 'components/com_redevent/assets/images/agt_action_fail.png', 
		                          $registration_status->status, 
		                          array('class' => 'hasTip', 'title' => $registration_status->status));
		  return $img;
		}
		// check that there is no loop with the tag inclusion
		if (preg_match('/\[[a-z]*signuppage\]/', $this->getEvent()->getData()->submission_type_email) == 0) {
			$text = $this->_getEmailSubmission($this->getEvent()->getData());
		}
		else {
			JError::raiseNotice(0, JText::_('COM_REDEVENT_ERROR_TAG_LOOP_XXXXSIGNUPPAGE'));
			$text = '';
		}
		return $text;
	}
	
	function _getTag_paymentrequest()
	{
		$text = '';
		$link = $this->_getTag_paymentrequestlink();
		if (!empty($link)) {
			$text = JHTML::link($link, JText::_('COM_REDEVENT_Checkout'), '');
		}
		return $text;
	}
	
	function _getTag_paymentrequestlink()
	{
		$link = '';
		if (!empty($this->_submitkey)) 
		{
			$title = urlencode($this->getEvent()->getData()->title
			               .' '
			               .ELOutput::formatdate($this->getEvent()->getData()->dates, 
			                                     $this->getEvent()->getData()->times));
			$link = $this->absoluteUrls(
			             'index.php?option=com_redform&controller=payment&task=select&source=redevent&key='
			                .$this->_submitkey.'&paymenttitle='.$title, 
			             false);
		}
		return $link;
	}
		
	/**
	 * Parses registrationid tag
	 * returns unique registration id
	 * @return string
	 */
	function _getTag_registrationid()
	{
		$text = '';
		if (!empty($this->_submitkey)) {
			$text = $this->getAttendeeUniqueId($this->_submitkey);
		}
		return $text;
	}
	
	/**
	 * Parses total_price tag
	 * total price for registration, including redform fields
	 * @return string
	 */
	function _getTag_total_price()
	{
		return $this->_getSubmissionTotalPrice();
	}
}

class RedeventParsedTag {
	
	/**
	 * full tag, including delimiters and parameters
	 * 
	 * @var string
	 */
	protected $full_tag;
	/**
	 * tag name
	 * 
	 * @var string
	 */
	protected $tag;
	/**
	 * array of parameters
	 * 
	 * @var array
	 */
	protected $params = array();
	
	/**
	 * constructor
	 * 
	 * @param string $full_tag full tag, including delimiters and parameters
	 * @throws Exception
	 */
	public function __construct($full_tag)
	{
		$this->full_tag = $full_tag;
		
		if (!preg_match('/\[([^\]\s]+)([^\]]*)\]/u', $this->full_tag, $matches)) {
			throw new Exception(JText::_('COM_REDEVENT_TAGS_WRONG_TAG_SYNTAX').':'. $this->full_tag);
		}
		$this->tag = trim($matches[1]);
		
		if (count($matches) > 2)
		{
			preg_match_all('/([^=\s]+)="([^"]*)"/u', $matches[2], $match_params_array, PREG_SET_ORDER);
			foreach ($match_params_array as $m) 
			{
				$property = strtolower($m[1]);
				$this->params[$property] = $m[2];
			}		
		}
	}

	/**
	 * returns full tag text, including delimiters and parameters
	 * @return string
	 */
	public function getFull()
	{
		return $this->full_tag;
	}
	
	/**
	 * returns tag name
	 * @return string
	 */
	public function getName()
	{
		return $this->tag;
	}
	
	/**
	 * return tag paramter value
	 * 
	 * @param string $name parameter name
	 * @param mixed $default default value if tag not founc
	 * @return mixed value
	 */
	public function getParam($name, $default = null)
	{
		if (isset($this->params[$name])) {
			return $this->params[$name];
		}
		else {
			return $default;
		}
	}
	
}
?>