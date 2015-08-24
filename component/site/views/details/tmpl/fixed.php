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

if ($this->row->venueid != 0) {
	$venuelink = RedeventHelperRoute::getVenueEventsRoute($this->row->venueslug);
}
?>
<div id="redevent" class="event_id<?php echo $this->row->did; ?> el_details">
	<p class="buttons">
		<?php echo RedeventHelperOutput::mailbutton( $this->row->slug, 'details', $this->params ); ?>

		<?php echo RedeventHelperOutput::printbutton( $this->print_link, $this->params ); ?>

		<?php if ($this->params->get('event_ics', 1)): ?>
			<?php $img = JHTML::image(JURI::base().'media/com_redevent/images/iCal2.0.png', JText::_('COM_REDEVENT_EXPORT_ICS')); ?>
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
		echo Jtext::_('COM_REDEVENT_VIEW_DETAILS_FIXED_SUMMARY_SECTION_TITLE');
    	echo '&nbsp;'.RedeventHelperOutput::editbutton($this->row->did, $this->params, $this->allowedtoeditevent, 'editevent' );
    	?>
	</h2>

	<?php //flyer
	$eventimage = RedeventImage::modalimage($this->row->datimage, $this->row->title);
	echo $eventimage;
	?>

	<dl class="event_info floattext">

		<dt class="title"><?php echo JText::_('COM_REDEVENT_TITLE' ).':'; ?></dt>
    <dd class="title"><?php echo $this->escape($this->row->full_title); ?></dd>

  	<dt class="when"><?php echo JText::_('COM_REDEVENT_WHEN' ).':'; ?></dt>
		<dd class="when">
			<?php
			$tmp = RedeventHelperOutput::formatdate($this->row->dates, $this->row->times);
			if (!empty($this->row->times) && strcasecmp('00:00:00', $this->row->times)) {
				$tmp .= ' ' .RedeventHelperOutput::formattime($this->row->dates, $this->row->times);
			}
			if (!empty($this->row->enddates) && $this->row->enddates != $this->row->dates)
			{
				$tmp .= ' - ' .RedeventHelperOutput::formatdate($this->row->enddates, $this->row->endtimes);
			}
			if (!empty($this->row->endtimes) && strcasecmp('00:00:00', $this->row->endtimes)) {
				$tmp .= ' ' .RedeventHelperOutput::formattime($this->row->dates, $this->row->endtimes);
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
    			<?php echo JHTML::link($this->row->url, $this->escape($this->row->venue)); ?>
				<?php else :?>
    			<?php echo JHTML::link($venuelink, $this->escape($this->row->venue)); ?>
				<?php endif; ?>
				<?php if ($this->escape($this->row->city)): ?>
					<?php echo ' - '.$this->escape($this->row->city); ?>
				<?php endif; ?>

			</dd>

		<?php endif;
		$n = count($this->row->categories);
		?>

		<dt class="category"><?php echo $n < 2 ? JText::_('COM_REDEVENT_CATEGORY' ) : JText::_('COM_REDEVENT_CATEGORIES' ); ?>:</dt>
    		<dd class="category">
    			<?php
				$i = 0;
    			foreach ($this->row->categories as $category) :
    				echo JHTML::link(RedeventHelperRoute::getCategoryEventsRoute($category->slug), $this->escape($category->name));
						$i++;
						if ($i != $n) :
							echo ',';
						endif;
					endforeach;
    			?>
			</dd>
	</dl>

  	<?php
  	$stripped = $this->row->datdescription;
  	$stripped = JFilterOutput::cleanText($stripped);
  	$stripped = trim($stripped);
  	if ($stripped) : ?>

  	    <h2 class="description"><?php echo JText::_('COM_REDEVENT_DESCRIPTION' ); ?></h2>
  		<div class="description event_desc">
				<?php $review_txt =  trim(strip_tags($this->row->review_message));
				echo $this->tags->ReplaceTags($this->row->datdescription, array('hasreview' => (!empty($review_txt))) ); ?>
  		</div>

  	<?php endif; ?>

  	<?php
  	$strip_details = $this->row->details;
  	$strip_details = JFilterOutput::cleanText($strip_details);
  	$strip_details = trim($strip_details);
  	if ($strip_details) : ?>

  	    <h2 class="description"><?php echo JText::_('COM_REDEVENT_SESSION_DETAILS' ); ?></h2>
  		<div class="description event_desc">
			<?php echo $this->tags->ReplaceTags($this->row->details); ?>
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
		  					<span class="event-file-dl-icon hasTooltip" title="<?php echo JText::_('COM_REDEVENT_Download').' '.$this->escape($file->file).'<br/>'.$this->escape($file->description);?>">
		  					<?php echo JHTML::link('index.php?option=com_redevent&task=getfile&format=raw&file='.$file->id,
		  					                       JHTML::image('media/com_redevent/images/download_16.png', JText::_('COM_REDEVENT_Download'))); ?></span>
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
		echo RedeventImage::modalimage($this->row->locimage, $this->row->venue);
		echo RedeventHelperOutput::mapicon($this->row, array('class' => 'event-map'));
		?>

		<dl class="location floattext">
			 <dt class="venue"><?php echo JText::_('COM_REDEVENT_VENUE').':'; ?></dt>
				<dd class="venue">
				<?php echo JHTML::link($venuelink, $this->escape($this->row->venue)); ?>
				<?php if (!empty($this->row->url)) : ?>
					&nbsp; - &nbsp;
					<a href="<?php echo $this->row->url; ?>"> <?php echo JText::_('COM_REDEVENT_WEBSITE' ); ?></a>
				<?php	endif; ?>
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
				<?php endif; ?>

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
    				<?php echo RedeventHelperCountries::getCountryFlag($this->row->country); ?>
    			</dd>
    		<?php endif; ?>
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
		<?php if (RedeventHelperDate::isValidDate($this->row->registrationend)): ?>
			<?php echo strftime('%F', strtotime($this->row->registrationend)); ?>
		<?php endif; ?>
		<?php $registration_status = RedeventHelper::canRegister($this->row->xref); ?>
		<div class="event-registration">
		<?php
		if (!$registration_status->canregister):
			$imgpath = 'media/com_redevent/images/'.$registration_status->error.'.png';
		  $img = JHTML::_('image', JURI::base() . $imgpath,
		                          $registration_status->status,
		                          array('class' => 'hasTooltip', 'title' => $registration_status->status));
			echo RedeventHelperOutput::moreInfoIcon($this->row->xslug, $img, $registration_status->status);
		else : ?>
		<?php $venues_html = '';
		/* Get the different submission types */
		$submissiontypes = explode(',', $this->row->submission_types);
		$imagepath = JURI::base() . 'media/com_redevent/images/';
		foreach ($submissiontypes as $key => $subtype)
		{
			switch ($subtype) {
				case 'email':
					$venues_html .= '<div class="registration_method">'.JHTML::_('link', JRoute::_(RedeventHelperRoute::getSignupRoute('email', $this->row->slug, $this->row->xslug)), JHTML::_('image', $imagepath.$this->elsettings->get('signup_email_img', 'email_icon.gif'),  JText::_($this->elsettings->get('signup_email_text')), 'width="24px" height="24px"')).'</div> ';
					break;
				case 'phone':
					$venues_html .= '<div class="registration_method">'.JHTML::_('link', JRoute::_(RedeventHelperRoute::getSignupRoute('phone', $this->row->slug, $this->row->xslug)), JHTML::_('image', $imagepath.$this->elsettings->get('signup_phone_img', 'phone_icon.gif'),  JText::_($this->elsettings->get('signup_phone_text')), 'width="24px" height="24px"')).'</div> ';
					break;
				case 'external':
		      if (!empty($this->row->external_registration_url)) {
		      	$link = $this->row->external_registration_url;
		      }
		      else {
		      	$link = $this->row->submission_type_external;
		      }
					$venues_html .= '<div class="registration_method hasTooltip" title="'.$this->elsettings->get('signup_external_text').'">'.JHTML::_('link', $link, JHTML::_('image', $imagepath.$this->elsettings->get('signup_external_img', 'external_icon.gif'),  $this->elsettings->get('signup_external_text')), 'target="_blank"').'</div> ';
					break;
				case 'webform':
					if ($this->prices && count($this->prices))
					{
						foreach ($this->prices as $p)
						{
							$title = ' title="'.$p->name.'<br/>'.addslashes(str_replace("\n", "<br/>", $p->tooltip)).'"';
							$img = empty($p->image) ? JHTML::_('image', $imagepath.$this->elsettings->get('signup_webform_img', 'form_icon.gif'),  JText::_($p->name))
							                        : JHTML::_('image', JURI::root().$p->image,  JText::_($p->name));
							$link = JRoute::_(RedeventHelperRoute::getSignupRoute('webform', $this->row->slug, $this->row->xslug, $p->slug));

							$venues_html .= '<div class="registration_method hasTooltip '.$p->alias.'"'.$title.'>'
								             .JHTML::_('link', $link, $img).'</div> ';
						}
					}
					else {
						$venues_html .= '<div class="registration_method webform">'.JHTML::_('link', JRoute::_(RedeventHelperRoute::getSignupRoute('webform', $this->row->slug, $this->row->xslug)), JHTML::_('image', $imagepath.$this->elsettings->get('signup_webform_img', 'form_icon.gif'),  JText::_($this->elsettings->get('signup_webform_text')))).'</div> ';
					}
					break;
				case 'formaloffer':
					$venues_html .= '<div class="registration_methodr">'.JHTML::_('link', JRoute::_(RedeventHelperRoute::getSignupRoute('formaloffer', $this->row->slug, $this->row->xslug)), JHTML::_('image', $imagepath.$this->elsettings->get('signup_formal_offer_img', 'formal_icon.gif'),  JText::_($this->elsettings->get('signup_formal_offer_text')), 'width="24px" height="24px"')).'</div> ';
					break;
			}
		}
		echo $venues_html; ?>
		<?php endif; ?>
		<div class="clear"></div>
		</div>
	<?php endif; ?>

	<?php /* If registration is enabled */
	if ($this->view_attendees_list) : ?>
	<?php $attendees_layout = ($this->params->get('details_attendees_layout', 0) ? 'attendees' : 'attendees_table'); ?>
	<?php echo $this->loadTemplate($attendees_layout); ?>
	<?php endif; ?>

	<?php if ($this->elsettings->get('commentsystem') != 0) :	?>

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
