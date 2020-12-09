<?php

class Tn_Exception extends Exception {
	protected $_timestamp;

	public function __construct($message='', $code=0, Exception $previous=null) {
		if ( defined('PHP_VERSION_ID') && PHP_VERSION_ID > 50300 ) {
			parent::__construct($message, $code, $previous);
		} else {
			parent::__construct($message, $code);
		}
		$this->_timestamp = microtime(true);
	}

	public function getTimestamp() {
		return $this->_timestamp;
	}
}

class Exception_ContextException extends Tn_Exception {
}

class Exception_NameException extends Tn_Exception {
	protected $_invalidName = '';

	public function __construct($message='', $code=0, Exception $previous=null, $name=null) {
		parent::__construct($message, $code, $previous);
		if ($name) $this->setInvalidName($name);
	}

	public function getInvalidName() {
		return $this->_invalidName;
	}

	public function setInvalidName($name) {
		$this->_invalidName = (string) $name;
		return $this;
	}
}

class Exception_ValueException extends Tn_Exception {
	protected $_expectedValue = null;
	protected $_providedValue = null;

	public function __construct($message='', $code=0, Exception $previous=null, $expected=null, $provided=null) {
		parent::__construct($message, $code, $previous);
		if ($expected) $this->setExpectedValue($expected);
		if ($provided) $this->setProvidedValue($provided);
	}

	public function getExpectedValue() {
		return $this->_expectedValue;
	}

	public function setExpectedValue($value) {
		$this->_expectedValue = $value;
		return $this;
	}

	public function getProvidedValue() {
		return $this->_providedValue;
	}

	public function setProvidedValue($value) {
		$this->_providedValue = $value;
		return $this;
	}
}

class Network_Telnet_Exception extends Tn_Exception
{
	const CONNECTION = 1;
	const TIMEOUT = 2;
	const NO_MATCH_FOUND = 3;
	const REACHED_EOF = 4;
	const STREAM_NOT_AVAILABLE = 4;
	const NEGOCIATION_FAILED = 5;
}

class Network_Telnet {
	protected static $_telnetProtocolChars = array();
	protected static $_telnetProtocolOptions = array();
	protected $_socket = null;
	protected $_host = '';
	protected $_port = 23;
	protected $_connectionTimeout = 60;
	protected $_timeout = 60;
	protected $_commandDelay = 1000;
	protected $_prompt = '\$';
	protected $_confirmationPrompt = '[confirm]';
	protected $_nextPagePrompt = '--More--';
	protected $_responseBuffer = '';
	protected $_responseBufferStack = array();
	protected $_autoHandleLongResponse = true;
	protected $_profile = false;
	protected $_profileData = array();
	protected $_isConnected = false;
	protected $_cacheLength = 0;
	protected $_cacheDataLength = 0;

	public static function getTelnetProtocolCharacters() {
		if ( !self::$_telnetProtocolChars ) {
			$characters = array();
			$characters['NULL'] = chr(0);
			$characters['EOF'] = chr(236);
			$characters['SUSP'] = chr(237);
			$characters['ABORT'] = chr(238);
			$characters['EOR'] = chr(239);
			$characters['SE'] = chr(240);
			$characters['NOP'] = chr(241);
			$characters['DM'] = chr(242);
			$characters['BRK'] = chr(243);
			$characters['IP'] = chr(244);
			$characters['AO'] = chr(245);
			$characters['AYT'] = chr(246);
			$characters['EC'] = chr(247);
			$characters['EL'] = chr(248);
			$characters['GA'] = chr(249);
			$characters['SB'] = chr(250);
			$characters['WILL'] = chr(251);
			$characters['WONT'] = chr(252);
			$characters['DO'] = chr(253);
			$characters['DONT'] = chr(254);
			$characters['IAC'] = chr(255);
			self::$_telnetProtocolChars = $characters;
		}
		return self::$_telnetProtocolChars;
	}

	public static function getTelnetProtocolOptions() {
		if ( !self::$_telnetProtocolOptions ) {
			$options = array();
			$options['BINARY'] = chr(0);
			$options['ECHO'] = chr(1);
			$options['RCP'] = chr(2);
			$options['SGA'] = chr(3);
			$options['NAMS'] = chr(4);
			$options['STATUS'] = chr(5);
			$options['TM'] = chr(6);
			$options['RCTE'] = chr(7);
			$options['NAOL'] = chr(8);
			$options['NAOP'] = chr(9);
			$options['NAOCRD'] = chr(10);
			$options['NAOHTS'] = chr(11);
			$options['NAOHTD'] = chr(12);
			$options['NAOFFD'] = chr(13);
			$options['NAOVTS'] = chr(14);
			$options['NAOVTD'] = chr(15);
			$options['NAOLFD'] = chr(16);
			$options['XASCII'] = chr(17);
			$options['LOGOUT'] = chr(18);
			$options['BM'] = chr(19);
			$options['DEL'] = chr(20);
			$options['SUPDUP'] = chr(21);
			$options['SUPDUPOUTPUT'] = chr(22);
			$options['SNDLOC'] = chr(23);
			$options['TTYPE'] = chr(48);
			$options['EOR'] = chr(25);
			$options['TUID'] = chr(26);
			$options['OUTMRK'] = chr(27);
			$options['TTYLOC'] = chr(28);
			$options['VT3270REGIME'] = chr(29);
			$options['X3PAD'] = chr(30);
			$options['NAWS'] = chr(31);
			$options['TSPEED'] = chr(32);
			$options['LFLOW'] = chr(33);
			$options['LINEMODE'] = chr(34);
			$options['XDISPLOC'] = chr(35);
			$options['OLD_ENVIRON'] = chr(36);
			$options['AUTHENTICATION'] = chr(37);
			$options['ENCRYPT'] = chr(38);
			$options['NEW_ENVIRON'] = chr(39);
			$options['TN3270'] = chr(40);
			$options['XAUTH'] = chr(41);
			$options['CHARSET'] = chr(42);
			$options['RSP'] = chr(43);
			$options['COM_PORT_OPTION'] = chr(44);
			$options['SUPPRESS_LOCAL_ECHO'] = chr(45);
			$options['TLS'] = chr(46);
			$options['KERMIT'] = chr(47);
			$options['SEND_URL'] = chr(48);
			$options['FORWARD_X'] = chr(49);
			$options['PRAGMA_LOGON'] = chr(138);
			$options['SSPI_LOGON'] = chr(139);
			$options['PRAGMA_HEARBEAT'] = chr(140);
			self::$_telnetProtocolOptions = $options;
		}
		return self::$_telnetProtocolOptions;
	}

	public function __construct($options=array()) {
		if ( is_string($options) ) {
			$options = array('host' => $options);			
		}
		if ( !is_array($options) ) {
			throw new Exception_ValueException("options should be a hostname string, or an associative array of options");
		}
		$this->setOptionsFromArray($options);
	}

	public function __destruct() {
		$this->disconnect(true);
	}

	public function __get($name) {
		$result = $this->getTelnetProtocolCharacter($name);
		if ($result !== null ) return $result;
		$result = $this->getTelnetProtocolOption($name);
		if ($result !== null ) return $result;
		$method = 'get' . ucfirst($name);
		if ( method_exists($this, $method) ) return $this->$method;
		$method = 'get' . $name;
		if ( method_exists($this, $method) ) return $this->$method;
		throw new Exception_NameException(
			"invalid object property. " . get_class($this) . " has no property as {$name}.",
			0, null, $name
		);
	}

	public function __sleep() {
		$lastConnection = $this->_isConnected;
		$this->disconnect(true);
		$this->_isConnected = $lastConnection;
	}

	public function __wakeup() {
		if ($this->_isConnected) $this->connect(true);
	}

	public function getTelnetProtocolOption($option=null) {
		$telnetOptionsList = self::getTelnetProtocolOptions();
		if ($option === null) return $telnetOptionsList;
		if ( is_int($option) ) {
			$key = array_search( chr($option), $telnetOptionsList);
			return (($key !== false) ? $key : null);
		}
		$option = strtoupper($option);
		if ( !array_key_exists($option, $telnetOptionsList) ) return null;
		return $telnetOptionsList[ $option ];
	}

	public function getTelnetProtocolCharacter($character=null) {
		$telnetChars = self::getTelnetProtocolCharacters();
		if ($character === null) return $telnetChars;
		if ( is_int($character) ) {
			$key = array_search( chr($character), $telnetChars);
			return (($key !== false) ? $key : null);
		}
		$character = strtoupper($character);
		if ( !array_key_exists($character, $telnetChars) ) return null;
		return $telnetChars[ $character ];
	}

	public function getHost() {
		return $this->_host;
	}

	public function setHost($host) {
		$this->_host = strval($host);
		return $this;
	}

	public function getPort() {
		return $this->_port;
	}

	public function setPort($port=23) {
		if ( 1 > $port || 65535 < $port) {
			throw new Exception_ValueException("TCP port '{$port}' is out of range of 1-65535");
		}
		$this->_port = intval($port);
		return $this;
	}

	public function getConnectionTimeout() {
		return $this->_connectionTimeout;
	}

	public function setConnectionTimeout($timeout) {
		if (0 > $timeout) {
			throw new Exception_ValueException("connection timeout should not be negative");
		}
		$this->_connectionTimeout = intval($timeout);
		return $this;
	}

	public function getTimeout() {
		return $this->_timeout;
	}

	public function setTimeout($timeout=0) {
		if ( 0 > $timeout) {
			throw new Exception_ValueException("timeout should not be negative");
		}
		$this->_timeout = intval($timeout);
		return $this;
	}

	public function getCommandDelay() {
		return $this->_commandDelay;
	}

	public function setCommandDelay($microsec) {
		if (0 > $microsec) {
			throw new Exception_ValueException("delay should be none-negative integer");
		}
		$this->_commandDelay = intval($microsec);
		return $this;
	}

	public function getPrompt() {
		return $this->_prompt;
	}

	public function setPrompt($prompt) {
		$this->_prompt = strval($prompt);
		return $this;
	}

	public function getConfirmationPrompt() {
		return $this->_confirmationPrompt;
	}

	public function setConfirmationPrompt($prompt) {
		$this->_confirmationPrompt = strval($prompt);
		return $this;
	}

	public function getNextPagePrompt() {
		return $this->_nextPagePrompt;
	}

	public function setNextPagePrompt($prompt) {
		$this->_nextPagePrompt = strval($prompt);
		return $this;
	}

	public function getResponse($clearLastLine=false, $endOfLine="\n") {
		$buffer = explode("\n", $this->_responseBuffer);
		$endOfLine = strval($endOfLine);
		if ( !!$clearLastLine ) {
			array_pop($buffer);
		}
		$buffer = implode($endOfLine,$buffer);
		return $buffer;
	}

	public function getResponseStack() {
		return $this->_responseBufferStack;
	}

	public function emptyResponseStack($endOfLine="\n") {
		$buffer = implode($endOfLine, $this->_responseBufferStack);
		$this->_responseBufferStack = array();
		$this->_cacheLength = 0;
		$this->_cacheDataLength = 0;
		return $buffer;
	}

	public function profileInputOutput($use=true) {
		$this->_profile = true && $use;
		return $this;
	}

	public function getProfileInfo($methodName=null) {
		if ( $methodName === null ) return $this->_profileData;
		$methodName = strval($methodName);
		if ( !method_exists($this, $methodName) ) {
			throw new Exception_ValueException("invalid method name '{$methodName}'", 0);
		}
		if ( !array_key_exists($methodName, $this->_profileData) ) {
			throw new Exception_ValueException("method '{$methodName}' is not profiled", 1);
		}
		return $this->_profileData[$methodName];
	}

	public function setOptionsFromArray(array $options) {
		$methods = get_class_methods($this);
		foreach ($options as $key => $value) {
			$method = 'set' . ucfirst( $key );
			if ( in_array($method, $methods) ) {
				$this->$method($value);
			}
		}
		return $this;
	}

	public function setOptionsFromObject($optionsObject) {
		if ( !is_object($optionsObject) ) {
			throw new Exception_ValueException("parameter should be an object.");
		}
		$internalMethods = get_class_methods($this);
		$externalProperties = get_object_vars($optionsObject);
		foreach ($externalProperties as $key => $value) {
			$method = 'set' . ucfirst( $key );
			if (in_array($method, $internalMethods)) {
				$this->$method($value);
			}
		}
		$externalMethods = get_class_methods($optionsObject);
		foreach ($externalMethods as $otherMethod ) {
			$matchedPattern = array();
			if ( preg_match('/^get(\S+\S*)$/',$otherMethod,$matchedPattern) ) {
				$method = 'set' . $matchedPattern[1];
				if (in_array($method, $internalMethods)) {
					$this->$method( $optionsObject->$otherMethod() );
				}
			}
		}
		return $this;
	}

	public function connect($init=true) {
		if (!$this->_host) {
			throw new Exception_ContextException('no host is specified to connect to.');
		}
		if ($this->_profile) $profileStart = microtime(true);
		$errorCode = 0;
		$errorMessage = '';
		$socket = fsockopen($this->_host, $this->_port, $errorCode, $errorMessage, $this->_connectionTimeout);
		if ( !is_resource($socket) ) {
			throw new Network_Telnet_Exception(
				"Failed to open a TCP connection to '{$this->_host}:{$this->_port}'. error {$errorCode}: {$errorMessage}", Network_Telnet_Exception::CONNECTION
			);
		}
		stream_set_blocking($socket, 1);
		stream_set_timeout($socket, $this->_timeout, 0);
		$this->_responseBufferStack = array();
		$this->_responseBuffer = '';
		$this->_pofileData = array();
		$this->_socket =& $socket;
		$this->_isConnected = true;
		if ($init) $this->_init();
		if ($this->_profile) $this->_profileData['connect'] = (microtime(true) - $profileStart);
		return $this;
	}

	public function disconnect() {
		$this->emptyBuffer();
		if ( is_resource($this->_socket) ) {
			$this->_terminate();
			fclose($this->_socket);
		}
		$this->_socket = null;
		$this->_isConnected = false;
		return $this;
	}

	public function isConnected($poke=true) {
		if ( !is_resource($this->_socket) ) {
			$this->_isConnected = false;
			return false;
		}
		if ( $this->isConnectionTimedOut() ) {
			$this->_isConnected = false;
			return false;
		}
		if ($poke) {
			$this->emptyBuffer();
			$this->writeCommand( $this->IAC . $this->AYT );
			$response = $this->readBytes(1);
			if ( empty($response) ) {
				$this->_isConnected = false;
				return false;
			}
			$this->emptyBuffer();
		}
		$this->_isConnected = true;
		return true;
	}

	public function getConnection($autoConnect=true) {
		if ( !is_resource($this->_socket) && $autoConnect ) {
			$this->connect();
		}
		return $this->_socket;
	}

	public function isConnectionTimedOut() {
		if ( !is_resource($this->_socket) ) return true;
		$metaData = stream_get_meta_data($this->_socket);
		if ( is_array($metaData) && array_key_exists('timed_out', $metaData) && $metaData['timed_out'] ) {
			return true;
		}
		return false;
	}

	protected function _init() {
		$initOptions =
			$this->IAC . $this->WILL . $this->NAWS .
			$this->IAC . $this->WILL . $this->TSPEED .
			$this->IAC . $this->WILL . $this->TTYPE .
			$this->IAC . $this->WILL . $this->NEW_ENVIRON .
			$this->IAC . $this->DO . $this->ECHO .
			$this->IAC . $this->WILL . $this->SGA .
			$this->IAC . $this->DO . $this->SGA .
			$this->IAC . $this->WONT . $this->XDISPLOC .
			$this->IAC . $this->WONT . $this->OLD_ENVIRON .
			$this->IAC . $this->SB .
				$this->NAWS . $this->NULL . chr(80) . $this->NULL . $this->TTYPE .
			$this->IAC . $this->SE .
			$this->IAC . $this->SB . $this->TSPEED . $this->NULL .
				chr(51) . chr(56) . chr(52) . chr(48) . chr(48) . chr(44) . chr(51) .
				chr(56) . chr(52) . chr(48) . chr(48) . $this->IAC . $this->SE .
			$this->IAC . $this->SB . $this->NEW_ENVIRON . $this->NULL . $this->IAC . $this->SE .
			$this->IAC . $this->SB . $this->TTYPE . $this->NULL .
				chr(88) . chr(84) . chr(69) . chr(82) . chr(77) .
			$this->IAC . $this->SE;
		$this->writeCommand($initOptions);
		$this->halt();
		$initOptions =
			$this->IAC . $this->WONT . $this->ECHO .
			$this->IAC . $this->WONT . $this->LINEMODE .
			$this->IAC . $this->DONT . $this->STATUS .
			$this->IAC . $this->WONT . $this->LFLOW;
		$this->writeCommand($initOptions);
		$this->halt();
		return $this;
	}
	protected function _terminate() {	
		try {
			$this->write('exit');
			$this->setTelnetOption($this->LOGOUT);
		} catch(Exception $exp) {
			$exp;
		}
	}

	public function halt($time=null) {
		if ($time === null) {
			$time = $this->_commandDelay;
		} elseif ( 0 > $time ) {
			throw new Exception_ValueException('halt time should not be negative');
		} else {
			$time = floatval($time);
		}
		usleep($time);
		return $this;
	}

	protected function _getChar() {
		if ($this->_profile) $profileStart = microtime(true);
		$char = $this->_socket ? fgetc( $this->_socket ) : false;
		if ($this->_profile) $this->_profileData['getChar'] = (microtime(true) - $profileStart);
		if ( $char === false ) return false;
		return $char;
	}

	public function saveResponseBuffer($emptyCurrentBuffer=true) {
		$buffer = $this->_responseBuffer;
		$this->_cacheDataLength += strlen($buffer);
		if ( $emptyCurrentBuffer ) {
			$this->_responseBuffer = '';
		}
		if ( !empty($buffer) ) {
			$this->_cacheLength = array_push($this->_responseBufferStack, $buffer);
			return $this->_cacheLength;
		}
		return $this;
	}

	public function emptyBuffer() {
		$this->_responseBuffer = '';
		return $this;
	}

	public function parseOptionNegociationString($string=null) {
		$telnetCommand = self::getTelnetProtocolCharacters();
		if ($string === null) $string = $this->readAvailableResponse();
		if ( strpos($telnetCommand['IAC'], $string) === false ) return false;
		$optionSequences = array(
								'do'		=>  $this->IAC . $this->DO,
								'dont'	  =>  $this->IAC . $this->DONT,
								'will'	  =>  $this->IAC . $this->WILL,
								'wont'	  =>  $this->IAC . $this->WONT
							);
		$options = array(
							'do'	=>  array(),
							'dont'  =>  array(),
							'will'  =>  array(),
							'wont'  =>  array()
					);
		foreach($optionSequences as $optionType => $sequence) {
			$parts = array();
			$part = null;
			if ( strpos($string, $sequence) !== false ) {
				$parts = expolode($sequence, $string);
				if (!$parts) continue;
				foreach($parts as $part) {
					if ( strlen($part) !== 1 ) continue;
					if ( $part === $this->IAC ) {
						next($parts);
						continue;
					}
					array_push($options[ $optionType ],$part);
				}
			}
		}
		return $options;
	}

	public function getRefuseResponseForOptionNegociation($options) {
		$wontOptions = array();
		$dontOptions = array();
		if ( array_key_exists('do', $options) && is_array($options['do']) ) $wontOptions = array_merge($wontOptions, $options['do']);
		if ( array_key_exists('dont', $options) && is_array($options['dont']) ) $wontOptions = array_merge($wontOptions, $options['dont']);
		if ( array_key_exists('will', $options) && is_array($options['will']) ) $dontOptions = array_merge($dontOptions, $options['will']);
		if ( array_key_exists('wont', $options) && is_array($options['wont']) ) $dontOptions = array_merge($dontOptions, $options['wont']);
		$response = array();
		$option = null;
		foreach ($dontOptions as $option) {
			array_push($response, $telnetCommand['IAC'] . $telnetCommand['WONT'] . $option);
		}
		foreach($wontOptions as $option) {
			array_push($response, $telnetCommand['IAC'] . $telnetCommand['DONT'] . $option );
		}
		return $response;
	}

	public function negociateTelnetOption($option=null) {
		$telnetOptions = self::getTelnetProtocolOptions();
		if ( !array_key_exists($option, $telnetOptions) ) {
			throw new Exception_ValueException("invalid telnet option '{$option}'.");
		}
		$telnetChars = self::getTelnetProtocolCharacters();
		$this->writeCommand( $telnetChars['IAC'] . $telnetChars['WILL'] . $option );
		$this->halt();
		$results = $this->readBytes(3);
		$remoteOptions = $this->parseOptionNegociationString($results);
		$this->emptyBuffer();
		if ( $remoteOptions['do'] ) {
			return true;
		} else if ( $remoteOptions['dont'] ) {
			return false;
		} else {
			$this->emptyBuffer();
			throw new Network_Telnet_Exception("invalid response from the host. buffer dump: " . $results, Network_Telnet_Exception::NEGOCIATION_FAILED);
		}
	}

	public function orderHostForTelnetOption($option=null) {
		$telnetOptions = self::getTelnetProtocolOptions();
		if ( !array_key_exists($option, $telnetOptions) ) {
			throw new Exception_ValueException("invalid telnet option '{$option}'.", 0);
		}
		$telnetChars = self::getTelnetProtocolCharacters();
		$this->writeCommand( $telnetChars['IAC'] . $telnetChars['DO'] . $option );
		$this->halt();
		$results = $this->readBytes(3);
		$this->emptyBuffer();
		$remoteOptions = $this->parseOptionNegociationString($results);
		if ( $remoteOptions['will'] ) {
			return true;
		} else if ( $remoteOptions['wont'] ) {
			return false;
		} else {
			throw new Network_Telnet_Exception("invalid response from the host. buffer dump: " . $results, Network_Telnet_Exception::NEGOCIATION_FAILED);
		}
	}

	public function setTelnetOption($option, $value=null, $preNegociate=false) {
		if ($preNegociate && !$this->negociateTelnetOption($option) ) {
			return false;
		}
		$command = $this->IAC . $this->SB . $option;
		if ( $value !== null ) $command .= $this->NULL . $value;
		$command .= $this->IAC . $this->SE;
		$this->writeCommand( $command );
		$this->halt();
		$output = $this->readAvailableResponse();
		$this->emptyBuffer();
		return $output;
	}

	public function read() {
		if ( !$this->isConnected(false) ) {
			throw new Network_Telnet_Exception('read operation failed. connection is lost', Network_Telnet_Exception::CONNECTION);
		}
		$this->emptyBuffer();
		if ($this->_profile) $profileStart = microtime(true);
		$this->_responseBuffer = stream_get_contents($this->_socket);		
		if ($this->_profile) $this->_profileData['read'] = (microtime(true) - $profileStart);
		if ( empty($this->_responseBuffer) ) {
			throw new Network_Telnet_Exception("reached end of the stream but read nothing", Network_Telnet_Exception::REACHED_EOF );
		}
		return $this->_responseBuffer;
	}

	public function readAvailableResponse() {
		if ($this->_profile) $profileStart = microtime(true);
		$this->emptyBuffer();
		if ( is_resource($this->_socket) ) {
			$buffer = stream_get_contents($this->_socket);
		} else {
			$buffer = '';
		}
		$this->_responseBuffer = is_string($buffer) ? $buffer : '';
		unset($buffer);
		if ($this->_profile) $this->_profileData['readAvailableResponse'] = (microtime(true) - $profileStart);
		return $this->_responseBuffer;
	}

	public function readBytes($bytes=1) {
		$bytes = floatval($bytes);
		if ( 1 > $bytes ) {
			throw new Exception_ValueException('number of bytes should be positive number');
		}
		if ( !$this->isConnected(false) ) {
			throw new Network_Telnet_Exception(
				'read operation failed. connection is lost',
				Network_Telnet_Exception::CONNECTION
			);
		}
		if ($this->_profile) $profileStart = microtime(true);
		$this->emptyBuffer();
		$this->_responseBuffer = stream_get_contents($this->_socket, $bytes);
		if ($this->_profile) $this->_profileData['readBytes'] = (microtime(true) - $profileStart);
		if ( empty($this->_responseBuffer) ) {
			throw new Network_Telnet_Exception("reached end of the stream but read nothing", Network_Telnet_Exception::REACHED_EOF );
		}
		return $this->_responseBuffer;
	}

	public function readUntil($targetString, $reportNoMatch=false) {
		global $DEBUG;
		if ( !$this->isConnected(false) ) {
			throw new Network_Telnet_Exception(
				'read operation failed. connection is lost',  Network_Telnet_Exception::CONNECTION
			);
		}
		$this->emptyBuffer();
		if ($this->_profile) $profileStart = microtime(true);
		$buffer = '';
		$len = 0;
		$targetStringLength = strlen($targetString);
		$targetStringLastChar = $targetString[ $targetStringLength - 1 ];
		$nextPagePrompt = $this->getNextPagePrompt();
		$nextPagePromptLength = strlen($nextPagePrompt);
		$nextPagePromptLastChar = $nextPagePrompt[ $nextPagePromptLength - 1 ];
		$found = false; $nextPage = false;
		while (true) {
			$char = $this->_getChar();
			if ( $char === false ) {
				break;
			}
			if($nextPage && ord($char) == 8){
				$buffer = substr($buffer,0,$len-1);
			}else{
				$buffer .= $char;
				$len++;
				if($DEBUG>0) {
					$ord = ord($char);
					if($ord>31 && $ord<127 || $ord==10 || $ord==9) echo $char; else printf(" %02x;",$ord);
				}
			}
			if ( $char == $targetStringLastChar && strpos($buffer, $targetString) !== false ) {
				$found = true;
				break;
			}
			if ( $char == $nextPagePromptLastChar && ($pos = strpos($buffer, $nextPagePrompt)) !== false ) {
				$this->write(" ", false);
				$nextPage = true;
			}
		}
		$this->_responseBuffer = $buffer;
		unset($buffer);
		if ($this->_profile) $this->_profileData['readUntil'] = (microtime(true) - $profileStart);
		if ( empty($this->_responseBuffer) ) {
			throw new Network_Telnet_Exception("reached end of the stream but read nothing", Network_Telnet_Exception::REACHED_EOF );
		}
		if ($reportNoMatch && !$found) {
			throw new Network_Telnet_Exception("no match found for '$targetString'", Network_Telnet_Exception::NO_MATCH_FOUND);
		}
		return $this->_responseBuffer;
	}

	public function readUntilRegex($pattern, $reportNoMatch=false) {
		if ( !$this->isConnected(false) ) {
			throw new Network_Telnet_Exception(
				'read operation failed. connection is lost',  Network_Telnet_Exception::CONNECTION
			);
		}
		$this->emptyBuffer();
		if ($this->_profile) $profileStart = microtime(true);
		$buffer = '';
		$found = false;
		while (true) {
			$char = $this->_getChar();
			if ( $char === false ) {
				break;
			}
			$buffer .= $char;
			$found = preg_match($pattern, $buffer);
			if ( $found ) {
				break;
			}
		}
		$this->_responseBuffer = $buffer;
		unset($buffer);
		if ($this->_profile) $this->_profileData['readUntilRegex'] = (microtime(true) - $profileStart);
		if ( empty($this->_responseBuffer) ) {
			throw new Network_Telnet_Exception(
				"reached end of the stream but read nothing", Network_Telnet_Exception::REACHED_EOF
			);
		}
		if ($reportNoMatch && !$found) {
			throw new Network_Telnet_Exception(
				"no match found for '$pattern'", Network_Telnet_Exception::NO_MATCH_FOUND
			);
		}
		return $this->_responseBuffer;
	}

	public function write($buffer, $appendEndOfLine=true, $skipIAC=true ) {
		global $DEBUG;
		if ( !$this->isConnected(false) ) {
			throw new Network_Telnet_Exception(
				'write operation failed. connection is lost', Network_Telnet_Exception::CONNECTION
			);
		}
		if ($this->_profile) $profileStart = microtime(true);
		$appendEndOfLine = !!$appendEndOfLine;
		$buffer = $appendEndOfLine ?  ($buffer . "\n") : strval($buffer);
		$IAC = chr(255);
		if ( $skipIAC ) str_replace($IAC, $IAC . $IAC, $buffer);
		if ( fwrite($this->_socket, $buffer) === false ) {
			$errorCode = socket_last_error();
			if ( function_exists('socket_strerror') ) {
				$errorMessage = socket_strerror($errorCode);
			} else {
				$errorMessage = 'unknown';
			}
			if ($this->_profile) $this->_profileData['write'] = (microtime(true) - $profileStart);
			throw new Network_Telnet_Exception(
				"error writing to stream. error: {$errorMessage}. buffer dump: {$buffer}. code: ". $errorCode,
				Network_Telnet_Exception::STREAM_NOT_AVAILABLE
			);
		}
		if($DEBUG>0 && ($len = strlen($buffer))>0){
			$s="";
			for($i=0; $i<$len; $i++){
				$ch = $buffer[$i]; $ord = ord($ch);
				if($ord>31 && $ord<127) $s.=$ch; else $s.=sprintf(" %02x;",$ord);
			}
			echo "\nout: $s\n";
		} 
		if ($this->_profile) $this->_profileData['write'] = (microtime(true) - $profileStart);
		return $this;
	}

	public function writeCommand($command, $appendEndOfLine=true ) {
		global $DEBUG;
		if ( !$this->isConnected(false) ) {
			throw new Network_Telnet_Exception(
				'write operation failed. connection is lost',
				Network_Telnet_Exception::CONNECTION
			);
		}
		if ($this->_profile) $profileStart = microtime(true);
		$command = ($appendEndOfLine) ? ($command . "\n") : strval($command);
		if ( fwrite($this->_socket, $command) === false ) {
			$errorCode = socket_last_error();
			if ( function_exists('socket_strerror') ) {
				$errorMessage = socket_strerror($errorCode);
			} else {
				$errorMessage = 'unknown';
			}
			if ($this->_profile) $this->_profileData['writeCommand'] = (microtime(true) - $profileStart);
			throw new Network_Telnet_Exception(
				"error writing to stream. error: {$errorMessage}. buffer dump: command: ". $errorCode,
				Network_Telnet_Exception::STREAM_NOT_AVAILABLE
			);
		}
		if($DEBUG>0 && ($len = strlen($command))>0){
			$s="";
			for($i=0; $i<$len; $i++){
				$ch = $command[$i]; $ord = ord($ch);
				if($ord>31 && $ord<127) $s.=$ch; else $s.=sprintf(" %02x;",$ord);
			}
			echo "\nout: $s\n";
		} 
		if ($this->_profile) $this->_profileData['writeCommand'] = (microtime(true) - $profileStart);
		return $this;
	}

	public function waitForPrompt($reportNoMatch = true) {
		if ($this->_profile) $profileStart = microtime(true);
		$buffer = $this->readUntil($this->_prompt, $reportNoMatch);
		if ($this->_profile) $this->_profileData['waitForPrompt'] = (microtime(true) - $profileStart);
		return $buffer;
	}

	public function waitForNextPage($reportNoMatch = true) {
		if ($this->_profile) $profileStart = microtime(true);
		$buffer = $this->readUntil($this->_nextPagePrompt, $reportNoMatch);
		if ($this->_profile) $this->_profileData['waitForNextPage'] = (microtime(true) - $profileStart);
		return $buffer;
	}

	public function runCommand($command='') {
		if ( !$this->isConnected(false) ) {
			throw new Network_Telnet_Exception(
				'run operation failed. connection is lost',
				Network_Telnet_Exception::CONNECTION
			);
		}
		if ($this->_profile) $profileStart = microtime(true);
		$this->emptyBuffer();
		$this->write($command);
		$this->halt();
		$buffer = $this->readAvailableResponse();
		$buffer = preg_replace("/^.*?\n(.*)\n[^\n]*$/", "$1", $buffer);
		$this->_responseBuffer = $buffer;
		if ($this->_profile) $this->_profileData['runCommand'] = (microtime(true) - $profileStart);
		return $buffer;
	}
}

function extelnet($ip='',$commands=array()){
	global $DEBUG;
	if(!is_array($commands) || count($commands)<3) return false;
	$telnet = new Network_Telnet($ip);
	$telnet->setPort(23);
	$telnet->setConnectionTimeout(5);

	$pr = ">"; $cm = ""; $response = ""; $out = ""; $vars = array(); $func = ''; $param = array();
	$str = array_shift($commands);
	$c = preg_split('/=>/',$str,2);
	$n = count($c);
	$cm = ($n>1)? $c[1] : $c[0];
	if($DEBUG>0) printf("\nsetPrompt '{$c[0]}'\n");
	if($n>1) $telnet->setPrompt($c[0]);
	try {
		if($DEBUG>0) printf("\nconnect\n");
		$telnet->connect();
		$response = $telnet->waitForPrompt();
		while($str = array_shift($commands)){
			$c = preg_split('/=>/',$str);
			$n = count($c);
			if($DEBUG>0 && $n>1) printf("\nsetPrompt '{$c[0]}'\n");
			if($n>1) $telnet->setPrompt($c[0]);
			if(count($vars)>0) foreach($vars as $k=>$v) $cm = preg_replace("/$k/",$v,$cm);
			$telnet->write($cm);
			$response = $telnet->waitForPrompt();
			if($func && function_exists($func)) $vars = $func($response,$param);
			if(preg_match('/^(show|config|create|enable|(ont )?delete|y) /',$cm)) $out .= $response;
			$cm = ($n>1)? $c[1] : $c[0];
			$func = ($n>2 && function_exists($c[2]))? $c[2] : '';
			$param = ($n>3)? array_slice($c,3) : null;
		}
		$telnet->write($cm);
		$telnet->disconnect();
		return ($out? $out:true);
	} catch (Network_Telnet_Exception $e) {
		$telnet->disconnect();
		$ERRORS[] = "Error occurred: ".$e;
		return false;
	}
}

function telnetCheckResult($r) {
	global $errors;
	if(!$r) return false;
	$s = preg_split("/\r?\n/",$r);
	if(count($s)>0){
		foreach($s as $i=>$line)
			if(preg_match('/error:?\s*(.*)/i',$line,$m)) $errors[] = $m[1];
	}
	if(isset($errors) && count($errors)>0) return false;
	return true;
}


function clearOnu($mac) {
	global $DEBUG, $q, $config, $errors, $opdata;
	$result = false;
	if(!is_object($q)) $q = new sql_query($config['db']);
	if(!preg_match('/^[0-9A-F\-:]{17}$/i',$mac)) $errors[] = "Неправильный мак адрес ($mac) !";
	if(!$errors) $dev = $q->get("devices",$mac,"macaddress");
	if($q->rows()!=1 || $dev['type']!='onu') $errors[] = "ONU ($mac) не найдено!";
	if(!$errors && !($port = $q->select("SELECT id FROM devports WHERE device='{$dev['id']}' AND porttype='fiber' AND link>0",4)))
		$errors[] = "Не найден порт устройства клиента!";
	if(!$errors && !($found = cutClients($port))) $errors[] = "На карте не найдено подключение!";
	if(!$errors && !($sw = $q->get("devices",$found[0]['rootid']))) $errors[] = "Не найден свич в базе!";
	if(!$errors && !class_exists('switch_snmp')) include_once "snmpclass.php";
	if(!$errors){ $swname = get_devname($sw,0,0); $switch = new switch_snmp($switch); }
	if(!$errors && !$switch->online) $errors[] = log_txt("свич '$swname' не доступен!");
	if(!$errors && !$sw['firmname'] && $switch->model) $sw['firmname'] = $switch->model;
	if(!$errors && !$sw['firmname'] && preg_match('/([-A-Za-z]+) ..*/',$sw['name'],$m)) $sw['firmname'] = $m[1];
	if(!$errors && !$sw['firmname']) $errors[] = "Неизвестный тип свича!";
	$sw['firmname'] = preg_replace('/[^A-Za-z]/','',$sw['firmname']);
	$func = "clearOnu".$sw['firmname'];
	if(!$errors && (!$sw['login'] || !$sw['password'])) $errors[] = "Для {$sw['firmname']} нет данных аутентификации!";
	if(!$errors && !function_exists($func)) $errors[] = "Работа с {$sw['firmname']} не подденживается!";
	if(!$errors && !($iface = $q->select("SELECT * FROM devports WHERE device='{$sw['id']}' AND number='{$found[0]['rootport']}'",1)))
		$errors[] = "Не найден интерфейс для {$found[0]['rootport']} порта!";
	if($DEBUG>0) log_txt(__FUNCTION__.": mac: $mac  switch: ".get_devname($sw)."  port: $port  interface: $iface  ".($errors? "errors: ".implode(', ',$errors):""));
	$data = array("wstype"=>'notify','to'=>$opdata['login'],"message"=>"");
	if(!$errors){
		$iface['numports'] = $sw['numports'];
		$result = $func($sw['ip'],$sw['login'],$sw['password'],$iface,$mac);
	}
	if(telnetCheckResult($result)){
		$data['message'] = "ONU $mac на ".get_devname($sw,0,0)." была удалена!";
		$user = $q->select("SELECT u.* FROM users u, map m, devices d WHERE u.user=m.name AND m.id=d.node1 AND d.id={$dev['id']}",1);
		log_db($user['user'],$user['uid'],"Сброс onu",$data['message']);
		use_ws_server($data);
	}else{
		$data['type'] = 'error';
		$data['message'] = "При удалении onu $mac возникли следующие ошибки:<BR>".implode("<BR>",$errors);
		use_ws_server($data);
		return false;
	}
}

function clearOnuBDCOM($ip,$login,$pass,$if,$mac){
	global $DEBUG;
	if(!$ip || !$login || !$pass || !$if || !$mac){
		log_txt(__FUNCTION__.": ERROR param = ".arrstr(func_get_args()));
		return false;
	}
	$mac = preg_replace(array('/[^0-9A-F]/i','/^(....)(....)(....)/'),array('','$1.$2.$3'),strtoupper($mac));
	$iface = "EPON0/".(($if['numports']>10)? $if['number']:$if['number']-6);
	$cmd = array(
		"Username:=>$login\r",
		"Password:=>$pass\r",
		">=>enable\r",
		"#=>config\r",
		"interface $iface\r",
		"no epon bind-onu mac $mac\r",
		"exit\r",
		"exit\r",
		"exit\r",
		">=>exit\r"
	);
	$r = extelnet($ip,$cmd);
	return $r;
}

function getOnuPortCDATA($r,$vars=null) {
	global $DEBUG;
	$a = explode("\n",$r); $out = null; $m = array();
	if(!isset($vars[0])) $vars=array('([0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}');
	foreach($a as $k=>$line){
		if($DEBUG>0) log_txt(__FUNCTION__.":\t$k) $line");
		if(preg_match('/\s*([0-9]\/[0-9])\s+([0-9]+)\s+([0-9]+)\s+'.$vars[0].'/',$line,$m)){
			$out = array('EPON_DEVICE'=>$m[1],'EPON_PORT'=>$m[2],'ONU_ID'=>$m[3]);
		}
	}
	if(!$out) $out = array('EPON_DEVICE'=>'0/0','EPON_PORT'=>'0','ONU_ID'=>'0');
	if($DEBUG>0) log_txt(__FUNCTION__.": return ".arrstr($out));
	return $out;
}

function clearOnuCDATA($ip,$login,$pass,$if,$mac){
	global $DEBUG;
	if($DEBUG>0) log_txt(__FUNCTION__.": ERROR param = ".arrstr(func_get_args()));
	if(!$ip || !$login || !$pass || !$if || !$mac){
		return false;
	}
	$mac = preg_replace(array('/[^0-9A-F]/i','/^(..)(..)(..)(..)(..)(..)/'),array('','$1:$2:$3:$4:$5:$6'),strtoupper($mac));
	$cmd = array(
		"User name:=>$login\r",
		"User password:=>$pass\r",
		">=>enable\r",
		"#=>config\r",
		"#=>show ont info by-mac $mac\r=>getOnuPortCDATA=>$mac",
		"interface epon EPON_DEVICE\r",
		"ont delete EPON_PORT ONU_ID\r",
		"exit\r",
		"exit\r",
		"exit\r",
		">=>exit\r"
	);
	$r = extelnet($ip,$cmd);
	return $r;
}

function clearPortOnu($port){
	global $DEBUG, $q, $config, $errors, $opdata;
	$result = false;
	if(!is_object($q)) $q = new sql_query($config['db']);
	if(!$port || !is_array($port)) $errors[] = "Неизвестный порт!";
	if(!$errors) $sw = $q->get("devices",$port['device']);
	if($q->rows()!=1 || $sw['type']!='switch') $errors[] = "Устройство не является свичем!";
	if(!$errors && !class_exists('switch_snmp')) include_once "snmpclass.php";
	if(!$errors){ $swname = get_devname($sw,0,0); $switch = new switch_snmp($switch); }
	if(!$errors && !$switch->online) $errors[] = log_txt("свич '$swname' не доступен!");
	if(!$errors && !$sw['firmname'] && $switch->model) $sw['firmname'] = $switch->model;
	if(!$errors && !$sw['firmname'] && preg_match('/([-A-Za-z]+) ..*/',$sw['name'],$m)) $sw['firmname'] = $m[1];
	if(!$errors && !$sw['firmname']) $errors[] = "Неизвестный тип свича!";
	$sw['firmname'] = preg_replace('/[^A-Za-z]/','',$sw['firmname']);
	$func = "clearPortOnu".$sw['firmname'];
	if(!$errors && (!$sw['login'] || !$sw['password'])) $errors[] = "Для {$swname} нет данных аутентификации!";
	if(!$errors && !function_exists($func)) $errors[] = "Данная операция для {$swname} не подденживается!";
	if($DEBUG>0) log_txt(__FUNCTION__.": switch: $swname  port: {$port['number']} ".($errors? "errors: ".implode(', ',$errors):""));
	$data = array("wstype"=>'notify','to'=>$opdata['login'],"message"=>"");

	if(!$errors) $result = $func($sw['ip'],$sw['login'],$sw['password'],$port['number']);
	if(telnetCheckResult($result)){
		$data['message'] = "Все ONU на {$port['number']} порту ".get_devname($sw,0,0)." были удалены!";
		log_db("",0,"Сброс onu",$data['message']);
		use_ws_server($data);
	}else{
		$data['type'] = 'error';
		$data['message'] = "При удалении onu возникли следующие ошибки:<BR>".implode("<BR>",$errors);
		use_ws_server($data);
		return false;
	}
}

function clearPortOnuCDATA($ip,$login,$pass,$port){
	global $DEBUG;
	if(!$ip || !is_numeric($port) || $port < 1 || $port > 16) return false;
	$cmd = array(
		"User name:=>$login\r",
		"User password:=>$pass\r",
		">=>enable\r",
		"#=>config\r",
		"interface epon 0/0\r",
		"ont delete $port all\r",
		"(y/n):=>y \r",
		"#=>exit\r",
		"exit\r",
		"exit\r",
		">=>exit\r"
	);
	return extelnet($ip,$cmd);
}

function configVlanDLINK($ip,$data){
	global $q, $errors;
	$switch = $q->get("devices",$ip,"ip");
	if(!$ip || !is_array($data)) return false;
	$sw = new switch_snmp($switch);
	if($sw->model != 'DLINK'){ log_txt("свич '$ip' не доступен!"); return false; }
	$result = array();
	$cmd[] = "UserName:=>".$switch['login'];
	$cmd[] = "PassWord:=>".$switch['password'];
	if(isset($data['command'])) $cmd[] = "#=>{$data['command']} vlan vlanid {$data['vlan']}";
	if(isset($data['port'])) $cmd[] = "#=>config vlan vlanid {$data['vlan']} add ".(($data['tagged'])? "tagged":"untagged"). $data['port'];
	$cmd[] = "logout";
	if($r = extelnet($ip,$cmd)){
		$str = "";
		for($i=0, $len = strlen($r); $i<$len; $i++){
			$ch = $r[$i]; $ord = ord($ch);
			if($ord>31 && $ord<127 || $ord==10) $str .= $ch;
		}
		$result = preg_split("/[\n]+/",$str);
		$result = array_splice($result,2,count($result)-3);
		if(preg_match('/fail/i',end($result))){
			$errors[] = implode(" ",array_splice($result,0,count($result)-1));
			return false;
		}
	}
	return $result;
}

function getTelnetFdbBDCOM($ip){
	global $q;
	$sw = $q->get("devices",$ip,"ip");
	if(!$sw) return false;
	$fdb = array();
	$cmd = array(
		"Username: =>".$sw['login'],
		"Password: =>".$sw['password'],
		">=>enable",
		"#=>show mac address-table",
		"exit",
		">=>exit"
	);
	if($r = extelnet($ip,$cmd)){
		$a = explode("\n",$r);
		foreach($a as $k=>$line){
			if(preg_match('/^([^ ]+)\s+([^ ]+)\s+([^ ]+)\s+([^ \r\n]+)/',$line,$m)){
				if($m[1] != 'Vlan' && substr($m[1],0,2) != '--'){
					if(substr($m[4],0,2)=='g0') $pn = substr_replace($m[4],'GigaEthernet',0,1);
					if(substr($m[4],0,4)=='epon') $pn = substr_replace($m[4],'EPON',0,4);
					$fdb[] = array("vlan"=>$m[1],"mac"=>normalize_mac($m[2]),"portname"=>$pn);
				}
			}
		}
	}
	return $fdb;
}
?>
