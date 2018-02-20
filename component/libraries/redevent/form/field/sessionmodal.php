<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008-2014 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

/**
 * Session form field class
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventFormFieldSessionmodal extends JFormField
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'Sessionmodal';

	/**
	 * Method to get the field input markup
	 *
	 * @return string
	 */
	protected function getInput()
	{
		$size  = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : ' size="35"';
		$reset = filter_var((string) $this->element['reset'], FILTER_VALIDATE_BOOLEAN);

		$link = 'index.php?option=com_redevent&amp;view=sessions&amp;layout=element&amp;tmpl=component'
			. '&amp;function=jSelectSession&fieldid=' . $this->id;

		if ($this->element['event'])
		{
			$link .= '&eventid=' . $this->element['event'];
		}

		if ($this->value)
		{
			$title = $this->getSessionTitle($this->value);
		}
		else
		{
			$title = JText::_('LIB_REDEVENT_SELECT_SESSION');
		}

		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		return RedeventLayoutHelper::render(
			'form.fields.session',
			array(
				'link'  => $link,
				'title' => $title,
				'size'  => $size,
				'reset' => $reset,
				'field' => $this
			)
		);
	}

	/**
	 * Get title
	 *
	 * @param   int  $sessionId  session if
	 *
	 * @return string
	 */
	private function getSessionTitle($sessionId)
	{
		$session = RedeventEntitySession::load($sessionId);

		return $session->getFullTitle() . ' @ ' . $session->getVenue()->name . ' - ' . $session->getFormattedStartDate();
	}
}
