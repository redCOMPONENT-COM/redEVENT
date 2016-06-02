<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
	<script type="text/javascript" src="plugins/ibc/sessionsdump/jquery.tablesorter.min.js"></script>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

	<link rel="stylesheet" href="plugins/ibc/sessionsdump/sorterstyle/style.css">

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
			<th>AMU Pris</th>
			<th>Standard Pris</th>
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
		<td><?php echo $row->amuprice; ?></td>
		<td><?php echo $row->standardprice; ?></td>
		<td><?php echo $row->budget; ?></td>
	</tr>
<?php endforeach; ?>
</tbody>

</table>
</body>
</html>
