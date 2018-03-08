<?php
/**
 * @package    Redevent.Admin
 * @copyright  redEVENT (C) 2008-2013 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * View class for the EventList event screen
 *
 * @package  Redevent.Admin
 * @since    0.9
 */
class RedeventViewSession extends RedeventViewAdmin
{
	/**
	 * @var  boolean
	 */
	protected $displaySidebar = false;

	/**
	 * Display the edit page
	 *
	 * @param   string  $tpl  The template file to use
	 *
	 * @return   string
	 */
	public function display($tpl = null)
	{
		$user = JFactory::getUser();

		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->customfields = $this->get('Customfields');
		$this->roles = $this->get('SessionRoles');
		$this->prices = $this->get('SessionPrices');

		$rolesoptions = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_Select_role')));
		$this->rolesoptions = array_merge($rolesoptions, RedeventHelper::getRolesOptions());

		$pricegroupsoptions = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_PRICEGROUPS_SELECT_PRICEGROUP')));
		$this->pricegroupsoptions = array_merge($pricegroupsoptions, RedeventHelper::getPricegroupsOptions());

		$currencyoptions = array(JHTML::_('select.option', '', JText::_('COM_REDEVENT_PRICEGROUPS_SELECT_CURRENCY')));
		$this->currencyoptions = array_merge($currencyoptions, RHelperCurrency::getCurrencyOptions());

		$this->canConfig = false;

		if ($user->authorise('core.admin', 'com_redevent'))
		{
			$this->canConfig = true;
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
			$subTitle = ' <small>' . $this->item->event_title . '</small>';
		}

		return JText::_('COM_REDEVENT_PAGETITLE_EDITSESSION') . $subTitle;
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;

		$save = RToolbarBuilder::createSaveButton('session.apply');
		$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('session.save');
		$saveAndNew = RToolbarBuilder::createSaveAndNewButton('session.save2new');
		$save2Copy = RToolbarBuilder::createSaveAsCopyButton('session.save2copy');

		$group->addButton($save)
			->addButton($saveAndClose)
			->addButton($saveAndNew)
			->addButton($save2Copy);

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('session.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('session.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		$this->toolbar = $toolbar;

		return parent::getToolbar();
	}
}
