<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component tags Model
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventModelTags extends RModel
{
	private $field = null;

	/**
	 * Constructor
	 *
	 * @since 2.0
	 */
	public function __construct()
	{
		parent::__construct();

		$this->field = JFactory::getApplication()->input->getString('field', '');
	}

	/**
	 * Get items
	 *
	 * @return array
	 */
	public function getItems()
	{
		$tags = array_merge($this->getStandardTags(), $this->getLibraryTags(), $this->getCustomTags());

		JPluginHelper::importPlugin('redevent');
		$dispatcher = RFactory::getDispatcher();
		$dispatcher->trigger('onRedeventGetAvailableTags', array(&$tags));

		$this->addTagsClasses($tags);

		return $this->tagsBySection($tags);
	}

	/**
	 * Core tags
	 *
	 * @return array
	 */
	private function getStandardTags()
	{
		$tags = array();
		$tags[] = new RedeventTagsTag('event_title', JText::_('COM_REDEVENT_SUBMISSION_EVENT_TITLE'));
		$tags[] = new RedeventTagsTag('event_full_title', JText::_('COM_REDEVENT_TAG_DESC_SUBMISSION_EVENT_FULLTITLE'));
		$tags[] = new RedeventTagsTag('code', JText::_('COM_REDEVENT_SUBMISSION_EVENT_CODE'));
		$tags[] = new RedeventTagsTag('session_code', JText::_('COM_REDEVENT_SESSION_CODE'));
		$tags[] = new RedeventTagsTag('category', JText::_('COM_REDEVENT_SUBMISSION_CATEGORY'));
		$tags[] = new RedeventTagsTag('date', JText::_('COM_REDEVENT_SUBMISSION_EVENT_DATE'));
		$tags[] = new RedeventTagsTag('enddate', JText::_('COM_REDEVENT_SUBMISSION_EVENT_ENDDATE'));
		$tags[] = new RedeventTagsTag('time', JText::_('COM_REDEVENT_SUBMISSION_EVENT_TIME'));
		$tags[] = new RedeventTagsTag('starttime', JText::_('COM_REDEVENT_SUBMISSION_EVENT_STARTTIME'));
		$tags[] = new RedeventTagsTag('endtime', JText::_('COM_REDEVENT_SUBMISSION_EVENT_ENDTIME'));
		$tags[] = new RedeventTagsTag('startenddatetime', JText::_('COM_REDEVENT_SUBMISSION_EVENT_STARTENDDATETIME'));
		$tags[] = new RedeventTagsTag('duration', JText::_('COM_REDEVENT_SUBMISSION_EVENT_DURATION'));
		$tags[] = new RedeventTagsTag('venues', JText::_('COM_REDEVENT_SUBMISSION_VENUES'));
		$tags[] = new RedeventTagsTag('price', JText::_('COM_REDEVENT_SUBMISSION_EVENT_PRICE'));
		$tags[] = new RedeventTagsTag('credits', JText::_('COM_REDEVENT_SUBMISSION_EVENT_CREDITS'));
		$tags[] = new RedeventTagsTag('event_image', JText::_('COM_REDEVENT_SUBMISSION_EVENT_IMAGE'));
		$tags[] = new RedeventTagsTag('event_thumb', JText::_('COM_REDEVENT_SUBMISSION_TAG_EVENT_THUMB'));
		$tags[] = new RedeventTagsTag('category_image', JText::_('COM_REDEVENT_SUBMISSION_CATEGORY_IMAGE'));
		$tags[] = new RedeventTagsTag('category_thumb', JText::_('COM_REDEVENT_SUBMISSION_TAG_CATEGORY_THUMB'));
		$tags[] = new RedeventTagsTag('eventcomments', JText::_('COM_REDEVENT_SUBMISSION_EVENT_COMMENTS'));
		$tags[] = new RedeventTagsTag('info', JText::_('COM_REDEVENT_SUBMISSION_XREF_INFO'));
		$tags[] = new RedeventTagsTag('permanentlink', JText::_('COM_REDEVENT_SUBMISSION_PERMANENT_LINK'));
		$tags[] = new RedeventTagsTag('datelink', JText::_('COM_REDEVENT_SUBMISSION_DATE_LINK'));
		$tags[] = new RedeventTagsTag('ical', JText::_('COM_REDEVENT_TAG_ICAL'));
		$tags[] = new RedeventTagsTag('ical_url', JText::_('COM_REDEVENT_TAG_ICAL_URL'));
		$tags[] = new RedeventTagsTag('summary', JText::_('COM_REDEVENT_TAG_SUMMARY'));
		$tags[] = new RedeventTagsTag('attachments', JText::_('COM_REDEVENT_TAG_ATTACHMENTS'));
		$tags[] = new RedeventTagsTag('author_name', JText::_('COM_REDEVENT_TAG_AUTHOR_NAME'));
		$tags[] = new RedeventTagsTag('author_email', JText::_('COM_REDEVENT_TAG_AUTHOR_EMAIL'));
		$tags[] = new RedeventTagsTag('event_created', JText::_('COM_REDEVENT_TAG_EVENT_CREATED_DESC'));
		$tags[] = new RedeventTagsTag('event_modified', JText::_('COM_REDEVENT_TAG_EVENT_MODIFIED_DESC'));
		$tags[] = new RedeventTagsTag('session_details', JText::_('COM_REDEVENT_TAG_SESSION_DETAILS_DESC'));
		$tags[] = new RedeventTagsTag('session_created', JText::_('COM_REDEVENT_TAG_SESSION_CREATED_DESC'));
		$tags[] = new RedeventTagsTag('session_modified', JText::_('COM_REDEVENT_TAG_SESSION_MODIFIED_DESC'));

		$tags[] = new RedeventTagsTag('venue_title', JText::_('COM_REDEVENT_SUBMISSION_EVENT_VENUE'), 'venue');
		$tags[] = new RedeventTagsTag('venue_code', JText::_('COM_REDEVENT_VENUE_CODE'), 'venue');
		$tags[] = new RedeventTagsTag('venue_link', JText::_('COM_REDEVENT_SUBMISSION_EVENT_VENUELINK'), 'venue');
		$tags[] = new RedeventTagsTag('venue_company', JText::_('COM_REDEVENT_TAGS_VENUE_COMPANY_DESC'), 'venue');
		$tags[] = new RedeventTagsTag('venue_city', JText::_('COM_REDEVENT_SUBMISSION_EVENT_CITY'), 'venue');
		$tags[] = new RedeventTagsTag('venue_street', JText::_('COM_REDEVENT_SUBMISSION_EVENT_STREET'), 'venue');
		$tags[] = new RedeventTagsTag('venue_zip', JText::_('COM_REDEVENT_SUBMISSION_EVENT_ZIP'), 'venue');
		$tags[] = new RedeventTagsTag('venue_state', JText::_('COM_REDEVENT_SUBMISSION_EVENT_STATE'), 'venue');
		$tags[] = new RedeventTagsTag('venue_website', JText::_('COM_REDEVENT_SUBMISSION_EVENT_VENUE_WEBSITE'), 'venue');
		$tags[] = new RedeventTagsTag('venue_image', JText::_('COM_REDEVENT_SUBMISSION_VENUE_IMAGE'), 'venue');
		$tags[] = new RedeventTagsTag('venue_thumb', JText::_('COM_REDEVENT_SUBMISSION_TAG_VENUE_THUMB'), 'venue');
		$tags[] = new RedeventTagsTag('venue_description', JText::_('COM_REDEVENT_TAGS_VENUE_DESCRIPTION_DESC'), 'venue');
		$tags[] = new RedeventTagsTag('venue_country', JText::_('COM_REDEVENT_TAGS_VENUE_COUNTRY_DESC'), 'venue');
		$tags[] = new RedeventTagsTag('venue_countryflag', JText::_('COM_REDEVENT_TAGS_VENUE_COUNTRYFLAG_DESC'), 'venue');
		$tags[] = new RedeventTagsTag('venue_mapicon', JText::_('COM_REDEVENT_TAGS_VENUE_MAPICON_DESC'), 'venue');
		$tags[] = new RedeventTagsTag('venue_map', JText::_('COM_REDEVENT_TAGS_VENUE_MAP_DESC'), 'venue');
		$tags[] = new RedeventTagsTag('latlong', JText::_('COM_REDEVENT_TAGS_VENUE_LATLONG_DESC'), 'venue');

		$tags[] = new RedeventTagsTag('redform', JText::_('COM_REDEVENT_SUBMISSION_EVENT_REDFORM'), 'registration');
		$tags[] = new RedeventTagsTag('redform_title', JText::_('COM_REDEVENT_SUBMISSION_EVENT_REDFORM_TITLE'), 'registration');
		$tags[] = new RedeventTagsTag('answers', JText::_('COM_REDEVENT_SUBMISSION_TAG_ANSWERS_DESC'), 'registration');
		$tags[] = new RedeventTagsTag('activatelink', JText::_('COM_REDEVENT_SUBMISSION_TAG_ACTIVATELINK_DESC'), 'registration');
		$tags[] = new RedeventTagsTag('cancellink', JText::_('COM_REDEVENT_TAG_SUBMISSION_CANCELLINK_DESC'), 'registration');
		$tags[] = new RedeventTagsTag('registrationend', JText::_('COM_REDEVENT_SUBMISSION_EVENT_REGISTRATIONEND'), 'registration');
		$tags[] = new RedeventTagsTag('webformsignup', JText::_('COM_REDEVENT_SUBMISSION_WEBFORM_SIGNUP_LINK'), 'registration');
		$tags[] = new RedeventTagsTag('emailsignup', JText::_('COM_REDEVENT_SUBMISSION_EMAIL_SIGNUP_LINK'), 'registration');
		$tags[] = new RedeventTagsTag('formalsignup', JText::_('COM_REDEVENT_SUBMISSION_FORMAL_SIGNUP_LINK'), 'registration');
		$tags[] = new RedeventTagsTag('externalsignup', JText::_('COM_REDEVENT_SUBMISSION_EXTERNAL_SIGNUP_LINK'), 'registration');
		$tags[] = new RedeventTagsTag('external_registration_url', JText::_('COM_REDEVENT_TAG_EXTERNAL_REGISTRATION_URL'), 'registration');
		$tags[] = new RedeventTagsTag('phonesignup', JText::_('COM_REDEVENT_SUBMISSION_PHONE_SIGNUP_LINK'), 'registration');
		$tags[] = new RedeventTagsTag('webformsignuppage', JText::_('COM_REDEVENT_SUBMISSION_WEBFORM_SIGNUP_PAGE'), 'registration');
		$tags[] = new RedeventTagsTag('emailsignuppage', JText::_('COM_REDEVENT_SUBMISSION_EMAIL_SIGNUP_PAGE'), 'registration');
		$tags[] = new RedeventTagsTag('formalsignuppage', JText::_('COM_REDEVENT_SUBMISSION_FORMAL_SIGNUP_PAGE'), 'registration');
		$tags[] = new RedeventTagsTag('phonesignuppage', JText::_('COM_REDEVENT_SUBMISSION_PHONE_SIGNUP_PAGE'), 'registration');
		$tags[] = new RedeventTagsTag('attending', JText::_('COM_REDEVENT_SUBMISSION_TAG_ATTENDING_DESC'), 'registration');
		$tags[] = new RedeventTagsTag('eventplaces', JText::_('COM_REDEVENT_SUBMISSION_EVENTPLACES'), 'registration');
		$tags[] = new RedeventTagsTag('waitinglistplaces', JText::_('COM_REDEVENT_SUBMISSION_WAITINGLISTPLACES'), 'registration');
		$tags[] = new RedeventTagsTag('eventplacesleft', JText::_('COM_REDEVENT_SUBMISSION_EVENTPLACES_LEFT'), 'registration');
		$tags[] = new RedeventTagsTag('waitinglistplacesleft', JText::_('COM_REDEVENT_SUBMISSION_WAITINGLISTPLACES_LEFT'), 'registration');
		$tags[] = new RedeventTagsTag('inputname', JText::_('COM_REDEVENT_SUBMISSION_TAG_INPUTENAME_DESC'), 'registration');
		$tags[] = new RedeventTagsTag('inputemail', JText::_('COM_REDEVENT_SUBMISSION_TAG_INPUTEMAIL_DESC'), 'registration');
		$tags[] = new RedeventTagsTag('submit', JText::_('COM_REDEVENT_SUBMISSION_TAG_SUBMIT_DESC'), 'registration');
		$tags[] = new RedeventTagsTag('userfullname', JText::_('COM_REDEVENT_SUBMISSION_TAG_FULLNAME_DESC'), 'registration');
		$tags[] = new RedeventTagsTag('username', JText::_('COM_REDEVENT_SUBMISSION_TAG_USERNAME_DESC'), 'registration');
		$tags[] = new RedeventTagsTag('useremail', JText::_('COM_REDEVENT_SUBMISSION_TAG_USEREMAIL_DESC'), 'registration');
		$tags[] = new RedeventTagsTag('field_<field id>', JText::_('COM_REDEVENT_SUBMISSION_TAG_REDFORM_FIELD_DESC'), 'registration');

		$tags[] = new RedeventTagsTag('paymentrequest', JText::_('COM_REDEVENT_SUBMISSION_EVENT_PAYMENTREQUEST'), 'payment');
		$tags[] = new RedeventTagsTag('paymentrequestlink', JText::_('COM_REDEVENT_SUBMISSION_EVENT_PAYMENTREQUESTLINK'), 'payment');
		$tags[] = new RedeventTagsTag('registrationid', JText::_('COM_REDEVENT_SUBMISSION_EVENT_REGISTRATIONID'), 'payment');
		$tags[] = new RedeventTagsTag('total_price', JText::_('COM_REDEVENT_SUBMISSION_TAG_REDFORM_TOTAL_PRICE_DESC'), 'payment');

		return $tags;
	}

	/**
	 * Get text snippets tags
	 *
	 * @return array
	 */
	private function getLibraryTags()
	{
		$query = $this->_db->getQuery(true);

		$query->select('id, text_description, text_name')
			->from('#__redevent_textlibrary')
			->order('text_name');

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		$tags = array();

		foreach ((array) $res as $r)
		{
			$tags[] = new RedeventTagsTag($r->text_name, $r->text_description, 'library', $r->id);
		}

		return $tags;
	}

	/**
	 * Get custom fields tags
	 *
	 * @return array
	 */
	private function getCustomTags()
	{
		$query = $this->_db->getQuery(true);

		$query->select('id, tag, name')
			->from('#__redevent_fields')
			->order('ordering');

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		$tags = array();

		foreach ((array) $res as $r)
		{
			$tags[] = new RedeventTagsTag($r->tag, $r->name, 'custom', $r->id);
		}

		return $tags;
	}

	/**
	 * index by section
	 *
	 * @param   array  $tags  all tags
	 *
	 * @return array
	 */
	private function tagsBySection($tags)
	{
		$res = array();

		foreach ($tags as $tag)
		{
			@$res[$tag->section][] = $tag;
		}

		return $res;
	}

	/**
	 * Add tags from library classes
	 *
	 * @param   array  &$tags  tags
	 *
	 * @return void
	 *
	 * @since 3.2.3
	 */
	private function addTagsClasses(&$tags)
	{
		$files = JFolder::files(JPATH_LIBRARIES . '/redevent/tags/lib', ".php", false, true);

		foreach ($files as $file)
		{
			$name = substr(basename($file), 0, -4);
			$className = 'RedeventTagsLib' . ucfirst($name);
			$tags[] = $className::getDescription();
		}
	}
}
