<?php
	//TOP SECRET DO NOT SHARE TOP SECRET//
	$app_key = '3mmLV70CPlH6EsM7IQqDo9DEO';
	$app_token = 'fG7RHLDtEnbcJDyJ1zHdogrSv37PBwGLfDzeBOcjGHsFX9kHNK';
	//TOP SECRET DO NOT SHARE TOP SECRET//
	$api_base = 'https://api.twitter.com/';
	$bearer_token_creds = base64_encode($app_key.':'.$app_token);
	$opts = array(
		'http'=>array(
			'method' => 'POST',
			'header' => 'Authorization: Basic '.$bearer_token_creds."\r\n".'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
			'content' => 'grant_type=client_credentials'
		)
	);
	$context = stream_context_create($opts);
	$json = file_get_contents($api_base.'oauth2/token',false,$context);
	$result = json_decode($json,true);
	$bearer_token = $result['access_token'];
	$opts = array(
		'http' => array(
			'method' => 'GET',
			'header' => 'Authorization: Bearer '.$bearer_token
		)
	);
	$context = stream_context_create($opts);
        $tweets = json_decode($json,true);
?>
