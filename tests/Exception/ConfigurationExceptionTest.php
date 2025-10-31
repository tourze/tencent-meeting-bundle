<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TencentMeetingBundle\Exception\ConfigurationException;

/**
 * @internal
 */
#[CoversClass(ConfigurationException::class)]
final class ConfigurationExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeCreated(): void
    {
        $message = 'Configuration error occurred';
        $exception = new ConfigurationException($message);

        $this->assertInstanceOf(ConfigurationException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionWithPreviousException(): void
    {
        $previous = new \RuntimeException('Previous error');
        $message = 'Configuration error with previous';
        $exception = new ConfigurationException($message, [], $previous);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testExceptionWithApiResponse(): void
    {
        $message = 'Configuration error with response';
        $apiResponse = ['error_code' => 500, 'error_message' => 'Server error'];
        $exception = new ConfigurationException($message, $apiResponse);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($apiResponse, $exception->getApiResponse());
    }
}
