<?php
/**
 * @package    Redevent.Site
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Frontend easy event submit
 *
 * @package  Redevent.Site
 * @since    3.0
 */
class RedeventViewEasyevent extends RViewSite
{
	/**
	 * Creates the output for event submissions
	 *
	 * @param   string  $tpl  tpl
	 *
	 * @return void
	 *
	 * @since 0.4
	 */
	public function display($tpl=null)
	{
		$app = JFactory::getApplication();

		echo '<pre>'; echo print_r($this, true); echo '</pre>'; exit;

		$this->item = $this->get('Item');
		$this->eventForm = $this->get('eventForm');
		$this->sessionForm = $this->get('sessionForm');
		$this->return = $app->input->get('return');
		$this->customfields = $this->get('Customfields');
		$this->roles = $this->get('SessionRoles');
		$this->prices = $this->get('SessionPrices');
		$this->params = RedeventHelper::config();

		$useracl = RedeventUserAcl::getInstance();

		if (!$useracl->canAddEvent())
		{
			echo JText::_('COM_REDEVENT_EDIT_EVENT_NOT_ALLOWED');

			return;
		}

		$this->canpublish = $useracl->canPublishEvent($this->item->id);

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$subTitle = ' <small>' . JText::_('COM_REDEVENT_NEW') . '</small>';

		if ($this->item->id)
		{
			$subTitle = ' <small>' . JText::_('COM_REDEVENT_EDIT') . '</small>';
		}

		return JText::_('COM_REDEVENT_PAGETITLE_EASY_EVENT_SUBMISSION') . $subTitle;
	}
}
