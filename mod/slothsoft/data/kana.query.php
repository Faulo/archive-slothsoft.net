<?php
namespace Slothsoft\CMS;

use Slothsoft\Lang\TranslatorJaEn;

$req = $this->httpRequest->getInputJSON();

$words = isset($req['translateWords'])
	? (array) $req['translateWords']
	: [];

$commonWords = isset($req['commonOnly'])
	? (bool) $req['commonOnly']
	: true;

$translator = new TranslatorJaEn();
$translator->commonWords = $commonWords;
$translator->translate($words);
return $translator->getDocument();