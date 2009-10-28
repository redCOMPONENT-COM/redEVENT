<?php
/**
 * @version		$Id: view.raw.php 30 2009-05-08 10:22:21Z roland $
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML Article View class for the Content component
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class RedeventViewSignup extends JView
{        
	function display($tpl = null)
	{
		global $mainframe;
		
		$dispatcher	=& JDispatcher::getInstance();
		
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
    
    // disable header and footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    
    //set the display mode
    $pdf->SetDisplayMode('default');
    
    //initialize document
    $pdf->AliasNbPages();
    
    // add a page
    $pdf->AddPage();
    $pdf->SetFontSize(10);

    /* This loads the tags replacer */
		JView::loadHelper('tags');
		$tags = new redEVENT_tags();
		
    $message = $tags->ReplaceTags($course->submission_type_email_pdf);
    $pdf->WriteHTML($message, true);
    
    // add the form data if requested
    if ($course->pdf_form_data) 
    {
    	JRequest::setVar('pdfform', $pdf);
    	JPluginHelper::importPlugin('content');
    	
    	$form = new stdClass();    	
    	$form->text = '{redform}'.$course->redform_id.',1{/redform}';
    	$form->eventid = $course->did;
    	$form->task = 'userregister';
  		$results = $dispatcher->trigger('onPrepareEvent', array(& $form, array(), 0));
  		
      $pdf->WriteHTML($form->text, true);
    }
    // output the file
    $pdf->Output($course->title .".pdf", "I");
    exit;     
	}
}
?>