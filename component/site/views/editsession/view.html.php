<?php
/**
 * @package    Redevent.Site
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Frontend edit session view
 *
 * @package  Redevent.front
 * @since    0.9
 */
class RedeventViewEditsession extends RViewSite
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl=null)
	{
		$app = JFactory::getApplication();

		$acl = RedeventUserAcl::getInstance();

		$this->item = $this->get('Item');
		$this->form     = $this->get('Form');
		$this->return = $app->input->get('return');
		$this->customfields = $this->get('Customfields');
		$this->roles = $this->get('SessionRoles');
		$this->prices = $this->get('SessionPrices');
		$this->params = RedeventHelper::config();
		$this->canpublish = $acl->canPublishEvent($this->item->eventid);

		$rolesoptions = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_Select_role')));
		$this->rolesoptions = array_merge($rolesoptions, RedeventHelper::getRolesOptions());

		$pricegroupsoptions = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_PRICEGROUPS_SELECT_PRICEGROUP')));
		$this->pricegroupsoptions = array_merge($pricegroupsoptions, RedeventHelper::getPricegroupsOptions());

		$currencyoptions = array(JHTML::_('select.option', '', JText::_('COM_REDEVENT_PRICEGROUPS_SELECT_CURRENCY')));
		$this->currencyoptions = array_merge($currencyoptions, RHelperCurrency::getCurrencyOptions());

		if ($this->item->id && !$acl->canEditXref($this->item->id))
		{
			echo JText::_('COM_REDEVENT_USER_NOT_ALLOWED_TO_EDIT_THIS_SESSION');

			return false;
		}
		elseif (!$this->item->id && !$acl->canAddSession())
		{
			echo JText::_('COM_REDEVENT_USER_NOT_ALLOWED_TO_ADD_SESSION');

			return false;
		}

		// Display the template
		return parent::display($tpl);
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

		return JText::_('COM_REDEVENT_PAGETITLE_EDITSESSION') . $subTitle;
	}
}
