<?php
/**
 * @package     Redform.Site
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

extract($displayData);

/**
 * Layout variables
 * --------------------
 *
 * @var  RedeventEntitySession  $session  session entity
 */

echo JText::sprintf(
	'PLG_AESIR_REDEVENT_SYNC_ITEM_SESSION_TITLE_FORMAT',
	$session->getEvent()->title,
	$session->getVenue()->name,
	$session->getFormattedStartDate()
);
