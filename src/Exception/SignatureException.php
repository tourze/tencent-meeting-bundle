<?php

namespace Tourze\TencentMeetingBundle\Exception;

/**
 * 签名异常
 */
class SignatureException extends \RuntimeException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
