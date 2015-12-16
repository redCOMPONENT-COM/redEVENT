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
$filter=JRequest::getVar('filter');
function highlightkeyword($str, $search) {
    $highlightcolor = "#daa732";
    $occurrences = substr_count(strtolower($str), strtolower($search));
    $newstring = $str;
    $match = array();
 
    for ($i=0;$i<$occurrences;$i++) {
        $match[$i] = stripos($str, $search, $i);
        $match[$i] = substr($str, $match[$i], strlen($search));
        $newstring = str_replace($match[$i], '[#]'.$match[$i].'[@]', strip_tags($newstring));
    }
 
    $newstring = str_replace('[#]', '<span style="color: '.$highlightcolor.';">', $newstring);
    $newstring = str_replace('[@]', '</span>', $newstring);
    return $newstring;
 
}
 
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
			<?php //print_r($row);die();?>
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
					<?php echo JHTML::_('link', $detaillink,highlightkeyword($row->title, $filter)); 
					
					
					?>
				</span>
				<div class="clear"></div>
				<span class="description">
					<?php echo 'Hos '.highlightkeyword($row->custom9,$filter).' ved '.highlightkeyword($row->custom1,$filter).'<br/>';?>
				</span>
				<span class="event-categories">
					<?php					
					$categories=$row->categories;					
					for($i=0;$i<count($categories);$i++)
					{
						echo('<span class="event_category"># '.highlightkeyword($categories[$i]->name,$filter).'</span>') ;
					}
					?>			
				</span>	
				<span class="description_venue">
					<?php echo highlightkeyword($row->details,$filter);?>
				</span>	
				<span class="readmore"><a href="<?php echo $detaillink; ?>"></a></span>				
			</div>


		</div>
	</div>
	<?php }?>
	




	<?php $k = 1 - $k; ?>
<?php endforeach; ?>
<?php endif; ?>


