<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TencentMeetingBundle\Exception\SignatureException;
use Tourze\TencentMeetingBundle\Service\SignatureService;

/**
 * @internal
 */
#[CoversClass(SignatureService::class)]
final class SignatureServiceTest extends TestCase
{
    private SignatureService $signatureService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signatureService = new SignatureService();
    }

    public function testSignatureServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(SignatureService::class, $this->signatureService);
    }

    public function testGenerateSignature(): void
    {
        $params = [
            'Action' => 'CreateMeeting',
            'Version' => '2021-03-25',
            'Timestamp' => '1640995200',
            'Nonce' => '123456',
        ];
        $secretKey = 'test-secret-key';

        $signature = $this->signatureService->generateSignature($params, $secretKey);

        $this->assertNotEmpty($signature);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $signature);
    }

    public function testGenerateTC3Signature(): void
    {
        $params = [
            'Action' => 'CreateMeeting',
            'Version' => '2021-03-25',
            'subject' => 'Test Meeting',
            'start_time' => '2025-01-01 10:00:00',
        ];
        $secretKey = 'test-secret-key';

        $signature = $this->signatureService->generateTC3Signature($params, $secretKey);

        $this->assertNotEmpty($signature);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $signature);
    }

    public function testGenerateTC3SignatureWithParameterSorting(): void
    {
        $params1 = [
            'b_param' => 'value2',
            'a_param' => 'value1',
            'c_param' => 'value3',
        ];
        $params2 = [
            'a_param' => 'value1',
            'b_param' => 'value2',
            'c_param' => 'value3',
        ];
        $secretKey = 'test-secret-key';

        $signature1 = $this->signatureService->generateTC3Signature($params1, $secretKey);
        $signature2 = $this->signatureService->generateTC3Signature($params2, $secretKey);

        $this->assertSame($signature1, $signature2);
    }

    public function testVerifySignature(): void
    {
        $params = [
            'Action' => 'CreateMeeting',
            'Version' => '2021-03-25',
            'Timestamp' => '1640995200',
        ];
        $secretKey = 'test-secret-key';

        $signature = $this->signatureService->generateSignature($params, $secretKey);
        $isValid = $this->signatureService->verifySignature($params, $secretKey, $signature);

        $this->assertTrue($isValid);
    }

    public function testVerifySignatureWithInvalidSignature(): void
    {
        $params = [
            'Action' => 'CreateMeeting',
            'Version' => '2021-03-25',
        ];
        $secretKey = 'test-secret-key';
        $invalidSignature = 'invalid-signature';

        $isValid = $this->signatureService->verifySignature($params, $secretKey, $invalidSignature);

        $this->assertFalse($isValid);
    }

    public function testVerifySignatureWithDifferentParams(): void
    {
        $params1 = ['Action' => 'CreateMeeting'];
        $params2 = ['Action' => 'DeleteMeeting'];
        $secretKey = 'test-secret-key';

        $signature = $this->signatureService->generateSignature($params1, $secretKey);
        $isValid = $this->signatureService->verifySignature($params2, $secretKey, $signature);

        $this->assertFalse($isValid);
    }

    public function testVerifySignatureThrowsExceptionOnError(): void
    {
        $this->expectException(SignatureException::class);
        $this->expectExceptionMessage('签名验证失败');

        // 使用一个会导致错误的场景，比如空的参数数组和非常复杂的处理
        $signatureService = $this->getMockBuilder(SignatureService::class)
            ->onlyMethods(['generateSignature'])
            ->getMock()
        ;

        $signatureService->method('generateSignature')
            ->willThrowException(new \Exception('Mock error'))
        ;

        $signatureService->verifySignature([], 'key', 'signature');
    }

    public function testGenerateStandardTC3Signature(): void
    {
        $method = 'POST';
        $host = 'api.meeting.qq.com';
        $path = '/v1/meetings';
        $queryParams = ['Action' => 'CreateMeeting'];
        $headers = [
            'Content-Type' => 'application/json',
            'Host' => 'api.meeting.qq.com',
        ];
        $body = '{"subject":"Test Meeting"}';
        $secretKey = 'test-secret-key';

        $signature = $this->signatureService->generateStandardTC3Signature(
            $method,
            $host,
            $path,
            $queryParams,
            $headers,
            $body,
            $secretKey
        );

        $this->assertNotEmpty($signature);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $signature);
    }

    public function testGenerateStandardTC3SignatureConsistency(): void
    {
        $method = 'GET';
        $host = 'api.meeting.qq.com';
        $path = '/v1/meetings/123';
        $queryParams = [];
        $headers = ['Host' => 'api.meeting.qq.com'];
        $body = '';
        $secretKey = 'test-secret-key';

        $signature1 = $this->signatureService->generateStandardTC3Signature(
            $method,
            $host,
            $path,
            $queryParams,
            $headers,
            $body,
            $secretKey
        );
        $signature2 = $this->signatureService->generateStandardTC3Signature(
            $method,
            $host,
            $path,
            $queryParams,
            $headers,
            $body,
            $secretKey
        );

        $this->assertSame($signature1, $signature2);
    }

    public function testVerifyStandardTC3Signature(): void
    {
        $method = 'POST';
        $host = 'api.meeting.qq.com';
        $path = '/v1/meetings';
        $queryParams = ['Action' => 'CreateMeeting'];
        $headers = ['Content-Type' => 'application/json'];
        $body = '{"subject":"Test Meeting"}';
        $secretKey = 'test-secret-key';

        $signature = $this->signatureService->generateStandardTC3Signature(
            $method,
            $host,
            $path,
            $queryParams,
            $headers,
            $body,
            $secretKey
        );

        $isValid = $this->signatureService->verifyStandardTC3Signature(
            $method,
            $host,
            $path,
            $queryParams,
            $headers,
            $body,
            $secretKey,
            $signature
        );

        $this->assertTrue($isValid);
    }

    public function testVerifyStandardTC3SignatureWithInvalidSignature(): void
    {
        $method = 'POST';
        $host = 'api.meeting.qq.com';
        $path = '/v1/meetings';
        $queryParams = ['Action' => 'CreateMeeting'];
        $headers = ['Content-Type' => 'application/json'];
        $body = '{"subject":"Test Meeting"}';
        $secretKey = 'test-secret-key';
        $invalidSignature = 'invalid-signature';

        $isValid = $this->signatureService->verifyStandardTC3Signature(
            $method,
            $host,
            $path,
            $queryParams,
            $headers,
            $body,
            $secretKey,
            $invalidSignature
        );

        $this->assertFalse($isValid);
    }

    public function testVerifyStandardTC3SignatureThrowsExceptionOnError(): void
    {
        $this->expectException(SignatureException::class);
        $this->expectExceptionMessage('标准TC3签名验证失败');

        $signatureService = $this->getMockBuilder(SignatureService::class)
            ->onlyMethods(['generateStandardTC3Signature'])
            ->getMock()
        ;

        $signatureService->method('generateStandardTC3Signature')
            ->willThrowException(new \Exception('Mock error'))
        ;

        $signatureService->verifyStandardTC3Signature(
            'POST',
            'host',
            '/path',
            [],
            [],
            '',
            'key',
            'signature'
        );
    }

    public function testGenerateAuthorizationHeader(): void
    {
        $secretId = 'test-secret-id';
        $secretKey = 'test-secret-key';
        $method = 'POST';
        $host = 'api.meeting.qq.com';
        $path = '/v1/meetings';
        $queryParams = [
            'Action' => 'CreateMeeting',
            'Version' => '2021-03-25',
            'Timestamp' => '1640995200',
            'Nonce' => '123456',
        ];
        $headers = ['Content-Type' => 'application/json'];
        $body = '{"subject":"Test Meeting"}';

        $authHeaders = $this->signatureService->generateAuthorizationHeader(
            $secretId,
            $secretKey,
            $method,
            $host,
            $path,
            $queryParams,
            $headers,
            $body
        );
        $this->assertArrayHasKey('Authorization', $authHeaders);
        $this->assertArrayHasKey('X-TC-Action', $authHeaders);
        $this->assertArrayHasKey('X-TC-Version', $authHeaders);
        $this->assertArrayHasKey('X-TC-Timestamp', $authHeaders);
        $this->assertArrayHasKey('X-TC-Nonce', $authHeaders);

        $this->assertStringContainsString('TC3-HMAC-SHA256', $authHeaders['Authorization']);
        $this->assertStringContainsString($secretId, $authHeaders['Authorization']);
        $this->assertStringContainsString(date('Y-m-d'), $authHeaders['Authorization']);
        $this->assertSame('CreateMeeting', $authHeaders['X-TC-Action']);
        $this->assertSame('2021-03-25', $authHeaders['X-TC-Version']);
        $this->assertSame('1640995200', $authHeaders['X-TC-Timestamp']);
        $this->assertSame('123456', $authHeaders['X-TC-Nonce']);
    }

    public function testGenerateAuthorizationHeaderWithMissingQueryParams(): void
    {
        $secretId = 'test-secret-id';
        $secretKey = 'test-secret-key';
        $method = 'GET';
        $host = 'api.meeting.qq.com';
        $path = '/v1/meetings';
        $queryParams = []; // Missing Action, Version, etc.
        $headers = ['Host' => 'api.meeting.qq.com'];
        $body = '';

        $authHeaders = $this->signatureService->generateAuthorizationHeader(
            $secretId,
            $secretKey,
            $method,
            $host,
            $path,
            $queryParams,
            $headers,
            $body
        );
        $this->assertArrayHasKey('Authorization', $authHeaders);
        $this->assertSame('', $authHeaders['X-TC-Action']);
        $this->assertSame('', $authHeaders['X-TC-Version']);
        $this->assertSame('', $authHeaders['X-TC-Timestamp']);
        $this->assertSame('', $authHeaders['X-TC-Nonce']);
    }

    public function testGenerateSignatureWithEmptyParams(): void
    {
        $params = [];
        $secretKey = 'test-secret-key';

        $signature = $this->signatureService->generateSignature($params, $secretKey);

        $this->assertNotEmpty($signature);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $signature);
    }

    public function testGenerateSignatureWithSpecialCharacters(): void
    {
        $params = [
            'special_chars' => 'test value with spaces & symbols!@#$%^&*()',
            'unicode' => '测试中文字符',
        ];
        $secretKey = 'test-secret-key';

        $signature = $this->signatureService->generateSignature($params, $secretKey);

        $this->assertNotEmpty($signature);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $signature);
    }
}
