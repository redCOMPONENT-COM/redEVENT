<?php
/**
 * @package    Redeventb2b.site
 * @copyright  Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the frontend admin View
 *
 * @since  2.0
 */
class Redeventb2bViewBookedsessions extends RViewAdmin
{
	/**
	 * Creates the View
	 *
	 * @param   string  $tpl  template to display
	 *
	 * @return void
	 *
	 * @since 2.5
	 */
	public function display($tpl = null)
	{
		$useracl = RedeventUserAcl::getInstance();
		$params = JFactory::getApplication()->getParams('com_redevent');
		$state = $this->get('state');

		$this->order_dir = $state->get('filter_order_Dir');
		$this->order     = $state->get('filter_order');

		$this->bookings_pagination = $this->get('BookingsPagination');
		$this->limitstart = $state->get('limitstart');

		$this->params  = $params;
		$this->state   = $state;

		$this->useracl = $useracl;
		$this->bookings = $this->get('Bookings');
		$this->params  = $params;

		$this->organization = $this->get('Organization');

		parent::display($tpl);

		JFactory::getApplication()->close();
	}

	/**
	 * return html for limit box
	 *
	 * @return string html
	 */
	protected function getLimitBox()
	{
		$state = $this->get('state');

		$options = array(
			JHtml::_('select.option', 15, 15),
			JHtml::_('select.option', 25, 25),
			JHtml::_('select.option', 50, 50)
		);
		$html = JHtml::_('select.genericlist', $options, 'limit',
			array('class' => 'inputbox ajaxlimit'), 'value', 'text', $state->get('limit')
		);

		return $html;
	}

	/**
	 * Check if event is full
	 *
	 * @param   object  $row  row
	 *
	 * @return bool
	 */
	protected function isFull($row)
	{
		// No limit
		if (!$row->maxattendees)
		{
			return false;
		}

		// Not full
		if ($row->registered >= $row->maxattendees)
		{
			return true;
		}

		return false;
	}

	/**
	 * Creates the attendees edit button
	 *
	 * @param   int  $id  xref id
	 *
	 * @return string html
	 */
	public static function manageBookingbutton($id)
	{
		JHTML::_('behavior.tooltip');

		$image = JHTML::image('media/com_redevent/images/b2b-bookuser.png', JText::_('COM_REDEVENT_MANAGE_BOOKINGS'));

		$tip  = JText::_('COM_REDEVENT_MANAGE_BOOKINGS_DESC');
		$text = JText::_('COM_REDEVENT_MANAGE_BOOKINGS');

		$attribs = array(
			'xref' => $id,
			'class' => 'manageBookings hasTip',
			'title' => $text,
			'tip' => $tip,
		);

		$output = JHtml::link('#', $image, $attribs);

		return $output;
	}

	/**
	 * returns string for available places display
	 *
	 * @param   object  $row         session data
	 * @param   bool    $showBooked  show number of booked places
	 *
	 * @return string
	 */
	protected function printPlaces($row, $showBooked = true)
	{
		if ($this->isFull($row))
		{
			return '';
		}

		$maxLeftDisplay = 2000;

		if (!$row->maxattendees)
		{
			if ($showBooked)
			{
				$tip = JText::sprintf('COM_REDEVENT_FRONTEND_ADMIN_PLACES_BOOKED_D', $row->registered);

				return '<span class="hasTip" title="' . $tip . '">' . $row->registered . '</span>';
			}
			else
			{
				return '';
			}
		}
		else
		{
			// Only display up to $maxLeftDisplay left places
			$left = max(array($row->maxattendees - $row->registered, 0));
			$left = $left > $maxLeftDisplay ? $maxLeftDisplay . '+' : $left;

			$tip = JText::sprintf('COM_REDEVENT_FRONTEND_ADMIN_PLACES_BOOKED_D_LEFT_S', $row->registered, $left);

			if ($showBooked)
			{
				return '<span class="hasTip" title="' . $tip . '">' . $row->registered . '/' . $left . '</span>';
			}
			else
			{
				return '<span class="hasTip" title="' . $tip . '">' . $left . '</span>';
			}
		}
	}

	/**
	 * returns string for info icon when session is full
	 *
	 * @param   object  $row  session data
	 *
	 * @return string
	 */
	protected function printInfoIcon($row)
	{
		if (!$this->isFull($row))
		{
			return '';
		}

		$image = JHTML::image('media/com_redevent/images/b2b-getinfo.gif', JText::_('COM_REDEVENT_FRONTEND_ADMIN_QUERY_INFO_SESSION_FULL'));

		$tip  = JText::_('COM_REDEVENT_FRONTEND_ADMIN_QUERY_INFO_SESSION_FULL_DESC');
		$text = JText::_('COM_REDEVENT_FRONTEND_ADMIN_QUERY_INFO_SESSION_FULL');

		$attribs = array(
			'xref' => $row->xref,
			'class' => 'getinfo hasTip',
			'title' => $text,
			'tip' => $tip
		);

		$output = JHtml::link('index.php?option=com_redeventb2b&task=frontadmin.getinfoform&tmpl=component&modal=1&xref=' . $row->xref, $image, $attribs);

		return $output;
	}
}
