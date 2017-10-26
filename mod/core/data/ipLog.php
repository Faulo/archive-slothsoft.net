<?php

//*
$archive = \Tracking\Manager::getArchive();
$archive->install();
//$res = $archive->import();
if (!$this->httpRequest->getInputValue('parse', 1)) {
	$res = $archive->parse();
}

$view = \Tracking\Manager::getView();
$view->parseRequest($this->httpRequest);

return $view->asNode($dataDoc);










//my_dump($res);

//die();
//*/


if (false) {

//\Tracking\Manager::track();
$manager = new \Tracking\Manager();
//$res = $manager->import();
//my_dump($res);
//die();
//$manager->parse();
//die();


$dataRoot = $dataDoc->createDocumentFragment();

$limit = (int) $this->httpRequest->getInputValue('limit', 500);
$page = (int) $this->httpRequest->getInputValue('page', 1);
$language = $this->httpRequest->getInputValue('language', '');
$encoding = $this->httpRequest->getInputValue('encoding', '');
$showBots = $this->httpRequest->getInputValue('bots', null);
$filterList = $this->httpRequest->getInputValue('filter', []);

$manager->setLimit($limit, $page);

$colList = [
	'REQUEST_TIME_DATE', 'RESPONSE_TIME', 'RESPONSE_MEMORY', 'REMOTE_ADDR', 'RESPONSE_STATUS',
	'REQUEST_METHOD', 'HTTP_HOST', 'REQUEST_URI', 'HTTP_USER_AGENT',
	'HTTP_ACCEPT_LANGUAGE', 'RESPONSE_LANGUAGE', 'HTTP_ACCEPT', 'RESPONSE_TYPE', 
	'HTTP_ACCEPT_ENCODING', 'RESPONSE_ENCODING', 'HTTP_REFERER', 'HTTP_FROM', 'RESPONSE_INPUT',  
];

$manager->setAttributes($colList);

foreach ($colList as $key) {
	$node = $dataDoc->createElement('col');
	$node->appendChild($dataDoc->createTextNode($key));
	$node->setAttribute('form-key', sprintf('filter[%s]', $key));
	if (isset($filterList[$key])) {
		$val = $filterList[$key];
		if (strlen($val)) {
			$node->setAttribute('form-val', $val);
			$manager->addFilter($key, $val);
			//$sql[] = sprintf('%s LIKE "%s"', $key, $val);
		}
	}
	$dataRoot->appendChild($node);
}



$res = $manager->select();

foreach ($res as $id => $arr) {
	/*
	if ($language !== IPLOG_LANG_WILDCARD and preg_match('/(\w+\-\w+)/', $arr['HTTP_ACCEPT_LANGUAGE'], $match) and strtolower($match[1]) !== $language) {
		//continue;
	}
	//*/
	$node = $dataDoc->createElement('log');
	//$node->setAttribute('id', $id);
	foreach ($arr as $key => $val) {
		$node->setAttribute($key, $val);
	}
	$dataRoot->appendChild($node);
}
//$dataDoc->appendChild($dataRoot);

return $dataRoot;

}



const IPLOG_LANG_WILDCARD = '*';
const IPLOG_ENCODING_WILDCARD = '*';

$limit = (int) $this->httpRequest->getInputValue('limit', 500);
$page = (int) $this->httpRequest->getInputValue('page', 1);
$page--;






$language = isset($_REQUEST['language'])
	? strtolower($_REQUEST['language'])
	: IPLOG_LANG_WILDCARD;
$encoding = isset($_REQUEST['encoding'])
	? strtolower($_REQUEST['encoding'])
	: IPLOG_ENCODING_WILDCARD;

$showBots = $this->httpRequest->getInputValue('bots', null);

$dbName = 'cms';
$tableName = 'access_log';

$dbmsTable = \DBMS\Manager::getTable($dbName, $tableName);

$idFilter = '';
if ($showBots !== null) {
	$showBots = (int) $showBots;
	$idList = $dbmsTable->select(
		'id',
		'HTTP_FROM LIKE "_%" OR HTTP_USER_AGENT LIKE "%bot%" OR HTTP_USER_AGENT LIKE "%crawler%" OR HTTP_USER_AGENT LIKE "%yahoo.com%" OR HTTP_USER_AGENT LIKE "%metauri.com%" OR HTTP_USER_AGENT LIKE "%loadimpact.com%"'
	);
	$idFilter = $showBots
		? ' AND id IN ('.implode(',', $idList).')'
		: ' AND id NOT IN ('.implode(',', $idList).')';
}

$langList = [];
$res = $dbmsTable->select(
	'DISTINCT HTTP_ACCEPT_LANGUAGE',
	'1'.$idFilter
);
foreach ($res as $lang) {
	/*
	if (preg_match_all('/(\w+\-\w+)/', $lang, $matches)) {
		foreach ($matches[1] as $match) {
			$langList[strtolower($match)] = true;
		}
	}
	//*/
	if (preg_match('/(\w+\-\w+)/', $lang, $match)) {
		$langList[strtolower($match[1])] = true;
	}
}
$langList = array_keys($langList);
sort($langList);

$res = $dbmsTable->select(
	'DISTINCT HTTP_ACCEPT_ENCODING',
	'1'.$idFilter
);
$encList = [];
foreach ($res as $lang) {
	if (preg_match_all('/([\w-]+)/', $lang, $matches)) {
		foreach ($matches[1] as $match) {
			$encList[strtolower($match)] = true;
		}
	}
}
$tmpList = array_keys($encList);
sort($tmpList);
$encList = [];
foreach ($tmpList as $val) {
	if (is_string($val) and strlen($val) > 1) {
		$encList[] = $val;
	}
}


//$dataDoc = new DOMDocument();
//$dataDoc->appendChild($dataDoc->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="ipLog.xsl"'));

$dataRoot = $dataDoc->createDocumentFragment();


/*
$cols = explode(
	' ',
	'MIBDIRS MYSQL_HOME OPENSSL_CONF PHP_PEAR_SYSCONF_DIR PHPRC TMP HTTP_HOST HTTP_USER_AGENT HTTP_ACCEPT HTTP_ACCEPT_LANGUAGE HTTP_ACCEPT_ENCODING HTTP_CONNECTION HTTP_REFERER HTTP_COOKIE PATH SystemRoot COMSPEC PATHEXT WINDIR SERVER_SIGNATURE SERVER_SOFTWARE SERVER_NAME SERVER_ADDR SERVER_PORT REMOTE_ADDR DOCUMENT_ROOT REQUEST_SCHEME CONTEXT_PREFIX CONTEXT_DOCUMENT_ROOT SERVER_ADMIN SCRIPT_FILENAME REMOTE_PORT GATEWAY_INTERFACE SERVER_PROTOCOL REQUEST_METHOD QUERY_STRING REQUEST_URI SCRIPT_NAME PHP_SELF REQUEST_TIME_FLOAT REQUEST_TIME REQUEST_TIME_DATE HTTP_FROM HTTP_ACCEPT_CHARSET HTTP_CACHE_CONTROL HTTP_DNT HTTP_IF_MODIFIED_SINCE HTTP_EXPECT HTTP_TE CONTENT_LENGTH CONTENT_TYPE HTTP_DATE HTTP_PRAGMA HTTP_KEEP_ALIVE HTTP_VIA HTTP_UA_CPU'
);
//*/
$cols = [
	'REQUEST_TIME_DATE', 'RESPONSE_TIME', 'RESPONSE_MEMORY', 'REMOTE_ADDR', 'RESPONSE_STATUS', 'REQUEST_METHOD', 'HTTP_HOST', 'REQUEST_URI', 'HTTP_USER_AGENT', 'HTTP_ACCEPT_LANGUAGE', 'RESPONSE_LANGUAGE', 'HTTP_ACCEPT', 'RESPONSE_TYPE', 'HTTP_ACCEPT_ENCODING', 'RESPONSE_ENCODING', 'HTTP_REFERER', 'HTTP_FROM', 'RESPONSE_INPUT', 'HTTP_LAST_EVENT_ID'
];
$sql = [];
foreach ($cols as $col) {
	$node = $dataDoc->createElement('col');
	$node->appendChild($dataDoc->createTextNode($col));
	$node->setAttribute('form-key', sprintf('filter[%s]', $col));
	if (isset($_REQUEST['filter']) and isset($_REQUEST['filter'][$col])) {
		$val = $_REQUEST['filter'][$col];
		$node->setAttribute('form-val', $val);
		if (strlen($val)) {
			$sql[] = sprintf('%s LIKE "%s"', $col, $val);
		}
	}
	$dataRoot->appendChild($node);
}

$sql[] = $language === IPLOG_LANG_WILDCARD
	? '1'
	: sprintf('HTTP_ACCEPT_LANGUAGE LIKE "%s"', '%'.$language.'%');

$sql[] = $encoding === IPLOG_ENCODING_WILDCARD
	? '1'
	: sprintf('HTTP_ACCEPT_ENCODING LIKE "%s"', '%'.$encoding.'%');

$sql = implode(' AND ', $sql);

$sql .= $idFilter;

	
$count = $dbmsTable->select(
	'id',
	$sql
);
$count = count($count);

$sql .= sprintf(' ORDER BY CAST(REQUEST_TIME_FLOAT as DECIMAL) DESC, id DESC LIMIT %d OFFSET %d', $limit, $page * $limit);

$log = $dbmsTable->select(
	true,
	$sql
);

for ($i = 0; $i < ceil($count/$limit); $i++) {
	$node = $dataDoc->createElement('page');
	if ($i === $page) {
		$node->setAttribute('active', 'active');
	}
	$node->setAttribute('uri', sprintf('?page=%d&language=%s&encoding=%s', $i+1, $language, $encoding));
	$dataRoot->appendChild($node);
}

array_unshift($langList, IPLOG_LANG_WILDCARD);
foreach ($langList as $lang) {
	$node = $dataDoc->createElement('language');
	if ($language === $lang) {
		$node->setAttribute('active', 'active');
	}
	$node->setAttribute('code', $lang);
	$node->setAttribute('uri', sprintf('?language=%s', $lang));
	$lang = explode('-', $lang);
	if (count($lang) === 2) {
		$node->setAttribute('lang', strtolower($lang[0]));
		$node->setAttribute('region', strtoupper($lang[1]));
	}
	$dataRoot->appendChild($node);
}

array_unshift($encList, IPLOG_ENCODING_WILDCARD);
foreach ($encList as $enc) {
	$node = $dataDoc->createElement('encoding');
	if ($encoding === $enc) {
		$node->setAttribute('active', 'active');
	}
	$node->setAttribute('code', $enc);
	$node->setAttribute('uri', sprintf('?encoding=%s', $enc));
	$dataRoot->appendChild($node);
}

foreach ($log as $arr) {
	if ($language !== IPLOG_LANG_WILDCARD and preg_match('/(\w+\-\w+)/', $arr['HTTP_ACCEPT_LANGUAGE'], $match) and strtolower($match[1]) !== $language) {
		//continue;
	}
	$node = $dataDoc->createElement('log');
	$node->setAttribute('id', $arr['id']);
	foreach ($cols as $key) {
		if (isset($arr[$key]) and strlen($arr[$key]) and $arr[$key] !== '') {
			$val = preg_replace('/^GIF89a.+/s', 'GIF89a', $arr[$key]);
			$node->setAttribute($key, $val);
		}
	}
	$dataRoot->appendChild($node);
}
//$dataDoc->appendChild($dataRoot);

return $dataRoot;