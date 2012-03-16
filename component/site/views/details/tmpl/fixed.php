<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div id="redevent" class="event_id<?php echo $this->row->did; ?> el_details">
	<p class="buttons">
		<?php echo ELOutput::mailbutton( $this->row->slug, 'details', $this->params ); ?>
		
		<?php echo ELOutput::printbutton( $this->print_link, $this->params ); ?>
		
		<?php if ($this->params->get('event_ics', 1)): ?>
			<?php $img = JHTML::image(JURI::base().'components/com_redevent/assets/images/iCal2.0.png', JText::_('COM_REDEVENT_EXPORT_ICS')); ?>
			<?php echo JHTML::link( JRoute::_(RedeventHelperRoute::getDetailsRoute($this->row->slug, $this->row->xslug).'&format=raw&layout=ics', false), 
			                        $img ); ?>
		<?php endif; ?>
	</p>

<?php if ($this->params->def( 'show_page_title', 1 )) : ?>
	<h1 class="componentheading">
		<?php echo $this->row->full_title; ?>
	</h1>
<?php endif; ?>

<!-- Details EVENT -->
	<h2 class="redevent">
		<?php
    	echo $this->row->full_title;
    	echo '&nbsp;'.ELOutput::editbutton($this->item->id, $this->row->did, $this->params, $this->allowedtoeditevent, 'editevent' );
    	?>
	</h2>

	<?php //flyer
	$eventimage = redEVENTImage::modalimage('events', basename($this->row->datimage), $this->row->title);
//  $eventimage = JHTML::image(JURI::root().'/'.$eventimage['original'], $this->row->title, array('title' => $this->row->title));
	echo $eventimage;
	?>

	<dl class="event_info floattext">
		
		<dt class="title"><?php echo JText::_('COM_REDEVENT_TITLE' ).':'; ?></dt>
    <dd class="title"><?php echo $this->escape($this->row->full_title); ?></dd>
  	
  	<dt class="when"><?php echo JText::_('COM_REDEVENT_WHEN' ).':'; ?></dt>
		<dd class="when">
			<?php
			$tmp = ELOutput::formatdate($this->row->dates, $this->row->times);
			if (!empty($this->row->times) && strcasecmp('00:00:00', $this->row->times)) {
				$tmp .= ' ' .ELOutput::formattime($this->row->dates, $this->row->times);
			}
			if (!empty($this->row->enddates) && $this->row->enddates != $this->row->dates)
			{
				$tmp .= ' - ' .ELOutput::formatdate($this->row->enddates, $this->row->endtimes);
			}
			if (!empty($this->row->endtimes) && strcasecmp('00:00:00', $this->row->endtimes)) {
				$tmp .= ' ' .ELOutput::formattime($this->row->dates, $this->row->endtimes);
			}
			echo $tmp;
			?>
		</dd>
  		<?php
  		if ($this->row->venueid != 0) :
  		?>
		    <dt class="where"><?php echo JText::_('COM_REDEVENT_WHERE' ).':'; ?></dt>
		    <dd class="where">
    		<?php if ((!empty($this->row->url))) : ?>

			    <a href="<?php echo $this->row->url; ?>"><?php echo $this->escape($this->row->venue); ?></a> -

			<?php 
			endif;

			echo $this->escape($this->row->city); ?>

			</dd>

		<?php endif; 
		$n = count($this->row->categories);
		?>

		<dt class="category"><?php echo $n < 2 ? JText::_('COM_REDEVENT_CATEGORY' ) : JText::_('COM_REDEVENT_CATEGORIES' ); ?>:</dt>
    		<dd class="category">
    			<?php
				$i = 0;
    			foreach ($this->row->categories as $category) :
    			?>
					<a href="<?php echo JRoute::_( 'index.php?view=categoryevents&id='. $category->slug ); ?>"><?php echo $this->escape($category->catname); ?></a>
				<?php 
					$i++;
					if ($i != $n) :
						echo ',';
					endif;
				endforeach;
    			?>
			</dd>
	</dl>

  	<?php if ($this->row->datdescription != '' && $this->row->datdescription != '<br />') : ?>

  	    <h2 class="description"><?php echo JText::_('COM_REDEVENT_DESCRIPTION' ); ?></h2>
  		<div class="description event_desc">
				<?php $review_txt =  trim(strip_tags($this->row->review_message));
				echo $this->tags->ReplaceTags($this->row->datdescription, array('hasreview' => (!empty($review_txt))) ); ?>
  		</div>

  	<?php endif; ?>
  	
  	<?php if ($this->row->attachments && count($this->row->attachments)):?>
  	    <h2 class="description"><?php echo JText::_( 'COM_REDEVENT_FILES' ); ?></h2>
  		<div>
  			<table class="event-file">
  				<tbody>
  			<?php foreach ($this->row->attachments as $file): ?>
	  				<tr>
	  					<td>
		  					<span class="event-file-dl-icon hasTip" title="<?php echo JText::_('COM_REDEVENT_Download').' '.$this->escape($file->file).'::'.$this->escape($file->description);?>">
		  					<?php echo JHTML::link('index.php?option=com_redevent&task=getfile&format=raw&file='.$file->id, 
		  					                       JHTML::image('components/com_redevent/assets/images/download_16.png', JText::_('COM_REDEVENT_Download'))); ?></span>  			
	  					</td>
	  					<td class="event-file-name"><?php echo $this->escape($file->name ? $file->name : $file->file); ?></td>
	  				</tr>
  				</tbody>
  			<?php endforeach; ?>
  			</table>
  		</div>
  	<?php endif; ?>

<!--  	Venue  -->

	<?php if ($this->row->venueid != 0) : ?>

		<h2 class="location">
			<?php echo JText::_('COM_REDEVENT_VENUE' ) ; ?>
		</h2>

		<?php //flyer
		echo redEVENTImage::modalimage('venues', basename($this->row->locimage), $this->row->venue);
		echo ELOutput::mapicon($this->row, array('class' => 'event-map'));
		?>

		<dl class="location floattext">
			 <dt class="venue"><?php echo JText::_('COM_REDEVENT_VENUE').':'; ?></dt>
				<dd class="venue">
				<?php echo "<a href='".JRoute::_( 'index.php?view=venueevents&id='.$this->row->venueslug )."'>".$this->escape($this->row->venue)."</a>"; ?>

				<?php if (!empty($this->row->url)) : ?>
					&nbsp; - &nbsp;
					<a href="<?php echo $this->row->url; ?>"> <?php echo JText::_('COM_REDEVENT_WEBSITE' ); ?></a>
				<?php
				endif;
				?>
				</dd>

  			<?php if ( $this->row->street ) : ?>
  			<dt class="venue_street"><?php echo JText::_('COM_REDEVENT_STREET' ).':'; ?></dt>
				<dd class="venue_street">
    				<?php echo $this->escape($this->row->street); ?>
				</dd>
				<?php endif; ?>

				<?php if ( $this->row->plz ) : ?>
  			<dt class="venue_plz"><?php echo JText::_('COM_REDEVENT_ZIP' ).':'; ?></dt>
				<dd class="venue_plz">
    				<?php echo $this->escape($this->row->plz); ?>
				</dd>

				<?php if ( $this->row->city ) : ?>
    			<dt class="venue_city"><?php echo JText::_('COM_REDEVENT_CITY' ).':'; ?></dt>
    			<dd class="venue_city">
    				<?php echo $this->escape($this->row->city); ?>
    			</dd>
    			<?php endif; ?>

    			<?php if ( $this->row->state ) : ?>
    			<dt class="venue_state"><?php echo JText::_('COM_REDEVENT_STATE' ).':'; ?></dt>
    			<dd class="venue_state">
    				<?php echo $this->escape($this->row->state); ?>
    			</dd>
				<?php endif; ?>

				<?php if ( $this->row->country ) : ?>
				<dt class="venue_country"><?php echo JText::_('COM_REDEVENT_COUNTRY' ).':'; ?></dt>
    			<dd class="venue_country">
    				<?php echo redEVENTHelperCountries::getCountryFlag( $this->row->country ); ?>
    			</dd>
    			<?php endif; ?>
			<?php
			endif;
			?>
		</dl>

		<?php if ($this->row->locdescription) :	?>

			<h2 class="location_desc"><?php echo JText::_('COM_REDEVENT_DESCRIPTION' ); ?></h2>
  			<div class="description location_desc">
  				<?php echo $this->row->locdescription;	?>
  			</div>

		<?php endif; ?>

	<?php	endif; ?>
	
	<?php if ($this->row->registra): ?>	
		<h2 class="location_desc"><?php echo JText::_('COM_REDEVENT_Registration' ); ?></h2>
		<?php $registration_status = redEVENTHelper::canRegister($this->row->xref); ?>
		<div class="event-registration">
		<?php 
		if (!$registration_status->canregister):
			$imgpath = 'components/com_redevent/assets/images/'.$registration_status->error.'.png';
		  $img = JHTML::_('image', JURI::base() . $imgpath, 
		                          $registration_status->status, 
		                          array('class' => 'hasTip', 'title' => $registration_status->status));
			echo ELOutput::moreInfoIcon($event->xslug, $img, $registration_status->status);
		else : ?>
		<?php $venues_html = '';	
		/* Get the different submission types */
		$submissiontypes = explode(',', $this->row->submission_types);
		$imagepath = JURI::base() . 'administrator/components/com_redevent/assets/images/';
		foreach ($submissiontypes as $key => $subtype) 
		{
			switch ($subtype) {
				case 'email':
					$venues_html .= '<div class="registration_method">'.JHTML::_('link', JRoute::_(RedeventHelperRoute::getSignupRoute('email', $this->row->slug, $this->row->xslug)), JHTML::_('image', $imagepath.$this->elsettings->signup_email_img,  JText::_($this->elsettings->signup_email_text), 'width="24px" height="24px"')).'</div> ';
					break;
				case 'phone':
					$venues_html .= '<div class="registration_method">'.JHTML::_('link', JRoute::_(RedeventHelperRoute::getSignupRoute('phone', $this->row->slug, $this->row->xslug)), JHTML::_('image', $imagepath.$this->elsettings->signup_phone_img,  JText::_($this->elsettings->signup_phone_text), 'width="24px" height="24px"')).'</div> ';
					break;
				case 'external':
		      if (!empty($this->row->external_registration_url)) {
		      	$link = $this->row->external_registration_url;
		      }
		      else {
		      	$link = $this->row->submission_type_external;
		      }
					$venues_html .= '<div class="registration_method hasTip" title="::'.$this->elsettings->signup_external_text.'">'.JHTML::_('link', $link, JHTML::_('image', $imagepath.$this->elsettings->signup_external_img,  $this->elsettings->signup_external_text), 'target="_blank"').'</div> ';
					break;
				case 'webform':
					if ($this->prices && count($this->prices))
					{
						foreach ($this->prices as $p) 
						{
							$title = ' title="'.$p->name.'::'.addslashes(str_replace("\n", "<br/>", $p->tooltip)).'"';
							$img = empty($p->image) ? JHTML::_('image', $imagepath.$this->elsettings->signup_webform_img,  JText::_($p->name)) 
							                        : JHTML::_('image', $imagepath.$p->image,  JText::_($p->name));
							$link = JRoute::_(RedeventHelperRoute::getSignupRoute('webform', $this->row->slug, $this->row->xslug, $p->slug));
							
							$venues_html .= '<div class="courseinfo_vlink courseinfo_webform hasTip '.$p->alias.'"'.$title.'>'
								             .JHTML::_('link', $link, $img).'</div> ';
						}
					}
					else {
						$venues_html .= '<div class="registration_method webform">'.JHTML::_('link', JRoute::_(RedeventHelperRoute::getSignupRoute('webform', $this->row->slug, $this->row->xslug)), JHTML::_('image', $imagepath.$this->elsettings->signup_webform_img,  JText::_($this->elsettings->signup_webform_text))).'</div> ';
					}
					break;
				case 'formaloffer':
					$venues_html .= '<div class="registration_methodr">'.JHTML::_('link', JRoute::_(RedeventHelperRoute::getSignupRoute('formaloffer', $this->row->slug, $this->row->xslug)), JHTML::_('image', $imagepath.$this->elsettings->signup_formal_offer_img,  JText::_($this->elsettings->signup_formal_offer_text), 'width="24px" height="24px"')).'</div> ';
					break;
			}
		}
		echo $venues_html; ?>
		<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php /* If registration is enabled */
	if ($this->row->show_names) : ?>
	<?php $attendees_layout = ($this->params->get('details_attendees_layout', 0) ? 'attendees' : 'attendees_table'); ?>
	<?php echo $this->loadTemplate($attendees_layout); ?>
	<?php endif; ?>
	
	<?php if ($this->elsettings->commentsystem != 0) :	?>
	
		<!-- Comments -->
		<?php echo $this->loadTemplate('comments'); ?>
		
  	<?php endif; ?>

	<ul class="redevent-social">
			<?php if ($this->params->get('fbopengraph', 0)):?>
			<li class="fb-like"><div><fb:like send="true" layout="button_count" width="90" show_faces="false" font=""></fb:like></div></li>
		  <?php endif; ?>
			<?php if ($this->params->get('gplusone', 0)): ?>
			<li class="plusonebutton"><div><g:plusone size="small"></g:plusone></div></li>
			<?php endif;?>
			<?php if ($this->params->get('tweet', 0)):?>
			<li class="tweetevent">
				<div>
					<a href="http://twitter.com/share" 
					   class="twitter-share-button" 
					   data-text="<?php echo $this->row->full_title; ?>" 
					   data-count="horizontal" 
					   <?php echo ($this->params->get('tweet_recommend') ? 'data-via="'.$this->params->get('tweet_recommend').'"' : ''); ?> 
					   <?php if ($this->params->get('tweet_recommend2')) {
					   	if ($this->params->get('tweet_recommend2_text')) {
					   		$text = 'data-related="'.$this->params->get('tweet_recommend2').':'.htmlspecialchars($this->params->get('tweet_recommend2_text')).'"';
					   	}
					   	else {
					   		$text = 'data-related="'.$this->params->get('tweet_recommend2').'"';
					   	}
					   	echo $text;
					   } 
					   ?>
					   data-lang="<?php echo substr($this->lang->getTag(), 0, 2); ?>">Tweet</a>
				</div></li>
		  <?php endif; ?>
	</ul>
	
</div>