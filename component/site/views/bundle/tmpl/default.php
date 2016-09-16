<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die( 'Restricted access' );

?>
<div id="redevent" class="bundle-details">
<h2><?= $this->bundle->name ?></h2>

	<div class="description"><?= $this->bundle->description ?></div>

	<div id="select-courses">
		<h3><?= JText::_('COM_REDEVENT_VIEW_BUNDLE_SELECT_EVENTS_DATES'); ?></h3>

		<?php foreach ($this->bundle->getBundleEvents() as $bundleEvent): ?>
			<div class="bundle-event">
				<h4><?= $bundleEvent->getEvent()->title ?></h4>

			</div>
		<?php endforeach; ?>
	</div>
</div>
