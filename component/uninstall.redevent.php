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

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Executes additional uninstallation processes
 *
 * @since 0.1
 */
function com_uninstall() {
	/* Remove sh404SEF plugin */
	jimport('joomla.filesystem.file');
	jimport('joomla.filesystem.folder');
	if (JFolder::exists(JPATH_SITE.DS.'components'.DS.'com_sh404sef')) {
		JFile::delete(JPATH_SITE.DS.'components'.DS.'com_sh404sef'.DS.'sef_ext'.DS.'com_redevent.php');
		JFile::delete(JPATH_SITE.DS.'components'.DS.'com_sh404sef'.DS.'sef_meta_ext'.DS.'com_redevent.php');
		JFile::delete(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_sh404sef'.DS.'language'.DS.'plugins'.DS.'com_redevent.php');
	}

	echo "<strong>redEVENT 2.0 beta 2 has been uninstalled</strong>";
}
?>