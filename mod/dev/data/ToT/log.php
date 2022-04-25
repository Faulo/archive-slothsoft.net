<?php
use Slothsoft\CMS\HTTPFile;
use Slothsoft\Core\DOMHelper;

//file_put_contents(__FILE__ . '.txt', print_r([$destination . DIRECTORY_SEPARATOR . $_FILES['log']['name'], isset($_FILES['log']), $_FILES['log']['error'] === UPLOAD_ERR_OK], true));
	
if (isset($_FILES['log']) and $_FILES['log']['error'] === UPLOAD_ERR_OK) {
	$destination = realpath(dirname(__FILE__) . '/../../res/ToT/logs');
	move_uploaded_file($_FILES['log']['tmp_name'], $destination . DIRECTORY_SEPARATOR . $_FILES['log']['name']);
	return;
}

if (isset($_REQUEST['id'])) {
	$id = $_REQUEST['id'];
	$resDir = $this->getResourceDir('/dev/tot-logs', 'status');
	if (isset($resDir[$id])) {
		$path = $resDir[$id]->documentElement->getAttribute('realpath');
		$name = $resDir[$id]->documentElement->getAttribute('path');
		return isset($this->httpRequest->input['standalone'])
			? DOMHelper::loadDocument($path)
			: HTTPFile::createFromPath($path, $name);
	}
}