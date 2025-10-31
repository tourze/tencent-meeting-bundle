<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TencentMeetingBundle\Exception\ApiException;
use Tourze\TencentMeetingBundle\Exception\AuthenticationException;
use Tourze\TencentMeetingBundle\Exception\NetworkException;

/**
 * @internal
 */
#[CoversClass(ApiException::class)]
final class TencentMeetingExceptionTest extends AbstractExceptionTestCase
{
    public function testApiExceptionCanBeCreated(): void
    {
        $message = 'API error';
        $code = 500;
        $exception = new ApiException($message, $code);

        $this->assertInstanceOf(ApiException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testApiExceptionInheritsFromRuntimeException(): void
    {
        $exception = new ApiException('test');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testApiExceptionWithPreviousException(): void
    {
        $previous = new \RuntimeException('Previous error');
        $message = 'API error with previous';
        $code = 400;
        $exception = new ApiException($message, $code, $previous);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
