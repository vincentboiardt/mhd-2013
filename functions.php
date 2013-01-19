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
		$song = $echonest->getSongApi()->profile($track['track']['song_id'], array( 'tracks', 'id:spotify-WW' ) );
		$track = $track['track'];
		$track['spotify'] = str_replace( '-WW', '', @$song[0]['tracks'][0]['foreign_id'] );
		$track['file_asset_id'] = $row->file_asset_id;
		$results[] = $track;
	}
	header( 'Content-type: application/json' );

	echo json_encode( $results );
	exit;
} elseif ( isset( $_GET['file'] ) ) {
	// UNSAFE AND HACKY AS FXCK, DON'T KNOW WHAT I'M DOING HERE BUT I NEED TO PROXY THE FILE SOMEHOW, BUT IT WON'T STREAM
	$source_url = $_GET['file'];

	$headers = get_headers( $source_url, 1 );

	header( 'Content-Transfer-Encoding: binary' ); 
	header( 'Content-Type: ' . $headers['Content-Type'] );
	header( 'Content-Length: ' . $headers['Content-Length'] );

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