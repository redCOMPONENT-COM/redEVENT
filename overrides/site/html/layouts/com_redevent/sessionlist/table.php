<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;
$jinput = JFactory::getApplication()->input;
$layout=JRequest::getVar('view');
$component=JRequest::getVar('option');
$filter=JRequest::getVar('filter');
$rows = $displayData['rows'];

?>
<div id="upcomingevents">
	<?php /*if view search*/ ?>
	<?php if($component =='com_redevent' && count($rows) > 0)
	{

		if($layout=='search')
			{?>
				<div class="featured_events search-results-event" style="display:none;">
					<?php echo $this->sublayout('featuredsearch',$displayData);?>
				</div>
				<div class="table-responsive search-results-event">
				<table class=" content_upcoming_events search-results-event" summary="eventlist search-results-event" style="clear:both;" id="simplelist">
					<?php if ($print): ?>
						<?php echo $this->sublayout('head', $displayData); ?>
						<?php echo $this->sublayout('bodyPrint', $displayData); ?>
					<?php else: ?>
						<?php echo $this->sublayout('head', $displayData); ?>
						<?php echo $this->sublayout('bodysearch', $displayData); ?>
					<?php endif; ?>
				</table>

			</div>

		<?php } 
		else
		{
			?>
			<!-- For featured events-->
			<?php
				if($layout =='simplelist' && $layout!='categoryevents' && $layout!='day' && $layout!='archive')
					{?>
				<div class="featured_events">
					<?php echo $this->sublayout('featured',$displayData);?>
				</div>
				<?php }


			?>
			<div class="table-responsive">
				<table class=" content_upcoming_events" summary="eventlist" style="clear:both;" id="simplelist">
					<?php if ($print): ?>
						<?php echo $this->sublayout('head', $displayData); ?>
						<?php echo $this->sublayout('bodyPrint', $displayData); ?>
					<?php else: ?>
						<?php echo $this->sublayout('head', $displayData); ?>
						<?php echo $this->sublayout('body', $displayData); ?>
					<?php endif; ?>
				</table>

			</div>
			<?php 
		}
	}
	else
{?>
	<div class="alert alert-info" role="alert">
		<div class="info">
  		<?php  echo JText::_('COM_REDEVENT_NO_EVENTS' ); ?>
  		</div>
	</div>
<?php } ?>
		
	</div>

