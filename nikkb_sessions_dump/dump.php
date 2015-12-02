<?php

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', $_SERVER['DOCUMENT_ROOT']);
define('JPATH_SITE', JPATH_BASE);

require_once JPATH_BASE . DS . 'libraries' . DS . 'import.php'; // framework
require_once JPATH_BASE . DS . 'configuration.php'; // config file
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
?>
<html>
<head>
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
		<td><?php echo $s->title; ?></td>
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
