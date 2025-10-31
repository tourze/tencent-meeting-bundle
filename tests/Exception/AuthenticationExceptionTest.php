<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TencentMeetingBundle\Exception\AuthenticationException;
use Tourze\TencentMeetingBundle\Exception\TencentMeetingException;

/**
 * @internal
 */
#[CoversClass(AuthenticationException::class)]
final class AuthenticationExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeCreated(): void
    {
        $message = 'Authentication failed';
        $code = 401;
        $exception = new AuthenticationException($message, $code);

        $this->assertInstanceOf(AuthenticationException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(['code' => $code], $exception->getApiResponse());
    }

    public function testExceptionInheritsFromTencentMeetingException(): void
    {
        $exception = new AuthenticationException('test');
        $this->assertInstanceOf(TencentMeetingException::class, $exception);
    }

    public function testExceptionWithPreviousException(): void
    {
        $previous = new \RuntimeException('Previous error');
        $message = 'Auth error with previous';
        $code = 403;
        $exception = new AuthenticationException($message, $code, $previous);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(['code' => $code], $exception->getApiResponse());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testExceptionWithDefaultValues(): void
    {
        $exception = new AuthenticationException();

        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals(['code' => 0], $exception->getApiResponse());
        $this->assertNull($exception->getPrevious());
    }
}
