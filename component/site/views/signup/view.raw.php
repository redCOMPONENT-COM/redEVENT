<?php
/**
 * @version		$Id$
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
		
		/* Load the event details */
		$course = $this->get('Details');
		$venue = $this->get('Venue');
		
		/* This loads the tags replacer */
		JView::loadHelper('tags');
		$tags = new redEVENT_tags;
		$this->assignRef('tags', $tags);
		
		/* Load the view */
		$this->assignRef('page', $course->submission_type_email_pdf);
		
		/*
		 * Setup external configuration options
		 */
		define('K_TCPDF_EXTERNAL_CONFIG', true);

		/*
		 * Path options
		 */

		// Installation path
		define("K_PATH_MAIN", JPATH_LIBRARIES.DS."tcpdf");

		// URL path
		define("K_PATH_URL", JPATH_BASE);

		// Fonts path
		define("K_PATH_FONTS", JPATH_SITE.DS.'language'.DS."pdf_fonts".DS);

		// Cache directory path
		define("K_PATH_CACHE", K_PATH_MAIN.DS."cache");

		// Cache URL path
		define("K_PATH_URL_CACHE", K_PATH_URL.DS."cache");

		// Images path
		define("K_PATH_IMAGES", K_PATH_MAIN.DS."images");

		// Blank image path
		define("K_BLANK_IMAGE", K_PATH_IMAGES.DS."_blank.png");

		/*
		 * Format options
		 */

		// Cell height ratio
		define("K_CELL_HEIGHT_RATIO", 1.25);

		// Magnification scale for titles
		define("K_TITLE_MAGNIFICATION", 1.3);

		// Reduction scale for small font
		define("K_SMALL_RATIO", 2/3);

		// Magnication scale for head
		define("HEAD_MAGNIFICATION", 1.1);
		define("PDF_FONT_SIZE_MAIN", 8);
		define("PDF_FONT_NAME_MAIN", "Freesans");
		
		define("PDF_MARGIN_LEFT", 5);
		define("PDF_MARGIN_TOP", 5);
		define("PDF_MARGIN_RIGHT", 5);
		define("PDF_MARGIN_BOTTOM", 5);
		define("PDF_MARGIN_HEADER", 5);
		define("PDF_MARGIN_FOOTER", 5);
		/*
		 * Create the pdf document
		 */

		jimport('tcpdf.tcpdf');
		$pdf = new TCPDF();
		
		$this->assignRef('pdf', $pdf);
		$this->assignRef('course', $course);
		$this->assignRef('venue', $venue);
		
		/* Display it all*/
		parent::display($tpl);
		
		while( @ob_end_clean() );
		header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		
		header( "Content-Type: application/pdf" );
		// header( 'Content-Length: '. filesize( $file ) );
		header( 'Content-Disposition: attachment; filename="signup.pdf";' );
		header( 'Content-Transfer-Encoding: Binary' );
		
		//Close and output PDF document
		$pdf->Output("signup.pdf", "I");
	}
}
?>