<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TencentMeetingBundle\Exception\ApiException;

/**
 * @internal
 */
#[CoversClass(ApiException::class)]
final class ExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeCreated(): void
    {
        $message = 'Test exception';
        $exception = new ApiException($message);

        $this->assertInstanceOf(ApiException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionInheritsFromRuntimeException(): void
    {
        $exception = new ApiException('test');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testExceptionWithCode(): void
    {
        $message = 'API exception with code';
        $code = 500;
        $exception = new ApiException($message, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
