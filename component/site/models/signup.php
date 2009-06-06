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

jimport('joomla.application.component.model');

/**
 * EventList Component Details Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedeventModelSignup extends JModel
{
	/**
	 * Details data in details array
	 *
	 * @var array
	 */
	var $_details = null;


	/**
	 * registeres in array
	 *
	 * @var array
	 */
	var $_registers = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$id = JRequest::getInt('id');
		$this->setId($id);
		$xref = JRequest::getInt('xref');
		$this->setXref($xref);
	}

	/**
	 * Method to set the details id
	 *
	 * @access	public
	 * @param	int	details ID number
	 */

	function setId($id)
	{
		// Set new details ID and wipe data
		$this->_id			= $id;
	}
	
	function setXref($xref)
	{
		// Set new details ID and wipe data
		$this->_xref			= $xref;
	}

	/**
	 * Method to get event data for the Detailsview
	 *
	 * @access public
	 * @return array
	 * @since 0.9
	 */
	function getDetails( )
	{
		/*
		 * Load the Category data
		 */
		if ($this->_loadDetails())
		{
			$user	= & JFactory::getUser();
		
      // Is the category published?
      if (!count($this->_details->categories))
      {
        JError::raiseError( 404, JText::_("CATEGORY NOT PUBLISHED") );
      }

      // Do we have access to each category ?
      foreach ($this->_details->categories as $cat)
      {
        if ($cat->access > $user->get('aid'))
        {
          JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
        }
      }

		}

		return $this->_details;
	}
 
	/**
	 * Method to load required data
	 *
	 * @access	private
	 * @return	array
	 * @since	0.9
	 */
	function _loadDetails()
	{
		if (empty($this->_details))
		{
			// Get the WHERE clause
			$where	= $this->_buildDetailsWhere();

			$query = 'SELECT a.id AS did, x.dates, x.enddates, a.title, x.times, x.endtimes, a.datdescription, a.meta_keywords, a.meta_description, a.datimage, a.registra, a.unregistra,' 
					. ' a.created_by, a.redform_id, x.maxwaitinglist, x.maxattendees, a.juser, a.show_names, a.showfields, a.show_attendants, a.show_waitinglist, '
					. ' a.max_multi_signup, a.confirmation_message, x.course_price, x.course_credit, a.course_code, c.catname, c.published, c.access, a.submission_type_phone,'
					. ' a.submission_type_webform, a.submission_type_formal_offer, a.submission_type_email, v.venue, v.city AS location, '
					. ' a.submission_type_email_pdf, a.submission_type_formal_offer_pdf, a.send_pdf_form, a.pdf_form_data, '
	        . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
	        . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug '
					. ' FROM #__redevent_events AS a'
					. ' LEFT JOIN #__redevent_event_venue_xref AS x ON a.id = x.eventid'
					. ' LEFT JOIN #__redevent_venues AS v ON x.venueid = v.id'
					. ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
          . ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
					. $where
					;
    		$this->_db->setQuery($query);
			$this->_details = $this->_db->loadObject();
						
      if ($this->_details->did) {
        $query =  ' SELECT c.id, c.catname, c.access, '
              . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug '
              . ' FROM #__redevent_categories as c '
              . ' INNER JOIN #__redevent_event_category_xref as x ON x.category_id = c.id '
              . ' WHERE c.published = 1 '
              . '   AND x.event_id = ' . $this->_db->Quote($this->_details->did)
              . ' ORDER BY c.ordering'
              ;
        $this->_db->setQuery( $query );
  
        $this->_details->categories = $this->_db->loadObjectList();
      }
			return (boolean) $this->_details;			
		}
		return true;
	}

	/**
	 * Method to build the WHERE clause of the query to select the details
	 *
	 * @access	private
	 * @return	string	WHERE clause
	 * @since	0.9
	 */
	function _buildDetailsWhere()
	{
		$where = ' WHERE x.id = '.$this->_xref;
		
		return $where;
	}

   /**
    * Initialise the mailer object to start sending mails
    */
    private function Mailer() {
       global $mainframe;
      jimport('joomla.mail.helper');
      /* Start the mailer object */
      $this->mailer = &JFactory::getMailer();
      $this->mailer->isHTML(true);
      $this->mailer->From = $mainframe->getCfg('mailfrom');
      $this->mailer->FromName = $mainframe->getCfg('sitename');
      $this->mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
    }
	
	/**
	 * Get the details of a venue
	 */
	public function getVenue() {
		$db = JFactory::getDBO();
		$q = "SELECT *
			FROM #__redevent_venues v
			LEFT JOIN #__redevent_event_venue_xref x
			ON v.id = x.venueid
			WHERE x.id = ".JRequest::getInt('xref');
		$db->setQuery($q);
		return $db->loadObject();
	}
	
	/**
	 * Send the signup email
	 * object $tags
	 * boolean $send_attachment
	 */
	public function getSendSignupEmail($tags, $send_attachment) {
		/* Initialise the mailer */
		$this->Mailer();
		
		/* Check if the attachment needs to be send */
		if ($send_attachment) {
			$pdf = file_get_contents(JURI::root().'index.php?option=com_redevent&view=signup&task=createpdfemail&subtype=email&xref='.JRequest::getInt('xref').'&id='.JRequest::getInt('id').'&format=raw');
			$pdffile = JPATH_CACHE.DS.'signup.pdf';
			file_put_contents($pdffile, $pdf);
			$this->mailer->AddAttachment($pdffile);
		}
		/* Add the recipient */
		$this->mailer->AddAddress(JRequest::getVar('subemailaddress'), JRequest::getVar('subemailname'));
		
		/* Add the body to the mail */
		/* Read the template */
		$db = JFactory::getDBO();
		$q = "SELECT submission_type_email_body, submission_type_email_subject FROM #__redevent_events WHERE id = ".JRequest::getInt('id');
		$db->setQuery($q);
		$email_settings = $db->loadObject();
		$message = $tags->ReplaceTags($email_settings->submission_type_email_body);
		$this->mailer->setBody($message);
		
		/* Set the subject */
		$this->mailer->setSubject($tags->ReplaceTags($email_settings->submission_type_email_subject));
		
		/* Sent out the mail */
		if (!$this->mailer->Send()) {
			JError::raiseWarning(0, JText::_('NO_MAIL_SEND').' '.$this->mailer->error);
			return false;
		}
		/* Clear the mail details */
		$this->mailer->ClearAddresses();
		
		/* Remove the temporary file */
		if ($send_attachment) {
			unlink($pdffile);
		}
		return true;
	}
	
	/**
	 * Send the signup email
	 */
	public function getSendFormalOfferEmail($tags) {
		/* Initialise the mailer */
		$this->Mailer();
		
		/* Load the details for this course */
		$db = JFactory::getDBO();
		$q = "SELECT * 
			FROM #__redevent_event_venue_xref x
			LEFT JOIN #__redevent_events e
			ON e.id = x.eventid
			LEFT JOIN #__redevent_venues v
			ON v.id = x.venueid
			WHERE x.id = ".JRequest::getInt('xref');
		$db->setQuery($q);
		$details = $db->loadObject();
		
		/* Add the recipient */
		$this->mailer->AddAddress(JRequest::getVar('subemailaddress'), JRequest::getVar('subemailname'));
		
		/* Set the subject */
		$this->mailer->setSubject($tags->ReplaceTags($details->submission_type_formal_offer_subject));
		
		/* Add the body to the mail */
		/* Read the template */
		$message = $tags->ReplaceTags($details->submission_type_formal_offer_body);
		$this->mailer->setBody($message);
		
		/* Sent out the mail */
		if (!$this->mailer->Send()) {
			JError::raiseWarning(0, JText::_('NO_MAIL_SEND').' '.$this->mailer->error);
			return false;
		}
		
		/* Clear the mail details */
		$this->mailer->ClearAddresses();
		
		return true;
	}
}
?>