<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * View class for email attendees form
 *
 * @package  Redevent.admin
 * @since    2.5
 */
class RedeventViewEmailattendees extends RedeventViewAdmin
{
	public function display($tpl = null)
	{
		$this->emails = $this->get('Emails');
		$this->session = $this->get('Session');
		$this->state = $this->get('State');
		$this->settings = RedeventHelper::config();
		$this->editor = JFactory::getEditor();

		parent::display($tpl);
  }

	/**
	 * Get the page title
	 *
	 * @return  string  The title to display
	 *
	 * @since   0.9.1
	 */
	public function getTitle()
	{
		return JText::sprintf('COM_REDEVENT_EMAIL_ATTENDEES_TITLE', $this->session->title);
	}

	/**
	 * Get the tool-bar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$user = JFactory::getUser();

		$firstGroup		= new RToolbarButtonGroup;

		$firstGroup->addButton(
			RToolbarBuilder::createStandardButton('emailattendees.send', 'COM_REDEVENT_ATTENDEES_TOOLBAR_EMAIL_SEND', 'btn-success', 'icon-envelop', false)
		);
		$firstGroup->addButton(
			RToolbarBuilder::createStandardButton('emailattendees.cancel', 'JCANCEL', 'btn-danger', 'icon-remove', false)
		);

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup);

		return $toolbar;
	}
}
