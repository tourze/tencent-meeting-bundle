<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\TencentMeetingBundle\Service\ConfigServiceInterface;
use Tourze\TencentMeetingBundle\Service\HttpClientService;

/**
 * @internal
 */
#[CoversClass(HttpClientService::class)]
final class HttpClientServiceTest extends TestCase
{
    private HttpClientService $service;

    private ConfigServiceInterface&MockObject $configService;

    private LoggerInterface&MockObject $loggerService;

    private MockHttpClient $mockHttpClient;

    protected function setUp(): void
    {
        // 创建 ConfigService Mock
        $this->configService = $this->createConfigServiceMock();

        // 创建 LoggerInterface Mock
        $this->loggerService = $this->createMock(LoggerInterface::class);

        // 创建Mock HTTP客户端
        $this->mockHttpClient = new MockHttpClient();

        $this->service = new HttpClientService($this->configService, $this->loggerService, $this->mockHttpClient);
    }

    /**
     * 创建 ConfigService Mock 对象
     *
     * @param array<string, mixed> $overrides
     */
    private function createConfigServiceMock(array $overrides = []): ConfigServiceInterface&MockObject
    {
        /** @var array<string, mixed> $defaults */
        $defaults = [
            'getApiUrl' => 'https://api.meeting.qq.com',
            'getTimeout' => 30,
            'getRetryTimes' => 0,
            'getLogLevel' => 'info',
            'isDebugEnabled' => false,
            'getCacheTtl' => 3600,
            'getWebhookSecret' => null,
            'getCacheDriver' => 'array',
            'getRedisHost' => null,
            'getRedisPort' => null,
            'getRedisPassword' => null,
            'getAuthToken' => '',
            'getSecretKey' => null,
            'getProxyHost' => null,
            'getProxyPort' => null,
            'getVerifySsl' => true,
            'getAllConfig' => [],
        ];

        $config = array_merge($defaults, $overrides);
        $mock = $this->createMock(ConfigServiceInterface::class);

        foreach ($config as $method => $returnValue) {
            $mock->method($method)->willReturn($returnValue);
        }

        return $mock;
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

    public function testGetClientInfo(): void
    {
        $info = $this->service->getClientInfo();

        $this->assertArrayHasKey('version', $info);
        $this->assertArrayHasKey('base_uri', $info);
        $this->assertArrayHasKey('timeout', $info);
        $this->assertArrayHasKey('retry_times', $info);
        $this->assertEquals('1.0.0', $info['version']);
        $this->assertEquals('https://api.meeting.qq.com', $info['base_uri']);
        $this->assertEquals(30, $info['timeout']);
        $this->assertEquals(0, $info['retry_times']);
    }

    public function testGetStatsReturnsDefaultStats(): void
    {
        $stats = $this->service->getStats();

        $this->assertArrayHasKey('total_requests', $stats);
        $this->assertArrayHasKey('successful_requests', $stats);
        $this->assertArrayHasKey('failed_requests', $stats);
        $this->assertArrayHasKey('average_response_time', $stats);
        $this->assertArrayHasKey('cache_hits', $stats);
        $this->assertEquals(0, $stats['total_requests']);
        $this->assertEquals(0, $stats['successful_requests']);
    }

    public function testServiceCreationWithRetryConfiguration(): void
    {
        // 创建带重试配置的ConfigService Mock
        $retryConfigService = $this->createConfigServiceMock(['getRetryTimes' => 3]);

        $serviceWithRetry = new HttpClientService($retryConfigService, $this->loggerService);

        $info = $serviceWithRetry->getClientInfo();
        $this->assertEquals(3, $info['retry_times']);
    }

    public function testServiceCreationWithAuthToken(): void
    {
        // 创建带认证令牌的ConfigService Mock
        $authConfigService = $this->createConfigServiceMock(['getAuthToken' => 'test-token']);

        $serviceWithAuth = new HttpClientService($authConfigService, $this->loggerService);

        $info = $serviceWithAuth->getClientInfo();
        $this->assertEquals('https://api.meeting.qq.com', $info['base_uri']);
    }

    public function testResetRecreatesClient(): void
    {
        $originalInfo = $this->service->getClientInfo();

        $this->service->reset();

        $newInfo = $this->service->getClientInfo();
        $this->assertEquals($originalInfo, $newInfo);
    }

    public function testServiceCreationWithDifferentConfiguration(): void
    {
        // 创建具有不同配置的服务
        $customConfigService = $this->createConfigServiceMock([
            'getApiUrl' => 'https://custom.api.com',
            'getTimeout' => 60,
            'getRetryTimes' => 5,
            'getAuthToken' => 'custom-token',
        ]);

        $customService = new HttpClientService($customConfigService, $this->loggerService);

        $info = $customService->getClientInfo();
        $this->assertEquals('https://custom.api.com', $info['base_uri']);
        $this->assertEquals(60, $info['timeout']);
        $this->assertEquals(5, $info['retry_times']);
    }

    public function testMultipleServicesWithDifferentConfigs(): void
    {
        // 测试可以创建多个不同配置的服务
        $config1 = $this->createConfigServiceMock([
            'getApiUrl' => 'https://api1.com',
            'getTimeout' => 10,
            'getRetryTimes' => 1,
        ]);

        $config2 = $this->createConfigServiceMock([
            'getApiUrl' => 'https://api2.com',
            'getTimeout' => 20,
            'getRetryTimes' => 2,
            'getAuthToken' => 'token',
        ]);

        $service1 = new HttpClientService($config1, $this->loggerService);
        $service2 = new HttpClientService($config2, $this->loggerService);

        $info1 = $service1->getClientInfo();
        $info2 = $service2->getClientInfo();

        $this->assertEquals('https://api1.com', $info1['base_uri']);
        $this->assertEquals(10, $info1['timeout']);
        $this->assertEquals(1, $info1['retry_times']);

        $this->assertEquals('https://api2.com', $info2['base_uri']);
        $this->assertEquals(20, $info2['timeout']);
        $this->assertEquals(2, $info2['retry_times']);

        $this->assertNotEquals($info1, $info2);
    }

    public function testCheckAvailabilityMethod(): void
    {
        // 模拟健康检查成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['status' => 'ok']),
        ]);

        $result = $this->service->checkAvailability();

        // checkAvailability returns bool by signature
        $this->assertTrue($result);
    }

    public function testCheckAvailabilityMethodFailure(): void
    {
        // 模拟健康检查失败响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(500, ['error' => 'Service unavailable']),
        ]);

        $result = $this->service->checkAvailability();

        // checkAvailability returns bool by signature
        $this->assertFalse($result);
    }

    public function testDeleteMethod(): void
    {
        // 模拟DELETE成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(204, []),
        ]);

        $url = '/test/delete/endpoint';
        $headers = ['X-Test-Header' => 'test-value'];
        $timeout = 10;

        $result = $this->service->delete($url, $headers, $timeout);

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('status_code', $result);
        $this->assertTrue($result['success']);
        $this->assertEquals(204, $result['status_code']);
    }

    public function testDeleteMethodWithDefaultParameters(): void
    {
        // 模拟DELETE成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['deleted' => true]),
        ]);

        $url = '/test/delete/simple';

        $result = $this->service->delete($url);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status_code']);
    }

    public function testPatchMethod(): void
    {
        // 模拟PATCH成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['updated' => true, 'id' => 123]),
        ]);

        $url = '/test/patch/endpoint';
        $data = ['field' => 'updated_value', 'id' => 123];
        $headers = ['Content-Type' => 'application/json'];
        $timeout = 15;

        $result = $this->service->patch($url, $data, $headers, $timeout);

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('status_code', $result);
        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status_code']);
    }

    public function testPatchMethodWithEmptyData(): void
    {
        // 模拟PATCH成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['patched' => true]),
        ]);

        $url = '/test/patch/empty';

        $result = $this->service->patch($url);

        $this->assertTrue($result['success']);
    }

    public function testPostMethod(): void
    {
        // 模拟POST成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(201, ['created' => true, 'id' => 456]),
        ]);

        $url = '/test/post/endpoint';
        $data = ['name' => 'Test Name', 'email' => 'test@example.com'];
        $headers = ['Authorization' => 'Bearer token'];
        $timeout = 20;

        $result = $this->service->post($url, $data, $headers, $timeout);

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('status_code', $result);
        $this->assertTrue($result['success']);
        $this->assertEquals(201, $result['status_code']);
    }

    public function testPostMethodWithDefaultParameters(): void
    {
        // 模拟POST成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['posted' => true]),
        ]);

        $url = '/test/post/simple';

        $result = $this->service->post($url);

        $this->assertTrue($result['success']);
    }

    public function testPutMethod(): void
    {
        // 模拟PUT成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['updated' => true, 'id' => 1]),
        ]);

        $url = '/test/put/endpoint';
        $data = ['id' => 1, 'name' => 'Updated Name', 'status' => 'active'];
        $headers = ['X-API-Version' => 'v1'];
        $timeout = 25;

        $result = $this->service->put($url, $data, $headers, $timeout);

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('status_code', $result);
        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status_code']);
    }

    public function testPutMethodWithEmptyData(): void
    {
        // 模拟PUT成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['empty_put' => true]),
        ]);

        $url = '/test/put/empty';

        $result = $this->service->put($url, []);

        $this->assertTrue($result['success']);
    }

    public function testRequestMethod(): void
    {
        // 模拟GET成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['message' => 'success', 'param1' => 'value1']),
        ]);

        $method = 'GET';
        $url = '/test/request/endpoint';
        $data = ['param1' => 'value1'];
        $headers = ['User-Agent' => 'Test Client'];
        $timeout = 30;

        $result = $this->service->request($method, $url, $data, $headers, $timeout);

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('status_code', $result);
        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status_code']);
    }

    public function testRequestMethodWithDifferentMethods(): void
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
        $url = '/test/methods';

        // 为每个方法设置成功响应
        $responses = [];
        foreach ($methods as $method) {
            $responses[] = $this->createMockResponse(200, ['method' => $method]);
        }
        $this->mockHttpClient->setResponseFactory($responses);

        foreach ($methods as $method) {
            $result = $this->service->request($method, $url);

            $this->assertArrayHasKey('success', $result);
            $this->assertTrue($result['success']);
        }
    }

    public function testRequestMethodWithNullData(): void
    {
        // 模拟GET成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['null_data_test' => true]),
        ]);

        $method = 'GET';
        $url = '/test/null-data';

        $result = $this->service->request($method, $url, null);

        $this->assertTrue($result['success']);
    }

    public function testRequestAsyncMethod(): void
    {
        // 模拟异步响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['async' => true, 'priority' => 'high']),
        ]);

        $method = 'POST';
        $url = '/test/async/endpoint';
        $data = ['async' => true, 'priority' => 'high'];
        $headers = ['Accept' => 'application/json'];
        $timeout = 10;

        $response = $this->service->requestAsync($method, $url, $data, $headers, $timeout);

        // requestAsync 返回 ResponseInterface 对象
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testRequestAsyncMethodWithMinimalParameters(): void
    {
        // 模拟简单异步响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['simple_async' => true]),
        ]);

        $method = 'GET';
        $url = '/test/async/simple';

        $response = $this->service->requestAsync($method, $url);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testRequestMultipleMethod(): void
    {
        // 为批量请求设置多个成功响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['id' => 1, 'result' => 'first']),
            $this->createMockResponse(201, ['name' => 'second', 'created' => true]),
            $this->createMockResponse(200, ['default' => true]),
        ]);

        $requests = [
            'request1' => [
                'method' => 'GET',
                'url' => '/test/multi/1',
                'data' => ['id' => 1],
                'headers' => ['X-Request' => 'first'],
                'timeout' => 10,
            ],
            'request2' => [
                'method' => 'POST',
                'url' => '/test/multi/2',
                'data' => ['name' => 'second'],
                'headers' => ['X-Request' => 'second'],
            ],
            'request3' => [
                'url' => '/test/multi/3', // 只指定URL，其他使用默认值
            ],
        ];

        $results = $this->service->requestMultiple($requests);

        $this->assertCount(3, $results);
        $this->assertArrayHasKey('request1', $results);
        $this->assertArrayHasKey('request2', $results);
        $this->assertArrayHasKey('request3', $results);

        foreach ($results as $result) {
            $this->assertArrayHasKey('success', $result);
            $this->assertArrayHasKey('status_code', $result);
            $this->assertTrue($result['success']);
        }
    }

    public function testRequestMultipleWithEmptyArray(): void
    {
        $results = $this->service->requestMultiple([]);

        $this->assertEmpty($results);
    }

    public function testStreamMethod(): void
    {
        // 模拟流式响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['stream' => true, 'data' => 'streaming']),
        ]);

        $method = 'GET';
        $url = '/test/stream/endpoint';
        $data = ['stream' => true];
        $headers = ['Accept' => 'text/event-stream'];
        $timeout = 60;

        $response = $this->service->stream($method, $url, $data, $headers, $timeout);

        // stream 方法返回 ResponseInterface 对象
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testStreamMethodWithMinimalParameters(): void
    {
        // 模拟简单流式响应
        $this->mockHttpClient->setResponseFactory([
            $this->createMockResponse(200, ['simple_stream' => true]),
        ]);

        $method = 'GET';
        $url = '/test/stream/simple';

        $response = $this->service->stream($method, $url);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testStreamMethodWithDifferentMethods(): void
    {
        $methods = ['GET', 'POST'];
        $url = '/test/stream/methods';

        // 为每个方法设置流式响应
        $responses = [];
        foreach ($methods as $method) {
            $responses[] = $this->createMockResponse(200, ['method' => $method, 'stream' => true]);
        }
        $this->mockHttpClient->setResponseFactory($responses);

        foreach ($methods as $method) {
            $response = $this->service->stream($method, $url);

            $this->assertInstanceOf(ResponseInterface::class, $response);
        }
    }

    public function testRequestMethodsWithAuthToken(): void
    {
        // 创建带认证令牌的配置
        $authConfigService = $this->createConfigServiceMock(['getAuthToken' => 'test-auth-token']);

        // 创建带认证的Mock客户端
        $authMockClient = new MockHttpClient();
        $authService = new HttpClientService($authConfigService, $this->loggerService, $authMockClient);

        // 为每个方法设置成功响应
        $responses = [
            $this->createMockResponse(200, ['auth_get' => true]),
            $this->createMockResponse(201, ['auth_post' => true]),
            $this->createMockResponse(200, ['auth_put' => true]),
            $this->createMockResponse(200, ['auth_patch' => true]),
            $this->createMockResponse(204, []),
        ];
        $authMockClient->setResponseFactory($responses);

        // 测试各种HTTP方法都能正确处理认证
        $getResult = $authService->get('/test/auth/get');
        $this->assertArrayHasKey('success', $getResult);
        $this->assertTrue($getResult['success']);

        $postResult = $authService->post('/test/auth/post', ['data' => 'test']);
        $this->assertArrayHasKey('success', $postResult);
        $this->assertTrue($postResult['success']);

        $putResult = $authService->put('/test/auth/put', ['data' => 'test']);
        $this->assertArrayHasKey('success', $putResult);
        $this->assertTrue($putResult['success']);

        $patchResult = $authService->patch('/test/auth/patch', ['data' => 'test']);
        $this->assertArrayHasKey('success', $patchResult);
        $this->assertTrue($patchResult['success']);

        $deleteResult = $authService->delete('/test/auth/delete');
        $this->assertArrayHasKey('success', $deleteResult);
        $this->assertTrue($deleteResult['success']);
    }

    public function testErrorHandlingInRequests(): void
    {
        // 创建一个404错误响应的Mock
        $errorResponse = new MockResponse('{"error": "Not Found"}', [
            'http_code' => 404,
            'response_headers' => ['content-type: application/json'],
        ]);

        $errorMockClient = new MockHttpClient([$errorResponse]);
        $service = new HttpClientService($this->configService, $this->loggerService, $errorMockClient);

        $result = $service->get('invalid-url-format');

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('status_code', $result);
        $this->assertFalse($result['success']);
        $this->assertEquals(404, $result['status_code']);
    }
}
