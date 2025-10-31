<?php

namespace Tourze\TencentMeetingBundle\Exception;

/**
 * 认证异常
 */
class AuthenticationException extends TencentMeetingException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, ['code' => $code], $previous);
    }
}
