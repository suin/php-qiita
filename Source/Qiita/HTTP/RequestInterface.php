<?php

namespace Qiita\HTTP;

interface RequestInterface
{
	/**
	 * Set timeout
	 * @param int $timeout Seconds for timeout
	 * @return $this
	 */
	public function setTimeout($timeout);

	/**
	 * Set user agent
	 * @param string $userAgent
	 * @return $this
	 */
	public function setUserAgent($userAgent);

	/**
	 * Disable SSL verification
	 * @return $this
	 */
	public function disableSSLVerification();

	/**
	 * Enable SSL verification
	 * @return $this
	 */
	public function enableSSLVerification();

	/**
	 * Set user password
	 * @param string $username
	 * @param string $password
	 * @return $this
	 */
	public function setUserPassword($username, $password);

	/**
	 * Unset user password
	 * @return $this
	 */
	public function unsetUserPassword();

	/**
	 * Execute request
	 * @param string $url
	 * @param array $options
	 * @return \Qiita\HTTP\ResponseInterface
	 * @throws \Qiita\HTTP\Exception
	 */
	public function execute($url, array $options = array());
}
