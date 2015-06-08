<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * HTML payment View class of the redEVENT component
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventViewPayment extends RViewSite
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
		$model = $this->getModel();

		/* Set which page to show */
		$state = JRequest::getVar('state', '');
		$submit_key = JRequest::getVar('submit_key', '');

		$document = JFactory::getDocument();
		$dispatcher = JDispatcher::getInstance();
		$elsettings = RedeventHelper::config();
		$uri = JFactory::getURI();

		$row = $this->get('Event');

		/* This loads the tags replacer */
		JRequest::setVar('xref', $row->xref); // neede for tag constructor
		$tags = new RedeventTags;
		$tags->setXref($row->xref);
		$tags->setSubmitkey($submit_key);

		// Get menu information
		$menu = $mainframe->getMenu();
		$item = $menu->getActive();

		if (!$item)
		{
			$item = $menu->getDefault();
		}

		$params = $mainframe->getParams('com_redevent');

		// Check if the id exists
		if ($row->eventid == 0)
		{
			return JError::raiseError(404, JText::sprintf('COM_REDEVENT_Event_d_not_found', $row->eventid));
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
		$pop = JRequest::getBool('pop');

		$params->def('page_title', JText::_('COM_REDEVENT_DETAILS'));

		if ($pop)
		{
			$params->set('popup', 1);
		}

		$print_link = JRoute::_(htmlspecialchars($uri->toString()) . '&pop=1&tmpl=component');

		// Set page title and meta stuff
		$document->setTitle($row->title . ' - ' . JText::_('COM_REDEVENT_Payment'));

		$text = '';

		switch ($state)
		{
			case 'processing':
				$text = $tags->ReplaceTags($row->paymentprocessing);
				$this->addTracking();
				break;

			case 'accepted':
				$text = $tags->ReplaceTags($row->paymentaccepted);
				$this->addTracking();
				$model->checkAndConfirm();

				// Trigger event for custom handling
				JPluginHelper::importPlugin('redevent');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('onAfterPaymentVerifiedRedevent', array($submit_key));

				break;

			case 'refused':
				$text = JText::_('COM_REDEVENT_PAYMENT_PAYMENT_REFUSED');
				break;

			default:
				$text = JText::_('COM_REDEVENT_PAYMENT_UNKNOWN_PAYMENT_STATUS');
				break;
		}

		// Assign vars to jview
		$this->assignRef('row', $row);
		$this->assign('text', $text);
		$this->assignRef('params', $params);
		$this->assignRef('print_link', $print_link);
		$this->assignRef('elsettings', $elsettings);
		$this->assignRef('item', $item);
		$this->assignRef('tags', $tags);

		$tpl = JRequest::getVar('tpl', $tpl);

		parent::display($tpl);
	}

	/**
	 * Add google analytics
	 *
	 * @return void
	 */
	protected function addTracking()
	{
		if (RdfHelperAnalytics::isEnabled())
		{
			$submit_key = JFactory::getApplication()->input->get('submit_key');
			$cartReference = RdfCore::getInstance()->getSubmitkeyCartReference($submit_key);
			$details = $this->get('Event');

			$options = array();
			$options['affiliation'] = 'redevent-b2b';
			$options['sku'] = $details->title;
			$options['productname'] = $details->venue . ' - ' . $details->xref . ' ' . $details->title
				. ($details->session_title ? ' / ' . $details->session_title : '');

			$cats = array();

			foreach ($details->categories as $c)
			{
				$cats[] = $c->name;
			}

			$options['category'] = implode(', ', $cats);

			RdfHelperAnalytics::recordTrans($cartReference, $options);
		}
	}
}
