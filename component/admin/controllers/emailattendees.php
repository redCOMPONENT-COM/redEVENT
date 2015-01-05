<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Email attendees Controller
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventControllerEmailattendees extends JControllerLegacy
{
	/**
	 * Constructor
	 *
	 * @param   array  $config  config
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('emailall', 'email');
	}

	/**
	 * Display email form
	 *
	 * @return void
	 */
	public function email()
	{
		$this->input->set('view', 'emailattendees');

		parent::display();
	}

	/**
	 * Send email
	 *
	 * @return void
	 */
	public function send()
	{
		$subject = $this->input->get('subject', '', 'string');
		$from = $this->input->get('from', '', 'string');
		$fromname = $this->input->get('fromname', '', 'string');
		$replyto  = $this->input->get('replyto', '', 'string');
		$body = $this->input->get('body', '', 'raw');

		$model = $this->getModel('Emailattendees');

		if ($model->send($subject, $body, $from, $fromname, $replyto))
		{
			$this->setMessage(JText::_('COM_REDEVENT_EMAIL_ATTENDEES_SENT'));
		}
		else
		{
			$this->setMessage($model->getError(), 'error');
		}

		$this->setRedirect('index.php?option=com_redevent&view=attendees');
	}

	/**
	 * Cancel sending
	 *
	 * @return void
	 */
	public function cancel()
	{
		$filters = $this->input->get('filter', array(), 'array');

		$this->setRedirect( 'index.php?option=com_redevent&view=attendees&sessionId=' . $filters['session']);
	}
}
