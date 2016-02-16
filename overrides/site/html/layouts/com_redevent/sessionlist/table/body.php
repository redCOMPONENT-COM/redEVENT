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
//print_r($rows);die();
$elsettings = RedeventHelper::config();
$imagepath = JURI::root() . 'media/com_redevent/images/';
$colnames = explode(",", $params->get('lists_columns_names', 'date, title, venue, city, category'));
$colnames = array_map('trim', $colnames);
?>
<tbody>

	<?php if (count($rows) > 0 ) : ?>
		
	<?php //else :

	$k = 0;
	foreach ($rows as $row) :
		//$isover = (RedeventHelper::isOver($row) ? ' isover' : '');

		//Link to details
	$detaillink = JRoute::_( RedeventHelperRoute::getDetailsRoute($row->slug, $row->xslug) );
		//Link to details
	$detaillink = JRoute::_( RedeventHelperRoute::getDetailsRoute($row->slug, $row->xslug) );
	$event_url = JRoute::_('index.php?option=com_redevent&view=details&xref=' . $event->xref . '&id=' . $event->slug);
	$registration_status = RedeventHelper::canRegister($row->xref);
	$user = JFactory::getUser();

	?>
	<tr class="sectiontableentry simplelist <?php echo ($k + 1) . $params->get( 'pageclass_sfx' ). ($row->featured ? ' featured' : ''); ?><?php echo $isover; ?>"
		itemscope itemtype="http://schema.org/Event">
		<td class="col-md-2 col-sm-2 col-xs-12 date">
			<div class="time_event">
				<?php 
				$dates=new DateTime($row->dates);
				$enddates=new DateTime($row->enddates);
				if($enddates != $dates)
				{
					?>
					<span class="day"><a href="<?php echo $detaillink;?>"><?php echo $dates->format('d').' - '.$enddates->format('d');?></a></span>
					<?php
				}
				else
				{
					?>
					<span class="day"><a href="<?php echo $detaillink;?>"><?php echo $dates->format('d');?></a></span>
					<?php
				}
				?>

				
				<span class="monthyear"><?php echo $dates->format('F').' '.$dates->format('Y');?></span>
				<span class="time_start_time_end">
					<?php 
					$times=new DateTime($row->times);
					$endtimes=new DateTime($row->endtimes);
					echo $times->format('G:i').' - '.$endtimes->format('G:i');
					?>
				</span>
			</div>  	

		</td>
		<td class="col-md-4 col-sm-4 col-xs-12 navn">
			<div class="information_event">
				<span class="event-title">								
					<a href="<?php echo $detaillink;?>"><?php echo $row->title;?></a>
				</span>
				<div class="clear"></div>
				<span class="description_venue">
					<?php echo $row->custom6;?>
					

				</span>	
				<div class="event-categories">
					<?php					
					$categories=$row->categories;					
					for($i=0;$i<count($categories);$i++)
					{
						echo('<span class="event_category"># '.$categories[$i]->name.'</span>') ;
					}
					?>			
				</div>	
			</div>
		</td>

		<td class="col-md-4 col-sm-4 col-xs-12 venue">
			<?php echo 'Hos '.$row->custom9.'<br/> ved '.$row->custom1.'<br/>';?>
			
			<?php echo $row->street.',<br/>'.strip_tags($row->locdescription,'<p>').' '.$row->city.' '.$row->state;?>
			<?php //if ($row->street):?>

		</td>
		<td class="col-md-1 col-sm-1 col-xs-12 register">
			<?php if ($user->username=='' || $user->username!=''):?>
			<?php if ($row->registra): 
			$venues_html = '<span class="readmore"><a href="'.$detaillink.'"></a></span></div>';?>
			<?php if (RedeventHelper::isValidDate($row->registrationend)): ?>
				<?php echo strftime('%F', strtotime($row->registrationend)); ?>
			<?php endif; ?>
			<?php $registration_status = RedeventHelper::canRegister($row->xref); ?>
			<?php
			
			if (!$registration_status->canregister):
                            if (($registration_status->error == RedeventRegistrationCanregister::ERROR_HAS_PENDING
                                || $registration_status->error == RedeventRegistrationCanregister::ERROR_USER_MAX)
                                && RedeventHelper::canUnregister($row->xref)
                            )
                            {
                            	echo '<span class="readmore"><a href="'.$detaillink.'"></a></span>';
                            	
                                echo JHtml::link('index.php?option=com_redevent&task=registration.canceluser&xref=' . $row->xref, JText::_('COM_REDEVENT_UNREGISTER'));
                            }
                            else
                            {
				$imgpath = 'media/com_redevent/images/'.$registration_status->error.'.png';
				$img = JHTML::_('image', JURI::base() . $imgpath,
                                    $registration_status->status,
                                    array('class' => 'hasTooltip', 'title' => $registration_status->status)
                                );
                                echo RedeventHelperOutput::moreInfoIcon($row->xslug, $img, $registration_status->status);
                            }
			else :// echo $venues_html;?>
			<?php
			/* Get the different submission types */
			$submissiontypes = explode(',', $row->submission_types);
			$venues_html = '';
			$imagepath = JURI::base() . 'media/com_redevent/images/';

			foreach ($submissiontypes as $key => $subtype)
			{
				
				switch ($subtype)
				{
					
					case 'webform':
					if ($row->prices && count($row->prices))
					{
						foreach ($row->prices as $p)
						{
							$title = ' title="' . $p->name . '<br/>' . addslashes(str_replace("\n", "<br/>", $p->tooltip)) . '"';
							$img = empty($p->image) ? JHTML::_('image', $imagepath . $elsettings->get('signup_webform_img', 'form_icon.gif'),  
								JText::_($elsettings->get('signup_webform_text')))
							: JHTML::_('image', $p->image,  JText::_($p->name));
							$link = JRoute::_(RedeventHelperRoute::getSignupRoute('webform', $event->slug, $event->xslug, $p->slug));

							$venues_html .= 'div class="vlink formaloffer hasTooltip ' . $p->alias . '"' . $title . '>'
							. JHTML::_('link', $link, $img) . ' ';
							$venues_html .= '<span class="readmore"><a href="'.$detaillink.'"></a></span></div>';

						}
					}
					else
					{
						$venues_html .= '<div class="vlink formaloffer">' .JHTML::_('link', JRoute::_(RedeventHelperRoute::getSignupRoute('webform', $row->slug, $row->xslug)), JHTML::_('image', $imagepath . $elsettings->get('signup_webform_img', 'form_icon.gif'),  JText::_($elsettings->get('signup_webform_text')))). ' ';
						$venues_html .= '<span class="readmore"><a href="'.$detaillink.'"></a></span></div>';
					}
					break;
					case 'formaloffer':

					$venues_html='<div class="vlink formaloffer">'.'
					<a href="'.JRoute::_('index.php?option=com_redevent&view=signup&subtype=formaloffer&task=signup&xref=' . $row->xref . '&id=' . $row->id).'">'.JText::_('COM_REDEVENT_EVENT_REGISTER').'</a>
					<span class="readmore"><a href="'.$detaillink.'"></a></span></div>';
					break;
				}
			}

			echo $venues_html;
			?>
		<?php endif; ?>
		<?php endif; ?>
		<div class="clear"></div>
	<?php else:
		/* Get the different submission types */
			$submissiontypes = explode(',', $row->submission_types);
			$venues_html = '';


			foreach ($submissiontypes as $key => $subtype)
			{
				
				switch ($subtype)
				{
					
					case 'webform':
					if ($row->prices && count($row->prices))
					{
						foreach ($row->prices as $p)
						{
							
							$venues_html .= '<span class="readmore"><a href="'.$detaillink.'"></a></span></div>';

						}
					}
					else
					{
						$venues_html .= '<span class="readmore"><a href="'.$detaillink.'"></a></span></div>';
					}
					break;
					case 'formaloffer':

					$venues_html='<span class="readmore"><a href="'.$detaillink.'"></a></span></div>';
					break;
				}
			}

			echo $venues_html;
	?>

	<?php endif; ?>

	</td>

</tr>

<?php $k = 1 - $k; ?>
<?php endforeach; ?>
<?php endif; ?>

</tbody>
