<?php

namespace Tourze\TencentMeetingBundle\Exception;

/**
 * 网络异常
 */
class NetworkException extends \RuntimeException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
