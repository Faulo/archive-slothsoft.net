<?php

class HTTPRequest
{
 // grabbed from php manual at de.php.net (fopen, from: info at b1g dot de)
    protected $_fp;
 // HTTP socket
    protected $_url;
 // full URL
    protected $_host;
 // HTTP host
    protected $_protocol;
 // protocol (HTTP/HTTPS)
    protected $_uri;
 // request URI
    protected $_port;
 // port
                      
    // constructor
    function __construct($url)
    {
        $this->_url = $url;
        $this->_scan_url();
    }

    // scan url
    function _scan_url()
    {
        $req = $this->_url;
        
        $pos = strpos($req, '://');
        $this->_protocol = strtolower(substr($req, 0, $pos));
        
        $req = substr($req, $pos + 3);
        $pos = strpos($req, '/');
        if ($pos === false) {
            $pos = strlen($req);
        }
        $host = substr($req, 0, $pos);
        
        if (strpos($host, ':') !== false) {
            list ($this->_host, $this->_port) = explode(':', $host);
        } else {
            $this->_host = $host;
            $this->_port = ($this->_protocol == 'https') ? 443 : 80;
        }
        
        $this->_uri = substr($req, $pos);
        if ($this->_uri == '')
            $this->_uri = '/';
    }

    // download URL to string
    function DownloadToString()
    {
        $crlf = "\r\n";
        
        // generate request
        $req = 'GET ' . $this->_uri . ' HTTP/1.0' . $crlf . 'Host: ' . $this->_host . $crlf . $crlf;
        
        // fetch
        $this->_fp = fsockopen(($this->_protocol == 'https' ? 'ssl://' : '') . $this->_host, $this->_port);
        fwrite($this->_fp, $req);
        $response = '';
        while (is_resource($this->_fp) && $this->_fp && ! feof($this->_fp))
            $response .= fread($this->_fp, 1024);
        fclose($this->_fp);
        
        // split header and body
        $pos = strpos($response, $crlf . $crlf);
        if ($pos === false)
            return ($response);
        $header = substr($response, 0, $pos);
        $body = substr($response, $pos + 2 * strlen($crlf));
        
        // parse headers
        $headers = array();
        $lines = explode($crlf, $header);
        foreach ($lines as $line)
            if (($pos = strpos($line, ':')) !== false)
                $headers[strtolower(trim(substr($line, 0, $pos)))] = trim(substr($line, $pos + 1));
        // my_dump($headers);
        // redirection or cookies?
        if (isset($headers['cookies'])) {
            $http = new HTTPRequest($headers['cookies']);
            return ($http->DownloadToString($http));
        } // else return($body); //wtf o.O
        
        if (isset($headers['location'])) {
            $http = new HTTPRequest($headers['location']);
            return ($http->DownloadToString($http));
        } // else return($body);
        
        return ($body);
    }

    public function downloadToDocument()
    {
        $responseText = $this->downloadToString();
        $Doc = new DOMDocument();
        @$Doc->loadHTML($responseText);
        return $Doc;
    }
} //end class HTTPRequest

