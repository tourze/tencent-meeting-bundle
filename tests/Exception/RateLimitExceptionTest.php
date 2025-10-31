<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TencentMeetingBundle\Exception\RateLimitException;
use Tourze\TencentMeetingBundle\Exception\TencentMeetingException;

/**
 * @internal
 */
#[CoversClass(RateLimitException::class)]
final class RateLimitExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeCreated(): void
    {
        $message = 'Rate limit exceeded';
        $apiResponse = ['error_code' => 429, 'error_message' => 'Too many requests'];
        $exception = new RateLimitException($message, $apiResponse);

        $this->assertInstanceOf(RateLimitException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($apiResponse, $exception->getApiResponse());
    }

    public function testExceptionInheritsFromTencentMeetingException(): void
    {
        $exception = new RateLimitException('test');
        $this->assertInstanceOf(TencentMeetingException::class, $exception);
    }

    public function testExceptionWithPreviousException(): void
    {
        $previous = new \RuntimeException('Previous error');
        $message = 'Rate limit error with previous';
        $apiResponse = ['error_code' => 429];
        $exception = new RateLimitException($message, $apiResponse, $previous);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($apiResponse, $exception->getApiResponse());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testExceptionWithDefaultValues(): void
    {
        $exception = new RateLimitException('');

        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals([], $exception->getApiResponse());
        $this->assertNull($exception->getPrevious());
    }
}
