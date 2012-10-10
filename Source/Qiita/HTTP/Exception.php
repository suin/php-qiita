<?php

namespace Qiita\HTTP;

class Exception extends \RuntimeException
{
	public static function connectionError($reason)
	{
		return new self(sprintf('Connection error: %s', $reason));
	}
}
