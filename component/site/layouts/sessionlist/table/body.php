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

<?php if (!count($rows)) : ?>
	<tr align="center"><td><?php echo JText::_('COM_REDEVENT_NO_EVENTS' ); ?></td></tr>
<?php else :

	$k = 0;
	foreach ($rows as $row) :
		$isover = (RedeventHelperDate::isOver($row) ? ' isover' : '');

		//Link to details
		$detaillink = JRoute::_( RedeventHelperRoute::getDetailsRoute($row->slug, $row->xslug) );
		?>
		<tr class="sectiontableentry<?php echo ($k + 1) . $params->get( 'pageclass_sfx' ). ($row->featured ? ' featured' : ''); ?><?php echo $isover; ?>"
		    itemscope itemtype="http://schema.org/Event">

			<?php foreach ($columns as $col): ?>
				<?php switch ($col):
					case 'date': ?>
						<td class="re_date">
							<?php if ($row->dates && strtotime($row->dates)): ?>
								<meta itemprop="startDate" content="<?php echo RedeventHelperDate::getISODate($row->dates, $row->times); ?>">
							<?php endif; ?>
							<?php if ($row->enddates && strtotime($row->enddates)): ?>
								<meta itemprop="endDate" content="<?php echo RedeventHelperDate::getISODate($row->enddates, $row->endtimes); ?>">
							<?php endif; ?>

							<?php if ($params->get('link_date', 0)): ?>
								<?php echo JHTML::link($detaillink, RedeventHelperDate::formatEventDateTime($row));	?>
							<?php else: ?>
								<?php echo RedeventHelperDate::formatEventDateTime($row);	?>
							<?php endif; ?>
						</td>
						<?php break;?>

					<?php case 'enddate': ?>
						<td class="re_title" itemprop="enddate">
							<?php if ($row->enddates && strtotime($row->enddates)): ?>
								<meta itemprop="endDate" content="<?php echo RedeventHelperDate::getISODate($row->enddates, $row->endtimes); ?>">
								<?php echo RedeventHelperDate::formatdate($row->enddates, $row->endtimes); ?>
							<?php endif; ?>
						</td>
						<?php break;?>

					<?php case 'title': ?>
						<td class="re_title" itemprop="name"><a href="<?php echo $detaillink ; ?>" itemprop="url"><?php echo $this->escape(RedeventHelper::getSessionFullTitle($row)); ?></a></td>
						<?php break;?>

					<?php case 'venue': ?>
						<td class="re_location"
						    itemprop="location" itemscope itemtype="http://schema.org/Place">
							<?php
							if ($params->get('showlinkvenue',1) == 1 ) :
								echo $row->xref != 0 ? JHTML::link(JRoute::_( RedeventHelperRoute::getVenueEventsRoute($row->venueslug) ), $this->escape($row->venue), 'itemprop="url"') : '-';
							else :
								echo $row->xref ? $this->escape($row->venue) : '-';
							endif;
							?>
							<?php if ($row->street):?>
								<meta itemprop="streetAddress" content="<?php echo $row->street; ?>" />
							<?php endif; ?>
							<?php if ($row->city):?>
								<meta itemprop="addressLocality" content="<?php echo $row->city; ?>" />
							<?php endif; ?>
							<?php if ($row->country):?>
								<meta itemprop="addressCountry" content="<?php echo $row->country; ?>" />
							<?php endif; ?>
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
								if ($params->get('catlinklist', 1) == 1) {
									$cats[] = JHTML::link(RedeventHelperRoute::getCategoryEventsRoute($cat->slug), $cat->name);
								}
								else {
									$cats[] = $this->escape($cat->name);
								}
							}
							echo implode("<br/>", $cats);
							?>
						</td>
						<?php break;?>

					<?php case 'picture': ?>
						<td class="re_places" itemprop="image"><?php echo RedeventImage::modalimage($row->datimage, $row->title, intval($params->get('lists_picture_size', 30))); ?></td>
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
