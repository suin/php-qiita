<?php

namespace Qiita;

interface QiitaInterface
{
	/**
	 * Return new Qiita object
	 * @param array $config
	 */
	public function __construct(array $config = array());

	/**
	 * Call a API method
	 * @param string $path
	 * @param string $method
	 * @param array  $params
	 * @return array
	 * @throws \Qiita\QiitaException
	 */
	public function api($path, $method = 'GET', $params = array());
}
