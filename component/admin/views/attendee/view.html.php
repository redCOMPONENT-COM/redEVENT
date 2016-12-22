<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * View class for the edit attendee screen
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventViewAttendee extends RedeventViewAdmin
{
	/**
	 * Display
	 *
	 * @param   string  $tpl  template
	 *
	 * @return mixed|void
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();

		$row = $this->get('data');

		// Make data safe
		JFilterOutput::objectHTMLSafe($row);

		// Create selectlists
		$lists = array();

		// User list
		$lists['user'] = JHTML::_('list.users', 'uid', $row->uid, 1, null, 'name', 0);

		$sessionpricegroups = $this->get('Pricegroups');

		$field = new RedeventRfieldSessionprice;
		$field->setOptions($sessionpricegroups);

		if ($row->sessionpricegroup_id)
		{
			$field->setValue($row->sessionpricegroup_id);
		}

		$extrafields = array(1 => array($field));

		$this->row = $row;
		$this->session = $this->get('Session');
		$this->extrafields = $extrafields;
		$this->lists = $lists;
		$this->returnUrl = $app->input->get('return');

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return JText::_('COM_REDEVENT_PAGETITLE_EDITATTENDEE');
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;

		$save = RToolbarBuilder::createSaveButton('attendee.apply');
		$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('attendee.save');

		$group->addButton($save)
			->addButton($saveAndClose);

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('attendee.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('attendee.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		$this->toolbar = $toolbar;

		return parent::getToolbar();
	}
}
