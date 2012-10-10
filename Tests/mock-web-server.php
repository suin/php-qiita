<?php
/**
 * Mock Web Server
 *
 * Requirements
 *
 * - PHP 5.4 or later
 *
 * Usage
 *
 * $ php -S 0.0.0.0:9000 mock-web-server.php
 */

namespace MockWebServer;

function log($message)
{
	$datetime = date('[Y-m-d H:i:s] ');
	$lines = explode("\n", $message);
	foreach ( $lines as $line )
	{
		file_put_contents('php://stdout', $datetime.$line.PHP_EOL);
	}
}

function get_status_string($code)
{
	$codes = [
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
	];

	if ( isset($codes[$code]) )
	{
		return $codes[$code];
	}

	return 'Unkown Status';
}

$params = [
	'code'    => 200,
	'body'    => 'Hello World!',
	'headers' => array(),
	'verbose' => 0,
];
$params = array_merge($params, $_GET);

log('=================================================');
log(	sprintf('%s %s %s', $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_SERVER['SERVER_PROTOCOL']));

$requestHeaders = [
	sprintf('%s %s %s', $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_SERVER['SERVER_PROTOCOL']),
];

foreach ( $_SERVER as $key => $value )
{
	if ( strpos($key, 'HTTP_') === 0 )
	{
		$key = substr($key, 5);
		$key = strtr($key, '_', ' ');
		$key = strtolower($key);
		$key = ucwords($key);
		$key = strtr($key, ' ', '-');
		log(sprintf('%s: %s', $key, $value));
		$requestHeaders[$key] = $value;
	}
}

header_register_callback(function() use (&$params) {

	log('- - - - - - - - - - - - - - - - - - - - - - - - -');

	$status = sprintf('HTTP/1.1 %u %s', $params['code'], get_status_string($params['code']));
	header($status, true);
	log($status);

	if ( is_array($params['headers']) )
	{
		foreach ( $params['headers'] as $header )
		{
			header($header);
		}
	}

	foreach (headers_list() as $header)
	{
		log($header);
	}

	log("\n".$params['body']);
});

if ( $params['verbose'] == 1 )
{
	$postRaw   = $_SERVER['REQUEST_METHOD'] === 'POST'    ? file_get_contents("php://input") : '';
	$putRaw    = $_SERVER['REQUEST_METHOD'] === 'PUT'     ? file_get_contents("php://input") : '';
	$deleteRaw = $_SERVER['REQUEST_METHOD'] === 'DELETE'  ? file_get_contents("php://input") : '';
	parse_str($putRaw, $put);
	parse_str($deleteRaw, $delete);

	$params['body'] = json_encode([
		'requestHeaders' => $requestHeaders,
		'body'           => $params['body'],
		'get'            => $_GET,
		'post_raw'       => $postRaw,
		'post'           => $_POST,
		'put_raw'        => $putRaw,
		'put'            => $put,
		'delete_raw'     => $deleteRaw,
		'delete'         => $delete,
	]);
}

echo $params['body'];
