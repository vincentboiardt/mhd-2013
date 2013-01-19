<?php
include('lib/EchoNest/Autoloader.php');
EchoNest_Autoloader::register();

$api_key = 'BHEQZ7KHOSFVZVB82';
$consumer_key = '4b87229f69f93ab2de275782d96b9004';
$consumer_secret = 'JIVIRhTETFeJ08lcdu5cDw';

$echonest = new EchoNest_Client();
$echonest->authenticate($api_key);

$timestamp = json_decode( file_get_contents( 'http://developer.echonest.com/api/v4/oauth/timestamp?api_key=' . $api_key ) );

if ( isset( $_GET['access'] ) ) {
	$id = $_GET['access'];
	
	$sandboxApi = $echonest->getSandboxApi()->setOAuthConfig(array(
		"consumer_key" => $consumer_key,
		"consumer_secret" => $consumer_secret
	))->setSandbox('emi_bluenote');

	$result = $sandboxApi->access($id);

	echo json_encode( $result );
	exit;
}
?>
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
			<p><input type="text" id="search" name="combined" placeholder="Artist and title..." value="skull snaps new day"></p>
			<p><input type="text" id="bpm" placeholder="BPM" disabled><input type="hidden" name="bpm"></p>
			<input type="submit" class="btn-primary btn" value="Search">
		</form>
		<table id="result" class="table table-striped"></table>
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
<script src="remix.js"></script>
<script src="app.js"></script>
<script>
App.key = '<?php echo $api_key; ?>';
App.timestamp = '<?php echo $timestamp->response->current_time; ?>';
</script>
</body>
</html>