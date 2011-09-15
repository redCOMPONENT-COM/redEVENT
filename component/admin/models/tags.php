<?php
/**
 * @version 2.0
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
 * redEVENT Component tags Model
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		2.0
 */
class RedEventModelTags extends JModel
{	
	var $field = null;
	
	/**
	 * Constructor
	 *
	 * @since 2.0
	 */
	function __construct()
	{
		parent::__construct();

		$this->field = JRequest::getVar('field',  null, '', 'string');
	}
	
	function getData()
	{
		$tags = array_merge($this->_getStandardTags(), $this->_getLibraryTags(), $this->_getCustomTags());		
		
		return $this->_tagsBySection($tags);
	}
	
	function _getStandardTags()
	{
		// tags
		$tags = array();	
		$tags[] = new TagsModelTag('event_title', JText::_('SUBMISSION_EVENT_TITLE'));
		$tags[] = new TagsModelTag('event_full_title', JText::_('COM_REDEVENT_TAG_DESC_SUBMISSION_EVENT_FULLTITLE'));
		$tags[] = new TagsModelTag('code', JText::_('SUBMISSION_EVENT_CODE'));
		$tags[] = new TagsModelTag('category', JText::_('SUBMISSION_CATEGORY'));
		$tags[] = new TagsModelTag('date', JText::_('SUBMISSION_EVENT_DATE'));
		$tags[] = new TagsModelTag('enddate', JText::_('SUBMISSION_EVENT_ENDDATE'));
		$tags[] = new TagsModelTag('time', JText::_('SUBMISSION_EVENT_TIME'));
		$tags[] = new TagsModelTag('startenddatetime', JText::_('SUBMISSION_EVENT_STARTENDDATETIME'));
		$tags[] = new TagsModelTag('duration', JText::_('SUBMISSION_EVENT_DURATION'));
		$tags[] = new TagsModelTag('venues', JText::_('SUBMISSION_VENUES'));
		$tags[] = new TagsModelTag('price', JText::_('SUBMISSION_EVENT_PRICE'));
		$tags[] = new TagsModelTag('credits', JText::_('SUBMISSION_EVENT_CREDITS'));
		$tags[] = new TagsModelTag('event_image', JText::_('SUBMISSION_EVENT_IMAGE'));
		$tags[] = new TagsModelTag('event_thumb', JText::_('REDEVENT_SUBMISSION_TAG_EVENT_THUMB'));
		$tags[] = new TagsModelTag('category_image', JText::_('SUBMISSION_CATEGORY_IMAGE'));
		$tags[] = new TagsModelTag('category_thumb', JText::_('REDEVENT_SUBMISSION_TAG_CATEGORY_THUMB'));
		$tags[] = new TagsModelTag('eventcomments', JText::_('SUBMISSION_EVENT_COMMENTS'));
		$tags[] = new TagsModelTag('info', JText::_('SUBMISSION_XREF_INFO'));
		$tags[] = new TagsModelTag('permanentlink', JText::_('SUBMISSION_PERMANENT_LINK'));
		$tags[] = new TagsModelTag('datelink', JText::_('SUBMISSION_DATE_LINK'));
		$tags[] = new TagsModelTag('ical', JText::_('COM_REDEVENT_TAG_ICAL'));
		$tags[] = new TagsModelTag('ical_url', JText::_('COM_REDEVENT_TAG_ICAL_URL'));
		$tags[] = new TagsModelTag('summary', JText::_('COM_REDEVENT_TAG_SUMMARY'));
		$tags[] = new TagsModelTag('attachments', JText::_('COM_REDEVENT_TAG_ATTACHMENTS'));
		$tags[] = new TagsModelTag('author_name', JText::_('COM_REDEVENT_TAG_AUTHOR_NAME'));
		$tags[] = new TagsModelTag('author_email', JText::_('COM_REDEVENT_TAG_AUTHOR_EMAIL'));
		
		$tags[] = new TagsModelTag('venue_title', JText::_('SUBMISSION_EVENT_VENUE'), 'venue');
		$tags[] = new TagsModelTag('venue_link', JText::_('SUBMISSION_EVENT_VENUELINK'), 'venue');
		$tags[] = new TagsModelTag('venue_city', JText::_('SUBMISSION_EVENT_CITY'), 'venue');
		$tags[] = new TagsModelTag('venue_street', JText::_('SUBMISSION_EVENT_STREET'), 'venue');
		$tags[] = new TagsModelTag('venue_zip', JText::_('SUBMISSION_EVENT_ZIP'), 'venue');
		$tags[] = new TagsModelTag('venue_state', JText::_('SUBMISSION_EVENT_STATE'), 'venue');
		$tags[] = new TagsModelTag('venue_website', JText::_('SUBMISSION_EVENT_VENUE_WEBSITE'), 'venue');
		$tags[] = new TagsModelTag('venue_image', JText::_('SUBMISSION_VENUE_IMAGE'), 'venue');
		$tags[] = new TagsModelTag('venue_thumb', JText::_('REDEVENT_SUBMISSION_TAG_VENUE_THUMB'), 'venue');
		$tags[] = new TagsModelTag('venue_description', JText::_('REDEVENT_TAGS_VENUE_DESCRIPTION_DESC'), 'venue');
		$tags[] = new TagsModelTag('venue_country', JText::_('REDEVENT_TAGS_VENUE_COUNTRY_DESC'), 'venue');
		$tags[] = new TagsModelTag('venue_countryflag', JText::_('REDEVENT_TAGS_VENUE_COUNTRYFLAG_DESC'), 'venue');
		$tags[] = new TagsModelTag('venue_mapicon', JText::_('REDEVENT_TAGS_VENUE_MAPICON_DESC'), 'venue');
		$tags[] = new TagsModelTag('venue_map', JText::_('REDEVENT_TAGS_VENUE_MAP_DESC'), 'venue');
		
		$tags[] = new TagsModelTag('redform', JText::_('SUBMISSION_EVENT_REDFORM'), 'registration');
		$tags[] = new TagsModelTag('redform_title', JText::_('SUBMISSION_EVENT_REDFORM_TITLE'), 'registration');
		$tags[] = new TagsModelTag('answers', JText::_('REDEVENT_SUBMISSION_TAG_ANSWERS_DESC'), 'registration');
		$tags[] = new TagsModelTag('activatelink', JText::_('REDEVENT_SUBMISSION_TAG_ACTIVATELINK_DESC'), 'registration');
		$tags[] = new TagsModelTag('cancellink', JText::_('COM_REDEVENT_TAG_SUBMISSION_CANCELLINK_DESC'), 'registration');
		$tags[] = new TagsModelTag('registrationend', JText::_('SUBMISSION_EVENT_REGISTRATIONEND'), 'registration');
		$tags[] = new TagsModelTag('webformsignup', JText::_('SUBMISSION_WEBFORM_SIGNUP_LINK'), 'registration');
		$tags[] = new TagsModelTag('emailsignup', JText::_('SUBMISSION_EMAIL_SIGNUP_LINK'), 'registration');
		$tags[] = new TagsModelTag('formalsignup', JText::_('SUBMISSION_FORMAL_SIGNUP_LINK'), 'registration');
		$tags[] = new TagsModelTag('externalsignup', JText::_('SUBMISSION_EXTERNAL_SIGNUP_LINK'), 'registration');
		$tags[] = new TagsModelTag('phonesignup', JText::_('SUBMISSION_PHONE_SIGNUP_LINK'), 'registration');
		$tags[] = new TagsModelTag('webformsignuppage', JText::_('SUBMISSION_WEBFORM_SIGNUP_PAGE'), 'registration');
		$tags[] = new TagsModelTag('emailsignuppage', JText::_('SUBMISSION_EMAIL_SIGNUP_PAGE'), 'registration');
		$tags[] = new TagsModelTag('formalsignuppage', JText::_('SUBMISSION_FORMAL_SIGNUP_PAGE'), 'registration');
		$tags[] = new TagsModelTag('phonesignuppage', JText::_('SUBMISSION_PHONE_SIGNUP_PAGE'), 'registration');
		$tags[] = new TagsModelTag('eventplaces', JText::_('SUBMISSION_EVENTPLACES'), 'registration');
		$tags[] = new TagsModelTag('waitinglistplaces', JText::_('SUBMISSION_WAITINGLISTPLACES'), 'registration');
		$tags[] = new TagsModelTag('eventplacesleft', JText::_('SUBMISSION_EVENTPLACES_LEFT'), 'registration');
		$tags[] = new TagsModelTag('waitinglistplacesleft', JText::_('SUBMISSION_WAITINGLISTPLACES_LEFT'), 'registration');
		$tags[] = new TagsModelTag('inputname', JText::_('REDEVENT_SUBMISSION_TAG_INPUTENAME_DESC'), 'registration');
		$tags[] = new TagsModelTag('inputemail', JText::_('REDEVENT_SUBMISSION_TAG_INPUTEMAIL_DESC'), 'registration');
		$tags[] = new TagsModelTag('submit', JText::_('REDEVENT_SUBMISSION_TAG_SUBMIT_DESC'), 'registration');
		$tags[] = new TagsModelTag('userfullname', JText::_('REDEVENT_SUBMISSION_TAG_FULLNAME_DESC'), 'registration');
		$tags[] = new TagsModelTag('username', JText::_('REDEVENT_SUBMISSION_TAG_USERNAME_DESC'), 'registration');
		$tags[] = new TagsModelTag('useremail', JText::_('REDEVENT_SUBMISSION_TAG_USEREMAIL_DESC'), 'registration');
		$tags[] = new TagsModelTag('answer_<field id>', JText::_('REDEVENT_SUBMISSION_TAG_REDFORM_FIELD_DESC'), 'registration');
		
		$tags[] = new TagsModelTag('paymentrequest', JText::_('SUBMISSION_EVENT_PAYMENTREQUEST'), 'payment');
		$tags[] = new TagsModelTag('paymentrequestlink', JText::_('SUBMISSION_EVENT_PAYMENTREQUESTLINK'), 'payment');
		$tags[] = new TagsModelTag('registrationid', JText::_('SUBMISSION_EVENT_REGISTRATIONID'), 'payment');
		$tags[] = new TagsModelTag('total_price', JText::_('REDEVENT_SUBMISSION_TAG_REDFORM_TOTAL_PRICE_DESC'), 'payment');
		return $tags;
	}
	
	function _getLibraryTags()
	{
		$query = ' SELECT id, text_description, text_name FROM #__redevent_textlibrary ';
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		
		$tags = array();
		foreach ((array) $res as $r)
		{
			$tags[] = new TagsModelTag($r->text_name, $r->text_description, 'library', $r->id);			
		}
		return $tags;
	}
	
	function _getCustomTags()
	{
		$query = ' SELECT id, tag, name FROM #__redevent_fields ORDER BY ordering ';
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		
		$tags = array();
		foreach ((array) $res as $r)
		{
			$tags[] = new TagsModelTag($r->tag, $r->name, 'custom', $r->id);			
		}
		return $tags;
	}
	
	function _tagsBySection($tags)
	{
		$res = array();
		foreach ($tags as $tag)
		{
			@$res[$tag->section][] = $tag;
		}
		return $res;
	}
}

class TagsModelTag {
	var $name;
	var $description;
	var $section;
	var $id = 0; // for custom and text library
	
	function __construct($name, $desc, $section = 'General', $id = 0)
	{
		$name = trim($name);
		$this->name        = JFilterOutput::cleanText($name);
		$this->description = trim($desc);
		$this->section     = trim($section);
		$this->id          = $id;
		return $this;
	}
}