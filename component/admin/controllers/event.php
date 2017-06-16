<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Event Controller
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventControllerEvent extends RedeventControllerForm
{
	/**
	 * Ajax call to get fields tab content.
	 *
	 * @return  void
	 */
	public function ajaxGetSessions()
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		$eventId = $input->getInt('id');

		if ($eventId)
		{
			$model = $this->getModel('Sessions', 'RedeventModel', array('ignore_request' => false));
			$model->getState();
			$model->setState('filter.event', $eventId);

			$formName = 'sessionsForm';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			echo RedeventLayoutHelper::render('event.sessions', array(
					'eventId' => $eventId,
					'state' => $model->getState(),
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'filter_form' => $model->getForm(),
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => true,
					'action' => 'index.php?option=com_redevent&view=event&model=sessions',
					'return' => base64_encode('index.php?option=com_redevent&view=event&layout=edit&id=' . $eventId . '&tab=sessions&from_form=1')
				)
			);
		}
		else
		{
			echo JText::_('COM_REDEVENT_EVENT_SESSIONS_SAVE_FIRST');
		}

		$app->close();
	}

	/**
	 * Get the JRoute object for a redirect to list.
	 *
	 * @param   string  $append  An optionnal string to append to the route
	 *
	 * @return  JRoute  The JRoute object
	 */
	protected function getRedirectToListRoute($append = null)
	{
		$returnUrl = $this->input->get('return', '', 'Base64');
		$returnUrl = $returnUrl ? base64_decode($returnUrl) : false;

		/**
		 * In return url, we have to check the 'view' value to be sure we are not looping after coming back from editing a session.
		 */
		if ($returnUrl && !strstr($returnUrl, '&view=event'))
		{
			return JRoute::_($returnUrl . $append, false);
		}
		else
		{
			return JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $append, false);
		}
	}
}
