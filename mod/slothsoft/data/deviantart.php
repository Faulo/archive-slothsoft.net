<?php
$retNode = null;

$name = 'FauloLio';
$pw = 'Faultierbaum3000';

$req = new \XMLHttpRequest();
$req->open('POST', 'https://www.deviantart.com/users/login');
$req->followRedirects = false;
$req->send([
	'username' => $name,
	'password'=> $pw,
	'ref' => 'https://www.deviantart.com/users/loggedin',
	'remember_me' => '1',
	'validate_key' => '1379408083',
	'validate_token' => '0a7296b6056ffb999862',
]);
my_dump($req->getAllResponseHeaders());

$req = new \XMLHttpRequest();
$req->open('POST', 'http://my.deviantart.com/global/difi/?');
$req->followRedirects = false;
$req->send([
	'c[]' 	=>  '"Notes","display_folder",["1","0",true]',
	't' 	=> 'json',
	'ui' 	=> urldecode($req::$cookies['userinfo'])
]);
$data = json_decode($req->responseText, true);
if (isset($data['DiFi']['response']['calls'][0]['response']['content']['body'])) {
	$html = $data['DiFi']['response']['calls'][0]['response']['content']['body'];
	$list = get_html_translation_table(HTML_ENTITIES, ENT_HTML5);
	foreach ($list as $key => $val) {
		$html = str_replace($val, htmlentities($key, ENT_SUBSTITUTE | ENT_XML1), $html);
	}
	$tmpDoc = new \DOMDocument();
	$tmpDoc->loadXML($html);
	$tmpDoc->documentElement->setAttribute('host', 'http://my.deviantart.com/notes/');
	$retNode = $tmpDoc->documentElement;
}

return $retNode;