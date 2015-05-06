<?php
/**
 * @package    Redevent.Site
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Frontend edit event view
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewEditevent extends RViewSite
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

		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
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

		return JText::_('COM_REDEVENT_PAGETITLE_EDITEVENT') . $subTitle;
	}
}
