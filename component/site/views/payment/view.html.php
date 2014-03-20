<?php
/**
 * @version 1.0 $Id: view.html.php 3085 2010-01-20 22:12:57Z julien $
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
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML payment View class of the redevent component
 *
 * @package Joomla
 * @subpackage redevent
 * @since 2.0
 */
class RedeventViewPayment extends JView
{
	/**
	 * Creates the output for the details view
	 *
 	 * @since 0.9
	 */
	function display($tpl = null)
	{
		$mainframe = &JFactory::getApplication();
		/* Set which page to show */
		$state      = JRequest::getVar('state', '');
		$submit_key = JRequest::getVar('submit_key', '');

		$document 	= JFactory::getDocument();
		$dispatcher = JDispatcher::getInstance();
		$elsettings = redEVENTHelper::config();
    $uri 		= & JFactory::getURI();

		$row		= $this->get('Event');

		/* This loads the tags replacer */
		JView::loadHelper('tags');
		JRequest::setVar('xref', $row->xref); // neede for tag constructor
		$tags = new redEVENT_tags();
		$tags->setXref($row->xref);
		$tags->setSubmitkey($submit_key);

		//get menu information
		$menu		= & JSite::getMenu();
		$item    	= $menu->getActive();
		if (!$item) $item = $menu->getDefault();

		$params 	= & $mainframe->getParams('com_redevent');

		//Check if the id exists
		if ($row->eventid == 0)
		{
			return JError::raiseError( 404, JText::sprintf( 'COM_REDEVENT_Event_d_not_found', $row->eventid ) );
		}

		//add css file
    if (!$params->get('custom_css')) {
      $document->addStyleSheet('media/com_redevent/css/redevent.css');
    }
    else {
      $document->addStyleSheet($params->get('custom_css'));
    }
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		//Print
		$pop	= JRequest::getBool('pop');

		$params->def( 'page_title', JText::_('COM_REDEVENT_DETAILS' ));

		if ( $pop ) {
			$params->set( 'popup', 1 );
		}

		$print_link = JRoute::_(htmlspecialchars($uri->toString()).'&pop=1&tmpl=component');

		//set page title and meta stuff
		$document->setTitle( $row->title. ' - '. JText::_('COM_REDEVENT_Payment') );

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

				// Trigger event for custom handling
				JPluginHelper::importPlugin('redevent');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('onAfterPaymentVerified', array($submit_key));
				break;

			case 'refused':
				$text = JText::_('COM_REDEVENT_PAYMENT_PAYMENT_REFUSED');
				break;

			default:
				$text = JText::_('COM_REDEVENT_PAYMENT_UNKNOWN_PAYMENT_STATUS');
				break;
		}

		//assign vars to jview
		$this->assignRef('row',           $row);
		$this->assign(   'text',          $text);
		$this->assignRef('params' ,       $params);
		$this->assignRef('print_link',    $print_link);
		$this->assignRef('elsettings',    $elsettings);
		$this->assignRef('item',          $item);
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
		if (RedformHelperAnalytics::isEnabled())
		{
			$submit_key = JFactory::getApplication()->input->get('submit_key');
			$details = $this->get('Event');

			$options = array();
			$options['affiliation'] = 'redevent-b2b';
			$options['sku']         = $details->title;
			$options['productname'] = $details->venue . ' - ' . $details->xref . ' ' . $details->title
				. ($details->session_title ? ' / ' . $details->session_title : '');

			$cats = array();
			foreach ($details->categories as $c)
			{
				$cats[] = $c->catname;
			}
			$options['category'] = implode(', ', $cats);

			RedformHelperAnalytics::recordTrans($submit_key, $options);
		}
	}
}
