<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;

$params = $displayData['params'];
$columns = $displayData['columns'];
$customs = $displayData['customs'];
$rows = $displayData['rows'];

$colnames = explode(",", $params->get('lists_columns_names', 'date, title, venue, city, category'));
$colnames = array_map('trim', $colnames);
?>
<tbody>
<?php if (!count($rows)): ?>
	<tr align="center"><td><?php echo JText::_('COM_REDEVENT_NO_EVENTS' ); ?></td></tr>
<?php else:
	$k = 0;
	foreach ($rows as $row) :
		$isover = (RedeventHelper::isOver($row) ? ' isover' : '');
		?>
		<tr class="sectiontableentry<?php echo ($k + 1) . $params->get( 'pageclass_sfx' ). ($row->featured ? ' featured' : ''); ?><?php echo $isover; ?>">

			<?php foreach ($columns as $col): ?>
				<?php switch ($col):
					case 'date': ?>
						<td class="re_date">
							<?php echo RedeventHelperOutput::formatEventDateTime($row);	?>
						</td>
						<?php break;?>

					<?php case 'title': ?>
						<td class="re_title" itemprop="name"><?php echo $this->escape(RedeventHelper::getSessionFullTitle($row)); ?></td>
						<?php break;?>

					<?php case 'venue': ?>
						<td class="re_location">
							<?php	echo $row->xref ? $this->escape($row->venue) : '-';	?>
						</td>
						<?php break;?>

					<?php case 'city': ?>
						<td class="re_city"><?php echo $row->city ? $this->escape($row->city) : '-'; ?></td>
						<?php break;?>

					<?php case 'country': ?>
						<td class="re_country"><?php echo $row->country ? RedeventHelperCountries::getShortCountryName($row->country) : ''; ?></td>
						<?php break;?>

					<?php case 'countryflag': ?>
						<td class="re_countryflag"><?php echo $row->country ? RedeventHelperCountries::getCountryFlag($row->country) : ''; ?></td>
						<?php break;?>

					<?php case 'state': ?>
						<td class="re_state"><?php echo $row->state ? $this->escape($row->state) : '-'; ?></td>
						<?php break;?>

					<?php case 'category': ?>
						<td class="re_category">
							<?php $cats = array();

							foreach ($row->categories as $cat)
							{
								$cats[] = $this->escape($cat->name);
							}

							echo implode("<br/>", $cats);
							?>
						</td>
						<?php break;?>

					<?php case 'picture': ?>
						<td class="re_places"><?php echo JHTML::image(RedeventImage::getThumbUrl('events', $row->datimage, intval($params->get('lists_picture_size', 30))), $row->title); ?></td>
						<?php break;?>

					<?php case 'places': ?>
						<td class="re_places"><?php echo RedeventHelper::getRemainingPlaces($row); ?></td>
						<?php break;?>

					<?php case 'price': ?>
						<td class="re_prices"><?php echo RedeventHelperOutput::formatListPrices($row->prices); ?></td>
						<?php break;?>

					<?php case 'credits': ?>
						<td class="re_credits"><?php echo $row->course_credit ? $row->course_credit : '-'; ?></td>
						<?php break;?>

					<?php default: ?>
						<?php if (isset($row->$col)):?>
							<td class="re_customs"><?php echo str_replace("\n", "<br/>", $row->$col); ?></td>
						<?php else: ?>
							<td class="re_customs"></td>
						<?php endif;?>
						<?php break;?>

					<?php endswitch;?>
			<?php endforeach;?>
		</tr>

		<?php $k = 1 - $k; ?>
	<?php endforeach; ?>
<?php endif; ?>

</tbody>
