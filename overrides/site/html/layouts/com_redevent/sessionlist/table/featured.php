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
/*print_r($rows);die();*/

$colnames = explode(",", $params->get('lists_columns_names', 'date, title, venue, city, category'));
$colnames = array_map('trim', $colnames);
?>





<?php if (!count($rows)) : ?>
	
<?php else :

	$k = 0;
	foreach ($rows as $row) :
		$isover = (RedeventHelper::isOver($row) ? ' isover' : '');

		//Link to details
	$detaillink = JRoute::_( RedeventHelperRoute::getDetailsRoute($row->slug, $row->xslug) );
	$event_url = JRoute::_('index.php?option=com_redevent&view=details&xref=' . $event->xref . '&id=' . $event->slug);

	
	if($row->featured==1)
	{


		?>
	<div class="feature">
		<div class="row">
			<div class="time_event col-md-4 col-sm-4 col-xs-12">
				<?php 
				$dates=new DateTime($row->dates);?>
				<span class="day"><?php echo $dates->format('d');?></span>
				<span class="monthyear"><?php echo $dates->format('F').' '.$dates->format('Y');?></span>
				<span class="time_start_time_end">
					<?php 
					$times=new DateTime($row->times);
					$endtimes=new DateTime($row->endtimes);
					echo $times->format('G:i').' - '.$endtimes->format('G:i');
					?>
				</span>
			</div>
			<div class="information_event col-md-8 col-sm-8 col-xs-12">
				<span class="event-title">								
					<?php echo JHTML::_('link', $detaillink,$row->title); ?>
				</span>
				<div class="clear"></div>
				<span class="description">
					<?php echo 'Hos '.$row->custom9.' ved '.$row->custom1.'<br/>';?>
				</span>
				<span class="event-categories">
					<?php					
					$categories=$row->categories;					
					for($i=0;$i<count($categories);$i++)
					{
						echo('<span class="event_category"># '.$categories[$i]->name.'</span>') ;
					}
					?>			
				</span>	
				<span class="description_venue">
					<?php echo $row->details;?>
				</span>	
				<span class="readmore"><a href="<?php echo $detaillink; ?>"></a></span>				
			</div>


		</div>
	</div>
	<?php }?>
	




	<?php $k = 1 - $k; ?>
<?php endforeach; ?>
<?php endif; ?>


