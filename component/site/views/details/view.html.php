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
		$uri = JFactory::getUri();

		$params = $mainframe->getParams('com_redevent');

		$document = JFactory::getDocument();
		$user = JFactory::getUser();
		$elsettings = RedeventHelper::config();

		$acl = RedeventUserAcl::getInstance();

		if ($params->get('gplusone', 1))
		{
			$document->addScript('https://apis.google.com/js/plusone.js');
		}

		if ($params->get('tweet', 1))
		{
			$document->addScript('http://platform.twitter.com/widgets.js');
		}

		try
		{
			$row = $this->get('Details');
		}
		catch (Exception $e)
		{
			echo $e->getMessage();

			return;
		}

		$registers = $this->get('Registers');
		$roles = $this->get('Roles');
		$prices = $this->get('Prices');
		$register_fields = $this->get('FormFields');
		$regcheck = $this->get('Usercheck');

		/* Get the venues information */
		$this->_venues = $this->get('Venues');

		/* This loads the tags replacer */
		$tags = new RedeventTags;
		$tags->setEventId($row->event_id);
		$tags->setXref($row->xref);
		$this->assignRef('tags', $tags);

		// Get menu information
		$menu = $mainframe->getMenu();
		$item = $menu->getActive();

		if (!$item)
		{
			$item = $menu->getDefault();
		}

		// Check if the id exists
		if ($row->did == 0)
		{
			return JError::raiseError(404, JText::sprintf('COM_REDEVENT_Event_d_not_found', $row->did));
		}

		// Check if user has access to the details
		if ($params->get('showdetails', 1) == 0)
		{
			$mainframe->redirect('index.php', JText::_('COM_REDEVENT_EVENT_DETAILS_NOT_AVAILABLE'), 'error');
		}

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
		$pop = JFactory::getApplication()->input->getBool('pop');

		$params->def('page_title', RedeventHelper::getSessionFullTitle($row));

		if ($pop)
		{
			$params->set('popup', 1);
		}

		$print_link = JRoute::_('index.php?option=com_redevent&view=details&id=' . $row->slug . '&xref=' . $row->xref
			. '&pop=1&tmpl=component');

		// Pathway
		$pathway = $mainframe->getPathWay();
		$pathway->addItem(RedeventHelper::getSessionFullTitle($row), JRoute::_('index.php?option=com_redevent&view=details&id=' . $row->slug));

		// Check user if he can edit
		$allowedtoeditevent = $acl->canEditEvent($row->did);

		// Timecheck for registration
		$jetzt = date("Y-m-d");
		$now = strtotime($jetzt);
		$date = strtotime($row->dates);
		$timecheck = $now - $date;

		// Is the user allready registered at the event
		if ($regcheck)
		{
			// Add javascript code for cancel button on attendees layout.
			JHTML::_('behavior.framework');
			JText::script('COM_REDEVENT_CONFIRM_CANCEL_REGISTRATION');
			RHelperAsset::load('frontcancelregistration.js');
		}

		// Generate Eventdescription
		if (($row->datdescription == '') || ($row->datdescription == '<br />'))
		{
			$row->datdescription = JText::_('COM_REDEVENT_NO_DESCRIPTION');
		}
		else
		{
			// Execute Plugins
			$row->datdescription = JHTML::_('content.prepare', $row->datdescription);
		}

		// Generate Metatags
		if (!empty($row->meta_keywords))
		{
			$meta_keywords_content = array();
			$keywords = explode(",", $row->meta_keywords);

			foreach ($keywords as $keyword)
			{
				if (preg_match("#\[([^\]]*)\]#", $keyword, $match))
				{
					$replace = $this->keyword_switcher($match[1], $row, $elsettings->get('formattime', '%H:%M'), $elsettings->get('formatdate', '%d.%m.%Y'));

					$keyword = str_replace('[' . $match[1] . ']', $replace, $keyword);
					$meta_keywords_content[] = trim($keyword);
				}
				else
				{
					$meta_keywords_content[] = trim($keyword);
				}
			}

			$meta_keywords_content = implode(',', $meta_keywords_content);
		}
		else
		{
			$meta_keywords_content = $row->title;
		}

		if (!empty($row->meta_description))
		{
			if (preg_match_all("#\[([^\]]*)\]#", $row->meta_description, $match))
			{
				$search = array();
				$replace = array();

				foreach ($match[1] as $keyword)
				{
					$search[] = '[' . $keyword . ']';
					$replace[] = $this->keyword_switcher($keyword, $row, $elsettings->get('formattime', '%H:%M'), $elsettings->get('formatdate', '%d.%m.%Y'));
				}

				$description_content = str_replace($search, $replace, $row->meta_description);
			}
		}
		else
		{
			$description_content = "";
		}

		// Set page title and meta stuff
		$document->setTitle(RedeventHelper::getSessionFullTitle($row));
		$document->setMetadata('keywords', $meta_keywords_content);
		$document->setDescription(strip_tags($description_content));

		// More metadata
		$document->addCustomTag('<meta property="og:title" content="' . RedeventHelper::getSessionFullTitle($row) . '"/>');
		$document->addCustomTag('<meta property="og:type" content="event"/>');
		$document->addCustomTag('<meta property="og:url" content="' . htmlspecialchars($uri->toString()) . '"/>');

		if ($row->datimage)
		{
			$document->addCustomTag('<meta property="og:image" content="' . JURI::base() . 'images/redevent/events/' . $row->datimage . '"/>');
		}

		$document->addCustomTag('<meta property="og:site_name" content="' . $mainframe->getCfg('sitename') . '"/>');
		$summary = $row->summary;
		$document->addCustomTag('<meta property="og:description" content="' . JFilterOutput::cleanText($summary) . '"/>');

		// Build the url
		if (!empty($row->url) && strtolower(substr($row->url, 0, 7)) != "http://")
		{
			$row->url = 'http://' . $row->url;
		}

		/* Get the Venue Dates */
		$venuedates = $this->get('VenueDates');

		// Add alternate feed link
		$link = 'index.php?option=com_redevent&view=details&format=feed';

		if (!empty($row->slug))
		{
			$link .= '&id=' . $row->slug;
		}

		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);

		// Check unregistration rights
		$unreg_check = RedeventHelper::canUnregister($row->xref);

		// Manages attendees
		$manage_attendees = $this->get('ManageAttendees') || $this->get('ViewFullAttendees');
		$candeleteattendees = $this->get('ManageAttendees');
		$view_attendees_list = $row->show_names
			&& in_array($params->get('frontend_view_attendees_access'), JFactory::getUser()->getAuthorisedViewLevels());

		// Assign vars to jview
		$this->assignRef('row', $row);
		$this->assignRef('params', $params);
		$this->assignRef('user', $user);
		$this->assignRef('allowedtoeditevent', $allowedtoeditevent);
		$this->assignRef('manage_attendees', $manage_attendees);
		$this->assignRef('view_attendees_list', $view_attendees_list);
		$this->assignRef('candeleteattendees', $candeleteattendees);
		$this->assignRef('print_link', $print_link);
		$this->assignRef('registers', $registers);
		$this->assignRef('registersfields', $register_fields);
		$this->assignRef('elsettings', $elsettings);
		$this->assignRef('item', $item);
		$this->assignRef('venuedates', $venuedates);
		$this->assignRef('unreg_check', $unreg_check);
		$this->assignRef('roles', $roles);
		$this->assignRef('prices', $prices);
		$this->assignRef('uri', $uri);
		$this->assign('lang', JFactory::getLanguage());

		if ($params->get('fbopengraph', 0))
		{
			$this->_opengraph();
		}

		$tpl = JFactory::getApplication()->input->get('tpl', $tpl);

		if ($tpl == '')
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

		parent::display($tpl);
	}

	/**
	 * structures the keywords
	 *
	 * @param   string  $keyword     keyword
	 * @param   object  $row         data
	 * @param   string  $formattime  time format for strftime
	 * @param   string  $formatdate  data format for strftime
	 *
	 * @return string
	 */
	protected function keyword_switcher($keyword, $row, $formattime, $formatdate)
	{
		$content = '';

		switch ($keyword)
		{
			case "title":
				$content = $row->event_title;
				break;

			case "catsid":
				// TODO: fix for multiple cats
				$content = '';
				break;

			case "a_name":
				$content = '';
				break;

			case "times":
			case "endtimes":
				$content = '';

				foreach ($this->_venues as $key => $venue)
				{
					if ($venue->$keyword)
					{
						$content .= strftime($formattime, strtotime($venue->$keyword)) . ' ';
					}
				}
				break;

			case "dates":
			case "enddates":
				$content = '';

				foreach ($this->_venues as $key => $venue)
				{
					if (RedeventHelper::isValidDate($venue->$keyword))
					{
						$content .= strftime($formatdate, strtotime($venue->$keyword)) . ' ';
					}
					else
					{
						$content .= Jtext::_('COM_REDEVENT_OPEN_DATE');
					}
				}
				break;

			default:
				if (!isset($row->$keyword))
				{
					JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_REDEVENT_UNDEFINED_META_KEYWORD_S', $keyword));
				}
				else
				{
					$content .= $row->$keyword;
				}
		}

		return $content;
	}

	/**
	 * Roles layout
	 *
	 * @return void
	 */
	public function showRoles()
	{
		if (file_exists(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_redmember') && JComponentHelper::isEnabled('com_redmember'))
		{
			$layout = $this->getLayout();
			$this->setLayout('default');
			echo $this->loadTemplate('rmroles');
			$this->setLayout($layout);
		}
		else
		{
			$layout = $this->getLayout();
			$this->setLayout('default');
			echo $this->loadTemplate('roles');
			$this->setLayout($layout);
		}
	}

	/**
	 * Add opengraph code
	 *
	 * @return void
	 */
	protected function _opengraph()
	{
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$params = $app->getParams('com_redevent');

		if ($params->get('fbadmin'))
		{
			$document->addCustomTag('<meta property="fb:admins" content="' . $params->get('fbadmin') . '"/>');
		}

		$document->addScript('http://connect.facebook.net/en_US/all.js#xfbml=1');
	}
}
