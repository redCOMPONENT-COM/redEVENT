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
defined('_JEXEC') or die('Restricted access');

/**
 * EventList settings table class
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEvent_settings extends JTable
{
	/**
	 * Unique Key
	 * @var int
	 */
	var $id					= "1";
	/** @var int */
	var $delivereventsyes 	= "-2";
	/** @var int */
	var $evdelrec 			= "1";
	/** @var int */
	var $sizelimit 			= "100";
	/** @var int */
	var $imagehight 		= "100";
	/** @var int */
	var $imagewidth 		= "100";
	/** @var int */
	var $gddisabled 		= "0";
	/** @var int */
	var $imageenabled 		= "1";
	/** @var int */
	var $comunsolution 		= "0";
	/** @var int */
	var $comunoption 		= "0";
	/** @var int */
	var $lightbox 			= "0";
	/** @var string */
	var $meta_keywords 		= null;
	/** @var string */
	var $meta_description 	= null;
	/** @var int */
	var $commentsystem		= 0;
	/** @var string */
	var $lastupdate 		= null;
	/** @var int */
	var $checked_out 		= null;
	/** @var date */
	var $checked_out_time 	= null;
	var $signup_external_text = null;
	var $signup_external_img = null;
	var $signup_webform_text = null;
	var $signup_webform_img = null;
	var $signup_email_text = null;
	var $signup_email_img = null;
	var $signup_formal_offer_text = null;
	var $signup_formal_offer_img = null;
	var $signup_phone_text = null;
	var $signup_phone_img = null;

	function redevent_settings(& $db) {
		parent::__construct('#__redevent_settings', 'id', $db);
	}
	
	/**
	 * for legacy purpose
	 * @see JObject::get()
	 */
	function get($property, $default=null)
	{
		if(isset($this->$property)) {
			return $this->$property;
		}
		$params = JComponentHelper::getParams('com_redevent');
		return $params->get($property, $default);
	}
}
