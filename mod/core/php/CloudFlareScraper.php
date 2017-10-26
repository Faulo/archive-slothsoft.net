<?php
namespace Slothsoft\Core;

use Slothsoft\CMS\HTTPFile;
use Serializable;

class CloudFlareScraper implements Serializable {
	
	protected $cookieFile;
	protected $trySolving;
	protected $lastURI;
	public function __construct($cookieFile = null, $trySolving = true) {
		$this->cookieFile = $cookieFile === null
			? HTTPFile::getTempFile()
			: $cookieFile;
		$this->trySolving = $trySolving;
	}
	public function getFile($uri) {
		$req = $this->_httpRequest($uri);
		return $req->responseText;
	}
	public function getXPath($uri) {
		$req = $this->_httpRequest($uri);
		return $req->responseXML
			? $this->_loadXPath($req->responseXML)
			: null;
	}
	protected function _httpRequest($uri) {
		$req = new XMLHttpRequest();
		$req->open('GET', $uri);
		$req->setCookieFile($this->cookieFile);
		$req->setRequestHeader('referer', $this->lastURI ? $this->lastURI : $uri);
		$req->send();
		
		if ($req->responseXML) {
			$this->lastURI = $uri;
			
			if ($this->_isProtected($req->responseText)) {
				if ($this->trySolving) {
					$this->trySolving = false;
					$req = $this->_solveChallenge($uri, $req->responseXML, $req->responseText);
				} else {
					$req->responseXML = null;
				}
			}
		}
		
		return $req;
	}
	protected function _solveChallenge($requestURI, DOMDocument $htmlDoc, $html) {
		$ret = null;
		$translationTable = [
			'!+[]' => '1',
			'!![]' => '1',
			'![]' => '0',
			'[]' => '0',
			')+(' => ').(',
		];
		
		if (preg_match('/f, (\w+)={"(\w+)":([^}]+)};/', $html, $match)) {
			//my_dump($match);
			$var = 0;
			$code = sprintf('$var+=%s;', $match[3]);
			$code = strtr($code, $translationTable);
			
			eval($code);
			
			//hLpaLqb.rkJvDPgfESVZ+=+((!+[]+!![]+!![]+!![]+!![]+[])+(+[]));
			$varName = $match[1] . '.' . $match[2];
			$query = sprintf('/%s\.%s.=([^;]+);/', $match[1], $match[2]);
			if (preg_match_all($query, $html, $matchList)) {
				//my_dump($matchList);
				$translationTable[$varName] = '$var';
				
				//my_dump($var);
				foreach ($matchList[0] as $code) {
					$code = strtr($code, $translationTable);
					
					//echo $code . PHP_EOL;
					eval($code);
					//my_dump($var);
				}
				
				//my_dump($var);
				
				$var += strlen(parse_url($requestURI, PHP_URL_HOST));
				
				if ($xpath = $this->_loadXPath($htmlDoc)) {
					$path = $xpath->evaluate('string(//form/@action)');
					$data = [];
					$data['jschl_vc'] = $xpath->evaluate('string(//input[@name = "jschl_vc"]/@value)');
					$data['pass'] = $xpath->evaluate('string(//input[@name = "pass"]/@value)');
					$data['jschl_answer'] = (string) $var;
					
					$uri = sprintf('%s://%s%s?%s',
						parse_url($requestURI, PHP_URL_SCHEME),
						parse_url($requestURI, PHP_URL_HOST),
						$path,
						http_build_query($data)
					);
					//echo $uri . PHP_EOL;
					//echo file_get_contents($options['cookieFile']) . PHP_EOL;
					//my_dump($options);
					sleep(5);
					
					$ret = $this->_httpRequest($uri);
				}
			}
		}
		return $ret;
	}
	protected function _isProtected($html) {
		return (bool) strpos($html, 's,t,o,p,b,r,e,a,k,i,n,g');
	}
	protected function _loadXPath(DOMDocument $doc) {
		return DOMHelper::loadXPath($doc);
	}
	
	public function serialize() {
		return serialize(['cookieFile' => $this->cookieFile, 'trySolving' => $this->trySolving]);
	}
	public function unserialize($data) {
		$data = unserialize($data);
		$this->__construct($data['cookieFile'], $data['trySolving']);
	}
}