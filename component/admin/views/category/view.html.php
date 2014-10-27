<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Category edit view
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventViewCategory extends RedeventViewAdmin
{
	/**
	 * @var  boolean
	 */
	protected $displaySidebar = false;

	/**
	 * Display the category edit page
	 *
	 * @param   string  $tpl  The template file to use
	 *
	 * @return   string
	 */
	public function display($tpl = null)
	{
		$user = JFactory::getUser();

		$this->form	= $this->get('Form');
		$this->item	= $this->get('Item');

		// Attachments
		JHtml::_('behavior.framework');
		RHelperAsset::load('attachments.js');
		JText::script('COM_REDEVENT_ATTACHMENT_CONFIRM_MSG');

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
		$subTitle = ' <small>' . JText::_('COM_REDEVENT_NEW') . '</small>';

		if ($this->item->id)
		{
			$subTitle = ' <small>' . JText::_('COM_REDEVENT_EDIT') . '</small>';
		}

		return JText::_('COM_REDEVENT_PAGETITLE_EDITCATEGORY') . $subTitle;
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @todo	We have setup ACL requirements for redMEMBER
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;

		$save = RToolbarBuilder::createSaveButton('category.apply');
		$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('category.save');
		$saveAndNew = RToolbarBuilder::createSaveAndNewButton('category.save2new');
		$save2Copy = RToolbarBuilder::createSaveAsCopyButton('category.save2copy');

		$group->addButton($save)
			->addButton($saveAndClose)
			->addButton($saveAndNew)
			->addButton($save2Copy);

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('category.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('category.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
