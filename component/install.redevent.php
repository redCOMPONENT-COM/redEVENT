<?php
/**
 * @version 2.0
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 - 2010 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
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

function upgrade1_2()
{
	$db = JFactory::getDBO();
	
	$tables = array( '#__redevent_events', );
	$tables = $db->getTableFields($tables, false);
	
	/** 
	 * event table
	 */
	$cols = $tables['#__redevent_events'];
	
	/* Check if an upgrade is needed */
	if (!array_key_exists('locid', $cols)) {
		return true;
	}
		
	/* 1. Make backup copies */
	$q = "CREATE TABLE IF NOT EXISTS #__redevent_events_bak_v1 SELECT * FROM #__redevent_events";
	$db->setQuery($q);
	$db->query();
	
	$q = "CREATE TABLE IF NOT EXISTS #__redevent_register_bak_v1 SELECT * FROM #__redevent_events";
	$db->setQuery($q);
	$db->query();
	
	/* 2. Copy events to the xref table */
	$q = "INSERT INTO #__redevent_event_venue_xref (eventid, venueid, dates, enddates, times, endtimes, maxattendees, maxwaitinglist, published) "
	   . "            SELECT id, locid, dates, enddates, times, endtimes, maxattendees, maxwaitinglist, published FROM #__redevent_events";
	$db->setQuery($q);
	$db->query();
	
	/* 3. Remove the columns from the events table */
	$q = "ALTER TABLE `#__redevent_events`
	  DROP `locid`,
	  DROP `dates`,
	  DROP `enddates`,
	  DROP `times`,
	  DROP `endtimes`,
	  DROP `maxattendees`,
	  DROP `maxwaitinglist`;";
	$db->setQuery($q);
	$db->query();
	
	/* 4. Register table */
	/* The submitter_id becomes sid */
	$q = "ALTER TABLE `#__redevent_register` CHANGE `submitter_id` `sid` INT NOT NULL";
	$db->setQuery($q);
	$db->query();
	
	/* waiting becomes waitinglist */
	$q = "ALTER TABLE `#__redevent_register` CHANGE `waiting` `waitinglist` TINYINT NOT NULL";
	$db->setQuery($q);
	$db->query();
	
	/* new column submit_key */
	$q = "ALTER TABLE `#__redevent_register` ADD `submit_key` VARCHAR(45) NOT NULL";
	$db->setQuery($q);
	$db->query();
	
	$q = ' UPDATE `#__redevent_register` AS r '
	   . ' INNER JOIN `#__rwf_submitters` AS s ON s.id = r.sid '
	   . ' SET r.submit_key = s.submit_key ' 
	   ;
	$db->setQuery($q);
	$db->query();
	
	/* event becomes xref */
	$q = "ALTER TABLE `#__redevent_register` CHANGE `event` `xref` INT( 11 ) NULL DEFAULT NULL";
	$db->setQuery($q);
	$db->query();
			
	/* Update the register table */
	$q = " UPDATE #__redevent_register AS r "
	   . " INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = r.xref "
	   . " SET r.xref = x.id "
	   ;
	$db->setQuery($q);
	$db->query();
	
	return true;
}

/**
 * Executes additional installation processes
 *
 * @since 0.1
 */
function com_install() {

jimport( 'joomla.filesystem.folder' );

// check upgrade from 1.0 to 2.0
upgrade1_2();

/**
 * get tables details
 */
$db = JFactory::getDBO();
$tables = array( '#__redevent_events', 
                 '#__redevent_venues',
                 '#__redevent_venues_categories',
                 '#__redevent_settings',
                 '#__redevent_event_venue_xref',
                 '#__redevent_register',
                 '#__redevent_categories',
                 '#__redevent_fields',
                 '#__redevent_groupmembers',
                 '#__redevent_groups',
                 '#__redevent_repeats',
               );
$tables = $db->getTableFields($tables, false);

/** 
 * event table
 */
$cols = $tables['#__redevent_events'];

if (is_array($cols)) {
		
	/* Check if we have the showfields column */
	if (!array_key_exists('showfields', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN showfields TEXT";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the show_waitinglist column */
	if (array_key_exists('show_waitinglist', $cols)) {
    $q = "ALTER IGNORE TABLE `#__redevent_events` DROP `show_waitinglist`";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the show_attendants column */
	if (array_key_exists('show_attendants', $cols)) {
		$q = "ALTER IGNORE TABLE `#__redevent_events` DROP `show_attendants`";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the confirmation_message column */
	if (!array_key_exists('confirmation_message', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `confirmation_message` TEXT";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the review_message column */
	if (!array_key_exists('review_message', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `review_message` TEXT";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the submission_type_email_pdf column */
	if (!array_key_exists('submission_type_email_pdf', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `submission_type_email_pdf` TEXT NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the submission_type_formal_offer_pdf column */
	if (!array_key_exists('submission_type_formal_offer_pdf', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `submission_type_formal_offer_pdf` TEXT NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the submission_types column */
	if (!array_key_exists('submission_types', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `submission_types` varchar(255) default 'email'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the course_code column */
	if (!array_key_exists('course_code', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `course_code` varchar(255) NOT NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the course_credit column */
	if (!array_key_exists('course_credit', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `course_credit` int(11) NOT NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the max_multi_signup column */
	if (!array_key_exists('max_multi_signup', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `max_multi_signup` int(2) unsigned NOT NULL default '1'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the submission_type_external column */
	if (!array_key_exists('submission_type_external', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `submission_type_external` varchar(255) NOT NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the submission_type_email_subject column */
	if (!array_key_exists('submission_type_email_subject', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `submission_type_email_subject` varchar(75) default NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the submission_type_email column */
	if (!array_key_exists('submission_type_email', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `submission_type_email` text";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the submission_type_webform column */
	if (!array_key_exists('submission_type_webform', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `submission_type_webform` text";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the submission_type_phone column */
	if (!array_key_exists('submission_type_phone', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `submission_type_phone` text";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the submission_type_formal_offer column */
	if (!array_key_exists('submission_type_formal_offer', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `submission_type_formal_offer` text";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the submission_type_formal_offer_subject column */
	if (!array_key_exists('submission_type_formal_offer_subject', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `submission_type_formal_offer_subject` varchar(75) default NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the submission_type_email_body column */
	if (!array_key_exists('submission_type_email_body', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `submission_type_email_body` text";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the send_pdf_form column */
	if (!array_key_exists('send_pdf_form', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `send_pdf_form` tinyint(1) NOT NULL default '0'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the pdf_form_data column */
	if (!array_key_exists('pdf_form_data', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `pdf_form_data` tinyint(1) NOT NULL default '0'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the submission_type_formal_offer_body column */
	if (!array_key_exists('submission_type_formal_offer_body', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `submission_type_formal_offer_body` text";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the submission_type_webform_formal_offer column */
	if (!array_key_exists('submission_type_webform_formal_offer', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `submission_type_webform_formal_offer` text";
		$db->setQuery($q);
		$db->query();
	}
	
  /* Check if we have the show_submission_type_webform_formal_offer column */
  if (!array_key_exists('show_submission_type_webform_formal_offer', $cols)) {
    $q = "ALTER IGNORE TABLE `#__redevent_events` ADD `show_submission_type_webform_formal_offer` TINYINT( 2 ) NOT NULL DEFAULT '0' AFTER `pdf_form_data`";
    $db->setQuery($q);
    $db->query();
  }	
	
  /* Check if we have the paymentaccepted column */
  if (!array_key_exists('paymentaccepted', $cols)) {
    $q = "ALTER IGNORE TABLE `#__redevent_events` ADD `paymentaccepted` text DEFAULT NULL";
    $db->setQuery($q);
    $db->query();
    $q = "ALTER IGNORE TABLE `#__redevent_events` ADD `paymentprocessing` text DEFAULT NULL";
    $db->setQuery($q);
    $db->query();
  }	
	
  /* Check if we have the summary column */
  if (!array_key_exists('summary', $cols)) {
    $q = "ALTER IGNORE TABLE `#__redevent_events` ADD `summary` MEDIUMTEXT NOT NULL AFTER `created`";
    $db->setQuery($q);
    $db->query();
  }	
	
	/* Check if we have the course_credit column */
	if (!array_key_exists('details_layout', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `details_layout` tinyint(2) NOT NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the enable_ical */
	if (!array_key_exists('enable_ical', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_events ADD COLUMN `enable_ical` tinyint(2) NOT NULL default '0'";
		$db->setQuery($q);
		$db->query();
	}
}


/* Get the venues table columns */
$cols = $tables['#__redevent_venues'];

if (is_array($cols)) 
{
  /* Check if we have the latitude / longitude columns */
  if (!array_key_exists('latitude', $cols)) 
  {
    $q = "ALTER IGNORE TABLE #__redevent_venues ADD COLUMN `latitude` float default NULL after `country`, ADD COLUMN `longitude` float default NULL after `latitude`";
    $db->setQuery($q);
    $db->query();
  }  
  
  /* category event template */
  if (!array_key_exists('private', $cols)) {
    $q = "ALTER IGNORE TABLE #__redevent_venues ADD COLUMN `private` TINYINT(1) NOT NULL DEFAULT 0";
    $db->setQuery($q);
    $db->query();    
  }
}

/* Get the venues categories table columns */
$cols = $tables['#__redevent_venues_categories'];

if (is_array($cols)) 
{  
  /* category event template */
  if (!array_key_exists('private', $cols)) {
    $q = "ALTER IGNORE TABLE #__redevent_venues_categories ADD COLUMN `private` TINYINT(1) NOT NULL DEFAULT 0";
    $db->setQuery($q);
    $db->query();    
  }
}

/* Get the current columns */
$cols = $tables['#__redevent_settings'];

if (is_array($cols)) {
	/* Check if we have the defaultredformid column */
	if (!array_key_exists('defaultredformid', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_settings ADD COLUMN defaultredformid INT(11) NOT NULL default '1'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the currency_decimals column */
	if (!array_key_exists('currency_decimals', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_settings ADD COLUMN `currency_decimals` varchar(10) default 'decimals'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the currency_decimal_separator column */
	if (!array_key_exists('currency_decimal_separator', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_settings ADD COLUMN `currency_decimal_separator` varchar(1) default ','";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the currency_thousand_separator column */
	if (!array_key_exists('currency_thousand_separator', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_settings ADD COLUMN `currency_thousand_separator` varchar(1) default '.'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the signup_external_text column */
	if (!array_key_exists('signup_external_text', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_settings ADD COLUMN `signup_external_text` VARCHAR( 50 ) NOT NULL DEFAULT 'SIGNUP_EXTERNAL'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the signup_external_img column */
	if (!array_key_exists('signup_external_img', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_settings ADD COLUMN `signup_external_img` VARCHAR( 50 ) NOT NULL DEFAULT 'external_icon.png'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the signup_webform_text column */
	if (!array_key_exists('signup_webform_text', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_settings ADD COLUMN `signup_webform_text` VARCHAR( 50 ) NOT NULL DEFAULT 'SIGNUP_WEBFORM'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the signup_webform_img column */
	if (!array_key_exists('signup_webform_img', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_settings ADD COLUMN `signup_webform_img` VARCHAR( 50 ) NOT NULL DEFAULT 'form_icon.png'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the signup_email_text column */
	if (!array_key_exists('signup_email_text', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_settings ADD COLUMN `signup_email_text` VARCHAR( 50 ) NOT NULL DEFAULT 'SIGNUP_EMAIL'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the signup_email_img column */
	if (!array_key_exists('signup_email_img', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_settings ADD COLUMN `signup_email_img` VARCHAR( 50 ) NOT NULL DEFAULT 'email_icon.png'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the signup_formal_offer_text column */
	if (!array_key_exists('signup_formal_offer_text', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_settings ADD COLUMN `signup_formal_offer_text` VARCHAR( 50 ) NOT NULL DEFAULT 'SIGNUP_FORMAL_OFFER'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the signup_formal_offer_img column */
	if (!array_key_exists('signup_formal_offer_img', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_settings ADD COLUMN `signup_formal_offer_img` VARCHAR( 50 ) NOT NULL DEFAULT 'formal_icon.png'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the signup_phone_text column */
	if (!array_key_exists('signup_phone_text', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_settings ADD COLUMN `signup_phone_text` VARCHAR( 50 ) NOT NULL DEFAULT 'SIGNUP_PHONE'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the signup_phone_img column */
	if (!array_key_exists('signup_phone_img', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_settings ADD COLUMN `signup_phone_img` VARCHAR( 50 ) NOT NULL DEFAULT 'phone_icon.png'";
		$db->setQuery($q);
		$db->query();
	}
}

/* Get the current columns */
$cols = $tables['#__redevent_event_venue_xref'];

if (is_array($cols)) {
	/* Check if we have the published column */
	if (!array_key_exists('published', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_event_venue_xref ADD COLUMN `published` TINYINT(1) NOT NULL DEFAULT 0";
		$db->setQuery($q);
		$db->query();
		
		/* Make all events published */
		$q = "UPDATE #__redevent_event_venue_xref SET published = 1";
		$db->setQuery($q);
		$db->query();
	}
	
	if (!array_key_exists('course_credit', $cols)) {
		$q = "ALTER IGNORE TABLE #__redevent_event_venue_xref ADD COLUMN `course_credit` INT(11) NOT NULL";
		$db->setQuery($q);
		$db->query();
	}
		
  if (!array_key_exists('details', $cols)) {
    $q ="ALTER IGNORE TABLE `#__redevent_event_venue_xref` ADD COLUMN `details` TEXT NOT NULL AFTER `endtimes`";
    $db->setQuery($q);
    $db->query();
  }

  if (stristr($cols['dates']->Null, 'no')) {
    $q = "ALTER TABLE `#__redevent_event_venue_xref` CHANGE `dates` `dates` DATE NULL DEFAULT NULL";
    $db->setQuery($q);
    $db->query();
  }

  if (!array_key_exists('registrationend', $cols)) {
    $q ="ALTER IGNORE TABLE `#__redevent_event_venue_xref` ADD COLUMN `registrationend` DATETIME NULL AFTER `endtimes`";
    $db->setQuery($q);
    $db->query();
  }
  
  if (!array_key_exists('groupid', $cols)) {
    $q ="ALTER IGNORE TABLE `#__redevent_event_venue_xref` ADD COLUMN `groupid` INT(11) NOT NULL DEFAULT '0' AFTER `venueid`";
    $db->setQuery($q);
    $db->query();
  }
  
  if (!array_key_exists('note', $cols)) {
    $q ="ALTER IGNORE TABLE `#__redevent_event_venue_xref` ADD COLUMN `note` VARCHAR(50) NULL DEFAULT NULL AFTER `registrationend`";
    $db->setQuery($q);
    $db->query();
  }
  
  if (!array_key_exists('external_registration_url', $cols)) {
    $q ="ALTER IGNORE TABLE `#__redevent_event_venue_xref` ADD COLUMN `external_registration_url` VARCHAR(255) NULL DEFAULT NULL";
    $db->setQuery($q);
    $db->query();
  }
  
  /** add indexes **/
  if (empty($cols['eventid']->Key)) {
    $q = "ALTER TABLE `#__redevent_event_venue_xref` ADD INDEX (`eventid`)";
    $db->setQuery($q);
    $db->query();  	
  }
  
  if (empty($cols['venueid']->Key)) {
    $q = "ALTER TABLE `#__redevent_event_venue_xref` ADD INDEX (`venueid`)";
    $db->setQuery($q);
    $db->query();  	
  }
  
  if (empty($cols['groupid']->Key)) {
    $q = "ALTER TABLE `#__redevent_event_venue_xref` ADD INDEX (`groupid`)";
    $db->setQuery($q);
    $db->query();  	
  }
  
  if (!array_key_exists('featured', $cols)) {
    $q ="ALTER IGNORE TABLE `#__redevent_event_venue_xref` ADD COLUMN `featured` tinyint(1) NOT NULL default '0'";
    $db->setQuery($q);
    $db->query();
  }
  
  if (!array_key_exists('icaldetails', $cols)) {
    $q ="ALTER IGNORE TABLE `#__redevent_event_venue_xref` ADD COLUMN `icaldetails` text NOT NULL";
    $db->setQuery($q);
    $db->query();
  }
}

/* register table */
$cols = $tables['#__redevent_register'];

if (is_array($cols)) 
{
  if (isset($cols['submit_key']) && !stristr($cols['submit_key']->Type, 'varchar')) {
  	$q = "ALTER TABLE `#__redevent_register` CHANGE `submit_key` `submit_key` VARCHAR( 45 ) NULL DEFAULT NULL";
  	$db->setQuery($q);
  	$db->query();
  }
  
  if (!array_key_exists('sid', $cols)) 
  {
    	//TODO: we must convert existing registrations !
    	//first, backup the table
    	$back_post = strftime('%Y%m%d%H%M');
    	$query = ' RENAME TABLE `#__redevent_register`  TO `#__redevent_register_back_'.$back_post.'` ';
    	$db->setQuery($query);
	    if ($db->query())
	    {	    	
	    	// recreate table register
	    	$query = ' CREATE TABLE #__redevent_register LIKE #__redevent_register_back_'.$back_post.' ';
	    	$db->setQuery($query);
		    if ($db->query())
		    {
		    	$res = true;
			  	$query = " ALTER IGNORE TABLE #__redevent_register "
			  	       . "      ADD COLUMN `sid` int(11) NOT NULL default '0' ";
		    	$db->setQuery($query);
			    
			    if (!$db->query()) {
			    	$res = false;
			    }
			    
			  	if (!array_key_exists('waitinglist', $cols)) 
			  	{
				  	$query = " ALTER IGNORE TABLE #__redevent_register "
				  	   . "      ADD COLUMN `waitinglist` tinyint(2) NOT NULL default '0' ";
			    	$db->setQuery($query);
				    if (!$db->query()) {
				    	$res = false;
				    }
			    }
			    
			    
			  	if (!array_key_exists('confirmed', $cols)) 
			  	{		  	
				  	$query = " ALTER IGNORE TABLE #__redevent_register "
				  	   . "      ADD COLUMN `confirmed` tinyint(2) NOT NULL default '0' ";
			    	$db->setQuery($query);
				    if (!$db->query()) {
				    	$res = false;
				    }
			    }			    
			    
			  	if (!array_key_exists('confirmdate', $cols)) 
			  	{
				  	$query = " ALTER IGNORE TABLE #__redevent_register "
				  	   . "      ADD COLUMN `confirmdate` datetime NULL default NULL ";
			    	$db->setQuery($query);
				    if (!$db->query()) {
				    	$res = false;
				    }
			    }
			  	
			    if ($res)
			    {
			    	// insert records
			    	$query = ' INSERT INTO #__redevent_register (xref, uid, sid, waitinglist, confirmed, confirmdate, uregdate, uip, submit_key) '
			    	       . ' SELECT rb.xref, rb.uid, s.id AS sid, s.waitinglist, s.confirmed, s.confirmdate, rb.uregdate, rb.uip, rb.submit_key '
			    	       . ' FROM #__redevent_register_back_'.$back_post.' AS rb '
			    	       . ' INNER JOIN #__rwf_submitters AS s ON rb.submit_key = s.submit_key '
			    	       ;
				    $db->setQuery($query);
				    if ($db->query())
				    {
			    		echo JText::_('converted attendees table to new structure');				    	
				    }
				    else {
				    	echo JText::_('failed importing attendees to new structure').$db->getErrorMsg();
				    	$error = true;
				    }			    	 
			    }
			    else {
			    	echo JText::_('failed inserting new fields in register table');
			    	$error = true;
			    }
		    }
		    else {
		    	echo JText::_('failed recreating register table');
		    	$error = true;
		    }
	    }
	    else {
	    	echo JText::_('register table backup failed');
	    	$error = true;
		}
	}
  
  if (!array_key_exists('checked_out', $cols)) {
    $q = ' ALTER TABLE `#__redevent_register` '
       . '   ADD `checked_out` int(11) NOT NULL default "0" ,'
       . '   ADD `checked_out_time` datetime NOT NULL default "0000-00-00 00:00:00"'
       ;
    $db->setQuery($q);
    $db->query();    
  }
  
  /** add indexes **/
  if (empty($cols['xref']->Key)) {
    $q = "ALTER TABLE `#__redevent_register` ADD INDEX (`xref`)";
    $db->setQuery($q);
    $db->query();  	
  }
  
  if (empty($cols['submit_key']->Key)) {
    $q = "ALTER TABLE `#__redevent_register` ADD INDEX (`submit_key`)";
    $db->setQuery($q);
    $db->query();  	
  }
  
  if (empty($cols['sid']->Key)) {
    $q = "ALTER TABLE `#__redevent_register` ADD INDEX (`sid`)";
    $db->setQuery($q);
    $db->query();  	
  }
  
  if (!array_key_exists('pricegroup_id', $cols)) 
  {
    $q = ' ALTER TABLE `#__redevent_register` '
       . '   ADD `pricegroup_id` int(11) NOT NULL default "0" '
       ;
    $db->setQuery($q);
    $db->query();    
    
    $q = "ALTER TABLE `#__redevent_register` ADD INDEX (`pricegroup_id`)";
    $db->setQuery($q);
    $db->query(); 

    // need to convert course_price to use pricegroups and sessions_pricegroups
    // create first a dummy pricegroup
    $q = ' INSERT INTO `#__redevent_pricegroups` (name, alias) VALUES ('.$db->Quote('unique').', '.$db->Quote('unique').') ';
    $db->setQuery($q);
    $db->query();    
    $id = $db->insertid();
    
    $q = ' INSERT INTO `#__redevent_sessions_pricegroups` (xref, price, pricegroup_id) '
       . ' SELECT id, course_price, '.$id
       . ' FROM `#__redevent_event_venue_xref` WHERE course_price > 0 '
       ;
    $db->setQuery($q);
    $db->query();    
  }
}

/* Get the categories columns */
$cols = $tables['#__redevent_categories'];

if (is_array($cols)) {
  
  /* multiple / hierarchical categories upgrade */
  if (!array_key_exists('lft', $cols)) {
  	
  	// this revision added the multiple categories: we need to add the lft and rgt fields, and convert catsid field to new table
  	    
  	$q = "ALTER IGNORE TABLE #__redevent_categories ADD COLUMN `lft` TINYINT(1) NOT NULL DEFAULT 0";
    $db->setQuery($q);
    $db->query();
        
    $q = "ALTER IGNORE TABLE #__redevent_categories ADD COLUMN `rgt` TINYINT(1) NOT NULL DEFAULT 0";
    $db->setQuery($q);
    $db->query();
    
    //Then, we'll need to rebuild the category tree
    require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_redevent'.DS.'tables'.DS.'redevent_categories.php');
    $table = & JTable::getInstance('RedEvent_categories', '');
    $table->rebuildTree();
    
    /* copy all event-category relationship to new table */
    $q = "INSERT IGNORE INTO #__redevent_event_category_xref (event_id, category_id) SELECT e.id, e.catsid FROM #__redevent_events AS e";
    $db->setQuery($q);
    $db->query();
  }  
  
  /* category color */
  if (!array_key_exists('color', $cols)) {
    $q = "ALTER IGNORE TABLE #__redevent_categories ADD COLUMN `color` VARCHAR(100) NOT NULL DEFAULT ''";
    $db->setQuery($q);
    $db->query();    
  }
  
  /* category event template */
  if (!array_key_exists('event_template', $cols)) {
    $q = "ALTER IGNORE TABLE #__redevent_categories ADD COLUMN `event_template` INT(11) NOT NULL DEFAULT 0";
    $db->setQuery($q);
    $db->query();    
  }
  
  /* category event template */
  if (!array_key_exists('private', $cols)) {
    $q = "ALTER IGNORE TABLE #__redevent_categories ADD COLUMN `private` TINYINT(1) NOT NULL DEFAULT 0";
    $db->setQuery($q);
    $db->query();    
  }
}

/* Get the custom fields columns */
$cols = $tables['#__redevent_fields'];

if (is_array($cols)) 
{  
	/* show in lists ? */
  if (!array_key_exists('in_lists', $cols)) {
    $q = "ALTER IGNORE TABLE #__redevent_fields ADD COLUMN `in_lists` TINYINT(1) NOT NULL DEFAULT '0' AFTER `searchable`";
    $db->setQuery($q);
    $db->query();    
  }	
  
	/* show in lists ? */
  if (array_key_exists('visible', $cols)) {
    $q = "ALTER IGNORE TABLE #__redevent_fields CHANGE `visible` `frontend_edit` TINYINT( 1 ) NULL DEFAULT '0'";
    $db->setQuery($q);
    $db->query();    
  }	
}

/* Get the group members fields columns */
$cols = $tables['#__redevent_groupmembers'];

if (is_array($cols)) 
{  
  
  if (array_key_exists('add_events', $cols)){
    $q = ' ALTER TABLE `#__redevent_groupmembers` '
       . '   CHANGE `add_events` `manage_events` TINYINT( 4 ) NOT NULL, '
       . '   CHANGE `add_xrefs` `manage_xrefs` TINYINT( 4 ) NOT NULL '
       ;
    $db->setQuery($q);
    $db->query();    
  }
  else
  {  
	  if (!array_key_exists('manage_events', $cols)) {
	    $q = ' ALTER TABLE `#__redevent_groupmembers` '
	       . '   ADD `manage_events` TINYINT( 4 ) NOT NULL '
	       ;
	    $db->setQuery($q);
	    $db->query();    
	  }
	  
	  if (!array_key_exists('manage_xrefs', $cols)) {
	    $q = ' ALTER TABLE `#__redevent_groupmembers` '
	       . '   ADD `manage_xrefs` TINYINT( 4 ) NOT NULL '
	       ;
	    $db->setQuery($q);
	    $db->query();    
	  }  	
  }	
  
  if (!array_key_exists('id', $cols)) {
    $q = ' ALTER TABLE `#__redevent_groupmembers` '
       . '   ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST '
       ;
    $db->setQuery($q);
    $db->query();    
  }
  
  if (!array_key_exists('is_admin', $cols)) {
    $q = ' ALTER TABLE `#__redevent_groupmembers` '
       . '   ADD `is_admin` TINYINT( 4 ) NOT NULL '
       ;
    $db->setQuery($q);
    $db->query();    
  }
  
  if (!array_key_exists('edit_venues', $cols)) {
    $q = ' ALTER TABLE `#__redevent_groupmembers` '
       . '   ADD `edit_venues` TINYINT( 4 ) NOT NULL '
       ;
    $db->setQuery($q);
    $db->query();    
  }
  
  if (!array_key_exists('publish_events', $cols)) {
    $q = ' ALTER TABLE `#__redevent_groupmembers` '
       . '   ADD `publish_events` TINYINT( 4 ) NOT NULL '
       ;
    $db->setQuery($q);
    $db->query();    
  }
  
  if (!array_key_exists('publish_venues', $cols)) {
    $q = ' ALTER TABLE `#__redevent_groupmembers` '
       . '   ADD `publish_venues` TINYINT( 4 ) NOT NULL '
       ;
    $db->setQuery($q);
    $db->query();    
  }
  
  if (!array_key_exists('receive_registrations', $cols)) {
    $q = ' ALTER TABLE `#__redevent_groupmembers` '
       . '   ADD `receive_registrations` TINYINT( 1 ) NOT NULL '
       ;
    $db->setQuery($q);
    $db->query();    
  }
  
  if (!array_key_exists('checked_out', $cols)) {
    $q = ' ALTER TABLE `#__redevent_groupmembers` '
       . '   ADD `checked_out` int(11) NOT NULL default "0" ,'
       . '   ADD `checked_out_time` datetime NOT NULL default "0000-00-00 00:00:00"'
       ;
    $db->setQuery($q);
    $db->query();    
  }
  
  if (!array_key_exists('manage_attendees', $cols)) {
    $q = ' ALTER TABLE `#__redevent_groupmembers` '
       . '   ADD `manage_attendees` TINYINT( 4 ) NOT NULL '
       ;
    $db->setQuery($q);
    $db->query();    
  }
}

/* Get the group members fields columns */
$cols = $tables['#__redevent_groups'];

if (is_array($cols)) 
{  
  if (!array_key_exists('isdefault', $cols)) {
    $q = ' ALTER TABLE `#__redevent_groups` '
       . '   ADD `isdefault` TINYINT( 1 ) NOT NULL DEFAULT "0" AFTER `description` '
       ;
    $db->setQuery($q);
    $db->query();    
  }	
  
  if (!array_key_exists('edit_events', $cols)) {
    $q = ' ALTER TABLE `#__redevent_groups` '
       . '   ADD `edit_events` tinyint(4) NOT NULL default "0", '
       . '   ADD `edit_venues` tinyint(4) NOT NULL default "0" '
       ;
    $db->setQuery($q);
    $db->query();    
  }	
  
  if (!array_key_exists('parameters', $cols)) {
    $q = ' ALTER TABLE `#__redevent_groups` '
       . '   ADD `parameters` TEXT NULL DEFAULT NULL AFTER `description` '
       ;
    $db->setQuery($q);
    $db->query();    
  }	

  
  if (!array_key_exists('publish_events', $cols)) {
    $q = ' ALTER TABLE `#__redevent_groups` '
       . '   ADD `publish_events` TINYINT( 4 ) NOT NULL '
       ;
    $db->setQuery($q);
    $db->query();    
  }
  
  if (!array_key_exists('publish_venues', $cols)) {
    $q = ' ALTER TABLE `#__redevent_groups` '
       . '   ADD `publish_venues` TINYINT( 4 ) NOT NULL '
       ;
    $db->setQuery($q);
    $db->query();    
  }
}

/** 
 * events repeat table
 */
$cols = $tables['#__redevent_repeats'];

if (is_array($cols)) 
{      
  if (!array_key_exists('id', $cols)) {
    $q = ' ALTER TABLE `#__redevent_repeats` '
       . '   ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY '
       ;
    $db->setQuery($q);
    $db->query();    
  }
  else {
  	if (empty($cols['xref_id']->Extra)) 
  	{
  		$q = "ALTER TABLE `jos_redevent_repeats` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";
	    $db->setQuery($q);
	    $db->query();    
  	}
  }
	
  /** add indexes **/
  if (empty($cols['xref_id']->Key)) {
    $q = "ALTER TABLE `#__redevent_repeats` ADD INDEX (`xref_id`)";
    $db->setQuery($q);
    $db->query();  	
  }
  if (empty($cols['recurrence_id']->Key)) {
    $q = "ALTER TABLE `#__redevent_repeats` ADD INDEX (`recurrence_id`)";
    $db->setQuery($q);
    $db->query();  	
  }
}
  
  
/* Add the basic configuration entry */
$q = "INSERT IGNORE INTO `#__redevent_settings` SET "
   . " id = 1, "
   . " showtime = 0, "
   . " showtitle = 1, "
   . " showlocate = 1, "
   . " showcity = 1, "
   . " showmapserv = 0, "
   . " map24id = '', "
   . " gmapkey = '', "
   . " tablewidth = '100%', "
   . " datewidth = '15%', "
   . " titlewidth = '25%', "
   . " locationwidth = '20%', "
   . " citywidth = '20%', "
   . " datename = 'Date', "
   . " titlename = 'Title', "
   . " locationname = 'Venue', "
   . " cityname = 'City', "
   . " formatdate = '%d.%m.%Y', "
   . " formattime = '%H.%M', "
   . " timename = 'h', "
   . " showdetails = 1, "
   . " showtimedetails = 0, "
   . " showevdescription = 1, "
   . " showdetailstitle = 1, "
   . " showdetailsadress = 1, "
   . " showlocdescription = 1, "
   . " showlinkvenue = 1, "
   . " showdetlinkvenue = 2, "
   . " delivereventsyes = -2, "
   . " mailinform = 0, "
   . " mailinformrec = 'example@example.com', "
   . " mailinformuser = 0, "
   . " datdesclimit = '1000', "
   . " autopubl = -2, "
   . " deliverlocsyes = -2, "
   . " autopublocate = -2, "
   . " showcat = 1, "
   . " catfrowidth = '20%', "
   . " catfroname = 'Type', "
   . " evdelrec = 1, "
   . " evpubrec = 1, "
   . " locdelrec = 1, "
   . " locpubrec = 1, "
   . " sizelimit = '100', "
   . " imagehight = '100', "
   . " imagewidth = '100', "
   . " gddisabled = 0, "
   . " imageenabled = 1, "
   . " comunsolution = 0, "
   . " comunoption = 0, "
   . " catlinklist = 1, "
   . " showfroregistra = 2, "
   . " showfrounregistra = 2, "
   . " eventedit = -2, "
   . " eventeditrec = 1, "
   . " eventowner = 0, "
   . " venueedit = -2, "
   . " venueeditrec = 1, "
   . " venueowner = 0, "
   . " lightbox = 0, "
   . " meta_keywords = '[title], [a_name], [catsid], [times]', "
   . " meta_description = 'The event titled [title] starts on [dates]!', "
   . " showstate = 0, "
   . " statename = 'State', "
   . " statewidth = 0, "
   . " regname = '', "
   . " storeip = 1, "
   . " commentsystem = 0, "
   . " lastupdate = '0', "
   . " checked_out = '', "
   . " checked_out_time = '', "
   . " defaultredformid = 1, "
   . " currency_decimals = 'decimals', "
   . " currency_decimal_separator = ',', "
   . " currency_thousand_separator = '.', "
   . " signup_external_text = 'SIGNUP_EXTERNAL', "
   . " signup_external_img = 'external_icon.gif', "
   . " signup_webform_text ='SIGNUP_WEBFORM', "
   . " signup_webform_img ='form_icon.gif', "
   . " signup_email_text ='SIGNUP_EMAIL', "
   . " signup_email_img ='email_icon.gif', "
   . " signup_formal_offer_text = 'SIGNUP_FORMAL_OFFER', "
   . " signup_formal_offer_img = 'formal_icon.gif', "
   . " signup_phone_text = 'SIGNUP_PHONE', "
   . " signup_phone_img ='phone_icon.gif'";
$db->setQuery($q);
$db->query();

// remove eventvenue key
$query = 'SHOW KEYS FROM #__redevent_event_venue_xref';
$db->setQuery($query);
$keys = $db->loadObjectList();

foreach ($keys as $key) 
{
	if ($key->Key_name == 'eventvenue') 
	{
		$query = 'ALTER TABLE `#__redevent_event_venue_xref` DROP INDEX `eventvenue`';
    $db->setQuery($query);
    $db->query();   
    break; 	
	}
}

// check if we still have the field_values table
$query = ' show tables like '.$db->Quote('%redevent_fields_values');
$db->setQuery($query);
if ($db->loadObject())
{
	// table exists, let's migrate all custom fields into corresponding tables
	
	// first event table
	$query = ' SELECT f.id ' 
	       . ' FROM #__redevent_fields AS f ' 
	       . ' WHERE object_key = ' . $db->Quote('redevent.event');
	$db->setQuery($query);
	$res = $db->loadResultArray();
	foreach ($res as $field_id)
	{
		// create the field
		$query = ' ALTER TABLE `#__redevent_events` ADD `custom'.$field_id.'` TEXT ';
		$db->setQuery($query);
		if ($db->query())
		{
			// update the values in event table
			$query = ' UPDATE #__redevent_events AS e '
			       . ' INNER JOIN #__redevent_fields_values AS v ON v.object_id = e.id '
			       . ' SET e.custom'.$field_id.' = v.value '
			       . ' WHERE v.field_id = '.$field_id
			       ;
			$db->setQuery($query);
			if (!$db->query()) {
				JError::raiseWarning(0, JText::_('COM_REDEVENT_INSTALL_ERROR_COPY_CUSTOM_TO_EVENT_TABLE'.' '.$field_id));
			}
		}
		else {
			JError::raiseWarning(0, JText::_('COM_REDEVENT_INSTALL_ERROR_ADD_CUSTOM_TO_EVENT_TABLE'.' '.$field_id));
		}
	}
	
	// then xref table
	$query = ' SELECT f.id ' 
	       . ' FROM #__redevent_fields AS f ' 
	       . ' WHERE object_key = ' . $db->Quote('redevent.xref');
	$db->setQuery($query);
	$res = $db->loadResultArray();
	foreach ($res as $field_id)
	{
		// create the field
		$query = ' ALTER TABLE `#__redevent_event_venue_xref` ADD `custom'.$field_id.'` TEXT ';
		$db->setQuery($query);
		if ($db->query())
		{
			// update the values in event table
			$query = ' UPDATE #__redevent_event_venue_xref AS e '
			       . ' INNER JOIN #__redevent_fields_values AS v ON v.object_id = e.id '
			       . ' SET e.custom'.$field_id.' = v.value '
			       . ' WHERE v.field_id = '.$field_id
			       ;
			$db->setQuery($query);
			if (!$db->query()) {
				JError::raiseWarning(0, JText::_('COM_REDEVENT_INSTALL_ERROR_COPY_CUSTOM_TO_XREF_TABLE'.' '.$field_id));
			}
		}
		else {
			JError::raiseWarning(0, JText::_('COM_REDEVENT_INSTALL_ERROR_ADD_CUSTOM_TO_XREF_TABLE'.' '.$field_id));
		}
	}
	
	// done, rename the values table to _bak
	$query = 'RENAME TABLE `#__redevent_fields_values` TO `#__redevent_fields_values_bak` ;';
	$db->setQuery($query);
	$db->query();
}

// remove old calendar layout
if (JFile::exists(JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'views'.DS.'calendar'.DS.'tmpl'.DS.'calendar.php')) {
  JFile::delete(JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'views'.DS.'calendar'.DS.'tmpl'.DS.'calendar.php');
  JFile::delete(JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'views'.DS.'calendar'.DS.'tmpl'.DS.'calendar.xml');
}

	/* Install redform plugin */
	jimport('joomla.filesystem.file');
	jimport('joomla.filesystem.folder');	
	JFolder::copy(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_redevent'.DS.'extras'.DS.'redform', JPATH_SITE.DS.'tmp'.DS.'redform_redevent', '', true);
	$installer = new JInstaller();
	$installer->setAdapter('plugin');
	if (!$installer->install(JPATH_SITE.DS.'tmp'.DS.'redform_redevent')) {
	  echo JText::_('Plugin install failed: ') . $installer->getError().'<br />';
	}
	else {
	  // autopublish the plugin
	  $query = ' UPDATE #__plugins SET published = 1 WHERE folder = '. $db->Quote('redform_integration') . ' AND element = '.$db->Quote('redevent');
    $db->setQuery($query);
    if ($db->query()) {
	    echo JText::_('Succesfully installed redform integration plugin').'<br />';
    }
    else {
      echo JText::_('Error publishing redform integration plugin').'<br />';      
    }	  
	}
?>

<center>
<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
	<tr>
		<td valign="top">
    		<img src="<?php echo 'components/com_redevent/assets/images/redevent_logo.png'; ?>" alt="redEVENT Logo" align="left">
		</td>
		<td valign="top" width="100%">
			<strong>redEVENT</strong><br/>
        	<font class="small">by <a href="http://www.redcomponent.com" target="_blank">redcomponent.com </a><br/>
       	 	<strong>EventList</strong></font><br/>
        	<font class="small">by <a href="http://www.schlu.net" target="_blank">schlu.net </a><br/>
        	Released under the terms and conditions of the <a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU General Public License</a>.
        	</font>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<h2>Installation Status:</h2>
			<?php
			$error = false;;
			// Check for existing /images/redevent directory
			if (!$direxists = JFolder::exists( JPATH_SITE.DS.'images'.DS.'redevent' )) {
				echo "<font color='orange'>Note:</font> The Directory /images/redevent does NOT exist. redEVENT will try to create them.<br />";

				//Image folder creation
				if ($makedir = JFolder::create( JPATH_SITE.DS.'images'.DS.'redevent')) {
					echo "<font color='green'>FINISHED:</font> Directory /images/redevent created.<br />";
				} else {
					echo "<font color='red'>ERROR:</font> Directory /images/redevent NOT created.<br />";
					$error = true;
				}
			} 
			
			// Check for existing /images/redevent/events directory
			if (!$direxists = JFolder::exists( JPATH_SITE.DS.'images'.DS.'redevent'.DS.'events' ))
			{
				if (JFolder::create(JPATH_SITE.DS.'images'.DS.'redevent'.DS.'events')) {
					echo "<font color='green'>FINISHED:</font> Directory /images/redevent/events created.<br />";
				} else {
					echo "<font color='red'>ERROR:</font> Directory /images/redevent/events NOT created.<br />";
					$error = true;
				}
				if (JFolder::create( JPATH_SITE.DS.'images'.DS.'redevent'.DS.'events'.DS.'small' )) {
					echo "<font color='green'>FINISHED:</font> Directory /images/redevent/events/small created.<br />";
				} else {
					echo "<font color='red'>ERROR:</font> Directory /images/redevent/events/small NOT created.<br />";
					$error = true;
				}
			}
			
			// Check for existing /images/redevent/venues directory
			if (!$direxists = JFolder::exists( JPATH_SITE.DS.'images'.DS.'redevent'.DS.'venues' ))
			{
				if (JFolder::create( JPATH_SITE.DS.'images'.DS.'redevent'.DS.'venues' )) {
					echo "<font color='green'>FINISHED:</font> Directory /images/redevent/venues created.<br />";
				} else {
					echo "<font color='red'>ERROR:</font> Directory /images/redevent/venues NOT created.<br />";
					$error = true;
				}
				if (JFolder::create( JPATH_SITE.DS.'images'.DS.'redevent'.DS.'venues'.DS.'small' )) {
					echo "<font color='green'>FINISHED:</font> Directory /images/redevent/venues/small created.<br />";
				} else {
					echo "<font color='red'>ERROR:</font> Directory /images/redevent/venues/small NOT created.<br />";
					$error = true;
				}
			}
			
			// Check for existing /images/redevent/categories directory
			if (!$direxists = JFolder::exists( JPATH_SITE.DS.'images'.DS.'redevent'.DS.'categories' ))
			{
				if (JFolder::create( JPATH_SITE.DS.'images'.DS.'redevent'.DS.'categories' )) {
					echo "<font color='green'>FINISHED:</font> Directory /images/redevent/categories created.<br />";
				} else {
					echo "<font color='red'>ERROR:</font> Directory /images/redevent/categories NOT created.<br />";
					$error = true;
				}
				if (JFolder::create( JPATH_SITE.DS.'images'.DS.'redevent'.DS.'categories'.DS.'small' )) {
					echo "<font color='green'>FINISHED:</font> Directory /images/redevent/categories/small created.<br />";
				} else {
					echo "<font color='red'>ERROR:</font> Directory /images/redevent/categories/small NOT created.<br />";
					$error = true;
				}
			}
        	?>

			<br />

			<?php
			if (!$error) {
			?>
				<font color="green"><b>redEVENT 2.0 beta Installed Successfully!</b></font><br />
				Ensure that redEVENT has write access to the directories shown above! Have Fun.
			<?php
			} else {
			?>
				<font color="red">
				<b>redEVENT 2.0 beta could NOT be installed successfully!</b>
				</font>
				<br /><br />
				Please check following directories:<br />
				<ul>
					<li>/images/redevent</li>
					<li>/images/redevent/events</li>
					<li>/images/redevent/events/small</li>
					<li>/images/redevent/venues</li>
					<li>/images/redevent/venues/small</li>
					<li>/images/redevent/categories</li>
					<li>/images/redevent/categories/small</li>
				</ul>
				<br />

				<code>
					If they do not exist, create them and ensure redEVENT has write access to these directories.<br />
					If you don't so, you prevent EventList from functioning correctly. (You can't upload images).
				</code>
			<?php
			}
			?>
		</td>
	</tr>
</table>

</center>
	<?php
	/* Install the sh404SEF router files */
	jimport('joomla.filesystem.file');
	jimport('joomla.filesystem.folder');
	$sh404sefext = JPATH_SITE.DS.'components'.DS.'com_sh404sef'.DS.'sef_ext';
	$sh404sefmeta = JPATH_SITE.DS.'components'.DS.'com_sh404sef'.DS.'meta_ext';
	$sh404sefadmin = JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_sh404sef';
	$redadmin = JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_redevent'.DS.'extras';
	/* Check if sh404SEF is installed */
	if (JFolder::exists(JPATH_SITE.DS.'components'.DS.'com_sh404sef')) {
		/* Copy the plugin */
		if(!JFile::copy($redadmin.DS.'sh404sef'.DS.'sef_ext'.DS.'com_redevent.php', $sh404sefext.DS.'com_redevent.php')) {
			echo JText::_('<b>Failed</b> to copy sh404SEF extension plugin file<br />');
		}
		if(!JFile::copy($redadmin.DS.'sh404sef'.DS.'meta_ext'.DS.'com_redevent.php', $sh404sefmeta.DS.'com_redevent.php')) {
			echo JText::_('<b>Failed</b> to copy sh404SEF meta plugin file<br />');
		}
		if(!JFile::copy($redadmin.DS.'sh404sef'.DS.'language'.DS.'com_redevent.php', $sh404sefadmin.DS.'language'.DS.'plugins'.DS.'com_redevent.php')) {
			echo JText::_('<b>Failed</b> to copy sh404SEF plugin language file<br />');
		}
		
		?>
		<h3>Note for sh404sef</h3>
		<p>If you want your modules to avoid disappearing while you browse pages on redevent, you should set "Insert menu Itemid if none", "Insert menu title if no Itemid"
		and "Always insert menu title" to "yes" in sh404sef configuration &gt; advanced tab &gt; category "itemid management"</p>
		<?php
	}
	?>
	<p><a href="index.php?option=com_redevent&task=sampledata">click here</a> to <span style="color:red;font-weight:bold;">install sample data</span></p>
	<p>Remember to check for updates on: 
	<a href="http://redcomponent.com/" target="_new"><img src="http://images.redcomponent.com/redcomponent.jpg" alt=""></a></p>	
	<?php 
} // end com_install
?>