<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Attendee Controller
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventControllerAttendee extends RControllerForm
{
	/**
	 * Method to add a new record.
	 *
	 * @return  mixed  True if the record can be added, a error object if not.
	 */
	public function add()
	{
		if (!parent::add())
		{
			return false;
		}

		// Set session id
		$context = "$this->option.edit.$this->context";
		JFactory::getApplication()->setUserState($context . '.session_id', $this->input->get('xref'));

		return true;
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  void
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries
		JSession::checkToken() or die('Invalid Token');

		$xref = $this->input->getInt('sessionId', 0) or die( 'Missing session id' );
		$task = $this->input->getCmd('task');

		$model = $this->getModel('attendee');

		$data = array();

		$data['id'] = $this->input->getInt('id');
		$data['xref'] = $xref;
		$data['uid'] = $this->input->getInt('uid');

		$nbPosted = $this->input->getInt('nbactive', 1);
		$pricegroups = array();

		for ($i = 1; $i < $nbPosted + 1; $i++)
		{
			$pricegroups[] = $this->input->getInt('sessionprice_' . $i);
		}

		$data['sessionpricegroup_id'] = $pricegroups[0];

		$isNew = $data['id'] == 0;

		if ($isNew)
		{
			$data['origin'] = 'backend';
		}

		if ($returnid = $model->store($data))
		{
			$model_wait = $this->getModel('Waitinglist');
			$model_wait->setXrefId($xref);
			$model_wait->UpdateWaitingList();

			$cache = JFactory::getCache('com_redevent');
			$cache->clean();

			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger($isNew ? 'onAttendeeCreated' : 'onAttendeeModified', array($returnid));

			switch ($task)
			{
				case 'apply' :
					$link = 'index.php?option=com_redevent&task=attendee.edit&id=' . $returnid;
					break;

				default :
					$link = $this->getRedirectToListRoute();
			}

			$this->setMessage(JText::_('COM_REDEVENT_REGISTRATION_SAVED'));
		}
		else
		{
			$link = $this->getRedirectToListRoute();
			$this->setMessage($model->getError(), 'error');
		}

		$model->checkin();
		$this->setRedirect($link);
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);

		$filter = $this->input->get('filter', array(), 'array');

		if (isset($filter['session']))
		{
			$append .= '&sessionId=' . $filter['session'];
		}

		return $append;
	}

	/**
	 * Get the JRoute object for a redirect to list.
	 *
	 * @param   string  $append  An optional string to append to the route
	 *
	 * @return  JRoute  The JRoute object
	 */
	protected function getRedirectToListRoute($append = null)
	{
		$returnUrl = $this->input->get('return');

		if ($returnUrl)
		{
			$returnUrl = base64_decode($returnUrl);

			return JRoute::_($returnUrl . $append, false);
		}
		else
		{
			if (!$sessionId = $this->input->getInt('sessionId', 0))
			{
				$sessionId = $this->input->getInt('xref', 0);

				if (!$sessionId)
				{
					die( 'Missing session Id' );
				}
			}

			return JRoute::_('index.php?option=com_redevent&view=attendees&xref=' . $sessionId . $append, false);
		}
	}
}
