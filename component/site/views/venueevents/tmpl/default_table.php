<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die( 'Restricted access' );

echo RedeventLayoutHelper::render(
	'sessionlist.table',
	array(
		'params' => $this->params,
		'columns' => $this->columns,
		'customs' => $this->customs,
		'rows' => $this->rows,
		'order' => $this->order,
		'orderDir' => $this->orderDir
	)
);
