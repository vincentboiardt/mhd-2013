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
	if ( ! empty( $result['assets'] ) ) {
		foreach( $result['assets'] as &$asset ) {
			$asset['url'] = '?file=' . urlencode( $asset['url'] );
		}
	}

	header( 'Content-type: application/json' );

	echo json_encode( $result );
	exit;
} elseif ( isset( $_GET['search'] ) ) {
	$link = mysql_connect( 'localhost', 'root', '' );
	$db_selected = mysql_select_db( 'mhd', $link );
	$term = mysql_real_escape_string( $_GET['search'] );
	
	$query = "SELECT MATCH(title,artist_name) AGAINST('$term') AS rel, echonest_id, file_asset_id, title, artist_name FROM emi_bluenote
	WHERE MATCH(title,artist_name) AGAINST('$term') AND type = 'audio'
	ORDER BY rel DESC LIMIT 0,10";

	$result = mysql_query( $query );
	$results = array();
	$ids = array();
	while ( $row = mysql_fetch_object($result) ) {
		$track = $echonest->getTrackApi()->profile($row->echonest_id, 'audio_summary');
		$track = $track['track'];
		$track['file_asset_id'] = $row->file_asset_id;
		$results[] = $track;
	}
	header( 'Content-type: application/json' );

	echo json_encode( $results );
	exit;
} elseif ( isset( $_GET['file'] ) ) {
	// UNSAFE AND HACKY AS FXCK
	$source_url = $_GET['file'];

	$headers = get_headers( $source_url, 1 );

	header( 'Content-Transfer-Encoding: binary' ); 
	header( 'Content-Type: ' . $headers['Content-Type'] );
	header( 'Content-Length: ' . $headers['Content-Length'] );
	
	/*$handle = fopen($source_url, "rb");
	while (!feof($handle)){
		echo fread($handle, 256 * 1024 );
		flush();
	}
	fclose($handle);*/
	$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $source_url);
curl_setopt($ch, CURLOPT_WRITEFUNCTION, 'writeCallback');
curl_exec($ch);
	exit;
}

function writeCallback($handle, $data){
	echo $data;
	return strlen($data);
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