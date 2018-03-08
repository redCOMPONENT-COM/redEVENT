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
class Redeventb2bViewEditmember extends RViewAdmin
{
	/**
	 * Creates the edit member View
	 *
	 * @param   string  $tpl  template to display
	 *
	 * @return void
	 */
	public function display($tpl= null)
	{
		$document = JFactory::getDocument();

		$member = $this->get('MemberInfo');
		$booked = $this->get('MemberBooked');
		$previous = $this->get('MemberPrevious');
		$state = $this->get('state');

		$this->params = JFactory::getApplication()->getParams('com_redevent');

		$modal = JFactory::getApplication()->input->get('modal');

		if (!$orgId = JFactory::getApplication()->input->get('orgId'))
		{
			RedeventHelperLog::simpleLog('edit member view missing orgid');
			echo 'edit member view missing orgid';

			return;
		}

		$rmUser = RedmemberApi::getUser(JFactory::getApplication()->input->get('uid'));

		$this->form = $rmUser->getBaseForm();
		$this->tabs = $rmUser->getTabs();

		if ($modal)
		{
			// Add css file
			if (!$this->params->get('custom_css'))
			{
				RHelperAsset::load('redevent-b2b.css');
			}
			else
			{
				$document->addStyleSheet($this->params->get('custom_css'));
			}
		}

		$this->assignRef('member',     $member);
		$this->assignRef('booked',     $booked);
		$this->assignRef('previous',   $previous);
		$this->assignRef('modal',      $modal);
		$this->uid       = $state->get('uid');
		$this->orgId = $orgId;

		$this->booked_order = $state->get('booked_order');
		$this->booked_order_dir = $state->get('booked_order_dir');
		$this->booked_pagination = $this->get('MemberBookedPagination');
		$this->booked_limitstart = $state->get('booked_limitstart');

		$this->previous_order = $state->get('previous_order');
		$this->previous_order_dir = $state->get('previous_order_dir');
		$this->previous_pagination = $this->get('MemberPreviousPagination');
		$this->previous_limitstart = $state->get('previous_limitstart');

		$this->organizations_options = $this->get('OrganizationsOptions');

		parent::display($tpl);
	}
}
