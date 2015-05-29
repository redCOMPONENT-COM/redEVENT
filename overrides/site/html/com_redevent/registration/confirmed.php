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
<?php
	$prices = RedformCore::getSubmissionPrice(JFactory::getApplication()->input->get('submit_key'));

	$total = 0;
	$currency = 'DKK';

	if ($prices)
	{
		foreach ($prices as $p)
		{
			$total += $p->price;
			$currency = $p->currency;
		}
	}
?>
<script type="text/javascript">
	var fb_param = {};
	fb_param.pixel_id = '6016529007931';
	fb_param.value = '<?php echo $total; ?>';
	fb_param.currency = '<?php echo $currency; ?>';
	(function() {
		var fpw = document.createElement('script'); fpw.async = true; fpw.src = '//connect.facebook.net/en_US/fp.js'; var ref = document.getElementsByTagName('script')[0]; ref.parentNode.insertBefore(fpw, ref);
	})();
</script>
<noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/offsite_event.php?id=6016529007931&value=<?php echo $total; ?>&currency=<?php echo $currency; ?>" /></noscript>
<div id="confirmation_message">
	<?php echo $this->message; ?>
</div>
<div><?php echo JHTML::_('link', JRoute::_(RedEventHelperRoute::getDetailsRoute($this->event->slug, JRequest::getInt('xref'))), JText::_('COM_REDEVENT_RETURN_EVENT_DETAILS'), array('class' => 're-back')); ?></div>
