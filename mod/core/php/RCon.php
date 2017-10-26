<?php
namespace Slothsoft\Core;

use Exception;

class RCon {
	const PACKET_SIZE 				= 1400;
	const SERVERQUERY_INFO 			= "\xFF\xFF\xFF\xFFTSource Engine Query";
	const REPLY_INFO 				= "\x49";
	const SERVERQUERY_GETCHALLENGE 	= "\xFF\xFF\xFF\xFF\x57";
	const REPLY_GETCHALLENGE		= "\x41";
	const SERVERDATA_RESPONSE_VALUE = 0;
	const SERVERDATA_AUTH_RESPONSE 	= 2; 
	const SERVERDATA_AUTH			= 3;
	const SERVERDATA_EXECCOMMAND	= 2;
	const NULL_BYTE					= "\0";
	
	const ERR_RESPONSE_CONNECT = 'Could not reach server %1$s:%2$s!';
	const ERR_RESPONSE_EMPTY = 'Empty response?!';
	const ERR_RESPONSE_CODE_UNKNOWN = 'Server responded with unknown command code: "%2$s"';
	const ERR_RESPONSE_CODE_AUTH = 'Server authentication failed!';
	protected $socket;
	protected $ip;
	protected $port;
	protected $requestId;
	public $errno;
	public $errstr;
	public function __construct($ip, $port, $password) {
		$this->ip = $ip;
		$this->port = $port;
		$this->requestId = 0;
		@$this->socket = fsockopen('tcp://'.$this->ip, $this->port, $this->errno, $this->errstr, 30);
		if ($this->errno) {
			$this->throwError(self::ERR_RESPONSE_CONNECT, array($this->ip, $this->port));
		}
		$this->send(self::SERVERDATA_AUTH, $password);
	}
	public function execute($message) {
		return $this->send(self::SERVERDATA_EXECCOMMAND, $message);
	}
	public function send($commandId, $messageBody) {
		$this->requestId++;
		$sendData = [];
		$sendData['requestId'] = $this->requestId;
		$sendData['commandId'] = $commandId;
		$sendData['string1'] = $messageBody . self::NULL_BYTE;
		$sendData['string2'] = '' . self::NULL_BYTE;
		
		$sendData['requestId'] = pack('V', $sendData['requestId']);
		$sendData['commandId'] = pack('V', $sendData['commandId']);
		
		$sendString = implode('', $sendData);
		$packetSize = strlen($sendString);
		$packetSize = pack('V', $packetSize);
		
		$sendString = $packetSize . $sendString;
		// Send packet
		//my_dump($sendData);die();
		fwrite($this->socket, $sendString, strlen($sendString));
		
		// Read response
		$responseData = [];
		$responseData['requestId'] = 0;
		$responseData['commandId'] = 0;
		$responseData['string1'] = '';
		$responseData['string2'] = '';
		
		$string = fread($this->socket, 4);
		if ($length = $this->getLong($string)) {
			$string = fread($this->socket, $length);
			$responseData['requestId'] = $this->getLong($string);
			$responseData['commandId'] = $this->getLong($string);
			$responseData['string1'] = $this->getString($string);
			$responseData['string2'] = $this->getString($string);
			switch ($responseData['commandId']) {
				case self::SERVERDATA_RESPONSE_VALUE:
				case self::SERVERDATA_AUTH_RESPONSE:
					if ($responseData['requestId'] === -1) {
						$this->throwError(self::ERR_RESPONSE_CODE_AUTH, $responseData);
					}
					break;
				default:
					$this->throwError(self::ERR_RESPONSE_CODE_UNKNOWN, $responseData);
			}
		} else {
			$this->throwError(self::ERR_RESPONSE_EMPTY, $responseData);
		}
		return $responseData['string1'];
	}
	protected function throwError($string, array $data) {
		throw new Exception(
			vsprintf($string, $data)
		);
	}
	/**
	 * Return a long and split it out of the string
	 *  - unsigned long (32 bit, little endian byte order)
	 *
	 * @param string    $string String
	 */
	protected function getLong(&$string) {
		$ret = 0;
		$data = substr($string, 0, 4);
		$string = substr($string, 4);
		if (strlen($data) === 4) {
			$data = unpack('V', $data);
			$ret = (int) reset($data);
		}
		return $ret;
	}
	/**
	 * Return a string and split it out of the string
	 *
	 * @param string    $string String
	 */
	protected function getString(&$string) {
		$ret = null;
		$data = explode("\0", $string, 2);
		if (isset($data[1])) {
			$string = $data[1];
		}
		if (isset($data[0])) {
			$ret = $data[0];
		}
		return $ret;
	}
}

