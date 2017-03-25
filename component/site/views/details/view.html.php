<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML Details View class of the redEVENT component
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewDetails extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		$params = $mainframe->getParams('com_redevent');
		$acl = RedeventUserAcl::getInstance();

		try
		{
			$row = $this->get('Details');
		}
		catch (Exception $e)
		{
			$mainframe->redirect('index.php', $e->getMessage(), 'error');
		}

		// Check if the id exists
		if (!$row->did)
		{
			$mainframe->redirect('index.php', JText::sprintf('COM_REDEVENT_Event_d_not_found', $row->did), 'error');
		}

		// Check if user has access to the details
		if ($params->get('showdetails', 1) == 0)
		{
			$mainframe->redirect('index.php', JText::_('COM_REDEVENT_EVENT_DETAILS_NOT_AVAILABLE'), 'error');
		}

		if (!$row->published && !$acl->canEditXref($row->id))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('COM_REDEVENT_NOT_ALLOWED'), 'error');
		}

		// Generate Event description
		if ($row->datdescription == '' || $row->datdescription == '<br />')
		{
			$row->datdescription = JText::_('COM_REDEVENT_NO_DESCRIPTION');
		}
		else
		{
			// Execute Plugins
			$row->datdescription = JHTML::_('content.prepare', $row->datdescription);
		}

		// Build the url
		if (!empty($row->url) && strtolower(substr($row->url, 0, 7)) != "http://")
		{
			$row->url = 'http://' . $row->url;
		}

		$this->row = $row;
		$this->registers = $this->get('Registers');
		$this->roles = $this->get('Roles');
		$this->prices = $this->get('Prices');
		$this->registersfields = $this->get('FormFields');
		$this->print_link = JRoute::_(RedeventHelperRoute::getDetailsRoute($row->slug, $row->xref) . '&pop=1&tmpl=component');
		$this->elsettings = RedeventHelper::config();

		// This loads the tags replacer
		$this->tags = new RedeventTags;
		$this->tags->setEventId($row->event_id);
		$this->tags->setXref($row->xref);

		// Get the Venue Dates
		$this->venuedates = $this->get('VenueDates');

		// Manages attendees
		$this->manage_attendees = $this->get('ManageAttendees') || $this->get('ViewFullAttendees');
		$this->candeleteattendees = $this->get('ManageAttendees');
		$this->view_attendees_list = $row->show_names
			&& in_array($params->get('frontend_view_attendees_access'), JFactory::getUser()->getAuthorisedViewLevels());

		// Assign vars to jview
		$this->user = JFactory::getUser();
		$this->params = $params;
		$this->allowedtoeditevent = $acl->canEditEvent($row->did);
		$this->unreg_check = RedeventHelper::canUnregister($row->xref);
		$this->uri = JFactory::getUri();
		$this->lang = JFactory::getLanguage();

		$this->prepareDocument();

		$tpl = JFactory::getApplication()->input->get('tpl', $tpl);

		if (empty($tpl))
		{
			switch ($row->details_layout)
			{
				case 2:
					$this->setLayout('fixed');
					break;

				case 1:
					$this->setLayout('default');
					break;

				case 0:
					$this->setLayout($params->get('details_layout', 'fixed'));
					break;
			}
		}

		return parent::display($tpl);
	}

	/**
	 * Roles layout
	 *
	 * @return void
	 */
	public function showRoles()
	{
		$layout = $this->getLayout();
		$this->setLayout('default');

		if (file_exists(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_redmember') && JComponentHelper::isEnabled('com_redmember'))
		{
			echo $this->loadTemplate('rmroles');
		}
		else
		{
			echo $this->loadTemplate('roles');
		}

		$this->setLayout($layout);
	}

	/**
	 * Add document metadata
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function addMeta()
	{
		$document = JFactory::getDocument();

		$meta_keywords_content = empty($this->row->meta_keywords) ? $this->row->title : $this->tags->replaceTags($this->row->meta_keywords);
		$meta_description = empty($this->row->meta_description) ? $this->row->title : $this->tags->replaceTags($this->row->meta_description);

		// Set page title and meta stuff
		$document->setTitle(RedeventHelper::getSessionFullTitle($this->row));
		$document->setMetadata('keywords', $meta_keywords_content);
		$document->setDescription(strip_tags($meta_description));

		// More metadata
		$document->addCustomTag('<meta property="og:title" content="' . RedeventHelper::getSessionFullTitle($this->row) . '"/>');
		$document->addCustomTag('<meta property="og:type" content="event"/>');
		$document->addCustomTag('<meta property="og:url" content="' . htmlspecialchars($this->uri->toString()) . '"/>');

		if ($this->row->datimage)
		{
			$document->addCustomTag('<meta property="og:image" content="' . JURI::base() . 'images/redevent/events/' . $this->row->datimage . '"/>');
		}

		$document->addCustomTag('<meta property="og:site_name" content="' . JFactory::getApplication()->get('sitename') . '"/>');
		$document->addCustomTag('<meta property="og:description" content="' . JFilterOutput::cleanText($this->row->summary) . '"/>');
	}

	/**
	 * Social icons integration
	 *
	 * @return void
	 */
	protected function addSocial()
	{
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$params = $app->getParams('com_redevent');

		if ($params->get('fbopengraph', 0))
		{
			if ($params->get('fbadmin'))
			{
				$document->addCustomTag('<meta property="fb:admins" content="' . $params->get('fbadmin') . '"/>');
			}

			$document->addScript('http://connect.facebook.net/en_US/all.js#xfbml=1');
		}

		if ($params->get('gplusone', 1))
		{
			$document->addScript('https://apis.google.com/js/plusone.js');
		}

		if ($params->get('tweet', 1))
		{
			$document->addScript('http://platform.twitter.com/widgets.js');
		}
	}

	/**
	 * Prepare document
	 *
	 * @return void
	 */
	protected function prepareDocument()
	{
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$params = $this->params;

		$params->def('page_title', RedeventHelper::getSessionFullTitle($this->row));

		// Add css file
		if (!$params->get('custom_css'))
		{
			RHelperAsset::load('redevent.css');
		}
		else
		{
			$document->addStyleSheet($params->get('custom_css'));
		}

		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		// Print
		if (JFactory::getApplication()->input->getBool('pop'))
		{
			$params->set('popup', 1);
		}

		// Add alternate feed link
		$link = RedeventHelperRoute::getDetailsRoute($this->row->slug) . '&format=feed';

		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);

		// Pathway
		$pathway = $app->getPathWay();
		$pathway->addItem(RedeventHelper::getSessionFullTitle($this->row), JRoute::_(RedeventHelperRoute::getDetailsRoute($this->row->slug)));

		// Is the user allready registered at the event
		if ($this->get('Usercheck'))
		{
			// Add javascript code for cancel button on attendees layout.
			JHTML::_('behavior.framework');
			JText::script('COM_REDEVENT_CONFIRM_CANCEL_REGISTRATION');
			RHelperAsset::load('frontcancelregistration.js');
		}

		$this->addMeta();
		$this->addSocial();
	}
}
