<?php include( 'functions.php' ); ?>
<html>
<head>
	<title>Music Hack Day</title>
	<link rel="stylesheet" href="bootstrap.min.css">
	<link rel="stylesheet" href="style.css">
</html>
<body>
<div class="wrap">
	<div class="search-col nav-bar nav-bar-inverse">
		<h1>MHD</h1>
		<form>
			<p><input type="text" id="search" name="combined" placeholder="Artist and title..." value="mystic brew"></p>
			<p><input type="text" id="bpm" placeholder="BPM" disabled><input type="hidden" name="bpm"></p>
			<input type="submit" class="btn-primary btn" value="Search">
		</form>
		<table id="result" class="table table-striped">
			<thead>
				<td>BPM</td>
				<td>Title</td>
			</thead>
			<tbody><tr><td colspan="2">No results</td></tr></tbody>
		</table>
	</div>
	<div class="sequencer-col">
		<h1>Sequencer <button class="btn btn-large btn-primary play">Play</button></h1>
		<div class="tracks">
			<div class="help">&plus; Drag tracks here</div>
		</div>
	</div>
</div>
<script src="jquery.min.js"></script>
<script src="jquery-ui.min.js"></script>
<script src="bootstrap.min.js"></script>
<script src="remix.js"></script>
<script src="app.js"></script>
<script>
App.key = '<?php echo $api_key; ?>';
App.timestamp = '<?php echo $timestamp->response->current_time; ?>';
</script>
</body>
</html>