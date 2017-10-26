<?php
$req = $this->httpRequest->getInputJSON();

$words = isset($req['translateWords'])
	? (array) $req['translateWords']
	: [];

$commonWords = isset($req['commonOnly'])
	? (bool) $req['commonOnly']
	: true;

$translator = new \Lang\TranslatorJaEn();
$translator->commonWords = $commonWords;
$translator->translate($words);
return $translator->getDocument();