<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Organization edit view
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventViewOrganization extends RedeventViewAdmin
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

		$this->canConfig = false;

		if ($user->authorise('core.admin', 'com_redevent'))
		{
			$this->canConfig = true;
		}

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		if ($this->item->id)
		{
			$title = JText::sprintf('COM_REDEVENT_PAGETITLE_EDIT_ORGANIZATION_S', $this->item->name);
			$title .= ' <small>' . JText::_('COM_REDEVENT_EDIT') . '</small>';

			return $title;
		}
		else
		{
			throw new RuntimeException('You cannot add organization, use synchronize button to import from redMEMBER first');
		}
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;

		$save = RToolbarBuilder::createSaveButton('organization.apply');
		$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('organization.save');
		$saveAndNew = RToolbarBuilder::createSaveAndNewButton('organization.save2new');
		$save2Copy = RToolbarBuilder::createSaveAsCopyButton('organization.save2copy');

		$group->addButton($save)
			->addButton($saveAndClose)
			->addButton($saveAndNew)
			->addButton($save2Copy);

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('organization.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('organization.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
