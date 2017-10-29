<?php
/***********************************************************************
 * DOMHelper v1.00 11.07.2014 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.00 11.07.2014
 *			initial release
 ***********************************************************************/
namespace Slothsoft\Core;

use DOMImplementation;
use DOMXPath;
use DOMDocument;
use DOMNode;
use XSLTProcessor;
use Exception;
declare(ticks = 1);

class DOMHelper
{

    const NS_CMS_MODULE = 'http://schema.slothsoft.net/cms/module';

    const NS_CMS_DICT = 'http://schema.slothsoft.net/cms/dictionary';

    const NS_SAVE_EDITOR = 'http://schema.slothsoft.net/savegame/editor';

    const NS_XML = 'http://www.w3.org/XML/1998/namespace';

    const NS_HTML = 'http://www.w3.org/1999/xhtml';

    const NS_XSL = 'http://www.w3.org/1999/XSL/Transform';

    const NS_XSD = 'http://www.w3.org/2001/XMLSchema';

    const NS_SVG = 'http://www.w3.org/2000/svg';

    const NS_XLINK = 'http://www.w3.org/1999/xlink';

    const NS_ATOM = 'http://www.w3.org/2005/Atom';

    const NS_PHP = 'http://php.net/xpath';

    const NS_EM = 'http://www.mozilla.org/2004/em-rdf#';

    const NS_GD = 'http://schemas.google.com/g/2005';

    const NS_MEDIA = 'http://search.yahoo.com/mrss/';

    const NS_SITEMAP = 'http://www.sitemaps.org/schemas/sitemap/0.9';

    protected static $namespaceList = [
        'module' => self::NS_CMS_MODULE,
        'dict' => self::NS_CMS_DICT,
        'editor' => self::NS_SAVE_EDITOR,
        
        'xml' => self::NS_XML,
        'html' => self::NS_HTML,
        'xsl' => self::NS_XSL,
        'xsd' => self::NS_XSD,
        'svg' => self::NS_SVG,
        'xlink' => self::NS_XLINK,
        'atom' => self::NS_ATOM,
        'php' => self::NS_PHP,
        'em' => self::NS_EM,
        'gd' => self::NS_GD,
        'media' => self::NS_MEDIA,
        'sitemap' => self::NS_SITEMAP
    ];

    const XPATH_NS = 1;

    // loadXPath loads all known namespaces
    const XPATH_HTML = 2;

    // loadXPath loads HTML namespace
    const XPATH_PHP = 4;

    // loadXPath loads PHP functions
    const XPATH_SLOTHSOFT = 8;

    // loadXPath loads Slothsoft namespaces
    public static function loadDocument($filePath, $asHTML = false)
    {
        $document = new DOMDocument();
        if ($asHTML) {
            $document->loadHTMLFile($filePath, LIBXML_PARSEHUGE);
        } else {
            $document->load($filePath, LIBXML_PARSEHUGE);
        }
        return $document;
    }

    public static function loadXPath(DOMDocument $document, $options = self::XPATH_HTML)
    {
        $xpath = new DOMXPath($document);
        $nsList = [];
        if ($options & self::XPATH_NS) {
            foreach (self::$namespaceList as $prefix => $ns) {
                $nsList[$prefix] = $ns;
            }
        }
        if ($options & self::XPATH_HTML) {
            $nsList['html'] = self::NS_HTML;
        }
        if ($options & self::XPATH_PHP) {
            $nsList['php'] = self::NS_PHP;
        }
        if ($options & self::XPATH_SLOTHSOFT) {
            $nsList['module'] = self::NS_CMS_MODULE;
            $nsList['dict'] = self::NS_CMS_DICT;
            $nsList['editor'] = self::NS_SAVE_EDITOR;
        }
        foreach ($nsList as $prefix => $ns) {
            $xpath->registerNamespace($prefix, $ns);
        }
        if ($options & self::XPATH_PHP) {
            $xpath->registerPHPFunctions();
        }
        return $xpath;
    }

    const HTML_FRAME = '<?xml version="1.0" encoding="UTF-8"?><html><body>%s</body></html>';

    protected static $impl;

    protected static function _implementation()
    {
        if (! self::$impl) {
            self::$impl = new DOMImplementation();
        }
        return self::$impl;
    }

    // returns DOMDocumentFragment
    public function parse($xmlCode, DOMDocument $targetDoc = null, $asHTML = false)
    {
        if ($asHTML) {
            $parseDoc = self::_implementation()->createDocument();
            
            $ret = @$parseDoc->loadHTML(sprintf(self::HTML_FRAME, $xmlCode));
            if (! $ret) {
                ob_start();
                $parseDoc->loadHTML(sprintf(self::HTML_FRAME, $xmlCode));
                $error = ob_get_contents();
                ob_end_clean();
                throw new Exception(sprintf('Error loading HTML:%s%s%s%s', PHP_EOL, substr($xmlCode, 0, 1024), PHP_EOL, substr($error, 0, 1024)));
            }
            $rootNode = $parseDoc->documentElement->lastChild;
            
            if ($targetDoc === null) {
                $targetDoc = $parseDoc;
            }
            
            $retFragment = $targetDoc->createDocumentFragment();
            
            $childNodeList = [];
            foreach ($rootNode->childNodes as $childNode) {
                $childNodeList[] = $childNode;
            }
            
            foreach ($childNodeList as $childNode) {
                if ($targetDoc === $parseDoc) {
                    $retFragment->appendChild($childNode);
                } else {
                    $retFragment->appendChild($targetDoc->importNode($childNode, true));
                }
            }
            if ($targetDoc === $parseDoc) {
                while ($targetDoc->hasChildNodes()) {
                    $targetDoc->removeChild($targetDoc->lastChild);
                }
            }
        } else {
            if ($targetDoc === null) {
                $targetDoc = self::_implementation()->createDocument();
            }
            
            $retFragment = $targetDoc->createDocumentFragment();
            
            if (strlen($xmlCode)) {
                $ret = @$retFragment->appendXML($xmlCode);
                if (! $ret) {
                    ob_start();
                    $retFragment->appendXML($xmlCode);
                    $error = ob_get_contents();
                    ob_end_clean();
                    throw new Exception(sprintf('Error loading XML:%s%s%s%s', PHP_EOL, substr($xmlCode, 0, 1024), PHP_EOL, substr($error, 0, 1024)));
                }
            }
        }
        
        return $retFragment;
    }

    // returns string
    public function stringify(DOMNode $sourceNode)
    {
        return $sourceNode->ownerDocument->saveXML($sourceNode);
    }

    public function load($url, $asHTML = false)
    {
        $doc = new DOMDocument();
        if ($asHTML) {
            $doc->loadHTMLFile((string) $url);
        } else {
            $doc->load((string) $url);
        }
        return $doc;
    }

    public function transform($dataDoc, $templateDoc)
    {
        if (! ($dataDoc instanceof DOMDocument)) {
            $dataDoc = $this->load($dataDoc);
        }
        if (! ($templateDoc instanceof DOMDocument)) {
            $templateDoc = $this->load($templateDoc);
        }
        
        $returnAsString = false;
        
        $xslt = new XSLTProcessor();
        
        $xslt->registerPHPFunctions();
        $xslt->importStylesheet($templateDoc);
        
        return $returnAsString ? $xslt->transformToXML($dataDoc) : $xslt->transformToDoc($dataDoc);
    }

    public function transformToFragment($dataDoc, $templateDoc, $targetDoc)
    {
        $finalDoc = $this->transform($dataDoc, $templateDoc);
        
        $retNode = $targetDoc->createDocumentFragment();
        foreach ($finalDoc->childNodes as $node) {
            $retNode->appendChild($targetDoc->importNode($node, true));
        }
        return $retNode;
    }
}