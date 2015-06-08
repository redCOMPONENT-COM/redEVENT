<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Holds the logic for image manipulation
 *
 * @package  Redevent.Library
 * @since    2.0
 *
 * @TODO: might be deprecated !
 */
class RedeventImage
{
	/**
	 * Creates a Thumbnail of an image
	 *
	 * @param   string  $file    The path to the file
	 * @param   string  $save    The targetpath
	 * @param   string  $width   The with of the image
	 * @param   string  $height  The height of the image
	 *
	 * @return true when success
	 */
	public static function thumb($file, $save, $width, $height)
	{
		@unlink($save);

		// Get sizes else stop
		if (!$infos = @getimagesize($file))
		{
			return false;
		}

		// Keep proportions
		$iWidth = $infos[0];
		$iHeight = $infos[1];
		$iRatioW = $width / $iWidth;
		$iRatioH = $height / $iHeight;

		if ($iRatioW < $iRatioH)
		{
			$iNewW = $iWidth * $iRatioW;
			$iNewH = $iHeight * $iRatioW;
		}
		else
		{
			$iNewW = $iWidth * $iRatioH;
			$iNewH = $iHeight * $iRatioH;
		}

		// Don't resize images which are smaller than thumbs
		if ($infos[0] < $width && $infos[1] < $height)
		{
			$iNewW = $infos[0];
			$iNewH = $infos[1];
		}

		if ($infos[2] == 1)
		{
			/*
			* Image is typ gif
			*/
			$imgA = imagecreatefromgif($file);
			$imgB = imagecreate($iNewW, $iNewH);

			// Keep gif transparent color if possible
			if (function_exists('imagecolorsforindex') && function_exists('imagecolortransparent'))
			{
				$transcolorindex = imagecolortransparent($imgA);

				// Transparent color exists
				if ($transcolorindex >= 0)
				{
					$transcolor = imagecolorsforindex($imgA, $transcolorindex);
					$transcolorindex = imagecolorallocate($imgB, $transcolor['red'], $transcolor['green'], $transcolor['blue']);
					imagefill($imgB, 0, 0, $transcolorindex);
					imagecolortransparent($imgB, $transcolorindex);
				}
				else
				{
					$whitecolorindex = @imagecolorallocate($imgB, 255, 255, 255);
					imagefill($imgB, 0, 0, $whitecolorindex);
				}
			}
			else
			{
				$whitecolorindex = imagecolorallocate($imgB, 255, 255, 255);
				imagefill($imgB, 0, 0, $whitecolorindex);
			}

			imagecopyresampled($imgB, $imgA, 0, 0, 0, 0, $iNewW, $iNewH, $infos[0], $infos[1]);
			imagegif($imgB, $save);
		}
		elseif ($infos[2] == 2)
		{
			/*
			* Image is typ jpg
			*/
			$imgA = imagecreatefromjpeg($file);
			$imgB = imagecreatetruecolor($iNewW, $iNewH);
			imagecopyresampled($imgB, $imgA, 0, 0, 0, 0, $iNewW, $iNewH, $infos[0], $infos[1]);
			imagejpeg($imgB, $save);
		}
		elseif ($infos[2] == 3)
		{
			/*
			* Image is typ png
			*/
			$imgA = imagecreatefrompng($file);
			$imgB = imagecreatetruecolor($iNewW, $iNewH);
			imagealphablending($imgB, false);
			imagecopyresampled($imgB, $imgA, 0, 0, 0, 0, $iNewW, $iNewH, $infos[0], $infos[1]);
			imagesavealpha($imgB, true);
			imagepng($imgB, $save);
		}
		else
		{
			return false;
		}

		return true;
	}

	/**
	 * Creates image information of an image
	 *
	 * @param   string  $image  The image relative path
	 *
	 * @return imagedata if available
	 */
	public static function flyercreator($image)
	{
		$settings = RedeventHelper::config();

		jimport('joomla.filesystem.file');

		if ($image)
		{
			// Set paths
			$dimage['original'] = $image;
			$dimage['thumb'] = static::getThumbUrl($image);

			// Get imagesize of the original
			$iminfo = @getimagesize(JPATH_SITE . '/' . $image);

			// If the width or height is too large this formula will resize them accordingly
			if (($iminfo[0] > $settings->get('imagewidth')) || ($iminfo[1] > $settings->get('imageheight', 100)))
			{
				$iRatioW = $settings->get('imagewidth') / $iminfo[0];
				$iRatioH = $settings->get('imageheight', 100) / $iminfo[1];

				if ($iRatioW < $iRatioH)
				{
					$dimage['width'] = round($iminfo[0] * $iRatioW);
					$dimage['height'] = round($iminfo[1] * $iRatioW);
				}
				else
				{
					$dimage['width'] = round($iminfo[0] * $iRatioH);
					$dimage['height'] = round($iminfo[1] * $iRatioH);
				}
			}
			else
			{
				$dimage['width'] = $iminfo[0];
				$dimage['height'] = $iminfo[1];
			}

			// Get imagesize of the thumbnail
			$thumbiminfo = @getimagesize(dirname(JPATH_SITE . '/' . $image) . '/re_thumb/' . basename($image));
			$dimage['thumbwidth'] = $thumbiminfo[0];
			$dimage['thumbheight'] = $thumbiminfo[1];

			return $dimage;
		}

		return false;
	}

	/**
	 * returns the hml code for modal display of image
	 * If thumbnails exits, display the thumbnail with a modal link,
	 * otherwise, just display the full size picture
	 *
	 * @param   string  $path     image path, relative to joomla base folder
	 * @param   string  $alt      alt attribute
	 * @param   int     $maxdim   maximum dimension of one size
	 * @param   array   $attribs  other attributes
	 *
	 * @return mixed boolean false if empty path, html string otherwise
	 */
	public static function modalimage($path, $alt, $maxdim = null, $attribs = array())
	{
		jimport('joomla.filesystem.file');
		$app = JFactory::getApplication();

		if (empty($path))
		{
			return false;
		}

		if (!file_exists(JPATH_SITE . '/' . $path))
		{
			RedeventHelperLog::simpleLog(sprintf('Image not found: %s', $path));

			return false;
		}

		$base = JURI::root();
		$thumb_path = static::getThumbUrl($path, $maxdim);

		JHTML::_('behavior.modal', 'a.imodal');

		if (isset($attribs['class']))
		{
			$attribs['class'] .= ' imodal';
		}
		else
		{
			$attribs['class'] = 'imodal';
		}

		$thumb = JHTML::image($thumb_path, $alt, $attribs);
		$html = JHTML::link(JRoute::_($base . $path), $thumb, $attribs);

		return $html;
	}

	/**
	 * return full url to thumbnail
	 *
	 * @param   string  $path    image path, relative to joomla images folder
	 * @param   int     $maxdim  maximum dimension of one size
	 *
	 * @return url or false if it doesn't exists
	 */
	public static function getThumbUrl($path, $maxdim = null)
	{
		jimport('joomla.filesystem.file');
		$app = JFactory::getApplication();
		$settings = RedeventHelper::config();

		if ($maxdim)
		{
			$width = $maxdim;
			$height = $maxdim;
		}
		else
		{
			$width = $settings->get('imagewidth', 100);
			$height = $settings->get('imageheight', 100);
		}

		$base = JURI::root();

		$thumb_name = md5(basename($path)) . $width . '_' . $height . '.png';

		if (dirname($path) != '.')
		{
			$thumb_path = JPATH_SITE . '/' . dirname($path) . '/re_thumb/' . $thumb_name;
			$thumb_uri = $base . str_replace("\"", "/", dirname($path)) . '/re_thumb/' . $thumb_name;
		}
		else
		{
			JError::raisewarning(0, JText::sprintf('COM_REDEVENT_THUMBNAILS_WRONG_BASE_PATH', dirname($path)));
		}

		if (JFile::exists($thumb_path))
		{
			return $thumb_uri;
		}
		elseif (JFile::exists(JPATH_SITE . '/' . $path))
		{
			// Try to generate the thumb
			if (!JFolder::exists(dirname($thumb_path)) && !JFolder::create(dirname($thumb_path)))
			{
				RedeventHelperLog::simpleLog(sprintf('Can\'t create path for thumbnail: %s', dirname($thumb_path)));

				return false;
			}

			if (static::thumb(JPATH_SITE . '/' . $path, $thumb_path, $width, $height))
			{
				return $thumb_uri;
			}
		}

		return false;
	}

	/**
	 * returns html code for category image, or just the category name if image is not set
	 *
	 * @param   object   $category  category data
	 * @param   boolean  $modal     make modal
	 * @param   array    $attribs   attributes
	 *
	 * @return html
	 */
	public static function getCategoryImage($category, $modal = true, $attribs = null)
	{
		$image_attribs = array('title' => $category->name);

		if ($attribs && is_array($attribs))
		{
			$image_attribs = array_merge($image_attribs, $attribs);
		}

		if ($category->image)
		{
			return static::modalimage($category->image, $category->name, null, $image_attribs);
		}
		else
		{
			return $category->name;
		}
	}
}
