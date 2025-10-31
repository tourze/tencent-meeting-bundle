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
final class ApiExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeCreated(): void
    {
        $message = 'API call failed';
        $code = 500;
        $exception = new ApiException($message, $code);

        $this->assertInstanceOf(ApiException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testExceptionInheritsFromRuntimeException(): void
    {
        $exception = new ApiException('test');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testExceptionWithPreviousException(): void
    {
        $previous = new \RuntimeException('Previous error');
        $message = 'API error with previous';
        $code = 400;
        $exception = new ApiException($message, $code, $previous);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testExceptionWithDefaultValues(): void
    {
        $exception = new ApiException();

        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
