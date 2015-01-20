<?php
/**
 * @package    Redevent.Site
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Frontend edit venue model
 *
 * @package  Redevent.front
 * @since    0.9
 */
class RedeventModelEditvenue extends RModelAdmin
{
	protected $formName = 'venue';

	/**
	 * Method to get a single record.
	 *
	 * @param   int  $pk  Record Id
	 *
	 * @return  mixed
	 */
	public function getItem($pk = null)
	{
		$result = parent::getItem($pk);

		if ($result && $result->id)
		{
			$helper = new RedeventHelperAttachment;
			$files = $helper->getAttachments('venue' . $result->id, JFactory::getUser()->getAuthorisedViewLevels());
			$result->attachments = $files;

			$result->categories = $this->getVenueCategories($result);
		}
		else
		{
			$params = RedeventHelper::config();

			$result->attachments = array();
			$result->categories = array();
			$result->map = $params->get('showmapserv', 1);
		}

		return $result;
	}

	/**
	 * Get the associated JTable
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  JTable
	 */
	public function getTable($name = null, $prefix = '', $config = array())
	{
		if (empty($name))
		{
			$name = 'Venue';
		}

		return parent::getTable($name, $prefix, $config);
	}

	/**
	 * Method to get the category data
	 *
	 * @param   object  $result  result to get categories from
	 *
	 * @return  array
	 */
	private function getVenueCategories($result)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('c.id');
		$query->from('#__redevent_venues_categories AS c');
		$query->join('INNER', '#__redevent_venue_category_xref AS x ON x.category_id = c.id');
		$query->where('x.venue_id = ' . $result->id);

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function save($data)
	{
		$result = parent::save($data);

		if ($result)
		{
			// Attachments
			$helper = new RedeventHelperAttachment;
			$helper->store('venue' . $this->getState($this->getName() . '.id'));
		}

		return $result;
	}

	/**
	 * logic to get the categories options
	 *
	 * @access public
	 * @return void
	 */
	function getCategoryOptions( )
	{
		$user = &JFactory::getUser();
		$app = &JFactory::getApplication();
		$params = $app->getParams();
		$superuser	= RedeventUserAcl::superuser();

		//administrators or superadministrators have access to all categories, also maintained ones
		if($superuser)
		{
			$cwhere = ' WHERE c.published = 1';
		}
		else
		{
			$acl = RedeventUserAcl::getInstance();
			$managed = $acl->getManagedVenuesCategories();
			if (!$managed || !count($managed))
			{
				return false;
			}
			$cwhere = ' WHERE c.id IN ('.implode(',', $managed).') ';
		}

		//get the maintained categories and the categories whithout any group
		//or just get all if somebody have edit rights
		$query = ' SELECT c.id, c.name, (COUNT(parent.name) - 1) AS depth, c.ordering '
		. ' FROM #__redevent_venues_categories AS c, '
		. ' #__redevent_venues_categories AS parent '
		. $cwhere
		. ' AND c.lft BETWEEN parent.lft AND parent.rgt '
		. ' GROUP BY c.id '
		. ' ORDER BY c.lft;'
		;
		$this->_db->setQuery($query);

		$results = $this->_db->loadObjectList();
		$options = array();
		foreach((array) $results as $cat)
		{
			$options[] = JHTML::_('select.option', $cat->id, str_repeat('>', $cat->depth) . ' ' . $cat->name);
		}

		$this->_categories = $options;

		return $this->_categories;
	}

	/**
	 * Method to store the venue
	 *
	 * @access	public
	 * @return	id
	 * @since	0.9
	 */
	function store($data, $file)
	{
		$mainframe = JFactory::getApplication();

		$user = JFactory::getUser();
		$elsettings = RedeventHelper::config();

		//Get mailinformation
		$SiteName = $mainframe->getCfg('sitename');
		$MailFrom = $mainframe->getCfg('mailfrom');
		$FromName = $mainframe->getCfg('fromname');
		$tzoffset = $mainframe->getCfg('offset');

		$params = $mainframe->getParams('com_redevent');

		$row = RTable::getAdminInstance('venue');

		//bind it to the table
		if (!$row->bind($data))
		{
			RedeventError::raiseError(500, $this->_db->stderr());

			return false;
		}

		//If image upload is required we will stop here if no file was attached
		if ( empty($file['name']) && $params->get('edit_image', 1) == 2 )
		{
			$this->setError( JText::_('COM_REDEVENT_IMAGE_EMPTY' ) );

			return false;
		}

		if ( ( $params->get('edit_image', 1) == 2 || $params->get('edit_image', 1) == 1 ) && ( !empty($file['name'])  ) )
		{
			jimport('joomla.filesystem.file');

			if ($params->get('default_image_path', 'redevent')) {
				$reldirpath = $params->get('default_image_path', 'redevent').DS.'venues'.DS;
			}
			else {
				$reldirpath = '';
			}
			$base_Dir 	= JPATH_SITE.DS.'images'.DS.$reldirpath;

			//check the image
			$check = RedeventImage::check($file, $elsettings);

			if ($check === false) {
				$mainframe->redirect($_SERVER['HTTP_REFERER']);
			}

			//sanitize the image filename
			$filename = RedeventImage::sanitize($base_Dir, $file['name']);
			$filepath = $base_Dir . $filename;

			if (!JFile::upload( $file['tmp_name'], $filepath ))
			{
				$this->setError( JText::_('COM_REDEVENT_UPLOAD_FAILED' ) );

				return false;
			}
			else
			{
				$row->locimage = 'images'.DS.$reldirpath.$filename;
			}
		}
		else
		{
			//keep image if edited and left blank
			$row->locimage = $row->curimage;
		}//end image upload if

		//check description --> wipe out code
		$row->locdescription = strip_tags($row->locdescription, '<br><br/>');

		//convert the linux \n (Mac \r, Win \r\n) to <br /> linebreaks
		$row->locdescription = str_replace(array("\r\n", "\r", "\n"), "<br />", $row->locdescription);

		//cut too long words
		$row->locdescription = wordwrap($row->locdescription, 75, " ", 1);

		//check length
		$length = JString::strlen($row->locdescription);

		if ($length > $params->get('max_description', 1000))
		{
			// if required shorten it
			$row->locdescription = JString::substr($row->locdescription, 0, $params->get('max_description', 1000));
			//if shortened add ...
			$row->locdescription = $row->locdescription.'...';
		}

		$row->venue = trim( JFilterOutput::ampReplace( $row->venue ) );

		//Make sure the data is valid
		if (!$row->check($elsettings))
		{
			$this->setError($row->getError());

			return false;
		}

		//is this an edited venue or not?
		//after store we allways have an id
		$edited = $row->id ? $row->id : false;

		//store it in the db
		if (!$row->store())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// attachments
		if ($params->get('allow_attachments', 1))
		{
			$helper = new RedeventHelperAttachment;
			$helper->store('venue'.$row->id);
		}

		jimport('joomla.utilities.mail');

		$link 	= JRoute::_(JURI::base().RedeventHelperRoute::getVenueEventsRoute($row->id), false);

		//create mail
		if (($params->get('mailinform') == 2) || ($params->get('mailinform') == 3))
		{
			$mail = JFactory::getMailer();

			$state 	= $row->published ? JText::sprintf('COM_REDEVENT_MAIL_VENUE_PUBLISHED', $link) : JText::_('COM_REDEVENT_MAIL_VENUE_UNPUBLISHED');

			If ($edited)
			{
				$modified_ip 	= getenv('REMOTE_ADDR');
				$edited 		= JHTML::Date( $row->modified, JText::_('DATE_FORMAT_LC2' ) );
				$mailbody 		= JText::sprintf('COM_REDEVENT_MAIL_EDIT_VENUE', $user->name, $user->username, $user->email, $modified_ip, $edited, $row->venue, $row->url, $row->street, $row->plz, $row->city, $row->country, $row->locdescription, $state);
				$mail->setSubject( $SiteName.JText::_('COM_REDEVENT_EDIT_VENUE_MAIL' ) );

			}
			else
			{
				$created 		= JHTML::Date( $row->modified, JText::_('DATE_FORMAT_LC2' ) );
				$mailbody 		= JText::sprintf('COM_REDEVENT_MAIL_NEW_VENUE', $user->name, $user->username, $user->email, $row->author_ip, $created, $row->venue, $row->url, $row->street, $row->plz, $row->city, $row->country, $row->locdescription, $state);
				$mail->setSubject( $SiteName.JText::_('COM_REDEVENT_NEW_VENUE_MAIL' ) );

			}

			$receivers = explode( ',', trim($params->get('mailinformrec')));

			$mail->addRecipient( $receivers );
			$mail->setSender( array( $MailFrom, $FromName ) );
			$mail->setBody( $mailbody );

			if (!$mail->Send())
			{
				RedeventHelperLog::simpleLog('Error sending created/edited venue notification to site owner');
			}
		}

		//create the mail for the user
		if (($params->get('mailinformuser') == 2) || ($params->get('mailinformuser') == 3))
		{
			$usermail = JFactory::getMailer();

			$state 	= $row->published ? JText::sprintf('COM_REDEVENT_USER_MAIL_VENUE_PUBLISHED', $link) : JText::_('COM_REDEVENT_USER_MAIL_VENUE_UNPUBLISHED');

			if ($edited)
			{
				$edited 		= JHTML::Date( $row->modified, JText::_('DATE_FORMAT_LC2' ) );
				$mailbody 		= JText::sprintf('COM_REDEVENT_USER_MAIL_EDIT_VENUE', $user->name, $user->username, $edited, $row->venue, $row->url, $row->street, $row->plz, $row->city, $row->country, $row->locdescription, $state);
				$usermail->setSubject( $SiteName.JText::_('COM_REDEVENT_EDIT_USER_VENUE_MAIL' ) );
			}
			else
			{
				$created 		= JHTML::Date( $row->modified, JText::_('DATE_FORMAT_LC2' ) );
				$mailbody 		= JText::sprintf('COM_REDEVENT_USER_MAIL_NEW_VENUE', $user->name, $user->username, $created, $row->venue, $row->url, $row->street, $row->plz, $row->city, $row->country, $row->locdescription, $state);
				$usermail->setSubject( $SiteName.JText::_('COM_REDEVENT_NEW_USER_VENUE_MAIL' ) );
			}

			$usermail->addRecipient( $user->email );
			$usermail->setSender( array( $MailFrom, $FromName ) );
			$usermail->setBody( $mailbody );

			if (!$usermail->Send())
			{
				RedeventHelperLog::simpleLog('Error sending created/edited venue notification to venue owner');
			}
		}

		//update item order
		$row->reorder();

		return $row->id;
	}
}
