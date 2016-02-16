<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * HTML Article View class for the Content component
 *
 * @package  Redevent.Site
 * @since    2.5
 */
class RedeventViewSignup extends RViewSite
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();

		$dispatcher = JDispatcher::getInstance();

		/* Load the event details */
		$course = $this->get('Details');
		$venue = $this->get('Venue');

		$pdf = new TCPDF("P", "mm", "A4", true);
		$pdf->SetCreator($mainframe->getCfg('sitename'));
		$pdf->SetAuthor($mainframe->getCfg('sitename'));
		$pdf->SetTitle($course->title);
		$pdf->SetSubject($course->title);

		$pdf->setHeaderFont(Array('freesans', '', 8));
		$pdf->setFooterFont(Array('freesans', '', 8));
		$pdf->setFont('freesans');

		// Disable header and footer
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(true);

		// Set the display mode
		$pdf->SetDisplayMode('default');

		// Initialize document
		$pdf->AliasNbPages();

		// Add a page
		$pdf->AddPage();
		$pdf->SetFontSize(10);

		/* This loads the tags replacer */
		$tags = new RedeventTags;
		$tags->setXref(JRequest::getInt('xref'));

		$message = $tags->replaceTags($course->submission_type_email_pdf);

		// Convert urls
		$htmlmsg = RedeventHelperOutput::ImgRelAbs($message);
		$pdf->WriteHTML($message, true);

		// Add the form data if requested
		if ($course->pdf_form_data)
		{
			JRequest::setVar('pdfform', $pdf);
			JPluginHelper::importPlugin('content');

			$form = new stdClass;
			$form->text = '{redform}' . $course->redform_id . ',1{/redform}';
			$form->eventid = $course->did;
			$form->task = 'userregister';
			$results = $dispatcher->trigger('onPrepareEvent', array(& $form, array(), 0));

			$pdf->WriteHTML($form->text, true);
		}

		// Output the file
		$pdf->Output($course->title . ".pdf", "I");
		$mainframe->close();
	}
}
