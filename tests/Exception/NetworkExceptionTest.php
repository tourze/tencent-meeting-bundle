<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TencentMeetingBundle\Exception\NetworkException;

/**
 * @internal
 */
#[CoversClass(NetworkException::class)]
final class NetworkExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeCreated(): void
    {
        $message = 'Network connection failed';
        $code = 500;
        $exception = new NetworkException($message, $code);

        $this->assertInstanceOf(NetworkException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testExceptionInheritsFromRuntimeException(): void
    {
        $exception = new NetworkException('test');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testExceptionWithPreviousException(): void
    {
        $previous = new \RuntimeException('Previous error');
        $message = 'Network error with previous';
        $code = 503;
        $exception = new NetworkException($message, $code, $previous);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testExceptionWithDefaultValues(): void
    {
        $exception = new NetworkException();

        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
