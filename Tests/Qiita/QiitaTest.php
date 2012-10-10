<?php

namespace Qiita;

use \Expose\Expose as e;
use \Mockery as m;

class QiitaTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @param array $methods
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	public function newQiitaPartialMock(array $methods)
	{
		return $this
			->getMockBuilder('\Qiita\Qiita')
			->disableOriginalConstructor()
			->setMethods($methods)
			->getMock();
	}

	public function test__construct()
	{
		$qiita = new Qiita();
		$this->assertAttributeSame(array(), 'config', $qiita);
		$this->assertAttributeSame(null, 'token', $qiita);
	}

	public function test__construct_with_config()
	{
		$config = array(
			'foo' => 'FOO',
			'bar' => 'BAR',
		);
		$qiita = new Qiita($config);
		$this->assertAttributeSame($config, 'config', $qiita);
	}

	public function testApi_do_authenticate_if_token_is_NULL()
	{
		$response = m::mock('\Qiita\HTTP\ResponseInterface');
		$response->shouldReceive('getData');
		$response->shouldReceive('getCode')->andReturn(200);

		$request = m::mock('\Qiita\HTTP\RequestInterface');
		$request->shouldReceive('execute')->andReturn($response);


		$qiita = $this
			->getMockBuilder('Qiita\Qiita')
			->setMethods(array('_authenticate', '_newRequest'))
			->setConstructorArgs(array(
				array(
					'username' => 'alice',
					'password' => 'p@ssW0rd',
				)
			))
			->getMock();
		$qiita
			->expects($this->once())
			->method('_authenticate')
			->with('alice', 'p@ssW0rd')
			->will($this->returnValue('token-token-token'));
		$qiita
			->expects($this->atLeastOnce())
			->method('_newRequest')
			->will($this->returnValue($request));

		$qiita->api('/do-something');
		$qiita->api('/do-something');
	}

	public function testApi_with_post_method()
	{
		$response = m::mock('\Qiita\HTTP\ResponseInterface');
		$response->shouldReceive('getData');
		$response->shouldReceive('getCode')->andReturn(200);
		$request = m::mock('\Qiita\HTTP\RequestInterface');
		$request->shouldReceive('execute')->with(m::any(), array(
			'format' => 'json',
			'method' => 'POST',
		))->andReturn($response)->once();

		$qiita = $this->newQiitaPartialMock(array('_newRequest'));
		e::expose($qiita)->attr('token', 'token-token-token');
		$qiita->expects($this->once())->method('_newRequest')->will($this->returnValue($request));

		$qiita->api('/do-something', 'POST');
	}

	public function testApi_with_data()
	{
		$response = m::mock('\Qiita\HTTP\ResponseInterface');
		$response->shouldReceive('getData');
		$response->shouldReceive('getCode')->andReturn(200);
		$request = m::mock('\Qiita\HTTP\RequestInterface');
		$request->shouldReceive('execute')->with(m::any(), array(
			'format' => 'json',
			'method' => 'POST',
			'data'   => array(
				'foo' => 'bar',
				'baz' => 'baz',
			),
		))->andReturn($response)->once();

		$qiita = $this->newQiitaPartialMock(array('_newRequest'));
		e::expose($qiita)->attr('token', 'token-token-token');
		$qiita->expects($this->once())->method('_newRequest')->will($this->returnValue($request));

		$qiita->api('/do-something', 'POST', array('foo' => 'bar', 'baz' => 'baz'));
	}

	public function testApi_convert_json_to_array_and_return_that_array()
	{
		$response = m::mock('\Qiita\HTTP\ResponseInterface');
		$response->shouldReceive('getData')->andReturn('{"foo":"bar"}')->once();
		$response->shouldReceive('getCode')->andReturn(200);
		$request = m::mock('\Qiita\HTTP\RequestInterface');
		$request->shouldReceive('execute')->andReturn($response)->once();

		$qiita = $this->newQiitaPartialMock(array('_newRequest'));
		e::expose($qiita)->attr('token', 'token-token-token');
		$qiita->expects($this->once())->method('_newRequest')->will($this->returnValue($request));

		$actual = $qiita->api('/do-something');
		$this->assertSame(array('foo' => 'bar'), $actual);
	}

	/**
	 * @expectedException \Qiita\QiitaException
	 * @expectedExceptionMessage Failed to request due to client error(HTTP status code: 400): Required key `url_name` is missing.
	 */
	public function testApi_with_client_error()
	{
		$response = m::mock('\Qiita\HTTP\ResponseInterface');
		$response->shouldReceive('getCode')->andReturn(400)->atLeast(1);
		$response->shouldReceive('getData')->andReturn('{"error": "Required key `url_name` is missing."}')->once();

		$request = m::mock('\Qiita\HTTP\RequestInterface');
		$request->shouldReceive('execute')->andReturn($response)->once();

		$qiita = $this->newQiitaPartialMock(array('_newRequest'));
		e::expose($qiita)->attr('token', 'token-token-token'); // fixture
		$qiita->expects($this->once())->method('_newRequest')->will($this->returnValue($request));

		$qiita->api('/do-something');
	}

	public function test_newRequest()
	{
		$qiita = new Qiita();
		$request = e::expose($qiita)->call('_newRequest');
		$this->assertInstanceOf('\Qiita\HTTP\RequestInterface', $request);
		$expectedRequest = new \Qiita\HTTP\Request();
		$expectedRequest->setUserAgent('suin/php-qiita/'.Qiita::LIBRARY_VERSION);
		$this->assertEquals($expectedRequest, $request);
	}

	public function test_authenticate()
	{
		$username = 'alice';
		$password = 'p@ssW0rd';
		$token = 'token-token-token';
		$responseData = '{"url_name":"alice","token":"token-token-token"}';

		$options = array(
			'method' => 'POST',
			'data'   => array(
				'url_name' => $username,
				'password' => $password,
			),
		);

		$response = m::mock('\Qiita\HTTP\ResponseInterface');
		$response->shouldReceive('getCode')->andReturn(200)->once();
		$response->shouldReceive('getData')->andReturn($responseData)->once();

		$request = m::mock('\Qiita\HTTP\RequestInterface');
		$request
			->shouldReceive('execute')
			->with(Qiita::BASE_URL.'/auth', $options)
			->andReturn($response)
			->once();

		$qiita = $this->newQiitaPartialMock(array(
			'_newRequest',
		));
		$qiita->expects($this->once())->method('_newRequest')->will($this->returnValue($request));

		// Test
		$this->assertSame($token, e::expose($qiita)->call('_authenticate', $username, $password));
	}

	/**
	 * @expectedException \Qiita\QiitaException
	 * @expectedExceptionMessage Failed to authenticate due to HTTP error
	 */
	public function test_authenticate_with_exception()
	{
		$request = m::mock('\Qiita\HTTP\RequestInterface');
		$request->shouldReceive('setUserPassword');
		$request->shouldReceive('execute')->andThrow('\Qiita\HTTP\Exception', 'Network Error');

		$qiita = $this->newQiitaPartialMock(array(
			'_newRequest',
		));
		$qiita->expects($this->once())->method('_newRequest')->will($this->returnValue($request));

		// Test
		e::expose($qiita)->call('_authenticate', 'bob', 'pa$$worD');
	}

	/**
	 * @expectedException \Qiita\QiitaException
	 * @expectedExceptionMessage Response code is 503
	 */
	public function test_autenticate_fails_due_to_Service_Temporarily_Unavailable()
	{
		$response = m::mock('\Qiita\HTTP\ResponseInterface');
		$response->shouldReceive('getCode')->andReturn(503)->atLeast(1);

		$request = m::mock('\Qiita\HTTP\RequestInterface');
		$request->shouldReceive('setUserPassword');
		$request->shouldReceive('execute')->andReturn($response);

		$qiita = $this->newQiitaPartialMock(array(
			'_newRequest',
		));
		$qiita->expects($this->once())->method('_newRequest')->will($this->returnValue($request));

		// Test
		e::expose($qiita)->call('_authenticate', 'bob', 'pa$$worD');
	}
}
