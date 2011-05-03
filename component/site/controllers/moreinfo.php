<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * redEVENT Component moreinfo Controller
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 2.0
 */
class RedEventControllerMoreinfo extends RedEventController
{
	/**
	 * Constructor
	 *
	 * @since 2.0
	 */
	function __construct() {
		parent::__construct();
	}
	
	function submitinfo()
	{
		jimport('joomla.mail.helper');
		
		$app = &JFactory::getApplication();
		
		$xref = JRequest::getInt('xref');
		$email = JRequest::getVar('email');
		
		$model = $this->getModel('details');
		$details = $model->getDetails();
		
		if ($xref && $email && JMailHelper::isEmailAddress($email))
		{			
			$mailer = &JFactory::getMailer();
			$mailer->IsHTML(true);
			
			$mailer->setSubject(JText::sprintf('COM_REDEVENT_MOREINFO_MAIL_SUBJECT', $details->full_title));
			$mailer->AddAddress($app->getCfg('mailfrom'), $app->getCfg('sitename'));
			
			$mailer->AddReplyTo(array($email, JRequest::getVar('name')));
			
			$data = array();
			if ($d = JRequest::getVar('name')) {
				$data[] = array(Jtext::_('COM_REDEVENT_MOREINFO_LABEL_NAME'), $d);
			}
			if ($d = JRequest::getVar('email')) {
				$data[] = array(Jtext::_('COM_REDEVENT_MOREINFO_LABEL_EMAIL'), $d);
			}
			if ($d = JRequest::getVar('company')) {
				$data[] = array(Jtext::_('COM_REDEVENT_MOREINFO_LABEL_COMPANY'), $d);
			}
			if ($d = JRequest::getVar('phonenumber')) {
				$data[] = array(Jtext::_('COM_REDEVENT_MOREINFO_LABEL_PHONENUMBER'), $d);
			}
			if ($d = JRequest::getVar('comments')) {
				$data[] = array(Jtext::_('COM_REDEVENT_MOREINFO_LABEL_COMMENTS'), str_replace("\n", "<br/>", $d));
			}
			
			$table = '<table>';
			foreach ($data as $d)
			{
				$table .= '<tr><td>'.$d[0].'</td><td>'.$d[1].'</td></tr>';
			}
			$table .= '</table>';
			
			$link = JRoute::_(JURI::base().RedeventHelperRoute::getDetailsRoute($details->did, $details->xslug));
			$link = JHTML::link($link, $details->full_title);
			
			$body = JText::sprintf('COM_REDEVENT_MOREINFO_MAIL_BODY', $link, $table);
			
			$mailer->setBody($body);
			$mailer->send();
		}
		
		// confirm sending
		JRequest::setVar('view', 'moreinfo');
		Jrequest::setVar('layout', 'final');
		$this->display();
	}
}
