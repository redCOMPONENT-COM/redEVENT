<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * View class for textsnippets list
 *
 * @package  Redevent.admin
 * @since    2.5
 */
class RedeventViewTextsnippetsimport extends RedeventViewAdmin
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
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
		return JText::_('COM_REDEVENT_PAGETITLE_TEXTLIBRARY_IMPORT');
	}

	/**
	 * Get the tool-bar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$user = JFactory::getUser();

		$firstGroup = new RToolbarButtonGroup;

		if ($user->authorise('core.create', 'com_redevent'))
		{
			$new = RToolbarBuilder::createStandardButton('textsnippets.doimport', 'COM_REDEVENT_BUTTON_IMPORT', '', 'icon-save', false);
			$firstGroup->addButton($new);
		}

		$cancel = RToolbarBuilder::createCancelButton('textsnippets.cancelimport');
		$firstGroup->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup);

		return $toolbar;
	}
}
