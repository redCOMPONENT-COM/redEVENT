<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;

// Link to map
$mapimage = RHelperAsset::load('mapsicon.png', 'com_redevent', array('alt' => JText::_('COM_REDEVENT_MAP')));

$attr = JArrayHelper::toString($displayData['attributes']);

JHTML::_('behavior.framework');
JHTML::_('behavior.modal', 'a.venuemap');
?>
<a title="<?php echo JText::_('COM_REDEVENT_MAP'); ?>"
   rel="{handler:'iframe'}"
   href="<?php echo $displayData['link']; ?>"
<?php echo $attr;?>>
	<?php echo $mapimage; ?>
</a>

