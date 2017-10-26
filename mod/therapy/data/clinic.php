<?php
namespace Therapy;

$patientDocList = $this->getResourceDir('therapy/wochenziele', 'xml');

$clinic = new Clinic();

$clinic->loadPatients($patientDocList);

if ($data = $this->httpRequest->getInputValue('clinic')) {
	if (is_array($data)) {
		$clinic->setData($data);
		$clinic->save();
	}
}

return $clinic->asNode($dataDoc);