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
require_once 'helper.php';
require_once 'tablerow.php';

$model = new DumpModel();
$sessions = $model->getItems();

$rows = DumpHelper::groupSessions($sessions);
$active = DumpHelper::countActive($rows);
$inactive = count($rows) - $active;
?>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
	<script type="text/javascript" src="jquery.tablesorter.min.js"></script>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

	<link rel="stylesheet" href="sorterstyle/style.css">

	<script type="text/javascript">
		$(document).ready(function() {
			$("#dumpTable").tablesorter();

			(function(){
				var showActive = 1;
				var showInactive = 1;

				var filter = function() {
					if (showActive == 1) {
						$("#btn-active").removeClass('btn-default').addClass('btn-success');
						$("tr.active").show();
					}
					else {
						$("#btn-active").removeClass('btn-success').addClass('btn-default');
						$("tr.active").hide();
					}

					if (showInactive == 1) {
						$("#btn-inactive").removeClass('btn-default').addClass('btn-danger');
						$("tr.inactive").show();
					}
					else {
						$("#btn-inactive").removeClass('btn-danger').addClass('btn-default');
						$("tr.inactive").hide();
					}
				}

				$("#btn-active").click(function(){
					showActive = 1 - showActive;
					filter();
				});

				$("#btn-inactive").click(function(){
					showInactive = 1 - showInactive;
					filter();
				});

			})();


		});
	</script>
</head>
<body>
<div id="activecount">
	<button id="btn-active" class="btn btn-success">Active <span class="badge"><?php echo $active; ?></span></button>
	<button id="btn-inactive" class="btn btn-danger">Inactive <span class="badge"><?php echo $inactive; ?></span></button>
</div>
<table id="dumpTable" class="table table-striped table-hover tablesorter">
	<thead>
		<tr>
			<th>Title</th>
			<th>Categories</th>
			<th>Date</th>
			<th>Mødested</th>
			<th>Attendees</th>
			<th>Niveau</th>
			<th>State</th>
			<th>Varighed</th>
			<th>Kursuspris</th>
			<th>Adwords budget</th>
		</tr>
	</thead>

	<tbody>
		<?php foreach ($rows as $row): ?>
	<tr class="<?php echo $row->active ? 'active' : 'inactive' ?>">
		<td><a href="<?php echo $row->link; ?>"><?php echo $row->title; ?></a></td>
		<td><?php echo implode(', ', $row->categories); ?></td>
		<td><?php echo implode(', ', DumpHelper::formatDates($row)); ?></td>
		<td><?php echo implode(', ', $row->venues); ?></td>
		<td><?php echo implode(', ', $row->attendees); ?></td>
		<td><?php echo implode(', ', $row->niveau); ?></td>
		<td><?php echo $row->active ? 'active' : 'inactive'; ?></td>
		<td><?php echo implode(', ', $row->varighed); ?></td>
		<td><?php echo $row->price; ?></td>
		<td><?php echo $row->budget; ?></td>
	</tr>
<?php endforeach; ?>
</tbody>

</table>
</body>
</html>
