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
jimport('joomla.filesystem.file');

/**
 * EventList Component Imagehandler Controller
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedeventControllerImagehandler extends RedeventController
{
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra task
		$this->registerTask( 'eventimgup', 	'uploadimage' );
		$this->registerTask( 'venueimgup', 	'uploadimage' );
		$this->registerTask( 'categoryimgup', 'uploadimage' );
	}

	/**
	 * logic for uploading an image
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function uploadimage()
	{
		$mainframe = JFactory::getApplication();

		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$elsettings = JComponentHelper::getParams('com_redevent');

		$file 		= JRequest::getVar( 'userfile', '', 'files', 'array' );
		$task 		= JRequest::getVar( 'task' );

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		//$ftp = JClientHelper::getCredentials('ftp');

		//set the target directory
		switch ($task)
		{
			case 'venueimgup':
				$base_Dir = JPATH_SITE.DS.'images'.DS.'redevent'.DS.'venues'.DS;
				break;
			case 'eventimgup':
				$base_Dir = JPATH_SITE.DS.'images'.DS.'redevent'.DS.'events'.DS;
				break;
			case 'categoryimgup':
				$base_Dir = JPATH_SITE.DS.'images'.DS.'redevent'.DS.'categories'.DS;
				break;
		}

		//do we have an upload?
		if (empty($file['name'])) {
			echo "<script> alert('".JText::_('COM_REDEVENT_IMAGE_EMPTY' )."'); window.history.go(-1); </script>\n";
			$mainframe->close();
		}

		//check the image
		$check = RedeventImage::check($file, $elsettings);

		if ($check === false) {
			$mainframe->redirect($_SERVER['HTTP_REFERER']);
		}

		//sanitize the image filename
		$filename = RedeventImage::sanitize($base_Dir, $file['name']);
		$filepath = $base_Dir . $filename;

		//upload the image
		if (!JFile::upload($file['tmp_name'], $filepath)) {
			echo "<script> alert('".JText::_('COM_REDEVENT_UPLOAD_FAILED' )."'); window.history.go(-1); </script>\n";
			$mainframe->close();

		} else {
			// create thumbnail
			RedeventImage::thumb($filepath, dirname($filepath).DS.'small'.DS.$filename, $elsettings->get('imagewidth'), $elsettings->get('imageheight', 100));

			echo "<script> alert('".JText::_('COM_REDEVENT_UPLOAD_COMPLETE' )."'); window.history.go(-1); window.parent.elSelectImage('$filename', '$filename'); </script>\n";
			$mainframe->close();
		}

	}

	/**
	 * logic to mass delete images
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function delete()
	{
		$mainframe = JFactory::getApplication();

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		// Get some data from the request
		$images	= JRequest::getVar( 'rm', array(), '', 'array' );
		$folder = JRequest::getVar( 'folder');

		if (count($images)) {
			foreach ($images as $image)
			{
				if ($image !== JFilterInput::clean($image, 'path')) {
					RedeventError::raiseWarning(100, JText::_('COM_REDEVENT_UNABLE_TO_DELETE').' '.htmlspecialchars($image, ENT_COMPAT, 'UTF-8'));
					continue;
				}

				$fullPath = JPath::clean(JPATH_SITE.DS.'images'.DS.'redevent'.DS.$folder.DS.$image);
				$fullPaththumb = JPath::clean(JPATH_SITE.DS.'images'.DS.'redevent'.DS.$folder.DS.'small'.DS.$image);
				if (is_file($fullPath)) {
					JFile::delete($fullPath);
					if (JFile::exists($fullPaththumb)) {
						JFile::delete($fullPaththumb);
					}
				}
			}
		}

		switch ($folder)
		{
			case 'events':
				$task = 'selecteventimg';
				break;
			case 'venues':
				$task = 'selectvenueimg';
				break;
			case 'categories':
				$task = 'selectcategoryimg';
				break;
		}

		$mainframe->redirect('index.php?option=com_redevent&view=imagehandler&task='.$task.'&tmpl=component');
	}

}
