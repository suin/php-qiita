<?php

namespace Qiita\HTTP;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
	public function test__construct()
	{
		$response = new Response('Response data', 1234);
		$this->assertAttributeSame('Response data', 'data', $response);
		$this->assertAttributeSame(1234, 'code', $response);
	}

	public function testGetData()
	{
		$response = new Response('response data', 1234);
		$this->assertSame('response data', $response->getData());
	}

	public function testGetCode()
	{
		$response = new Response('response data', 1234);
		$this->assertSame(1234, $response->getCode());
	}
}
