<?php

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

// Load system defines
if (file_exists(dirname(dirname(__FILE__)) . '/defines.php'))
{
	require_once dirname(dirname(__FILE__)) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(dirname(__FILE__)));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.php';
require_once JPATH_LIBRARIES . '/joomla/application/component/helper.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Force library to be in JError legacy mode
JError::$legacy = true;

// Import necessary classes not handled by the autoloaders
jimport('joomla.application.menu');
jimport('joomla.environment.uri');
jimport('joomla.event.dispatcher');
jimport('joomla.utilities.utility');
jimport('joomla.utilities.arrayhelper');

// Import the configuration.
require_once JPATH_CONFIGURATION . '/configuration.php';

// System configuration.
$config = new JConfig;

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once JPATH_BASE . '/components/com_redevent/classes/output.class.php';
require_once JPATH_BASE . '/components/com_redevent/helpers/helper.php';
require_once 'model.php';

$model = new DumpModel();
$sessions = $model->getItems();

function formatDate($session)
{
	if (!redEVENTHelper::isValidDate($session->dates))
	{
		return 'Open date';
	}

	$date = new DateTime($session->dates);

	return $date->format('d-m-Y');
}

function buildLink($session)
{
	$target = $session->custom13;

	if (strstr($target, 'http') !== false)
	{
		return $target;
	}

	if (strpos($target, '/') !== 0)
	{
		$target = "/" . $target;
	}

	return 'https://kurser.ibc.dk' . $target;
}
?>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
</head>
<body>
<table class="table table-striped table-hover">
	<thead>
		<tr>
			<th>Title</th>
			<th>Categories</th>
			<th>Date</th>
			<th>Attendees</th>
			<th>Niveau</th>
			<th>State</th>
			<th>Varighed</th>
			<th>Kursuspris</th>
			<th>Discounted</th>
		</tr>
	</thead>

	<tbody>
		<?php foreach ($sessions as $s): ?>
	<tr>
		<td><a href="<?php echo buildLink($s); ?>"><?php echo $s->title; ?></a></td>
		<td><?php echo implode('<br/>', $s->categories); ?></td>
		<td><?php echo formatDate($s); ?></td>
		<td><?php echo $s->registered; ?></td>
		<td><?php echo str_replace("\n", "<br/>", $s->custom5); ?></td>
		<td><?php echo $s->published ? 'published' : 'unpublished'; ?></td>
		<td><?php echo str_replace("\n", "<br/>", $s->custom6); ?></td>
		<td><?php echo !empty($s->prices) ? implode('<br/>', array_map(function($item) { return $item->price; }, $s->prices)) : '0'; ?></td>
		<td><?php echo !empty($s->prices) ? implode('<br/>', array_map(function($item) { return round($item->price * 0.65); }, $s->prices)) : '0'; ?></td>
	</tr>
<?php endforeach; ?>
</tbody>

</table>
</body>
</html>
