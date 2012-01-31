<?php
/**
 * @version 0.9 $Id$
 * @package Joomla
 * @subpackage RedEvent
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENCE.php
 * RedEvent is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * RedEvent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with RedEvent; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
defined('_JEXEC') or die('Restricted access');
$i = 0;
?>
<table class="redeventmod">
	<thead>
	<tr>
		<?php foreach ($cols as $c): ?>
		<th>
		<?php 
		switch ($c)
		{
			case 'date':
				echo Jtext::_('MOD_REDEVENT_TABLE_HEADER_Date');
				break;
			case 'title':
				echo Jtext::_('MOD_REDEVENT_TABLE_HEADER_Title');
				break;
			case 'category':
				echo Jtext::_('MOD_REDEVENT_TABLE_HEADER_Category');
				break;
			case 'venue':
				echo Jtext::_('MOD_REDEVENT_TABLE_HEADER_Venue');
				break;
			case 'state':
				echo Jtext::_('MOD_REDEVENT_TABLE_HEADER_state');
				break;
			case 'city':
				echo Jtext::_('MOD_REDEVENT_TABLE_HEADER_City');
				break;
			case 'picture':
				echo Jtext::_('MOD_REDEVENT_TABLE_HEADER_PICTURE');
				break;
			default:
				if (strpos($c, 'custom') === 0)
				{
					$customid = intval(substr($c, 6));
					if (isset($customfields[$customid])) {
						echo $customfields[$customid]->name;
					}
				}
		}?>
		</th>
		<?php endforeach; ?>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($list as $item) :  ?>
		<?php $isover = (redEVENTHelper::isOver($item) ? ' isover' : ''); ?>
		<tr class="sectiontableentry<?php echo ($i+1).$isover; ?>">
			
			<?php foreach ($cols as $c): ?>
			<?php 
			switch ($c)
			{
				case 'date':
					$tdclass = 'mre-date';
					if ($params->get('linkdet', 2) == 1) {
						$tdtext = JHTML::link($item->link, $item->dateinfo);
					}
					else {
						$tdtext = $item->dateinfo;
					}
					break;
					
				case 'title':
					$tdclass = 'mre-title';
					if ($params->get('linkdet', 2) == 2) {
						$tdtext = JHTML::link($item->link, $item->title_short);
					}
					else {
						$tdtext = $item->title_short;
					}
					break;
					
				case 'category':
					$tdclass = 'mre-category';
					$tdtext = modRedEventHelper::displayCats($item->categories);
					break;
					
				case 'venue':
					$tdclass = 'mre-venue';
					if ($params->get('linkloc', 1) == 1) {
						$tdtext = JHTML::link($item->venueurl, $item->venue_short);
					}
					else {
						$tdtext = $item->venue_short;
					}
					break;
					
				case 'city':
					$tdclass = 'mre-city';
					$tdtext = $item->city;
					break;
					
				case 'state':
					$tdclass = 'mre-state';
					$tdtext = $item->state;
					break;
					
				case 'picture':
					$tdclass = 'mre-picture';
					$tdtext = redEVENTImage::modalimage($item->datimage, $item->title_short, intval($params->get('picture_size', 30)));
					break;
					
				default:
					if (strpos($c, 'custom') === 0)
					{
						$customid = intval(substr($c, 6));
						$tdclass = 'mre-custom'.$customid;
						if (isset($item->$c)) {
							$tdtext = str_replace("\n", "<br/>", $item->$c);
						}
					}
			}?>			
			<td class="<?php echo $tdclass; ?>">
				<?php echo $tdtext; ?>
			</td>
			<?php endforeach; ?>
		</tr>
		<?php $i = 1 - $i; ?>
	<?php endforeach; ?>
	</tbody>
</table>