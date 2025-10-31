<?php

namespace Tourze\TencentMeetingBundle\Exception;

/**
 * API异常
 */
class ApiException extends \RuntimeException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
