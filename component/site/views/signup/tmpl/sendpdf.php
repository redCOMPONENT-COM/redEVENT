<?php
/**
 * @version 1.0 $Id: admin.class.php 662 2008-05-09 22:28:53Z schlu $
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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
// set document information
$this->pdf->SetCreator('Axcon');
$this->pdf->SetAuthor("Axcon");
$this->pdf->SetTitle($this->course->title);
$this->pdf->SetSubject($this->course->title);

// set header and footer fonts
$this->pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
//$this->pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$this->pdf->setFont(PDF_FONT_NAME_MAIN);

//set margins
$this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// disable header and footer
$this->pdf->setPrintHeader(false);
$this->pdf->setPrintFooter(false);

//set auto page breaks
$this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set the display mode
$this->pdf->SetDisplayMode('default');

//initialize document
$this->pdf->AliasNbPages();

// add a page
$this->pdf->AddPage();
$this->pdf->SetFontSize(10);

/* Add the logo */
//$this->pdf->Image($this->logo, 5, 5);

/* Add introduction text */
$message = $this->tags->ReplaceTags($this->page);
$this->pdf->WriteHtml($message);

if ($this->course->pdf_form_data) {
	$this->pdf->AddPage();
	/* Include redFORM */
	JRequest::setVar('pdfform', $this->pdf);
	JPluginHelper::importPlugin( 'content' );
	$dispatcher = JDispatcher::getInstance();
	
	$form = new stdClass();
	
	/* TODO: real form */
	$form->text = '{redform}'.$this->course->redform_id.','.$this->course->max_multi_signup.'{/redform}';
	$form->eventid = $course->did;
	$form->task = 'userregister';
	$results = $dispatcher->trigger('PrepareEvent', array($form));
	if (!isset($results[0])) {
		/* Registration not possible redirect the user */
		$mainframe->redirect(JRoute::_('index.php'), JText::_('REGISTRATION_NOT_POSSIBLE') );
	}
	
	$this->pdf = JRequest::getVar('pdfform');
}
?>