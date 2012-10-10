<?php

namespace Qiita;

use \Exception;

class QiitaException extends \RuntimeException
{
	public static function failedToAuthenticateDueToHTTPError(Exception $previousException)
	{
		return new self('Failed to authenticate due to HTTP error', 0, $previousException);
	}

	public static function responseCodeIs($code)
	{
		return new self(sprintf('Response code is %s', $code));
	}

	public static function failedToRequestDueToClientError($httpStatusCode, $reason)
	{
		return new self(sprintf('Failed to request due to client error(HTTP status code: %s): %s', $httpStatusCode, $reason));
	}
}
