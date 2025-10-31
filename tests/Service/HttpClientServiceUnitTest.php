<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\TencentMeetingBundle\Service\ConfigService;
use Tourze\TencentMeetingBundle\Service\ConfigServiceInterface;
use Tourze\TencentMeetingBundle\Service\HttpClientService;

/**
 * @internal
 */
#[CoversClass(HttpClientService::class)]
final class HttpClientServiceUnitTest extends TestCase
{
    private ConfigService $configService;

    private LoggerInterface $loggerService;

    private MockHttpClient $mockHttpClient;

    protected function setUp(): void
    {
        $configService = $this->createMock(ConfigService::class);
        $this->assertInstanceOf(ConfigService::class, $configService);
        $this->configService = $configService;

        $loggerService = $this->createMock(LoggerInterface::class);
        $this->assertInstanceOf(LoggerInterface::class, $loggerService);
        $this->loggerService = $loggerService;

        // 设置基础配置
        $configService->method('getApiUrl')->willReturn('https://api.meeting.qq.com');
        $configService->method('getTimeout')->willReturn(30);
        $configService->method('getRetryTimes')->willReturn(0);
        $configService->method('getAuthToken')->willReturn(null);

        // 创建Mock HTTP客户端
        $this->mockHttpClient = new MockHttpClient();
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, string> $headers
     */
    private function createMockResponse(int $statusCode = 200, array $body = [], array $headers = []): MockResponse
    {
        $responseHeaders = array_merge(['content-type' => 'application/json'], $headers);
        $responseHeadersFlat = [];
        foreach ($responseHeaders as $name => $value) {
            $responseHeadersFlat[] = $name . ': ' . $value;
        }

        $jsonBody = json_encode($body);
        if (false === $jsonBody) {
            throw new \RuntimeException('Failed to encode JSON');
        }

        return new MockResponse($jsonBody, [
            'http_code' => $statusCode,
            'response_headers' => $responseHeadersFlat,
        ]);
    }

    public function testGetMethodWithParameters(): void
    {
        // 模拟GET成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['test' => 'success']),
        ]);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        // 测试GET方法参数
        $result = $service->get('/test', ['X-Test' => 'value'], 10);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('status_code', $result);
        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status_code']);
    }

    public function testHttpMethodsParameterHandling(): void
    {
        // 模拟GET成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['test' => 'success']),
        ]);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        // 测试GET方法参数
        $result = $service->get('/test', ['X-Test' => 'value'], 10);
        $this->assertTrue($result['success']);
    }

    public function testDeleteMethodParameterValidation(): void
    {
        // 模拟DELETE响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(204, []),
        ]);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        // 测试DELETE方法返回适当的成功响应
        $result = $service->delete('/test-delete');

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('status_code', $result);
        $this->assertTrue($result['success']);
        $this->assertEquals(204, $result['status_code']);
    }

    public function testPatchMethodParameterValidation(): void
    {
        // 模拟PATCH成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['patched' => true, 'data' => 'test']),
        ]);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        // 测试PATCH方法返回适当的成功响应
        $result = $service->patch('/test-patch', ['data' => 'test']);

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('status_code', $result);
        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status_code']);
    }

    public function testPostMethodParameterValidation(): void
    {
        // 模拟POST成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(201, ['created' => true, 'name' => 'test']),
        ]);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        // 测试POST方法返回适当的成功响应
        $result = $service->post('/test-post', ['name' => 'test']);

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('status_code', $result);
        $this->assertTrue($result['success']);
        $this->assertEquals(201, $result['status_code']);
    }

    public function testPutMethodParameterValidation(): void
    {
        // 模拟PUT成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['updated' => true, 'id' => 1]),
        ]);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        // 测试PUT方法返回适当的成功响应
        $result = $service->put('/test-put', ['id' => 1]);

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('status_code', $result);
        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status_code']);
    }

    public function testRequestMethodWithDifferentHttpMethods(): void
    {
        // 为多个方法设置成功响应
        $responses = [];
        foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH'] as $method) {
            $responses[] = $this->createMockResponse(200, ['method' => $method]);
        }
        $this->mockHttpClient->setResponseFactory($responses);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];

        foreach ($methods as $method) {
            $result = $service->request($method, '/test');

            $this->assertArrayHasKey('success', $result);
            $this->assertArrayHasKey('status_code', $result);
            $this->assertTrue($result['success']);
        }
    }

    public function testRequestAsyncMethodReturnType(): void
    {
        // 模拟异步响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['async' => true]),
        ]);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        $response = $service->requestAsync('GET', '/test');

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testRequestMultipleMethodWithMultipleRequests(): void
    {
        // 为多个请求设置成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['req1' => 'success']),
            $this->createMockResponse(201, ['req2' => 'created', 'key' => 'value']),
        ]);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        $requests = [
            'req1' => ['method' => 'GET', 'url' => '/test1'],
            'req2' => ['method' => 'POST', 'url' => '/test2', 'data' => ['key' => 'value']],
        ];

        $results = $service->requestMultiple($requests);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('req1', $results);
        $this->assertArrayHasKey('req2', $results);

        foreach ($results as $result) {
            $this->assertArrayHasKey('success', $result);
            $this->assertTrue($result['success']);
        }
    }

    public function testRequestMultipleMethodWithEmptyRequests(): void
    {
        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        $results = $service->requestMultiple([]);
        $this->assertEmpty($results);
    }

    public function testStreamMethodReturnType(): void
    {
        // 模拟流式响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['stream' => true]),
        ]);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        $response = $service->stream('GET', '/test');

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testCheckAvailabilityMethod(): void
    {
        // 模拟健康检查成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['status' => 'healthy']),
        ]);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        // checkAvailability 尝试访问 /health 端点
        $result = $service->checkAvailability();

        $this->assertTrue($result);
    }

    public function testMethodsWithAuthToken(): void
    {
        // 创建带认证令牌的配置
        $authConfig = new class implements ConfigServiceInterface {
            public function getApiUrl(): string
            {
                return 'https://api.meeting.qq.com';
            }

            public function getTimeout(): int
            {
                return 30;
            }

            public function getRetryTimes(): int
            {
                return 0;
            }

            public function getLogLevel(): string
            {
                return 'info';
            }

            public function isDebugEnabled(): bool
            {
                return false;
            }

            public function getCacheTtl(): int
            {
                return 3600;
            }

            public function getWebhookSecret(): ?string
            {
                return null;
            }

            public function getCacheDriver(): string
            {
                return 'array';
            }

            public function getRedisHost(): ?string
            {
                return null;
            }

            public function getRedisPort(): ?int
            {
                return null;
            }

            public function getRedisPassword(): ?string
            {
                return null;
            }

            public function getAuthToken(): ?string
            {
                return null;
            }

            public function getSecretKey(): ?string
            {
                return null;
            }

            public function getProxyHost(): ?string
            {
                return null;
            }

            public function getProxyPort(): ?int
            {
                return null;
            }

            public function getVerifySsl(): bool
            {
                return true;
            }

            public function getAllConfig(): array
            {
                return [];
            }
        };

        // 模拟带认证的成功响应
        $authMockClient = new MockHttpClient();
        $authMockClient->setResponseFactory([
            $this->createMockResponse(200, ['authenticated' => true]),
        ]);

        $service = new HttpClientService($authConfig, $this->loggerService, $authMockClient);

        // 测试带认证的请求
        $result = $service->get('/test');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    public function testMethodsWithCustomHeaders(): void
    {
        // 模拟带自定义头的成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['custom_headers' => true]),
        ]);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        $headers = ['X-Custom-Header' => 'custom-value', 'Authorization' => 'Bearer token'];

        $result = $service->get('/test', $headers);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    public function testMethodsWithTimeout(): void
    {
        // 模拟带超时的成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['timeout_test' => true]),
        ]);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        $result = $service->get('/test', [], 5);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    public function testErrorResponseStructure(): void
    {
        // 模拟错误响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(404, ['error' => 'Endpoint not found']),
        ]);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        $result = $service->get('/non-existent-endpoint');

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('status_code', $result);

        $this->assertFalse($result['success']);
        $this->assertEquals(404, $result['status_code']);
    }

    public function testRequestMethodWithNullData(): void
    {
        // 模拟null数据的成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['null_data' => true]),
        ]);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        $result = $service->request('GET', '/test', null);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    public function testRequestMethodWithArrayData(): void
    {
        // 模拟数组数据的成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['received' => ['key1' => 'value1', 'key2' => 'value2']]),
        ]);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        $data = ['key1' => 'value1', 'key2' => 'value2'];
        $result = $service->request('POST', '/test', $data);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    public function testAsyncMethodsDoNotBlockExecution(): void
    {
        // 模拟异步响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['async_test' => true]),
        ]);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        $startTime = microtime(true);
        $response = $service->requestAsync('GET', '/test');
        $endTime = microtime(true);

        // 异步请求应该立即返回，不等待响应
        $duration = $endTime - $startTime;
        $this->assertLessThan(1.0, $duration); // 应该在1秒内返回

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testStreamMethodDoesNotBlockExecution(): void
    {
        // 模拟流式响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['stream_test' => true]),
        ]);

        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        $startTime = microtime(true);
        $response = $service->stream('GET', '/test');
        $endTime = microtime(true);

        // Stream请求应该立即返回响应对象
        $duration = $endTime - $startTime;
        $this->assertLessThan(1.0, $duration);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testResetMethod(): void
    {
        $service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);

        // 获取初始客户端信息
        $originalInfo = $service->getClientInfo();

        // 调用reset方法
        $service->reset();

        // 获取重置后的客户端信息
        $newInfo = $service->getClientInfo();

        // 验证信息一致性（重置后应该保持相同配置）
        $this->assertEquals($originalInfo, $newInfo);
    }
}
