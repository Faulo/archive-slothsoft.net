<?php
namespace Slothsoft\Therapy;

use Slothsoft\Core\DOMHelper;
use DOMDocument;

class Clinic
{

    protected $patientList;

    public function loadPatients(array $documentList)
    {
        $this->patientList = [];
        foreach ($documentList as $document) {
            $xpath = DOMHelper::loadXPath($document);
            $nodeList = $xpath->evaluate('//patient');
            foreach ($nodeList as $node) {
                $patientPath = $node->parentNode->getAttribute('realpath');
                $patientName = basename($node->parentNode->getAttribute('uri'));
                $patient = new Patient($xpath, $node);
                $patient->setName($patientName);
                $this->patientList[$patientPath] = $patient;
            }
        }
    }

    public function getPatientByName($name)
    {
        $ret = null;
        foreach ($this->patientList as $patient) {
            if ($patient->getName() === $name) {
                $ret = $patient;
                break;
            }
        }
        return $ret;
    }

    public function setData(array $data)
    {
        foreach ($data as $patientName => $arr) {
            if ($patient = $this->getPatientByName($patientName)) {
                $patient->setData($arr);
            }
        }
    }

    public function save()
    {
        foreach ($this->patientList as $patientPath => &$patient) {
            $doc = new DOMDocument();
            $patient->clearEmpty();
            $node = $patient->asNode($doc);
            $doc->appendChild($node);
            $doc->save($patientPath);
            
            $xpath = DOMHelper::loadXPath($doc);
            $patient = new Patient($xpath, $node);
        }
        unset($patient);
    }

    public function asNode(DOMDocument $doc)
    {
        $retNode = $doc->createElement('clinic');
        foreach ($this->patientList as $patient) {
            $retNode->appendChild($patient->asNode($doc));
        }
        return $retNode;
    }
}