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
$class_prefix = 'mre-';
?>
<table class="redeventmod">
	<thead>
	<tr>
		<?php foreach ($cols as $c): ?>
		<?php
		switch ($c)
		{
			case 'date':
				$thtxt = Jtext::_('MOD_REDEVENT_TABLE_HEADER_Date');
				$tdclass = $class_prefix .  'date';
				break;
			case 'title':
				$thtxt = Jtext::_('MOD_REDEVENT_TABLE_HEADER_Title');
				$tdclass = $class_prefix .  'title';
				break;
			case 'category':
				$thtxt = Jtext::_('MOD_REDEVENT_TABLE_HEADER_Category');
				$tdclass = $class_prefix .  'category';
				break;
			case 'venue':
				$thtxt = Jtext::_('MOD_REDEVENT_TABLE_HEADER_Venue');
				$tdclass = $class_prefix .  'venue';
				break;
			case 'state':
				$thtxt = Jtext::_('MOD_REDEVENT_TABLE_HEADER_state');
				$tdclass = $class_prefix .  'state';
				break;
			case 'city':
				$thtxt = Jtext::_('MOD_REDEVENT_TABLE_HEADER_City');
				$tdclass = $class_prefix .  'city';
				break;
			case 'picture':
				$thtxt = Jtext::_('MOD_REDEVENT_TABLE_HEADER_PICTURE');
				$tdclass = $class_prefix .  'picture';
				break;
			case 'webform':
				$thtxt = Jtext::_('MOD_REDEVENT_TABLE_HEADER_REGISTRATION');
				$tdclass = $class_prefix .  'webform';
				break;
			default:
				if (strpos($c, 'custom') === 0)
				{
					$customid = intval(substr($c, 6));
					if (isset($customfields[$customid])) {
						$thtxt = $customfields[$customid]->name;
						$tdclass = $class_prefix .  'custom'.$customid;
					}
				}
		}?>
		<th class="<?php echo $tdclass; ?>"><?php echo $thtxt; ?></th>
		<?php endforeach; ?>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($list as $item) :  ?>
		<?php $isover = (RedeventHelper::isOver($item) ? ' isover' : ''); ?>
		<tr class="sectiontableentry<?php echo ($i+1).$isover; ?>">

			<?php foreach ($cols as $c): ?>
			<?php
			switch ($c)
			{
				case 'date':
					$tdclass = $class_prefix .  'date';
					if ($params->get('linkdet', 2) == 1) {
						$tdtext = JHTML::link($item->link, $item->dateinfo);
					}
					else {
						$tdtext = $item->dateinfo;
					}
					break;

				case 'title':
					$tdclass = $class_prefix .  'title';
					if ($params->get('linkdet', 2) == 2) {
						$tdtext = JHTML::link($item->link, $item->title_short);
					}
					else {
						$tdtext = $item->title_short;
					}
					break;

				case 'category':
					$tdclass = $class_prefix .  'category';
					$tdtext = modRedEventHelper::displayCats($item->categories);
					break;

				case 'venue':
					$tdclass = $class_prefix .  'venue';
					if ($params->get('linkloc', 1) == 1) {
						$tdtext = JHTML::link($item->venueurl, $item->venue_short);
					}
					else {
						$tdtext = $item->venue_short;
					}
					break;

				case 'city':
					$tdclass = $class_prefix .  'city';
					$tdtext = $item->city;
					break;

				case 'state':
					$tdclass = $class_prefix .  'state';
					$tdtext = $item->state;
					break;

				case 'picture':
					$tdclass = $class_prefix .  'picture';
					$tdtext = redEVENTImage::modalimage($item->datimage, $item->title_short, intval($params->get('picture_size', 30)));
					break;


				case 'webform':
					$tdclass = $class_prefix .  'webform';
					$link = JRoute::_(RedeventHelperRoute::getSignupRoute('webform', $item->slug, $item->xslug));
					$img = JHTML::image('modules/mod_redevent/webform_icon.gif', 'register');
					$tdtext = JHTML::link($link, $img, 'class="webform-icon"');
					break;

				default:
					if (strpos($c, 'custom') === 0)
					{
						$customid = intval(substr($c, 6));
						$tdclass = $class_prefix .  'custom'.$customid;
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
