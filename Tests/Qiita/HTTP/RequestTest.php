<?php

namespace Qiita\HTTP;

use \Expose\Expose as e;

class RequestTest extends \PHPUnit_Framework_TestCase
{
	protected static $isMockWebServerIsRunning = false;

	public static function setUpBeforeClass()
	{
		$result = @file_get_contents('http://localhost:9000/?body=Running');

		if ( $result === 'Running' )
		{
			self::$isMockWebServerIsRunning = true;
		}
	}

	public function skipWhenMockWebServerIsNotRunning()
	{
		if ( self::$isMockWebServerIsRunning === false )
		{
			$this->markTestSkipped('Mock Web Server is not running');
		}
	}

	public function testSetTimeout()
	{
		$request = new Request();
		$this->assertAttributeSame(10, 'timeout', $request);
		$this->assertSame($request, $request->setTimeout(20));
		$this->assertAttributeSame(20, 'timeout', $request);
	}

	public function testSetUserAgent()
	{
		$request = new Request();
		$this->assertAttributeSame('PHP', 'userAgent', $request);
		$this->assertSame($request, $request->setUserAgent('FooBar Agent'));
		$this->assertAttributeSame('FooBar Agent', 'userAgent', $request);
	}

	public function testDisableSSLVerification()
	{
		$request = new Request();
		$this->assertAttributeSame(true, 'sslVerification', $request);
		$this->assertSame($request, $request->disableSSLVerification());
		$this->assertAttributeSame(false, 'sslVerification', $request);
	}

	public function testEnableSSLVerification()
	{
		$request = new Request();
		e::expose($request)->attr('sslVerification', false);
		$this->assertSame($request, $request->enableSSLVerification());
		$this->assertAttributeSame(true, 'sslVerification', $request);
	}

	public function testSetUserPassword()
	{
		$request = new Request();
		$this->assertAttributeSame(null, 'username', $request);
		$this->assertAttributeSame(null, 'password', $request);
		$this->assertSame($request, $request->setUserPassword('alice', 'p@ssW0rd'));
		$this->assertAttributeSame('alice', 'username', $request);
		$this->assertAttributeSame('p@ssW0rd', 'password', $request);
	}

	public function testUnsetUserPassword()
	{
		$request = new Request();
		e::expose($request)
			->attr('username', 'bob')
			->attr('password', 'Pa$$worD');
		$this->assertSame($request, $request->unsetUserPassword());
		$this->assertAttributeSame(null, 'username', $request);
		$this->assertAttributeSame(null, 'password', $request);
	}

	public function testExecute()
	{
		$this->skipWhenMockWebServerIsNotRunning();

		$url = 'http://localhost:9000/?code=200&body=FooBarBaz';
		$response = new Response('FooBarBaz', 200);
		$request = new Request();
		$this->assertEquals($response, $request->execute($url));
	}

	public function testExecute_with_user_agent()
	{
		$this->skipWhenMockWebServerIsNotRunning();

		$url = 'http://localhost:9000/?code=200&body=FooBarBaz&verbose=1';
		$request = new Request();
		$response = $request->execute($url);
		$response = json_decode($response->getData(), true);
		$this->assertSame($response['requestHeaders']['User-Agent'], 'PHP');
	}

	/**
	 * @expectedException \Qiita\HTTP\Exception
	 * @expectedExceptionMessage Connection error:
	 */
	public function testExecute_with_error_status()
	{
		$url = 'http://localhost:9876/no_such_server';
		$request = new Request();
		$request->execute($url);
	}

	public function testExecute_DO_NOT_throws_exception_even_if_status_code_is_not_200()
	{
		$this->skipWhenMockWebServerIsNotRunning();

		$url = 'http://localhost:9000/?code=500&body=InternalServerError';
		$response = new Response('InternalServerError', 500);
		$request = new Request();
		$this->assertEquals($response, $request->execute($url));
	}

	public function testExecute_with_POST_method()
	{
		$this->skipWhenMockWebServerIsNotRunning();
		$url = 'http://localhost:9000/?verbose=1';
		$request = new Request();
		$response = $request->execute($url, array('method' => 'POST'));
		$response = json_decode($response->getData(), true);
		$this->assertSame('POST /?verbose=1 HTTP/1.1', $response['requestHeaders'][0]);
	}

	public function testExecute_with_username_and_password()
	{
		$this->skipWhenMockWebServerIsNotRunning();
		$url = 'http://localhost:9000/?verbose=1';
		$request = new Request();
		$request->setUserPassword('alice', 'p@ssW0rd');
		$response = $request->execute($url);
		$response = json_decode($response->getData(), true);
		$this->assertSame('Basic '.base64_encode('alice:p@ssW0rd'), $response['requestHeaders']['Authorization']);
	}

	public function testExecute_with_post_data()
	{
		$this->skipWhenMockWebServerIsNotRunning();
		$url = 'http://localhost:9000/?verbose=1';
		$postData = array('foo' => 'bar');
		$request = new Request();
		$response = $request->execute($url, array('method' => 'POST', 'data' => $postData));
		$response = json_decode($response->getData(), true);
		$this->assertSame($postData, $response['post']);
	}

	public function testExecute_with_content_type_json()
	{
		$this->skipWhenMockWebServerIsNotRunning();
		$url = 'http://localhost:9000/?verbose=1';
		$postData = array('foo' => 'bar');
		$request = new Request();
		$response = $request->execute($url, array(
			'method' => 'POST',
			'data'   => $postData,
			'format' => 'json',
		));
		$response = json_decode($response->getData(), true);
		$this->assertSame(json_encode($postData), $response['post_raw']);
		$this->assertSame('application/json', $response['requestHeaders']['Content-Type']);
	}

	public function testExecute_with_put_data()
	{
		$this->skipWhenMockWebServerIsNotRunning();
		$url = 'http://localhost:9000/?verbose=1';
		$putData = array('foo' => 'bar');
		$request = new Request();
		$response = $request->execute($url, array(
			'method' => 'PUT',
			'data'   => $putData,
			'format' => 'json',
		));
		$response = json_decode($response->getData(), true);
		$this->assertSame(json_encode($putData), $response['put_raw']);
	}

	public function testExecute_with_delete_method()
	{
		$this->skipWhenMockWebServerIsNotRunning();
		$url = 'http://localhost:9000/?verbose=1';
		$request = new Request();
		$response = $request->execute($url, array('method' => 'DELETE'));
		$response = json_decode($response->getData(), true);
		$this->assertSame('DELETE /?verbose=1 HTTP/1.1', $response['requestHeaders'][0]);
	}
}
