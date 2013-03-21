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

require_once (JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'helpers'.DS.'log.php');

/**
 * Holds the logic for image manipulation
 *
 * @package Joomla
 * @subpackage redEVENT
 */
class redEVENTImage
{
	/**
	* Creates a Thumbnail of an image
	*
 	* @author Christoph Lukes
	* @since 0.9
 	*
 	* @param string $file The path to the file
	* @param string $save The targetpath
	* @param string $width The with of the image
	* @param string $height The height of the image
	* @return true when success
	*/
	public static function thumb($file, $save, $width, $height)
	{
		//GD-Lib > 2.0 only!
		@unlink($save);

		//get sizes else stop
		if (!$infos = @getimagesize($file)) {
			return false;
		}

		// keep proportions
		$iWidth = $infos[0];
		$iHeight = $infos[1];
		$iRatioW = $width / $iWidth;
		$iRatioH = $height / $iHeight;

		if ($iRatioW < $iRatioH) {
			$iNewW = $iWidth * $iRatioW;
			$iNewH = $iHeight * $iRatioW;
		} else {
			$iNewW = $iWidth * $iRatioH;
			$iNewH = $iHeight * $iRatioH;
		}

		//Don't resize images which are smaller than thumbs
		if ($infos[0] < $width && $infos[1] < $height) {
			$iNewW = $infos[0];
			$iNewH = $infos[1];
		}

		if($infos[2] == 1) {
			/*
			* Image is typ gif
			*/
			$imgA = imagecreatefromgif($file);
			$imgB = imagecreate($iNewW,$iNewH);

       		//keep gif transparent color if possible
          	if(function_exists('imagecolorsforindex') && function_exists('imagecolortransparent')) {
            	$transcolorindex = imagecolortransparent($imgA);
            		//transparent color exists
            		if($transcolorindex >= 0 ) {
             			$transcolor = imagecolorsforindex($imgA, $transcolorindex);
              			$transcolorindex = imagecolorallocate($imgB, $transcolor['red'], $transcolor['green'], $transcolor['blue']);
              			imagefill($imgB, 0, 0, $transcolorindex);
              			imagecolortransparent($imgB, $transcolorindex);
              		//fill white
            		} else {
              			$whitecolorindex = @imagecolorallocate($imgB, 255, 255, 255);
              			imagefill($imgB, 0, 0, $whitecolorindex);
            		}
            //fill white
          	} else {
            	$whitecolorindex = imagecolorallocate($imgB, 255, 255, 255);
            	imagefill($imgB, 0, 0, $whitecolorindex);
          	}
          	imagecopyresampled($imgB, $imgA, 0, 0, 0, 0, $iNewW, $iNewH, $infos[0], $infos[1]);
			imagegif($imgB, $save);

		} elseif($infos[2] == 2) {
			/*
			* Image is typ jpg
			*/
			$imgA = imagecreatefromjpeg($file);
			$imgB = imagecreatetruecolor($iNewW,$iNewH);
			imagecopyresampled($imgB, $imgA, 0, 0, 0, 0, $iNewW, $iNewH, $infos[0], $infos[1]);
			imagejpeg($imgB, $save);

		} elseif($infos[2] == 3) {
			/*
			* Image is typ png
			*/
			$imgA = imagecreatefrompng($file);
			$imgB = imagecreatetruecolor($iNewW, $iNewH);
			imagealphablending($imgB, false);
			imagecopyresampled($imgB, $imgA, 0, 0, 0, 0, $iNewW, $iNewH, $infos[0], $infos[1]);
			imagesavealpha($imgB, true);
			imagepng($imgB, $save);
		} else {
			return false;
		}
		return true;
	}

	/**
	* Determine the GD version
	* Code from php.net
	*
	* @since 0.9
	* @param int
	*
	* @return int
	*/
	public static function gdVersion($user_ver = 0)
	{
		if (! extension_loaded('gd')) {
			return;
		}
		static $gd_ver = 0;

		// Just accept the specified setting if it's 1.
		if ($user_ver == 1) {
			$gd_ver = 1;
			return 1;
		}
		// Use the static variable if function was called previously.
		if ($user_ver !=2 && $gd_ver > 0 ) {
			return $gd_ver;
		}
		// Use the gd_info() function if possible.
		if (function_exists('gd_info')) {
			$ver_info = gd_info();
			preg_match('/\d/', $ver_info['GD Version'], $match);
			$gd_ver = $match[0];
			return $match[0];
		}
		// If phpinfo() is disabled use a specified / fail-safe choice...
		if (preg_match('/phpinfo/', ini_get('disable_functions'))) {
			if ($user_ver == 2) {
				$gd_ver = 2;
				return 2;
			} else {
				$gd_ver = 1;
				return 1;
			}
		}
		// ...otherwise use phpinfo().
		ob_start();
		phpinfo(8);
		$info = ob_get_contents();
		ob_end_clean();
		$info = stristr($info, 'gd version');
		preg_match('/\d/', $info, $match);
		$gd_ver = $match[0];

		return $match[0];
	}

	/**
	* Creates image information of an image
	*
	* @author Christoph Lukes
	* @since 0.9
	*
	* @param string $image The image relative path
	*
	* @return imagedata if available
	*/
	public static function flyercreator($image)
	{
		$settings = & redEVENTHelper::config();

		jimport('joomla.filesystem.file');

		if ( $image ) {

			//set paths
			$dimage['original'] = $image;
			$dimage['thumb'] 	= self::getThumbUrl($image);

			//get imagesize of the original
			$iminfo = @getimagesize(JPATH_SITE.DS.$image);

			//if the width or height is too large this formula will resize them accordingly
			if (($iminfo[0] > $settings->get('imagewidth')) || ($iminfo[1] > $settings->get('imageheight', 100))) {

				$iRatioW = $settings->get('imagewidth') / $iminfo[0];
				$iRatioH = $settings->get('imageheight', 100) / $iminfo[1];

				if ($iRatioW < $iRatioH) {
					$dimage['width'] 	= round($iminfo[0] * $iRatioW);
					$dimage['height'] 	= round($iminfo[1] * $iRatioW);
				} else {
					$dimage['width'] 	= round($iminfo[0] * $iRatioH);
					$dimage['height'] 	= round($iminfo[1] * $iRatioH);
				}

			} else {

				$dimage['width'] 	= $iminfo[0];
				$dimage['height'] 	= $iminfo[1];

			}

			//get imagesize of the thumbnail
			$thumbiminfo = @getimagesize(dirname(JPATH_SITE.DS.$image).DS.'re_thumb'.DS.basename($image));
			$dimage['thumbwidth'] 	= $thumbiminfo[0];
			$dimage['thumbheight'] 	= $thumbiminfo[1];

			return $dimage;
		}
		return false;
	}

	/**
	 * returns the hml code for modal display of image
	 * If thumbnails exits, display the thumbnail with a modal link,
	 * otherwise, just display the full size picture
	 *
	 * @param string image path, relative to joomla base folder
	 * @param string alt attribute
	 * @param array other attributes
	 * @return mixed boolean false if empty path, html string otherwise
	 */
	public static function modalimage($path, $alt, $maxdim = null, $attribs = array())
	{
		jimport('joomla.filesystem.file');
		$app = &JFactory::getApplication();

		if (empty($path)) {
			return false;
		}

		if (!file_exists(JPATH_SITE.DS.$path)) {
			RedeventHelperLog::simpleLog(sprintf('Image not found: %s', $path));
			return false;
		}

		$base = JURI::root();

		$thumb_path = self::getThumbUrl($path, $maxdim);

		JHTML::_('behavior.modal', 'a.imodal');
		if (isset($attribs['class'])) {
			$attribs['class'] .= ' imodal';
		}
		else {
			$attribs['class'] = 'imodal';
		}
		$thumb = JHTML::image($thumb_path, $alt, $attribs);
		$html = JHTML::link(JRoute::_($base.$path), $thumb, $attribs);

		return $html;
	}

	/**
	 * return full url to thumbnail
	 *
	 * @param string image path, relative to joomla images folder
	 * @return url or false if it doesn't exists
	 */
	public static function getThumbUrl($path, $maxdim = null)
	{
		jimport('joomla.filesystem.file');
		$app = &JFactory::getApplication();
		$settings = redEVENTHelper::config();

		if ($maxdim)
		{
			$width  = $maxdim;
			$height = $maxdim;
		}
		else
		{
			$width  = $settings->get('imagewidth', 100);
			$height = $settings->get('imageheight', 100);
		}

		$base = JURI::root();

		$thumb_name = md5(basename($path)).$width.'_'.$height.'.png';
		if (dirname($path) != '.')
		{
			$thumb_path = JPATH_SITE.DS.dirname($path).DS.'re_thumb'.DS.$thumb_name;
			$thumb_uri = $base.str_replace("\"", "/", dirname($path)).'/re_thumb/'.$thumb_name;
		}
		else
		{
			JError::raisewarning(0, JText::sprintf('COM_REDEVENT_THUMBNAILS_WRONG_BASE_PATH',dirname($thumb_path)));
		}

		if (JFile::exists($thumb_path))
		{
			return $thumb_uri;
		}
		else if (JFile::exists(JPATH_SITE.DS.$path))
		{
			//try to generate the thumb
			if (!JFolder::exists(dirname($thumb_path)) && !JFolder::create(dirname($thumb_path))) {
				RedeventHelperLog::simpleLog(sprintf('Can\'t create path for thumbnail: %s',dirname($thumb_path)));
				return false;
			}
			if (redEVENTImage::thumb(JPATH_SITE.DS.$path, $thumb_path, $width, $height)) {
				return $thumb_uri;
			}
		}
		return false;
	}

	public static function check($file, $elsettings)
	{
		jimport('joomla.filesystem.file');

		$sizelimit 	= $elsettings->get('sizelimit', '100')*1024; //size limit in kb
		$imagesize 	= $file['size'];

		//check if the upload is an image...getimagesize will return false if not
		if (!getimagesize($file['tmp_name'])) {
			JError::raiseWarning(100, JText::_('COM_REDEVENT_UPLOAD_FAILED_NOT_AN_IMAGE').': '.htmlspecialchars($file['name'], ENT_COMPAT, 'UTF-8'));
			return false;
		}

		//check if the imagefiletype is valid
		$fileext 	= strtolower(JFile::getExt($file['name']));

		$allowable 	= array ('gif', 'jpg', 'png');
		if (!in_array($fileext, $allowable)) {
			JError::raiseWarning(100, JText::_('COM_REDEVENT_WRONG_IMAGE_FILE_TYPE').': '.htmlspecialchars($file['name'], ENT_COMPAT, 'UTF-8'));
			return false;
		}

		//Check filesize
		if ($imagesize > $sizelimit) {
			JError::raiseWarning(100, JText::_('COM_REDEVENT_IMAGE_FILE_SIZE').': '.htmlspecialchars($file['name'], ENT_COMPAT, 'UTF-8'));
			return false;
		}

		//XSS check
		$xss_check =  JFile::read($file['tmp_name'],false,256);
		$html_tags = array('abbr','acronym','address','applet','area','audioscope','base','basefont','bdo','bgsound','big','blackface','blink','blockquote','body','bq','br','button','caption','center','cite','code','col','colgroup','comment','custom','dd','del','dfn','dir','div','dl','dt','em','embed','fieldset','fn','font','form','frame','frameset','h1','h2','h3','h4','h5','h6','head','hr','html','iframe','ilayer','img','input','ins','isindex','keygen','kbd','label','layer','legend','li','limittext','link','listing','map','marquee','menu','meta','multicol','nobr','noembed','noframes','noscript','nosmartquotes','object','ol','optgroup','option','param','plaintext','pre','rt','ruby','s','samp','script','select','server','shadow','sidebar','small','spacer','span','strike','strong','style','sub','sup','table','tbody','td','textarea','tfoot','th','thead','title','tr','tt','ul','var','wbr','xml','xmp','!DOCTYPE', '!--');
		foreach($html_tags as $tag) {
			// A tag is '<tagname ', so we need to add < and a space or '<tagname>'
			if(stristr($xss_check, '<'.$tag.' ') || stristr($xss_check, '<'.$tag.'>')) {
				RedeventError::raiseWarning(100, JText::_('COM_REDEVENT_WARN_IE_XSS'));
				return false;
			}
		}

		return true;
	}

	/**
	* Sanitize the image file name and return an unique string
	*
	* @since 0.9
	* @author Christoph Lukes
	*
	* @param string $base_Dir the target directory
	* @param string $filename the unsanitized imagefile name
	*
	* @return string $filename the sanitized and unique image file name
	*/
	public static function sanitize($base_Dir, $filename)
	{
		jimport('joomla.filesystem.file');

		//check for any leading/trailing dots and remove them (trailing shouldn't be possible cause of the getEXT check)
		$filename = preg_replace( "/^[.]*/", '', $filename );
		$filename = preg_replace( "/[.]*$/", '', $filename ); //shouldn't be necessary, see above

		//we need to save the last dot position cause preg_replace will also replace dots
		$lastdotpos = strrpos( $filename, '.' );

		//replace invalid characters
		$chars = '[^0-9a-zA-Z()_-]';
		$filename 	= strtolower( preg_replace( "/$chars/", '_', $filename ) );

		//get the parts before and after the dot (assuming we have an extension...check was done before)
		$beforedot	= substr( $filename, 0, $lastdotpos );
		$afterdot 	= substr( $filename, $lastdotpos + 1 );

		//make a unique filename for the image and check it is not already taken
		//if it is already taken keep trying till success
		$now = time();

		while( JFile::exists( $base_Dir . $beforedot . '_' . $now . '.' . $afterdot ) )
		{
   			$now++;
		}

		//create out of the seperated parts the new filename
		$filename = $beforedot . '_' . $now . '.' . $afterdot;

		return $filename;
	}

	/**
	 * returns html code for category image, or just the category name if image is not set
	 *
	 * @param object $category
	 * @param boolean lightbox effect
	 * @param array attribs
	 * @return html
	 */
	public static function getCategoryImage($category, $modal = true, $attribs = null)
	{
		$image_attribs = array('title' => $category->catname);

		if ($attribs && is_array($attribs)) {
			$image_attribs = array_merge( $image_attribs, $attribs);
		}
		if ($category->image) {
		  return self::modalimage($category->image, $category->catname, null, $image_attribs);
		}
		else return $category->catname;
	}
}
