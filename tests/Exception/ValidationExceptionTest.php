<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TencentMeetingBundle\Exception\TencentMeetingException;
use Tourze\TencentMeetingBundle\Exception\ValidationException;

/**
 * @internal
 */
#[CoversClass(ValidationException::class)]
final class ValidationExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeCreated(): void
    {
        $message = 'Validation failed';
        $apiResponse = ['errors' => ['field' => 'required']];
        $exception = new ValidationException($message, $apiResponse);

        $this->assertInstanceOf(ValidationException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($apiResponse, $exception->getApiResponse());
    }

    public function testExceptionInheritsFromTencentMeetingException(): void
    {
        $exception = new ValidationException('test');
        $this->assertInstanceOf(TencentMeetingException::class, $exception);
    }

    public function testExceptionWithPreviousException(): void
    {
        $previous = new \RuntimeException('Previous error');
        $message = 'Validation error with previous';
        $apiResponse = ['validation_errors' => ['field1', 'field2']];
        $exception = new ValidationException($message, $apiResponse, $previous);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($apiResponse, $exception->getApiResponse());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testExceptionWithDefaultValues(): void
    {
        $exception = new ValidationException('');

        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals([], $exception->getApiResponse());
        $this->assertNull($exception->getPrevious());
    }
}
