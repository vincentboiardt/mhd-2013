<?php include( 'functions.php' ); ?>
<html>
<head>
	<meta charset="utf-8">
	<title>Music Hack Day</title>
	<link rel="stylesheet" href="bootstrap.min.css">
	<link rel="stylesheet" href="style.css">
</html>
<body>
<div class="wrap">
	<div class="search-col nav-bar nav-bar-inverse">
		<h1>MHD</h1>
		<form>
			<p><input type="text" id="search" name="combined" placeholder="Artist and title..." value="david axelrod edge"></p>
			<!--<p><input type="text" id="bpm" placeholder="BPM" disabled><input type="hidden" name="bpm"></p>-->
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
		<h1>Sequencer <button class="btn btn-large btn-primary play">▶</button> <button class="btn btn-large btn-danger stop">∎</button></h1>
		<div class="tracks">
			<div class="help">&plus; Drag &amp; drop tracks here</div>
		</div>
	</div>
</div>
<div id="info" class="modal hide fade" tabindex="-1" role="dialog">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3>Blue Note Beat Builder</h3>
	</div>
	<div class="modal-body">
		<p><em>Another Echonest Remix Project</em></p>
		<p>Find classic break beats and jazz samples from the Blue Note catalog and create your own beat, or try to re-create another famous beat from scratch.</p>
		<h4>Uses</h4>
		<ul>
			<li>The EMI Blue Note Echonest Sandbox to fetch audio files on search.</li>
			<li>Echonest and Echonest Remix JavaScript library to play the audio.</li>
			<li>bshaffer's PHP wrapper for the EchoNest API</li>
			<li>Some nifty Twitter Bootstrap and jQuery UI stuff</p>
		</ul>
		<p><a href="https://github.com/vincentboiardt/mhd-2013">https://github.com/vincentboiardt/mhd-2013</a></p>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
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