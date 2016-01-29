<?php
/**
 * @package    RedEVENT
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

$fieldId = JFactory::getApplication()->input->get('fieldId');
$venueId = JFactory::getApplication()->input->get('venueId');
$venueName = JFactory::getApplication()->input->get('name', '', 'string');
?>
<script type="text/javascript">
	parent.window.updateRevenuelist('<?php echo $fieldId; ?>', '<?php echo $venueId; ?>', '<?php echo $venueName; ?>');
</script>
