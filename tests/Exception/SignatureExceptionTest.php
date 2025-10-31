<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TencentMeetingBundle\Exception\SignatureException;

/**
 * @internal
 */
#[CoversClass(SignatureException::class)]
final class SignatureExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeCreated(): void
    {
        $message = 'Signature verification failed';
        $code = 401;
        $exception = new SignatureException($message, $code);

        $this->assertInstanceOf(SignatureException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testExceptionInheritsFromRuntimeException(): void
    {
        $exception = new SignatureException('test');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testExceptionWithPreviousException(): void
    {
        $previous = new \RuntimeException('Previous error');
        $message = 'Signature error with previous';
        $code = 403;
        $exception = new SignatureException($message, $code, $previous);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testExceptionWithDefaultValues(): void
    {
        $exception = new SignatureException();

        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
