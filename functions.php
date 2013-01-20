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

	header( 'Content-Type: application/json' );

	echo json_encode( array_filter( $result ) );
	exit;
} elseif ( isset( $_GET['search'] ) ) {
	$services_json = json_decode(getenv("VCAP_SERVICES"),true);
	if ( ! empty( $services_json ) ) {
		$mysql_config = $services_json["mysql-5.1"][0]["credentials"];
		$username = $mysql_config["username"];
		$password = $mysql_config["password"];
		$hostname = $mysql_config["hostname"];
		$port = $mysql_config["port"];
		$db = $mysql_config["name"];
		$link = mysql_connect("$hostname:$port", $username, $password);
		$db_selected = mysql_select_db($db, $link);

	} else {
		$link = mysql_connect( 'localhost', 'root', '' );
		$db_selected = mysql_select_db( 'mhd', $link );
	}
	$term = mysql_real_escape_string( $_GET['search'] );
	
	$query = "SELECT MATCH(title,artist_name) AGAINST('$term') AS rel, echonest_id, file_asset_id, title, artist_name FROM emi_bluenote
	WHERE MATCH(title,artist_name) AGAINST('$term') AND type = 'audio'
	ORDER BY rel DESC LIMIT 0,15";

	$result = mysql_query( $query );
	$results = array();

	while ( $row = mysql_fetch_object($result) ) {
		#$_track = $echonest->getTrackApi()->profile($row->echonest_id, 'audio_summary');
		#$song = $echonest->getSongApi()->profile($track['track']['song_id'], array( 'tracks', 'id:spotify-WW' ) );
		#$track = $_track['track'];
		#$track['spotify'] = str_replace( '-WW', '', @$song[0]['tracks'][0]['foreign_id'] );
		#$track['file_asset_id'] = $row->file_asset_id;
		$obj = json_decode( file_get_contents( 'http://developer.echonest.com/api/v4/track/profile?api_key=' . $api_key . '&format=json&id=' . $row->echonest_id . '&bucket=audio_summary' ) );
		#$song = json_decode( file_get_contents( 'http://developer.echonest.com/api/v4/song/profile?api_key=' . $api_key . '&format=json&id=' . $track->track->song_id . '&bucket=tracks&bucket=id:spotify-WW' ) );

		#$track->track->spotify = str_replace( '-WW', '', $song->response[0] );
		$obj->response->track->file_asset_id = $row->file_asset_id;
		$results[] = $obj->response->track;

	}

	header( 'Content-Type: application/json' );

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