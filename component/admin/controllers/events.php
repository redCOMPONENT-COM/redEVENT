<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Events Controller
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventControllerEvents extends RControllerAdmin
{
	/**
	 * Logic to archive past sessions and corresponding events if no more active sessions
	 *
	 * @return void
	 */
	public function archivepast()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		$model = $this->getModel('events');

		if (empty($cid))
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			try
			{
				$updated = $model->archivePast($cid);
				$this->setMessage(
					JText::sprintf(
						'COM_REDEVENT_OLD_EVENT_DATE_ARCHIVED_SESSIONS_D_EVENTS_D',
						$updated['sessions'],
						$updated['events']
					)
				);
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}

	/**
	 * Override
	 *
	 * @param   RModelAdmin  $model  The data model object.
	 * @param   array        $cid    The validated data.
	 *
	 * @return  void
	 */
	protected function postDeleteHook(RModelAdmin $model, $cid = null)
	{
		if (!(is_array($cid) && count($cid)))
		{
			return false;
		}

		// Trigger plugins
		foreach ($cid as $id)
		{
			JPluginHelper::importPlugin('redevent');
			JPluginHelper::importPlugin('finder');
			$dispatcher = JDispatcher::getInstance();

			$dispatcher->trigger('onAfterEventRemoved', array($id));

			$obj = new stdclass;
			$obj->id = $id;
			$dispatcher->trigger('onFinderAfterDelete', array('com_redevent.event', $obj));
		}
	}
}
